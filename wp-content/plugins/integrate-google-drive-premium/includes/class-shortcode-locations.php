<?php

namespace IGD;

class Shortcode_Locations {

	private static $instance = null;

	public function __construct() {
		// Monitoring hooks.
		add_action( 'save_post', [ $this, 'save_post' ], 10, 3 );
		add_action( 'post_updated', [ $this, 'post_updated' ], 10, 3 );
		add_action( 'wp_trash_post', [ $this, 'trash_post' ] );
		add_action( 'untrash_post', [ $this, 'untrash_post' ] );
		add_action( 'delete_post', [ $this, 'trash_post' ] );
	}

	public function save_post( $post_ID, $post, $update ) {

		if (
			$update ||
			! in_array( $post->post_type, $this->get_post_types(), true ) ||
			! in_array( $post->post_status, $this->get_post_statuses(), true )
		) {
			return;
		}

		$shortcode_ids = $this->get_shortcode_ids( $post->post_content );

		$this->update_shortcode_locations( $post, [], $shortcode_ids );
	}

	public function post_updated( $post_id, $post_after, $post_before ) {

		if (
			! in_array( $post_after->post_type, $this->get_post_types(), true ) ||
			! in_array( $post_after->post_status, $this->get_post_statuses(), true )
		) {
			return;
		}

		$shortcode_ids_before = $this->get_shortcode_ids( $post_before->post_content );
		$shortcode_ids_after  = $this->get_shortcode_ids( $post_after->post_content );

		$this->update_shortcode_locations( $post_after, $shortcode_ids_before, $shortcode_ids_after );
	}

	public function trash_post( $post_id ) {

		$post                 = get_post( $post_id );
		$shortcode_ids_before = $this->get_shortcode_ids( $post->post_content );
		$shortcode_ids_after  = [];

		$this->update_shortcode_locations( $post, $shortcode_ids_before, $shortcode_ids_after );
	}

	public function untrash_post( $post_id ) {

		$post                 = get_post( $post_id );
		$shortcode_ids_before = [];
		$shortcode_ids_after  = $this->get_shortcode_ids( $post->post_content );

		$this->update_shortcode_locations( $post, $shortcode_ids_before, $shortcode_ids_after );
	}

	public function update_shortcode_locations( $post_after, $shortcode_ids_before, $shortcode_ids_after ) {

		global $wpdb;

		$table = $wpdb->prefix . 'integrate_google_drive_shortcodes';

		$post_id = $post_after->ID;
		$url     = get_permalink( $post_id );
		$url     = ( $url === false || is_wp_error( $url ) ) ? '' : $url;
		//$url     = str_replace( home_url(), '', $url );

		$shortcode_ids_to_remove = array_diff( $shortcode_ids_before, $shortcode_ids_after );
		$shortcode_ids_to_add    = array_diff( $shortcode_ids_after, $shortcode_ids_before );

		foreach ( $shortcode_ids_to_remove as $shortcode_id ) {
			$locations = $this->get_locations_without_current_post( $shortcode_id, $post_id );

			$wpdb->update( $table, [ 'locations' => maybe_serialize( $locations ), ], [ 'id' => $shortcode_id ] );

		}

		foreach ( $shortcode_ids_to_add as $shortcode_id ) {
			$locations = $this->get_locations_without_current_post( $shortcode_id, $post_id );

			$locations[] = [
				'type'         => $post_after->post_type,
				'title'        => $post_after->post_title,
				'shortcode_id' => $shortcode_id,
				'post_id'      => $post_id,
				'status'       => $post_after->post_status,
				'url'          => $url,
			];

			$wpdb->update( $table, [ 'locations' => maybe_serialize( $locations ), ], [ 'id' => $shortcode_id ] );

		}
	}

	/**
	 * Get post types for search in.
	 *
	 * @return string[]
	 * @since 1.7.4
	 *
	 */
	public function get_post_types() {

		$args       = [
			'public'             => true,
			'publicly_queryable' => true,
		];
		$post_types = get_post_types( $args, 'names', 'or' );

		unset( $post_types['attachment'] );

		$post_types[] = 'wp_template';
		$post_types[] = 'wp_template_part';

		return $post_types;
	}

	/**
	 * Get post statuses for search in.
	 *
	 * @return string[]
	 * @since 1.7.4
	 *
	 */
	public function get_post_statuses() {

		return [ 'publish', 'pending', 'draft', 'future', 'private' ];
	}

	public function get_shortcode_ids( $content ) {

		$shortcode_ids = [];

		if (
			preg_match_all(
			/**
			 * Extract id from shortcode or block.
			 * Examples:
			 * [integrate_google_drive id="32" ]
			 * <!-- wp:igd/shortcodes {"id":"32"} /-->
			 * In both, we should find 32.
			 */
				'#\[\s*integrate_google_drive.+id\s*=\s*"(\d+?)".*]|<!-- wp:igd/shortcodes {"id":(\d+).*?} /-->#',
				$content,
				$matches
			)
		) {
			array_shift( $matches );
			$shortcode_ids = array_map(
				'intval',
				array_unique( array_filter( array_merge( ...$matches ) ) )
			);
		}

		return $shortcode_ids;
	}

	private function get_locations_without_current_post( $shortcode_id, $post_id ) {

		global $wpdb;
		$table = $wpdb->prefix . 'integrate_google_drive_shortcodes';

		$locations = $wpdb->get_var( $wpdb->prepare( "SELECT locations FROM $table WHERE id = %d", $shortcode_id ) );
		$locations = ! empty( $locations ) ? array_values( maybe_unserialize( $locations ) ) : [];

		if ( ! is_array( $locations ) ) {
			$locations = [];
		}

		return array_filter(
			$locations,
			static function ( $location ) use ( $post_id ) {

				return $location['post_id'] !== $post_id;
			}
		);
	}

	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self;
		}

		return self::$instance;
	}

}

Shortcode_Locations::instance();