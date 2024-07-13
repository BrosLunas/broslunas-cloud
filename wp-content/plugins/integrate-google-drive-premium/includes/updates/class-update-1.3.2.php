<?php

namespace IGD;

defined('ABSPATH') || exit();

class Update_1_3_2 {
	private static $instance;

	public function __construct() {
		$this->update_table_col();
	}

	public function update_table_col() {
		global $wpdb;

		$table_name = $wpdb->prefix . 'integrate_google_drive_files';

		$sql = "ALTER TABLE $table_name CHANGE `id` `id` VARCHAR(255) NOT NULL;";
		$wpdb->query( $sql );
	}

	public static function instance() {
		if ( null == self::$instance ) {
			self::$instance = new self;
		}

		return self::$instance;
	}

}

Update_1_3_2::instance();