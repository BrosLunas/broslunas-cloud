<?php

namespace IGD;

use Elementor\Controls_Manager;
use ElementorPro\Modules\Forms\Classes;
use ElementorPro\Modules\Forms\Fields\Field_Base;
use ElementorPro\Plugin;

defined( 'ABSPATH' ) || exit;

class Google_Drive_Upload extends Field_Base {
	public $depended_scripts = [
		'igd-frontend',
	];

	public $depended_styles = [
		'igd-frontend',
	];

	public function get_type() {
		return 'google_drive_upload';
	}

	public function get_name() {
		return esc_html__( 'Google Drive Upload', 'integrate-google-drive' );
	}

	public function add_field_type( $field_types ) {
		$field_types[ $this->get_type() ] = $this->get_name();

		return $field_types;
	}

	public function update_controls( $widget ) {
		$elementor = Plugin::elementor();

		$control_data = $elementor->controls_manager->get_control_from_stack( $widget->get_unique_name(), 'form_fields' );

		if ( is_wp_error( $control_data ) ) {
			return;
		}

		$field_controls = [
			'module_data' => [
				'name'         => 'module_data',
				'label'        => __( 'Module Data', 'integrate-google-drive' ),
				'type'         => Controls_Manager::HIDDEN,
				'tab'          => 'content',
				'inner_tab'    => 'form_fields_content_tab',
				'tabs_wrapper' => 'form_fields_tabs',
				'condition'    => [
					'field_type' => $this->get_type(),
				],
			],

			'edit_field_form' => [
				'name'         => 'edit_field_form',
				'type'         => Controls_Manager::BUTTON,
				'label'        => '<span class="eicon eicon-settings" style="margin-right: 5px"></span>' . __( 'Configure  Uploader', 'integrate-google-drive' ),
				'text'         => __( 'Configure', 'integrate-google-drive' ),
				'event'        => 'igd:editor:edit_module',
				'description'  => __( 'Configure the uploader', 'integrate-google-drive' ),
				'tab'          => 'content',
				'inner_tab'    => 'form_fields_content_tab',
				'tabs_wrapper' => 'form_fields_tabs',
				'condition'    => [
					'field_type' => $this->get_type(),
				],
			],
		];

		$control_data['fields'] = $this->inject_field_controls( $control_data['fields'], $field_controls );
		$widget->update_control( 'form_fields', $control_data );
	}

	public function render( $item, $item_index, $form ) {
		$data = json_decode( $item['module_data'], true );

		$data['type']           = 'uploader';
		$data['isFormUploader'] = 'elementor';

		if ( ! empty( $item['required'] ) ) {
			$data['isRequired'] = true;
		}

		// Check if multi-step form
		if ( $this->is_multi_step_form( $form ) ) {
			$data['uploadImmediately'] = true;
		}

		echo Shortcode::instance()->render_shortcode( [], $data );

		$form->add_render_attribute( 'input' . $item_index, 'class', 'elementor-field-textual  upload-file-list' );

		?>
        <input type="text" <?php $form->print_render_attribute_string( 'input' . $item_index ); ?> autocomplete="off"/>
		<?php

	}

	/**
	 * Check if multi-step form
	 *
	 * @param $form
	 *
	 * @return bool
	 */
	public function is_multi_step_form( $form ) {

		if ( ! empty( $form_fields = $form->get_settings( 'form_fields' ) ) ) {
			foreach ( $form_fields as $field ) {
				if ( $field['field_type'] == 'step' ) {
					return true;
				}
			}
		}


		return false;

	}

	public function validation( $field, Classes\Form_Record $record, Classes\Ajax_Handler $ajax_handler ) {
		// Get the module data
		$form_fields = $record->get_form_settings( 'form_fields' );

		// Search for the field data where the custom_id matches the field id
		$field_data_key = array_search( $field['id'], array_column( $form_fields, 'custom_id' ) );
		if ( $field_data_key === false ) {
			return; // Exit early if field not found
		}

		$field_data = $form_fields[ $field_data_key ];

		// Parse and retrieve minimum file uploads setting, if any
		$igd_data         = json_decode( $field_data['module_data'], true );
		$min_file_uploads = isset( $igd_data['minFiles'] ) ? $igd_data['minFiles'] : 0;

		// Validate file uploads only if minimum file uploads is more than 0
		if ( $min_file_uploads > 0 ) {
			$files = explode( ' ),', $field['value'] );


			// If $files is not an array or if it contains fewer items than required
			if ( ! is_array( $files ) || count( $files ) < $min_file_uploads ) {
				/* translators: %d: minimum file uploads */
				$ajax_handler->add_error( $field['id'], sprintf( __( 'Please upload at least %d file(s)', 'integrate-google-drive' ), $min_file_uploads ) );
			}
		}
	}


}

