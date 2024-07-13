<?php

namespace IGD;

defined( 'ABSPATH' ) || exit;

use FluentForm\App\Helpers\Helper;
use FluentForm\App\Services\FormBuilder\BaseFieldManager;
use FluentForm\Framework\Helpers\ArrayHelper;

class FluentForms_Field extends BaseFieldManager {
	public $field_type = 'integrate_google_drive';

	public function __construct() {

		parent::__construct( $this->field_type, __( 'Google Drive Upload', 'integrate-google-drive' ), [
			'cloud',
			'google drive',
			'drive',
			'files',
			'upload',
		], 'general' );

		// Data render
		add_filter( 'fluentform/response_render_' . $this->key, [ $this, 'renderResponse' ], 10, 3 );

		// Validation
		add_filter( 'fluentform/validate_input_item_' . $this->key, [ $this, 'validateInput' ], 10, 5 );

		// After form submission
		add_action( 'fluentform/submission_inserted', [ $this, 'may_create_entry_folder' ], 10, 3 );

	}

	public function may_create_entry_folder( $insertId, $formData, $form ) {
		$igd_fields = [];

		foreach ( $formData as $key => $value ) {
			if ( strpos( $key, 'integrate_google_drive' ) === 0 ) {
				$igd_fields[ $key ] = $value;
			}
		}

		if ( ! empty( $igd_fields ) ) {

			foreach ( $igd_fields as $key => $value ) {

				if ( empty( $value ) ) {
					continue;
				}

				$files = json_decode( $value, true );

				// If no files, skip
				if ( empty( $files ) ) {
					continue;
				}

				$form_fields = json_decode( $form->form_fields, true )['fields'];

				$field = array_filter( $form_fields, function ( $item ) use ( $key ) {
					return $item['attributes']['name'] === $key;
				} );

				$field = array_shift( $field );

				// IGD field config
				$igd_data = json_decode( $field['settings']['igd_data'], true );
				$tag_data = [
					'form' => [
						'form_title' => $form->title,
						'form_id'    => $form->id,
						'entry_id'   => $insertId,
					]
				];

				$upload_folder = ! empty( $igd_data['folders'] ) ? reset( $igd_data['folders'] ) : [
					'id'        => 'root',
					'accountId' => '',
				];

				// Rename files
				$file_name_template = ! empty( $igd_data['uploadFileName'] ) ? $igd_data['uploadFileName'] : '%file_name%%file_extension%';

				// Check if the file name template contains dynamic tags
				if ( igd_contains_tags( 'field', $file_name_template ) ) {

					// Get dynamic tags by filtering the form data
					$extra_tags = $this->handle_form_field_tags( $file_name_template, $formData );

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

				// Create Entry Folder
				$create_entry_folder   = ! empty( $igd_data['createEntryFolders'] );
				$create_private_folder = ! empty( $igd_data['createPrivateFolder'] );

				if ( ! $create_entry_folder && ! $create_private_folder ) {
					continue;
				}

				$entry_folder_name_template = ! empty( $igd_data['entryFolderNameTemplate'] ) ? $igd_data['entryFolderNameTemplate'] : 'Entry (%entry_id%) - %form_title%';

				if ( igd_contains_tags( 'user', $entry_folder_name_template ) ) {
					if ( is_user_logged_in() ) {
						$tag_data['user'] = get_userdata( get_current_user_id() );
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
					$extra_tags = $this->handle_form_field_tags( $entry_folder_name_template, $formData );
				}

				$tag_data['name'] = $entry_folder_name_template;
				$folder_name      = igd_replace_template_tags( $tag_data, $extra_tags );


				// Check Private Folders
				$private_folders = ! empty( $igd_data['privateFolders'] );
				if ( $private_folders && is_user_logged_in() ) {
					$folders = get_user_option( 'folders', get_current_user_id() );

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

	private function handle_form_field_tags( $name_template, $formData ) {
		$extra_tags = [];

		// get %field_{key}% from the file name template
		preg_match_all( '/%field_([^%]+)%/', $name_template, $matches );
		$field_keys = $matches[1];

		if ( ! empty( $field_keys ) ) {
			foreach ( $formData as $field_key => $field_value ) {
				if ( ! in_array( $field_key, $field_keys ) ) {
					continue;
				}

				// Handle array values, such as checkboxes
				if ( is_array( $field_value ) ) {
					$field_value = implode( ', ', $field_value );
				}

				$extra_tags[ '%field_' . $field_key . '%' ] = $field_value;
			}

		}

		return $extra_tags;
	}

	public function getComponent() {
		return [
			'index'          => 99,
			'element'        => $this->key,
			'attributes'     => [
				'name' => $this->key,
				'type' => 'hidden',
			],
			'settings'       => [
				'container_class'    => '',
				'html_codes'         => $this->getUploaderPreview(),
				'label'              => esc_html__( 'Attach your documents', 'integrate-google-drive' ),
				'label_placement'    => 'top',
				'help_message'       => '',
				'igd_data'           => '',
				'admin_field_label'  => '',
				'validation_rules'   => [
					'required' => [
						'value'   => false,
						'message' => esc_html__( 'This field is required', 'integrate-google-drive' ),
					],
				],
				'conditional_logics' => [],
			],
			'editor_options' => [
				'title'      => $this->title,
				'icon_class' => 'ff-edit-files',
				'template'   => 'customHTML',
			],
		];
	}

	public function getGeneralEditorElements() {
		return [
			'label',
			'admin_field_label',
			'value',
			'igd_data',
			'label_placement',
			'validation_rules',
		];
	}

	public function generalEditorElement() {
		return [
			'igd_data' => [
				'template'         => 'inputTextarea',
				'label'            => __( 'Shortcode Data', 'integrate-google-drive' ),
				'help_text'        => __( 'Grab the Shortcode Data via the shortcode builder and copy + paste in this field.', 'integrate-google-drive' ),
				'css_class'        => 'igd-uploader-data',
				'inline_help_text' => sprintf( '<br/><div class="igd-form-uploader-config"><button type="button" class="igd-form-uploader-trigger igd-form-uploader-trigger-fluentforms igd-btn btn-primary"><i class="dashicons dashicons-admin-generic"></i><span>%s</span></button></div><br/>%s', __( 'Configure', 'integrate-google-drive' ), __( 'Configure the uploader field using the module shortcode builder and copy + paste the Shortcode Data in this field.', 'integrate-google-drive' ) ),
				'rows'             => 8,
			],
		];
	}

	public function getAdvancedEditorElements() {
		return [
			'name',
			'help_message',
			'container_class',
			'class',
			'conditional_logics',
		];
	}

	public function getUploaderPreview() {

		$default_data = [
			'type'           => 'uploader',
			'isFormUploader' => 'fluentforms',
		];

		$saved_data = [];

		$data = wp_parse_args( $saved_data, $default_data );


		ob_start();
		?>

        <div class="el-form-item">
            <label class="el-form-item__label"><span><?php echo esc_html__( 'Attach your documents', 'integrate-google-drive' ); ?></span></label>

            <div class="el-form-item__content">
				<?php echo Shortcode::instance()->render_shortcode( [], $data ); ?>
            </div>

        </div>

		<?php
		return ob_get_clean();
	}

	public function render( $element, $form ) {

		$elementName = $element['element'];

		$element = apply_filters( 'fluenform_rendering_field_data_' . $elementName, $element, $form );

		$default_data = [
			'id'             => $this->key,
			'type'           => 'uploader',
			'isFormUploader' => 'fluentforms',
			'isRequired'     => ! empty( $element['settings']['validation_rules']['required']['value'] ),
		];

		$saved_data     = json_decode( $element['settings']['igd_data'], true );
		$shortcode_data = wp_parse_args( $saved_data, $default_data );

		if ( $this->is_multi_step_form( $form ) ) {
			$shortcode_data['uploadImmediately'] = true;
		}

		$shortcode_render = Shortcode::instance()->render_shortcode( [], $shortcode_data );

		$field_id = $this->makeElementId( $element, $form ) . '_' . Helper::$formInstance;
		$prefill  = ( isset( $_REQUEST[ $field_id ] ) ? stripslashes( $_REQUEST[ $field_id ] ) : '' );

		$element['attributes']['type']  = 'text';
		$element['attributes']['id']    = $field_id;
		$element['attributes']['class'] = 'upload-file-list igd-hidden';
		$element['attributes']['value'] = $prefill;

		$elMarkup = "%s <input %s>";
		$elMarkup = sprintf( $elMarkup, $shortcode_render, $this->buildAttributes( $element['attributes'], $form ) );
		$html     = $this->buildElementMarkup( $elMarkup, $element, $form );

		$this->printContent( 'fluentform_rendering_field_html_' . $elementName, $html, $element, $form );

	}

	public function is_multi_step_form( $form ) {

		if ( ! empty( $form_fields = $form->fields['fields'] ) ) {
			foreach ( $form_fields as $field ) {
				if ( $field['element'] == 'form_step' ) {
					return true;
				}
			}
		}


		return false;
	}

	public function renderResponse( $response, $field, $form_id ) {
		return apply_filters( 'igd_render_form_field_data', $response, true, $this );
	}

	public function validateInput( $errors, $field, $formData, $fields, $form ) {
		$fieldName = $field['name'];

		$value = $formData[ $fieldName ]; // This is the user input value

		$uploaded_files = json_decode( $value, true );

		$is_required = ! empty( $field['rules']['required']['value'] );
		if ( $is_required && ( empty( $uploaded_files ) || ( 0 === count( (array) $uploaded_files ) ) ) ) {
			return [ ArrayHelper::get( $field, 'raw.settings.validation_rules.required.message' ) ];
		}

		// Validate minFiles
		$igd_data = json_decode( $field['raw']['settings']['igd_data'], true );
		$minFiles = ArrayHelper::get( $igd_data, 'minFiles' );

		if ( ! empty( $minFiles ) && ( count( (array) $uploaded_files ) < $minFiles ) ) {
			return [ sprintf( __( 'Please upload at least %d files.', 'integrate-google-drive' ), $minFiles ) ];
		}

		return $errors;
	}

}

new FluentForms_Field();