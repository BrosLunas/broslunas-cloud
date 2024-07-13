<?php


namespace IGD;

defined( 'ABSPATH' ) || exit;

class FormidableForms {
	public $field_type = 'integrate-google-drive';
	public $default_value = '';

	public function __construct() {
		$this->add_hooks();
	}

	public function add_hooks() {
		// Add Form button to Form Builder
		add_filter( 'frm_available_fields', [ $this, 'add_field' ] );

		// Set Field default values
		add_filter( 'frm_before_field_created', [ $this, 'add_field_defaults' ] );

		// Add extra options to the field option box
		add_action( 'frm_field_options_form', [ $this, 'field_options_form' ], 10, 3 );

		// Save the extra added options
		add_filter( 'frm_update_field_options', [ $this, 'update_field_options' ], 10, 3 );

		// The render in the Form Builder
		add_action( 'frm_display_added_fields', [ $this, 'admin_field' ] );
		add_action( 'frm_enqueue_builder_scripts', [ $this, 'enqueue' ] );

		// The Front-End render
		add_action( 'frm_form_fields', [ $this, 'frontend_field' ], 10, 3 );
		add_action( 'frm_entries_footer_scripts', [ $this, 'enqueue_for_ajax' ], 20, 2 );

		// Validate the field
		add_filter( 'frm_validate_' . $this->field_type . '_field_entry', [ $this, 'validation' ], 9, 4 );

		// Store Submission value
		add_filter( 'frm_pre_create_entry', [ $this, 'save_value' ] );

		// Field Submission value render
		add_filter( 'frm_display_' . $this->field_type . '_value_custom', [ $this, 'render_value_custom' ], 15, 2 );
		add_filter( 'frm_display_value', [ $this, 'render_value' ], 15, 3 );
		add_filter( 'frm_graph_value', [ $this, 'graph_value' ], 10, 2 );

		// XML / CSV export value
		add_filter( 'frm_csv_value', [ $this, 'csv_value' ], 10, 2 );

		// After form submission
		add_action( 'frm_after_create_entry', [ $this, 'may_create_entry_folder' ], 10, 2 );
	}

