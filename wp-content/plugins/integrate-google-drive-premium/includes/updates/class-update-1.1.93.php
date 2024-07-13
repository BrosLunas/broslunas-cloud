<?php

namespace IGD;

defined( 'ABSPATH' ) || exit();

class Update_1_1_93 {
	private static $instance;

	public function __construct() {
		$this->add_table_col();
		$this->update_shortcode_locations();
	}

	public function add_table_col() {
		global $wpdb;

		$table_name = $wpdb->prefix . 'integrate_google_drive_shortcodes';

		$sql = "ALTER TABLE $table_name ADD `locations` LONGTEXT NULL AFTER `title`;";
		$wpdb->query( $sql );
	}

	public function update_shortcode_locations() {
		$pages = get_pages( [
			'number' => 999,
		] );

		$locator = new Shortcode_Locations();

		foreach ( $pages as $page ) {
			$shortcode_ids = $locator->get_shortcode_ids( $page->post_content );
			$locator->update_shortcode_locations( $page, [], $shortcode_ids );
		}

	}

	public static function instance() {
		if ( null == self::$instance ) {
			self::$instance = new self;
		}

		return self::$instance;
	}

}

Update_1_1_93::instance();