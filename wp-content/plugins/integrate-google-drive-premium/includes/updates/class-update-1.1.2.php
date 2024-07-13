<?php

namespace IGD;

defined( 'ABSPATH' ) || exit();

class Update_1_1_2 {
	private static $instance;

	public function __construct() {
		$this->create_table();
	}

	/**
	 * Create the statistics table
	 * @return void
	 */
	public function create_table() {
		global $wpdb;

		$wpdb->hide_errors();

		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

		dbDelta( "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}integrate_google_drive_logs(
			id INT NOT NULL AUTO_INCREMENT,
			`type` varchar(255) NULL,
			`user_id` INT NULL,
    		file_id TEXT NOT NULL,
    		file_type varchar(255) NULL,
			file_name TEXT NULL,
    		account_id TEXT NOT NULL,
    		created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
			PRIMARY KEY  (id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8;" );

	}


	public static function instance() {
		if ( null == self::$instance ) {
			self::$instance = new self;
		}

		return self::$instance;
	}

}

Update_1_1_2::instance();