<?php

namespace IGD;

defined('ABSPATH') || exit();

class Update_1_1_1 {
	private static $instance;

	public function __construct() {
		$this->update_settings();
	}

	public function update_settings() {
		$settings = get_option('igd_settings');

		if(igd_fs()->can_use_premium_code__premium_only()){
			$settings['integrations'] = [ 'classic-editor', 'gutenberg-editor', 'elementor', 'woocommerce', 'edd', 'cf7', 'wpforms', 'gravityforms' ];
		}else {
			$settings['integrations'] = [ 'classic-editor', 'gutenberg-editor', 'elementor' ];
		}

		update_option('igd_settings', $settings);
	}


	public static function instance() {
		if ( null == self::$instance ) {
			self::$instance = new self;
		}

		return self::$instance;
	}

}

Update_1_1_1::instance();