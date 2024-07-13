<?php

namespace IGD;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

\GFForms::include_addon_framework();

class GravityForms extends \GFAddOn {

	protected $_version = '2.0';
	protected $_min_gravityforms_version = '2.5';
	protected $_slug = 'integrate_google_drive';
	protected $_path = IGD_INCLUDES . '/integrations/class-gravityforms.php';
	protected $_full_path = __FILE__;
	protected $_title = 'Google Drive Add-On for GravityForms';
	protected $_short_title = 'Google Drive Add-On';

	public function init() {
		parent::init();

		if ( ! $this->is_gravityforms_supported( $this->_min_gravityforms_version ) ) {
			return;
		}

		// Add default value for field
		add_action( 'gform_editor_js_set_default_values', [ $this, 'field_defaults' ] );

		// Add a custom setting to the field
		add_action( 'gform_field_standard_settings', [ $this, 'custom_field_settings' ], 10, 2 );

		// Filter to add the tooltip for the field
		add_filter( 'gform_tooltips', [ $this, 'add_tooltip' ] );

		// Add support for wpDataTables <> Gravity Form integration
		if ( class_exists( 'WPDataTable' ) ) {
			add_action( 'wpdatatables_before_get_table_metadata', [ $this, 'render_wpdatatables_field' ], 10, 1 );
		}

		// Deprecated hooks, but still in use by e.g. GravityView + GravityFlow?
		add_filter( 'gform_entry_field_value', [ $this, 'filter_entry_field_value' ], 10, 4 );

		\GF_Fields::register( new GravityForms_Field() );

		//Preview scripts
		add_action( 'gform_preview_header', function () {
			Enqueue::instance()->frontend_scripts();
		} );

		// Maybe create entry folder
		add_action( 'gform_after_submission', [ $this, 'may_create_entry_folder' ], 10, 2 );
	}

	public function may_create_entry_folder( $entry, $form ) {
		$igd_fields = [];

		foreach ( $form['fields'] as $field ) {
			if ( $field->type == 'integrate_google_drive' ) {
				$igd_fields[] = $field;
			}
		}

		if ( ! empty( $igd_fields ) ) {
			foreach ( $igd_fields as $field ) {
				$value = $entry[ $field->id ];

				if ( empty( $value ) ) {
					continue;
				}

				$files = json_decode( $value, true );

				if ( empty( $files ) ) {
					continue;
				}

				$igd_data = json_decode( $field->igdData, true );

				$tag_data = [
					'form' => [
						'form_title' => $form['title'],
						'form_id'    => $form['id'],
						'entry_id'   => $entry['id'],
					]
				];

				$upload_folder = ! empty( reset( $igd_data['folders'] ) ) ? reset( $igd_data['folders'] ) : [
					'id'        => 'root',
					'accountId' => '',
				];

				// Rename files
				$file_name_template = ! empty( $igd_data['uploadFileName'] ) ? $igd_data['uploadFileName'] : '%file_name%%file_extension%';

				// Check if the file name template contains dynamic tags
				if ( igd_contains_tags( 'field', $file_name_template ) ) {

					// Get dynamic tags by filtering the form data
					$extra_tags = $this->handle_form_field_tags( $file_name_template, $form['fields'], $entry );

					$rename_files = [];
					foreach ( $files as $file ) {
						// We will rename the file name
						$tag_data['name'] = $file['name'];

						$name = igd_replace_template_tags( $tag_data, $extra_tags );

						$rename_files[] = [
							'id'   => $file['id'],
							'name' => $name,
						];
					}

					// Rename files
					if ( ! empty( $rename_files ) ) {
						App::instance( $upload_folder['accountId'] )->rename_files( $rename_files );
					}

				}

				// Check and create entry folder
				$create_entry_folder   = ! empty( $igd_data['createEntryFolders'] );
				$create_private_folder = ! empty( $igd_data['createPrivateFolder'] );

				if ( ! $create_entry_folder && ! $create_private_folder ) {
					continue;
				}

				$entry_folder_name_template = ! empty( $igd_data['entryFolderNameTemplate'] ) ? $igd_data['entryFolderNameTemplate'] : 'Entry (%entry_id%) - %form_title%';

				if ( igd_contains_tags( 'user', $entry_folder_name_template ) ) {
					if ( $entry['created_by'] ) {
						$tag_data['user'] = get_userdata( $entry['created_by'] );
					}
				}

				if ( igd_contains_tags( 'post', $entry_folder_name_template ) ) {
					$referrer = wp_get_referer();

					if ( ! empty( $referrer ) ) {
						$post_id = url_to_postid( $referrer );
						if ( ! empty( $post_id ) ) {
							$tag_data['post'] = get_post( $post_id );
							if ( $tag_data['post']->post_type == 'product' ) {
								$tag_data['wc_product'] = wc_get_product( $post_id );
							}
						}
					}
				}

				// Dynamic tags
				$extra_tags = [];
				if ( igd_contains_tags( 'field', $entry_folder_name_template ) ) {
					$extra_tags = $this->handle_form_field_tags( $entry_folder_name_template, $form['fields'], $entry );
				}

				$tag_data['name'] = $entry_folder_name_template;
				$folder_name      = igd_replace_template_tags( $tag_data, $extra_tags );

				// Check Private Folders
				$private_folders = ! empty( $igd_data['privateFolders'] );
				if ( $private_folders && ! empty( $entry['created_by'] ) ) {
					$folders = get_user_option( 'folders', $entry['created_by'] );

					if ( ! empty( $folders ) ) {
						$folders = array_values( array_filter( (array) $folders, function ( $item ) {
							return igd_is_dir( $item );
						} ) );
					} elseif ( $create_private_folder ) {
						$folders = Private_Folders::instance()->create_user_folder( get_current_user_id(), $igd_data );
					}

					if ( ! empty( $folders ) ) {
						$igd_data['folders'] = $folders;
					}

				}

				$merge_folders = isset( $igd_data['mergeFolders'] ) ? filter_var( $igd_data['mergeFolders'], FILTER_VALIDATE_BOOLEAN ) : false;

				Uploader::instance( $upload_folder['accountId'] )->create_entry_folder_and_move( $files, $folder_name, $upload_folder, $merge_folders, $create_entry_folder );
			}
		}
	}

