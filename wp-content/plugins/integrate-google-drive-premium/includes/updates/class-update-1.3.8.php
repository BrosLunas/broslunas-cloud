<?php

namespace IGD;

defined( 'ABSPATH' ) || exit();

class Update_1_3_8 {
	private static $instance;

	public function __construct() {
		igd_delete_cache();

		$this->delete_cache_folder();
	}

	public function delete_cache_folder() {
		$dir_path = WP_CONTENT_DIR . '/integrate-google-drive-cache';

		// Check if the directory exists
		if ( ! file_exists( $dir_path ) ) {
			return; // The directory does not exist, so exit the function
		}

		// Recursive function to delete directory contents
		$delete_contents = function ( $path ) use ( &$delete_contents ) {
			// Open the directory
			$dir = opendir( $path );

			while ( false !== ( $file = readdir( $dir ) ) ) {
				if ( $file != '.' && $file != '..' ) {
					// Generate the absolute path
					$fullPath = $path . '/' . $file;

					// If the file is a directory, recurse into it; otherwise, delete the file
					if ( is_dir( $fullPath ) ) {
						$delete_contents( $fullPath );
						rmdir( $fullPath ); // Delete the now-empty directory
					} else {
						unlink( $fullPath ); // Delete the file
					}
				}
			}

			// Close the directory
			closedir( $dir );
		};

		// Delete the directory contents
		$delete_contents( $dir_path );

		// Finally, delete the top-level directory itself
		rmdir( $dir_path );
	}


	public static function instance() {
		if ( null == self::$instance ) {
			self::$instance = new self;
		}

		return self::$instance;
	}

}

Update_1_3_8::instance();