<?php

namespace IGD;

defined( 'ABSPATH' ) || exit;

/**
 * Class Install
 */
class Install {

	/**
	 * Plugin activation stuffs
	 *
	 * @since 1.0.0
	 */
	public static function activate() {
		if ( ! class_exists( 'IGD\Update' ) ) {
			require_once IGD_INCLUDES . '/class-update.php';
		}

		$updater = new Update();

		if ( $updater->needs_update() ) {
			$updater->perform_updates();
		} else {
			self::create_default_data();
			self::add_settings();
			self::create_tables();
		}

	}

	public static function add_settings() {

		// Don't need to add settings if not first time install
		$version = get_option( 'igd_version' );
		if ( $version ) {
			return;
		}

		$settings = igd_get_settings();

		$integrations = [ 'classic-editor', 'gutenberg-editor', 'elementor', 'divi' ];
		if ( igd_fs()->can_use_premium_code__premium_only() ) {
			$integrations = array_merge( $integrations, [
				'acf',
				'woocommerce',
				'dokan',
				'edd',
				'tutor',
				'wpforms',
				'gravityforms',
				'ninjaforms',
				'formidableforms',
			] );
		}

		$settings['integrations'] = $integrations;

		update_option( 'igd_settings', $integrations );
	}

	public static function deactivate() {
		self::remove_cron_event();
	}


	public static function remove_cron_event() {

		$hooks = [
			'igd_sync_interval',
			'igd_statistics_daily_report',
			'igd_statistics_weekly_report',
			'igd_statistics_monthly_report',
		];

		foreach ( $hooks as $hook ) {
			$timestamp = wp_next_scheduled( $hook );
			if ( $timestamp ) {
				wp_unschedule_event( $timestamp, $hook );
			}
		}

	}

	public static function create_tables() {
		global $wpdb;

		$wpdb->hide_errors();

		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

		$tables = [
			// files table
			"CREATE TABLE IF NOT EXISTS {$wpdb->prefix}integrate_google_drive_files( id VARCHAR(60) NOT NULL, `name` TEXT NULL, `size` BIGINT NULL, `parent_id` TEXT, `account_id` TEXT NOT NULL, `type` VARCHAR(255) NOT NULL, `extension` VARCHAR(10) NOT NULL, `data` LONGTEXT, is_computers TINYINT(1) DEFAULT 0, is_shared_with_me TINYINT(1) DEFAULT 0, is_starred TINYINT(1) DEFAULT 0, is_shared_drive TINYINT(1) DEFAULT 0, `created` TEXT NULL, `updated` TEXT NULL, PRIMARY KEY (id)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;",

			// shortcodes table
			"CREATE TABLE IF NOT EXISTS {$wpdb->prefix}integrate_google_drive_shortcodes( id BIGINT(20) NOT NULL AUTO_INCREMENT, title VARCHAR(255) NULL, status VARCHAR(6) NULL DEFAULT 'on', config LONGTEXT NULL, locations LONGTEXT NULL, created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP, updated_at TIMESTAMP NULL, PRIMARY KEY (id)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;",

			// logs table
			"CREATE TABLE IF NOT EXISTS {$wpdb->prefix}integrate_google_drive_logs( id INT NOT NULL AUTO_INCREMENT, `type` VARCHAR(255) NULL, `user_id` INT NULL, file_id TEXT NOT NULL, file_type text NULL, file_name TEXT NULL, account_id TEXT NOT NULL, created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP, PRIMARY KEY (id)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;",
		];


		foreach ( $tables as $table ) {
			dbDelta( $table );
		}

	}

	/**
	 * Create plugin settings default data
	 *
	 * @since 1.0.0
	 */
	private static function create_default_data() {

		$version      = get_option( 'igd_version' );
		$install_time = get_option( 'igd_install_time', '' );

		if ( empty( $version ) ) {
			update_option( 'igd_version', IGD_VERSION );
		}

		if (  empty( $install_time ) ) {
			$date_format = get_option( 'date_format' );
			$time_format = get_option( 'time_format' );
			update_option( 'igd_install_time', date( $date_format . ' ' . $time_format ) );
		}

		set_transient( 'igd_rating_notice_interval', 'off', 10 * DAY_IN_SECONDS );

	}

}