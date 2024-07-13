<?php

namespace IGD;

defined( 'ABSPATH' ) || exit();

class Update_1_1_96 {
	private static $instance;

	public function __construct() {

		//add new column to files table
		$this->add_table_col();

		// Convert user_id property to array
		if ( ! empty( get_option( 'igd_accounts', [] ) ) ) {
			$this->update_accounts();
		}

		// delete all cache files and folders
		igd_delete_cache();

		// update preloader settings
		$this->update_settings();

	}

	public function update_settings() {
		$settings = get_option( 'igd_settings' );

		if ( !empty($settings['preloader']) && 'skeleton' === $settings['preloader'] ) {
			$settings['preloader'] = 'default';
			update_option( 'igd_settings', $settings );
		}

	}

	public function add_table_col() {
		global $wpdb;

		$table_name = $wpdb->prefix . 'integrate_google_drive_files';

		$sql = "ALTER TABLE $table_name
ADD `size` bigint NULL AFTER `name`,
ADD `created` text NULL AFTER `data`,
ADD `updated` text NULL AFTER `created`;";

		$wpdb->query( $sql );
	}

	public function update_accounts() {
		$accounts = array_filter( get_option( 'igd_accounts', [] ) );

		if ( ! empty( $accounts ) ) {
			$accounts = array_map( function ( $account ) {
				if ( empty( $account['user_id'] ) ) {
					$account['user_id'] = [ 'admin' ];
				} else {
					$account['user_id'] = [ $account['user_id'] ];
				}

				return $account;
			}, $accounts );

			update_option( 'igd_accounts', $accounts );
		}
	}

	public static function instance() {
		if ( null == self::$instance ) {
			self::$instance = new self;
		}

		return self::$instance;
	}

}

Update_1_1_96::instance();