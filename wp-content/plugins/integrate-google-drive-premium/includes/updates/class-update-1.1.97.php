<?php

namespace IGD;

defined( 'ABSPATH' ) || exit();

class Update_1_1_97 {
	private static $instance;

	public function __construct() {

		// Add extension column to the files table
		$this->add_table_col();

		// Remove recent column from the files table
		$this->remove_recent_col();

		// Migrate exclude settings to filters
		$this->migrate_shortcode_filters();

		// delete all cache files and folders
		igd_delete_cache();
	}

	public function add_table_col() {
		global $wpdb;

		$table_name = $wpdb->prefix . 'integrate_google_drive_files';

		$sql = "ALTER TABLE $table_name ADD `extension` VARCHAR(255) NULL AFTER `type`;";

		$wpdb->query( $sql );
	}

	public function remove_recent_col() {
		global $wpdb;

		$table_name = $wpdb->prefix . 'integrate_google_drive_files';

		$sql = "ALTER TABLE $table_name DROP `is_recent`;";

		$wpdb->query( $sql );
	}

	public function migrate_shortcode_filters() {
		$shortcodes = Shortcode_Builder::instance()->get_shortcodes();

		if ( ! empty( $shortcodes ) ) {
			foreach ( $shortcodes as $shortcode ) {
				$id     = $shortcode->id;
				$config = unserialize( $shortcode->config );
				$type   = $config['type'];

				if ( in_array( $type, [ 'downloadLink', 'viewLink' ] ) ) {
					continue;
				}

				// Migrate the excludes config to filters config
				$allowExtensions       = '';
				$allowAllExtensions    = true;
				$allowExceptExtensions = '';
				$allowNames            = '';
				$allowAllNames         = true;
				$allowExceptNames      = '';

				$should_migrate = false;
				if (
					( ! empty( $config['excludeExtensions'] ) && empty( $config['excludeAllExtensions'] ) )
					|| ( ! empty( $config['excludeExceptExtensions'] ) && ! empty( $config['excludeAllExtensions'] ) )
					|| ( ! empty( $config['excludeNames'] ) && empty( $config['excludeAllNames'] ) )
					|| ( ! empty( $config['excludeExceptNames'] ) && ! empty( $config['excludeAllNames'] ) )
				) {
					$should_migrate = true;
				}

				if ( ! $should_migrate ) {
					continue;
				}

				if ( ! empty( $config['excludeAllExtensions'] ) && ! empty( $config['excludeExceptExtensions'] ) ) {
					$allowAllExtensions = false;
					$allowExtensions    = $config['excludeExceptExtensions'];
				} else if ( ! empty( $config['excludeExtensions'] ) ) {
					$allowExceptExtensions = $config['excludeExtensions'];
				}

				if ( ! empty( $config['excludeAllNames'] ) && ! empty( $config['excludeExceptNames'] ) ) {
					$allowAllNames = false;
					$allowNames    = $config['excludeExceptNames'];
				} else if ( ! empty( $config['excludeNames'] ) ) {
					$allowExceptNames = $config['excludeNames'];
				}

// We may need this later if the migration doesn't work
//				unset( $config['excludeExtensions'] );
//				unset( $config['excludeAllExtensions'] );
//				unset( $config['excludeExceptExtensions'] );
//				unset( $config['excludeNames'] );
//				unset( $config['excludeAllNames'] );
//				unset( $config['excludeExceptNames'] );

				$config['allowExtensions']       = $allowExtensions;
				$config['allowAllExtensions']    = $allowAllExtensions;
				$config['allowExceptExtensions'] = $allowExceptExtensions;
				$config['allowNames']            = $allowNames;
				$config['allowAllNames']         = $allowAllNames;
				$config['allowExceptNames']      = $allowExceptNames;

				global $wpdb;
				$table_name = $wpdb->prefix . 'integrate_google_drive_shortcodes';

				$wpdb->update(
					$table_name,
					[
						'config' => serialize( $config ),
					],
					[
						'id' => $id,
					]
				);
			}
		}
	}

	public static function instance() {
		if ( null == self::$instance ) {
			self::$instance = new self;
		}

		return self::$instance;
	}

}

Update_1_1_97::instance();