	private function handle_form_field_tags( $name_template, $form_fields, $entry ) {
		$extra_tags = [];

		// get %field_{key}% from the file name template
		preg_match_all( '/%field_id_([^%]+)%/', $name_template, $matches );
		$field_ids = $matches[1];

		if ( ! empty( $field_ids ) ) {
			foreach ( $form_fields as $tagField ) {
				$field_id = $tagField->id;

				if ( ! in_array( $field_id, $field_ids ) ) {
					continue;
				}

				$field_value = $entry[ $tagField->id ];

				// Handle array values, such as checkboxes
				if ( is_array( $field_value ) ) {
					$field_value = implode( ', ', $field_value );
				}

				$extra_tags[ '%field_id_' . $field_id . '%' ] = $field_value;
			}

		}

		return $extra_tags;
	}

	public function custom_field_settings( $position, $form_id ) {
		if ( 1430 == $position ) { ?>
            <li class="igd_settings field_setting">
                <label><?php esc_html_e( 'Configure', 'integrate-google-drive' ); ?><?php echo gform_tooltip( 'form_field_' . $this->_slug ); ?></label>
                <input type="hidden" class="igd-uploader-data" onchange="SetFieldProperty('igdData', this.value)"/>

                <button id="igd-form-uploader-config-gravityforms" type="button"
                        class="igd-form-uploader-trigger igd-form-uploader-trigger-gravityforms igd-btn btn-primary">
                    <i class="dashicons dashicons-admin-generic"></i>
                    <span><?php esc_html_e( 'Configure Uploader', 'integrate-google-drive' ); ?></span>
                </button>

            </li>
			<?php
		}
	}

	public function add_tooltip( $tooltips ) {
		$tooltips[ 'form_field_' . $this->_slug ] = esc_html__( 'Configure the uploader field.', 'integrate-google-drive' );

		return $tooltips;
	}

	public function field_defaults() {
		?>
        case 'integrate_google_drive':
        field.label = <?php echo json_encode( esc_html__( 'Attach your documents', 'integrate-google-drive' ) ); ?>;
        break;
		<?php
	}

	public function render_wpdatatables_field( $tableId ) {
		add_filter( 'gform_get_input_value', [ $this, 'entry_field_value' ], 10, 4 );
	}

	public function filter_entry_field_value( $value, $field, $entry, $form ) {
		return $this->entry_field_value( $value, $entry, $field, null );
	}

	public function entry_field_value( $value, $entry, $field, $input_id ) {
		if ( 'integrate_google_drive' !== $field->type ) {
			return $value;
		}

		return apply_filters( 'igd_render_form_field_data', html_entity_decode( $value ), true, $this );
	}
}

class GravityForms_Field extends \GF_Field {
	public $type = 'integrate_google_drive';
	public $defaultValue = '';

	public function get_form_editor_field_title() {
		return __( 'Google Drive', 'integrate-google-drive' );
	}

	public function add_button( $field_groups ) {
		$field_groups = $this->maybe_add_field_group( $field_groups );

		return parent::add_button( $field_groups );
	}

	public function maybe_add_field_group( $field_groups ) {
		foreach ( $field_groups as $field_group ) {
			if ( 'igd_group' == $field_group['name'] ) {
				return $field_groups;
			}
		}

		$field_groups[] = [
			'name'   => 'igd_group',
			'label'  => __( 'Integrate Google Drive Fields', 'integrate-google-drive' ),
			'fields' => [],
		];

		return $field_groups;
	}

