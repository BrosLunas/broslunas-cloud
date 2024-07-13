<?php

namespace IGD;

defined( 'ABSPATH' ) || exit();

class Update_1_1_80 {
	private static $instance;

	public function __construct() {
		if ( ! empty( get_option( 'igd_accounts' ) ) ) {
			update_option( 'igd_account_notice', true );
			update_option( 'igd_accounts', [] );
		}

		set_transient( 'igd_rating_notice_interval', 'off', 7 * DAY_IN_SECONDS );

		$this->add_table_col();
	}

	public function add_table_col() {
		global $wpdb;

		$table_name = $wpdb->prefix . 'integrate_google_drive_files';

		//if not exists column is_shared_drive then add
		$sql    = "SHOW COLUMNS FROM $table_name LIKE 'is_shared_drive'";
		$column = $wpdb->get_results( $sql );

		if ( empty( $column ) ) {
			$sql = "ALTER TABLE $table_name ADD `is_shared_drive` tinyint(1) DEFAULT 0;";
			$wpdb->query( $sql );
		}

	}

	public static function instance() {
		if ( null == self::$instance ) {
			self::$instance = new self;
		}

		return self::$instance;
	}

}

Update_1_1_80::instance();