<?php

namespace IGD;

defined( 'ABSPATH' ) || exit();


class TinyMCE {
	/**
	 * @var null
	 */
	protected static $instance = null;

	public function __construct() {
		add_filter( 'mce_buttons', [ $this, 'add_buttons' ] );
		add_filter( 'mce_external_plugins', [ $this, 'add_plugins' ] );
		add_filter( 'mce_css', [ $this, 'enqueue_css' ] );

		//add media button
		add_action( 'media_buttons', [ $this, 'add_media_button' ], 20 );
	}

	public function add_media_button() {
		if ( ! function_exists( 'get_current_screen' ) ) {
			return;
		}

		$screen = get_current_screen();
		if ( ! empty( $screen ) && $screen->base !== 'post' ) {
			return;
		}

		printf(
			'<a href="#" class="button" id="igd-media-button" title="%1$s"><img width="20" src="%2$s/images/drive.png" /> %1$s</a>',
			__( 'Google Drive', 'integrate-google-drive' ),
			IGD_ASSETS
		);

	}

	public function add_buttons( $buttons ) {
		$buttons[] = 'integrate_google_drive';

		return $buttons;
	}

	public function add_plugins( $plugins ) {
		Enqueue::instance()->admin_scripts( '', false );

		$plugins['igd_tinymce_js'] = IGD_ASSETS . '/js/admin.js';

		return $plugins;
	}

	public function enqueue_css( $mce_css ) {
		if ( ! empty( $mce_css ) ) {
			$mce_css .= ',';
		}

		$mce_css .= IGD_ASSETS . '/css/tinymce.css';

		return $mce_css;
	}

	/**
	 * @return TinyMCE|null
	 */
	public static function instance() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

}

TinyMCE::instance();