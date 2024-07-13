<?php

namespace IGD;

defined( 'ABSPATH' ) || exit();


class Shortcode_Builder {
	/**
	 * @var null
	 */
	protected static $instance = null;

	public function __construct() {
	}

	public function get_shortcode( $id ) {
		global $wpdb;

		$table = $wpdb->prefix . 'integrate_google_drive_shortcodes';

		return $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $table WHERE id=%d", $id ) );
	}

	public function get_shortcodes( $args = [] ) {
		$offset   = ! empty( $args['offset'] ) ? intval( $args['offset'] ) : 0;
		$limit    = ! empty( $args['limit'] ) ? intval( $args['limit'] ) : 999;
		$order_by = ! empty( $args['order_by'] ) ? sanitize_key( $args['order_by'] ) : 'created_at';
		$order    = ! empty( $args['order'] ) ? sanitize_key( $args['order'] ) : 'DESC';

		global $wpdb;

		$table = $wpdb->prefix . 'integrate_google_drive_shortcodes';

		return $wpdb->get_results( "SELECT * FROM $table ORDER BY $order_by $order LIMIT $offset, $limit" );
	}

	public function get_shortcodes_count() {
		global $wpdb;

		$table = $wpdb->prefix . 'integrate_google_drive_shortcodes';

		return $wpdb->get_var( "SELECT COUNT(*) FROM $table" );
	}

	public function update_shortcode( $posted, $force_insert = false ) {
		global $wpdb;

		$table  = $wpdb->prefix . 'integrate_google_drive_shortcodes';
		$id     = ! empty( $posted['id'] ) ? intval( $posted['id'] ) : '';
		$status = ! empty( $posted['status'] ) ? sanitize_key( $posted['status'] ) : 'on';
		$title  = ! empty( $posted['title'] ) ? sanitize_text_field( $posted['title'] ) : '';

		$data = [
			'title'  => $title,
			'status' => $status,
			'config' => ! empty( $posted['config'] ) ? $posted['config'] : serialize( $posted ),
		];


		$data_format = [ '%s', '%s', '%s' ];

		if ( ! empty( $posted['created_at'] ) ) {
			$data['created_at'] = $posted['created_at'];
			$data_format[]      = '%s';
		}

		if ( ! empty( $posted['updated_at'] ) ) {
			$data['updated_at'] = $posted['updated_at'];
			$data_format[]      = '%s';
		}

		if ( ! $id || $force_insert ) {
			$wpdb->insert( $table, $data, $data_format );

			return $wpdb->insert_id;
		} else {
			$wpdb->update( $table, $data, [ 'id' => $id ], $data_format, [ '%d' ] );

			return $id;
		}

	}

	public function duplicate_shortcode( $id ) {
		if ( empty( $id ) ) {
			return false;
		}

		$shortcode = $this->get_shortcode( $id );
		if ( $shortcode ) {
			$shortcode               = (array) $shortcode;
			$shortcode['title']      = 'Copy of ' . $shortcode['title'];
			$shortcode['created_at'] = current_time( 'mysql' );
			$shortcode['updated_at'] = current_time( 'mysql' );
			$shortcode['locations']  = serialize( [] );
			$insert_id               = $this->update_shortcode( $shortcode, true );

			$data = array_merge( $shortcode, [
				'id'        => $insert_id,
				'config'    => unserialize( $shortcode['config'] ),
				'locations' => [],
			] );

			return $data;
		}

		return false;
	}

	public function delete_shortcode( $id = false ) {
		global $wpdb;
		$table = $wpdb->prefix . 'integrate_google_drive_shortcodes';

		if ( $id ) {
			$wpdb->delete( $table, [ 'id' => $id ], [ '%d' ] );
		} else {
			$wpdb->query( "TRUNCATE TABLE $table" );
		}

	}

	public static function view() { ?>
        <div id="igd-shortcode-builder"></div>
	<?php }

	/**
	 * @return Shortcode_Builder|null
	 */
	public static function instance() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

}

Shortcode_Builder::instance();