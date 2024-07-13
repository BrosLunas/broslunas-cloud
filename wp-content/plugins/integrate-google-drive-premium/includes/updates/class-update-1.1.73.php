<?php

namespace IGD;

defined( 'ABSPATH' ) || exit();

class Update_1_1_73 {
	private static $instance;

	public function __construct() {
		$this->add_table_col();
		$this->update_shortcode_types();
	}

	public function add_table_col() {
		global $wpdb;

		$table_name = $wpdb->prefix . 'integrate_google_drive_files';

		$sql = "ALTER TABLE $table_name ADD `is_shared_drive` tinyint(1) NULL;";
		$wpdb->query( $sql );
	}

	public function update_shortcode_types() {
		$shortcodes = Shortcode_Builder::instance()->get_shortcodes();

		if ( ! empty( $shortcodes ) ) {
			foreach ( $shortcodes as $shortcode ) {
				$id     = $shortcode->id;
				$config = unserialize( $shortcode->config );
				$type   = $config['type'];

				if ( ! in_array( $type, [ 'audioVideo', 'downloadLink', 'viewLink' ] ) ) {
					continue;
				}

				if ( 'audioVideo' === $type ) {
					$type = 'media';
				} elseif ( 'downloadLink' === $type ) {
					$type = 'download';
				} elseif ( 'viewLink' === $type ) {
					$type = 'view';
				}

				$config['type'] = $type;

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

Update_1_1_73::instance();