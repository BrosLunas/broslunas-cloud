<?php

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'ET_Builder_Element' ) ) {
	return;
}

$module_files = glob( __DIR__ . '/modules/*/*.php' );

$is_pro = igd_fs()->can_use_premium_code__premium_only();

// Load custom Divi Builder modules
foreach ( (array) $module_files as $module_file ) {
	if ( $module_file && preg_match( "/\/modules\/\b([^\/]+)\/\\1\.php$/", $module_file ) ) {

		if(!$is_pro){
			$pro_modules = [
				'Browser',
				'Gallery',
				'Uploader',
				'Media',
				'Search',
				'Slider',
			];

			$module_name = basename($module_file, '.php');

			if(in_array($module_name, $pro_modules)){
				continue;
			}
		}

		require_once $module_file;
	}
}