	public function get_form_editor_button() {
		return [
			'group' => 'igd_group',
			'text'  => $this->get_form_editor_field_title(),
		];
	}

	public function get_form_editor_field_icon() {
		return 'gform-icon--upload';
	}

	public function get_form_editor_field_description() {
		return esc_attr__( 'Let users attach files to this form. The files will be stored in the Google Drive', 'integrate-google-drive' );
	}

	public function get_form_editor_field_settings() {
		return [
			'conditional_logic_field_setting',
			'error_message_setting',
			'label_setting',
			'label_placement_setting',
			'admin_label_setting',
			'rules_setting',
			'visibility_setting',
			'duplicate_setting',
			'description_setting',
			'css_class_setting',
			'igd_settings',
		];
	}

	public function get_value_default() {
		return $this->is_form_editor() ? $this->defaultValue : \GFCommon::replace_variables_prepopulate( $this->defaultValue );
	}

	public function is_conditional_logic_supported() {
		return false;
	}

	public function get_field_input( $form, $value = '', $entry = null ) {
		$form_id         = $form['id'];
		$is_entry_detail = $this->is_entry_detail();
		$id              = $this->id;

		if ( $is_entry_detail ) {
			$input = "<input type='hidden' id='input_{$id}' name='input_{$id}' value='{$value}' />";

			return $input . '<br/>' . esc_html__( 'This field is not editable', 'integrate-google-drive' );
		}

		$default_data = [
			'id'             => $id,
			'type'           => 'uploader',
			'isFormUploader' => 'gravityforms',
			'isRequired'     => ! empty( $this->isRequired ),
			'uploadedFiles'  => ! empty( $value ) ? json_decode( $value, 1 ) : [],
		];

		$saved_data = json_decode( $this->igdData, 1 );

		$data = wp_parse_args( $saved_data, $default_data );

		// Check if form is multipage form
		if ( $this->is_multi_page_form( $form ) ) {
			$data['uploadImmediately'] = true;
		}

		$input = Shortcode::instance()->render_shortcode( '', $data );

		$input .= "<input type='text' name='input_" . $id . "' id='input_" . $form_id . '_' . $id . "'  class='upload-file-list igd-hidden' value='" . $value . "'>";

		return $input;
	}

	public function is_multi_page_form( $form ) {
		$is_multi_page = false;
		foreach ( $form['fields'] as $field ) {
			if ( $field->type == 'page' ) {
				$is_multi_page = true;
				break;
			}
		}

		return $is_multi_page;
	}

	public function validate( $value, $form ) {

		// Get information uploaded files from hidden input
		$attached_files = json_decode( $value );

		if ( empty( $attached_files ) && $this->isRequired ) {
			$this->failed_validation = true;

			if ( ! empty( $this->errorMessage ) ) {
				$this->validation_message = $this->errorMessage;
			} else {
				$this->validation_message = esc_html__( 'This field is required. Please upload your files.', 'integrate-google-drive' );
			}
		}

		//Check minimum files
		$igd_data = json_decode( $this->igdData, true );

		if ( ! empty( $igd_data['minFiles'] ) && ( empty( $attached_files ) || count( $attached_files ) < $igd_data['minFiles'] ) ) {
			$this->failed_validation = true;

			if ( ! empty( $this->errorMessage ) ) {
				$this->validation_message = $this->errorMessage;
			} else {
				$this->validation_message = sprintf( esc_html__( 'Please upload at least %d files.', 'integrate-google-drive' ), $igd_data['minFiles'] );
			}
		}

	}

	public function get_value_merge_tag( $value, $input_id, $entry, $form, $modifier, $raw_value, $url_encode, $esc_html, $format, $nl2br ) {
		return $this->renderUploadedFiles( html_entity_decode( $value ), ( 'html' === $format ) );
	}

	public function get_value_entry_detail( $value, $currency = '', $use_text = false, $format = 'html', $media = 'screen' ) {
		return $this->renderUploadedFiles( html_entity_decode( $value ), ( 'html' === $format ) );
	}

	public function get_value_entry_list( $value, $entry, $field_id, $columns, $form ) {
		if ( ! empty( $value ) ) {
			return $this->renderUploadedFiles( html_entity_decode( $value ) );
		}
	}

	public function get_value_export( $entry, $input_id = '', $use_text = false, $is_csv = false ) {
		$value = rgar( $entry, $input_id );

		return $this->renderUploadedFiles( html_entity_decode( $value ), false );
	}

	public function renderUploadedFiles( $data, $as_html = true ) {
		return apply_filters( 'igd_render_form_field_data', $data, $as_html, $this );
	}

	public function get_field_container_tag( $form ) {
		if ( \GFCommon::is_legacy_markup_enabled( $form ) ) {
			return parent::get_field_container_tag( $form );
		}

		return 'fieldset';
	}
}

new GravityForms();
