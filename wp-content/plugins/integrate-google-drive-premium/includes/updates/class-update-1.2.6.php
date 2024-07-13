<?php

namespace IGD;

defined('ABSPATH') || exit();

class Update_1_2_6 {
	private static $instance;

	public function __construct() {
		$this->update_table_col();
	}

	public function update_table_col() {
		global $wpdb;

		$table_name = $wpdb->prefix . 'integrate_google_drive_logs';

		$sql = "ALTER TABLE $table_name CHANGE `file_type` `file_type` text NULL AFTER `file_id`;";
		$wpdb->query( $sql );
	}

	public static function instance() {
		if ( null == self::$instance ) {
			self::$instance = new self;
		}

		return self::$instance;
	}

}

Update_1_2_6::instance();