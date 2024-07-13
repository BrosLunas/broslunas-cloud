<?php

namespace IGD;

defined( 'ABSPATH' ) || exit();

class Update_1_1_99 {
	private static $instance;

	public function __construct() {
		// delete all cache files and folders
		igd_delete_cache();
	}

	public static function instance() {
		if ( null == self::$instance ) {
			self::$instance = new self;
		}

		return self::$instance;
	}

}

Update_1_1_99::instance();