	public function may_create_entry_folder( $entry_id, $form_id ) {
		$form  = \FrmForm::getOne( $form_id );
		$entry = \FrmEntry::getOne( $entry_id, true );

		//get form field settings
		$form_fields = \FrmField::get_all_for_form( $form_id );

		$igd_fields = [];

		foreach ( $form_fields as $field ) {
			if ( $field->type == 'integrate-google-drive' ) {
				$igd_fields[] = $field;
			}
		}

		if ( ! empty( $igd_fields ) ) {

			foreach ( $igd_fields as $field ) {
				$value = $entry->metas[ $field->id ];

				if ( empty( $value ) ) {
					continue;
				}

				$files = json_decode( str_replace( 'integrate-google-drive-', '', $value ), true );
				if ( empty( $files ) ) {
					continue;
				}

				$igd_data = json_decode( $field->field_options['igd_data'], true );

				$tag_data = [
					'form' => [
						'form_title' => $form->name,
						'form_id'    => $form_id,
						'entry_id'   => $entry_id,
					]
				];

				$upload_folder = ! empty( reset( $igd_data['folders'] ) ) ? reset( $igd_data['folders'] ) : [
					'id'        => 'root',
					'accountId' => '',
				];


				// Rename files
				$file_name_template = ! empty( $igd_data['uploadFileName'] ) ? $igd_data['uploadFileName'] : '%file_name%%file_extension%';

				// Check if the file name template contains dynamic tags
				if ( igd_contains_tags( 'field_id', $file_name_template ) ) {

					// Get dynamic tags by filtering the form data
					$extra_tags = $this->handle_form_field_tags( $file_name_template, $form_fields, $entry );

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

					if ( ! empty( $rename_files ) ) {
						App::instance( $upload_folder['accountId'] )->rename_files( $rename_files );
					}

				}

				// Maybe create entry folder
				$create_entry_folder   = ! empty( $igd_data['createEntryFolders'] );
				$create_private_folder = ! empty( $igd_data['createPrivateFolder'] );

				if ( ! $create_entry_folder && ! $create_private_folder ) {
					continue;
				}

				$entry_folder_name_template = ! empty( $igd_data['entryFolderNameTemplate'] ) ? $igd_data['entryFolderNameTemplate'] : 'Entry (%entry_id%) - %form_title%';

				if ( igd_contains_tags( 'user', $entry_folder_name_template ) ) {
					if ( ! empty( $entry->user_id ) ) {
						$tag_data['user'] = get_userdata( $entry->user_id );;
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
					$extra_tags = $this->handle_form_field_tags( $entry_folder_name_template, $form_fields, $entry);
				}

				$tag_data['name'] = $entry_folder_name_template;
				$folder_name = igd_replace_template_tags( $tag_data, $extra_tags );

				// Check Private Folders
				$private_folders = ! empty( $igd_data['privateFolders'] );
				if ( $private_folders && ! empty( $entry->user_id ) ) {
					$folders = get_user_option( 'folders', $entry->user_id );

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

				$field_value = $entry->metas[ $tagField->id ];

				// Handle array values, such as checkboxes
				if ( is_array( $field_value ) ) {
					$field_value = implode( ', ', $field_value );
				}

				$extra_tags[ '%field_' . $field_id . '%' ] = $field_value;
			}

		}

		return $extra_tags;
	}

	public function add_field( $fields ) {
		$fields[ $this->field_type ] = [
			'name' => __( 'Google Drive', 'integrate-google-drive' ),
			'icon' => 'frm_icon_font frm_upload_icon',
		];

		return $fields;
	}

	public function add_field_defaults( $field_data ) {
		if ( $this->field_type == $field_data['type'] ) {
			$field_data['name']  = esc_html__( 'Attach your documents', 'integrate-google-drive' );
			$field_data['blank'] = esc_html__( 'No file selected', 'integrate-google-drive' );

			$defaults = [
				'igd_data' => $this->default_value,
			];

			foreach ( $defaults as $k => $v ) {
				$field_data['field_options'][ $k ] = $v;
			}
		}

		return $field_data;
	}

	public function field_options_form( $field, $display, $values ) {
		if ( $this->field_type != $field['type'] ) {
			return;
		}

		if ( ! isset( $field['igd_data'] ) ) {
			$field['igd_data'] = $this->default_value;
		} ?>

        <tr>
            <td><label><?php esc_html_e( 'Configure Uploader', 'integrate-google-drive' ); ?></label></td>
            <td>
                <input type="hidden" id="igd_data_<?php echo esc_attr( $field['id'] ); ?>"
                       name="field_options[igd_data_<?php echo esc_attr( $field['id'] ); ?>]"
                       class="frm_long_input igd-uploader-data" value="<?php echo esc_attr( $field['igd_data'] ); ?>"/>

                <div class="igd-form-uploader-config">
                    <button type="button"
                            class="igd-form-uploader-trigger igd-form-uploader-trigger-formidableforms igd-btn btn-primary">
                        <i class="dashicons dashicons-admin-generic"></i>
                        <span><?php esc_html_e( 'Configure', 'integrate-google-drive' ); ?></span>
                    </button>
                </div>

            </td>
        </tr>
		<?php
	}

	public function admin_field( $field ) {
		if ( $this->field_type != $field['type'] ) {
			return;
		}

		$this->enqueue();

		$default_data = [
			'id'             => $field['id'],
			'type'           => 'uploader',
			'isFormUploader' => 'formidableforms',
			'isRequired'     => false,
		];

		$saved_data = json_decode( $field['igd_data'], true );

		$data = wp_parse_args( $saved_data, $default_data );

		?>

        <div class="frm_html_field_placeholder">
			<?php echo Shortcode::instance()->render_shortcode( [], $data ); ?>
        </div>
		<?php
	}

	public function frontend_field( $field, $field_name, $atts ) {

		if ( $this->field_type != $field['type'] ) {
			return;
		}

		$field_id = $field['id'];
		$prefill  = ( isset( $_REQUEST['item_meta'][ $field_id ] ) ? stripslashes( $_REQUEST['item_meta'][ $field_id ] ) : '' );

		$default_data = [
			'id'             => $field['id'],
			'type'           => 'uploader',
			'isFormUploader' => 'formidableforms',
			'isRequired'     => false,
			'uploadedFiles'  => ! empty( $prefill ) ? json_decode( $prefill, true ) : [],
		];

		$saved_data = json_decode( $field['igd_data'], true );

		$data = wp_parse_args( $saved_data, $default_data );

		if ( $this->is_multi_step_form( $field ) ) {
			$data['uploadImmediately'] = true;
		}

		echo Shortcode::instance()->render_shortcode( [], $data );

		echo sprintf( "<input type='text' class='hidden igd-hidden upload-file-list' name='%s' id='%s' value='%s'>", $field_name, $atts['html_id'], $prefill );
	}

	public function is_multi_step_form( $field ) {

		if ( ! class_exists( 'FrmProPageField' ) ) {
			return false;
		}

		$form           = \FrmForm::getOne( $field['form_id'] );
		$has_page_break = \FrmProPageField::get_form_pages( $form );

		return ! empty( $has_page_break );
	}

	public function update_field_options( $field_options, $field, $values ) {
		if ( $this->field_type != $field->type ) {
			return $field_options;
		}

		$defaults = [
			'igd_data' => $this->default_value,
		];

		foreach ( $defaults as $opt => $default ) {
			$field_options[ $opt ] = isset( $values['field_options'][ $opt . '_' . $field->id ] ) ? $values['field_options'][ $opt . '_' . $field->id ] : $default;
		}

		return $field_options;
	}

	public function validation( $errors, $posted_field, $posted_value, $args ) {
		$uploaded_files = json_decode( $posted_value );

		if ( ( empty( $uploaded_files ) || ( 0 === count( (array) $uploaded_files ) ) ) && ! empty( $posted_field->required ) ) {
			$errors[ 'field' . $posted_field->id ] = $posted_field->field_options['blank'];
		}

		$igd_data  = json_decode( $posted_field->field_options['igd_data'], true );
		$min_files = isset( $igd_data['minFiles'] ) ? $igd_data['minFiles'] : 0;

		if ( ! empty( $min_files ) && count( (array) $uploaded_files ) < $min_files ) {
			/* translators: %d: minimum number of files */
			$errors[ 'field' . $posted_field->id ] = sprintf( __( 'Please upload at least %d file(s).', 'integrate-google-drive' ), $min_files );
		}

		return $errors;
	}

	public function render_value_custom( $value, $args ) {
		if ( $this->field_type != $args['field']->type ) {
			return $value;
		}

		// Hack to let Formidable Form think that the value is altered and while frm_display_value() will still be called with the original value
		return $value . ' ';
	}

	public function render_value( $value, $field, $atts ) {
		if ( $this->field_type != $field->type ) {
			return $value;
		}

		$value = trim( str_ireplace( $this->field_type . '-', '', $value ) );

		$as_html = true;
		if ( isset( $atts['plain_text'] ) ) {
			$as_html = ! $atts['plain_text'];
		}

		if ( isset( $atts['html'] ) ) {
			$as_html = ! $atts['html'];
		}

		if ( isset( $atts['entry_id'] ) && ( empty( $value ) || ( isset( $atts['truncate'] ) && true === $atts['truncate'] ) ) ) {
			$data  = \FrmEntry::getOne( $atts['entry_id'], true );
			$value = $data->metas[ $field->id ];

			// Value can be different depending on FF addons installed
			if ( false === is_array( $value ) ) {
				$value = trim( str_ireplace( $this->field_type . '-', '', $value ) );
			} else {
				$value = json_encode( $value );
			}
		}

		return $this->render_value_as_text( $value, $as_html );
	}

	public function render_value_as_text( $json_data, $ashtml = true ) {

		return apply_filters( 'igd_render_form_field_data', $json_data, $ashtml, $this );
	}

	public function save_value( $values ) {
		foreach ( $values['item_meta'] as $field_id => $value ) {
			$field = \FrmField::getOne( $field_id );

			if ( empty( $field ) ) {
				continue;
			}

			if ( $this->field_type != $field->type ) {
				continue;
			}

			if ( '{}' === $value ) {
				unset( $values['item_meta'][ $field_id ] );
			} else {
				$values['item_meta'][ $field_id ] = $this->field_type . '-' . $value;
			}
		}

		return $values;
	}

	public function graph_value( $value, $field ) {
		if ( ! is_object( $field ) || $this->field_type != $field->type ) {
			return $value;
		}

		$value = trim( str_ireplace( $this->field_type . '-', '', $value ) );

		$data = json_decode( $value, true );

		if ( ( null === $data ) || ( 0 === count( (array) $data ) ) ) {
			return $value;
		}

		return 'Uploads: ' . count( $data );
	}

	public function csv_value( $value, $atts ) {
		if ( $this->field_type != $atts['field']->type ) {
			return $value;
		}

		// Value can be different depending on FF addons installed
		if ( false === is_array( $value ) ) {
			$value = trim( str_ireplace( $this->field_type . '-', '', $value ) );
			$data  = json_decode( $value, true );
		} else {
			$data = $value;
		}

		if ( ( null === $data ) || ( 0 === count( (array) $data ) ) ) {
			return $value;
		}

		$return = '';
		foreach ( $data as $fileid => $file ) {
			$return .= urldecode( $file['link'] ) . "\n";
		}

		return $return;
	}

	public function enqueue() {
		$action          = \FrmAppHelper::simple_get( 'frm_action', 'sanitize_title' );
		$is_builder_page = \FrmAppHelper::is_admin_page( 'formidable' ) && ( 'edit' === $action || 'duplicate' === $action );

		if ( ! $is_builder_page ) {
			return;
		}

		Enqueue::instance()->admin_scripts( '', false );
	}

	public function enqueue_for_ajax( $fields, $form ) {
		$form_is_using_ajax = ( null !== $form && '1' === $form->options['ajax_load'] );
		$form_has_fields    = \FrmField::get_all_types_in_form( $form->id, $this->field_type );

		if ( false === $form_is_using_ajax || 0 === count( $form_has_fields ) ) {
			return;
		}

		foreach ( $form_has_fields as $field ) {
			// Process shortcodes to load required styles and scripts, but don't echo the output itself
			$default_data = [
				'id'             => $field->id,
				'type'           => 'uploader',
				'isFormUploader' => 'formidableforms',
				'isRequired'     => false,
			];

			$saved_data = json_decode( $field->field_options['igd_data'], true );

			$data = wp_parse_args( $saved_data, $default_data );

			Shortcode::instance()->render_shortcode( [], $data );
		}
	}
}

new FormidableForms();
