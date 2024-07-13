<?php

namespace IGD;

use Elementor\Controls_Manager;
use Elementor\Plugin;
use Elementor\Widget_Base;
use MetForm\Traits\Common_Controls;
use MetForm\Traits\Conditional_Controls;
use MetForm\Utils\Util;
use MetForm\Widgets\Widget_Notice;

defined( 'ABSPATH' ) || exit();

class Metform extends Widget_Base {
	use Common_Controls;
	use Conditional_Controls;
	use Widget_Notice;

	public function get_name() {
		return 'mf-igd-uploader';
	}

	public function get_title() {
		return __( 'Google Drive Upload', 'integrate-google-drive' );
	}

	public function get_icon() {
		return 'igd-uploader';
	}

	public function show_in_panel() {
		return 'metform-form' == get_post_type();
	}

	public function get_categories() {
		return [ 'metform' ];
	}

	public function get_keywords() {
		return [
			"file uploader",
			"uploader",
			"google drive",
			"drive",
			"module",
			"integrate google drive",
		];
	}

	public function get_script_depends() {
		return [
			'igd-frontend',
		];
	}

	public function get_style_depends() {
		return [
			'igd-frontend',
		];
	}

	public function register_controls() {

		$this->start_controls_section(
			'content_section',
			[
				'label' => esc_html__( 'Content', 'integrate-google-drive' ),
				'tab'   => Controls_Manager::TAB_CONTENT,
			]
		);

		$this->add_control( 'module_data', [
			'label'   => __( 'Module Data', 'integrate-google-drive' ),
			'type'    => Controls_Manager::HIDDEN,
			'default' => '{"isFormUploader":"metform","status":"on","type":"uploader","folders":[],"moduleWidth": "100%","displayFor":"everyone","displayUsers":["everyone"],"displayExcept":[]}',
		] );

		//Edit button
		$this->add_control( 'edit_field_metform', [
			'type'        => Controls_Manager::BUTTON,
			'label'       => '<span class="eicon eicon-settings" style="margin-right: 5px"></span>' . __( 'Configure  Uploader', 'integrate-google-drive' ),
			'text'        => __( 'Configure', 'integrate-google-drive' ),
			'event'       => 'igd:editor:edit_module',
			'description' => __( 'Configure the uploader', 'integrate-google-drive' ),
		] );

		// Label controls
		$this->input_content_controls( [ 'NO_PLACEHOLDER' ] );

		// Required controls
		$this->input_setting_controls();

		$this->add_control(
			'mf_input_validation_type',
			[
				'label'   => __( 'Validation Type', 'integrate-google-drive' ),
				'type'    => Controls_Manager::HIDDEN,
				'default' => 'none',
			]
		);

		$this->add_control(
			'mf_input_file_size_status',
			[
				'label'        => esc_html__( 'File Size Limit : ', 'integrate-google-drive' ),
				'type'         => Controls_Manager::HIDDEN,
				'label_off'    => 'Off',
				'label_on'     => 'On',
				'return_value' => 'off',
			]
		);

		$this->end_controls_section();


		if ( class_exists( '\MetForm_Pro\Base\Package' ) ) {
			$this->input_conditional_control();
		}

		$this->start_controls_section(
			'label_section',
			[
				'label'      => esc_html__( 'Label', 'integrate-google-drive' ),
				'tab'        => Controls_Manager::TAB_STYLE,
				'conditions' => [
					'relation' => 'or',
					'terms'    => [
						[
							'name'     => 'mf_input_label_status',
							'operator' => '===',
							'value'    => 'yes',
						],
						[
							'name'     => 'mf_input_required',
							'operator' => '===',
							'value'    => 'yes',
						],
					],
				],
			]
		);

		$this->input_label_controls( [ 'FILE_SIZE_WARNING' ] );

		$this->end_controls_section();

	}

	public function render() {
		$settings = $this->get_settings_for_display();
		extract( $settings );

		$render_on_editor = false;
		$is_edit_mode     = 'metform-form' === get_post_type() && Plugin::$instance->editor->is_edit_mode();

		$module_data = json_decode( $module_data, true );

		$configData = [
			'message'  => $errorMessage = isset( $mf_input_validation_warning_message ) ? ! empty( $mf_input_validation_warning_message ) ? $mf_input_validation_warning_message : esc_html__( 'This field is required.', 'integrate-google-drive' ) : esc_html__( 'This field is required.', 'integrate-google-drive' ),
			'required' => isset( $mf_input_required ) && $mf_input_required == 'yes',

		];

		?>

        <div class="mf-input-wrapper ${validation.errors['<?php echo esc_attr( $mf_input_name ); ?>'] ? 'has-error' : '' }">

			<?php if ( 'yes' == $mf_input_label_status ) : ?>
            <label class="mf-input-label"
                   for="mf-input-google-drive-upload-<?php echo esc_attr( $this->get_id() ); ?>">
				<?php echo esc_html( Util::react_entity_support( $mf_input_label, $render_on_editor ) ); ?>
                <span class="mf-input-required-indicator"><?php echo esc_html( ( $mf_input_required === 'yes' ) ? '*' : '' ); ?></span>
            </label>
			<?php endif; ?>

			<?php

			echo Shortcode::instance()->render_shortcode( [], $module_data );

			?>

            <input type="text" class="mf-input upload-file-list igd-hidden"
                   id="mf-input-google-drive-upload-<?php echo esc_attr( $this->get_id() ); ?>"
                   name="<?php echo esc_attr( $mf_input_name ); ?>"

				<?php if ( ! $is_edit_mode ): ?>
                   aria-invalid=${validation.errors['<?php echo esc_attr( $mf_input_name ); ?>'] ? 'true' : 'false' }
            ref=${ el => parent.activateValidation(<?php echo json_encode( $configData ); ?>, el) }
			<?php endif; ?>

            />

			<?php if ( ! $is_edit_mode ) : ?>
            <${validation.ErrorMessage}
            errors=${validation.errors}
            name="<?php echo esc_attr( $mf_input_name ); ?>"
            as=${html`<span className="mf-error-message"></span>`}
            />
			<?php endif; ?>

			<?php echo( '' !== trim( $mf_input_help_text ) ? sprintf( '<span class="mf-input-help"> %s </span>', esc_html( Util::react_entity_support( trim( $mf_input_help_text ), $render_on_editor ) ) ) : '' ); ?>
        </div>
		<?php

	}

}
