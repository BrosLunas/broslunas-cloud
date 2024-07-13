<?php

namespace IGD;

defined('ABSPATH') || exit();

class Update_1_0_5 {
	private static $instance;

	public function __construct() {
		$this->remove_old_cron();
		$this->add_table_col();
	}

	public function remove_old_cron() {
		$timestamp = wp_next_scheduled( 'igd_reset_cache' );
		if ( $timestamp ) {
			wp_unschedule_event( $timestamp, 'igd_reset_cache' );
		}
	}

	public function add_table_col() {
		global $wpdb;

		$table_name = $wpdb->prefix . 'integrate_google_drive_files';

		$sql = "ALTER TABLE $table_name ADD `is_recent` INT(4) DEFAULT 0 AFTER `is_starred`;";
		$wpdb->query( $sql );
	}

	public static function instance() {
		if ( null == self::$instance ) {
			self::$instance = new self;
		}

		return self::$instance;
	}

}

Update_1_0_5::instance();