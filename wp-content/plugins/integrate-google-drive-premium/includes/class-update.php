<?php

namespace IGD;

defined( 'ABSPATH' ) || exit();

class Update {

	private static $instance = null;

	/**
	 * The upgrades
	 *
	 * @var array
	 */
	private static $upgrades = array(
		'1.0.5',
		'1.0.9',
		'1.1.1',
		'1.1.2',
		'1.1.73',
		'1.1.80',
		'1.1.93',
		'1.1.96',
		'1.1.97',
		'1.1.99',
		'1.2.6',
		'1.3.2',
		'1.3.8',
	);

	public function installed_version() {

		return get_option( 'igd_version' );
	}

	/**
	 * Check if the plugin needs any update
	 *
	 * @return boolean
	 */
	public function needs_update() {

		// maybe it's the first install
		if ( empty( $this->installed_version() ) ) {
			return false;
		}

		//if previous version is lower
		if ( version_compare( $this->installed_version(), IGD_VERSION, '<' ) ) {
			return true;
		}


		return false;
	}

	/**
	 * Perform all the necessary upgrade routines
	 *
	 * @return void
	 */
	public function perform_updates() {

		foreach ( self::$upgrades as $version ) {

			if ( version_compare( $this->installed_version(), $version, '<' ) ) {
				$file = IGD_INCLUDES . "/updates/class-update-$version.php";

				if ( file_exists( $file ) ) {
					include_once $file;
				}

				update_option( 'igd_version', $version );
			}
		}

		delete_option( 'igd_version' );
		update_option( 'igd_version', IGD_VERSION );
	}

	public static function instance() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

}
