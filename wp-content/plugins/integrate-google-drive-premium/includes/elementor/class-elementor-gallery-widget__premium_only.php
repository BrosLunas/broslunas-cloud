<?php

namespace IGD;

use Elementor\Controls_Manager;
use Elementor\Plugin;
use Elementor\Widget_Base;
use IGD\Shortcode;

defined( 'ABSPATH' ) || exit();

class Gallery_Widget extends Widget_Base {

	public function get_name() {
		return 'igd_gallery';
	}

	public function get_title() {
		return __( 'Gallery', 'integrate-google-drive' );
	}

	public function get_icon() {
		return 'igd-gallery';
	}


	public function get_categories() {
		return [ 'integrate_google_drive' ];
	}

	public function get_keywords() {
		return [
			"gallery",
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

		$this->start_controls_section( '_section_module_builder',
			[
				'label' => __( 'Gallery Module', 'integrate-google-drive' ),
				'tab'   => Controls_Manager::TAB_CONTENT,
			] );

		$this->add_control( 'module_data', [
			'label'       => __( 'Module Data', 'integrate-google-drive' ),
			'type'        => Controls_Manager::HIDDEN,
			'default'     => '{"isInit":true,"status":"on","type":"gallery","folders":[],"showFiles":true,"showFolders":true,"moduleWidth": "100%", "moduleHeight": "auto","fileNumbers":1000,"sort":{"sortBy":"name","sortDirection":"asc"},"view":"list","maxFileSize":"","preview":"true","download":true,"displayFor":"everyone","displayUsers":["everyone"],"displayExcept":[]}',
		] );

		//Edit button
		$this->add_control( 'edit_module', [
			'type'        => Controls_Manager::BUTTON,
			'label'       => '<span class="eicon eicon-settings" style="margin-right: 5px"></span>' . __( 'Configure  Module', 'integrate-google-drive' ),
			'text'        => __( 'Configure', 'integrate-google-drive' ),
			'event'       => 'igd:editor:edit_module',
			'description' => __( 'Configure the module first to display the content', 'integrate-google-drive' ),
		] );

		$this->end_controls_section();
	}

	public function render() {
		$settings = $this->get_settings_for_display();

		$settings_data = json_decode( $settings['module_data'], true );

		$is_init = ! empty( $settings_data['isInit'] );

		if ( $is_init && Plugin::$instance->editor->is_edit_mode() ) { ?>
            <div class="module-builder-placeholder">

                <img src="<?php echo IGD_ASSETS . '/images/shortcode-builder/types/gallery.svg' ?>">
                <h3><?php _e( 'Photo Gallery', 'integrate-google-drive' ); ?></h3>
                <p><?php esc_html_e( 'Please, configure the module first to display the content', 'integrate-google-drive' ); ?></p>

                <button type="button" class="igd-btn btn-primary"
                        onclick="setTimeout(() => {window.parent.jQuery(`[data-event='igd:editor:edit_module']`).trigger('click')}, 100)"
                >
                    <i class="dashicons dashicons-admin-generic"></i>
                    <span><?php esc_html_e( 'Configure Module', 'integrate-google-drive' ); ?></span>
                </button>
            </div>
		<?php } else {
			echo Shortcode::instance()->render_shortcode( [], $settings_data );
		}
	}

}