<?php

namespace IGD;

defined( 'ABSPATH' ) || exit;


class EDD {
	/**
	 * @var null
	 */
	protected static $instance = null;

	public function __construct() {
		add_action( 'edd_process_verified_download', [ $this, 'do_download' ], 1 );
		add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_scripts' ] );
	}

	public function enqueue_scripts() {

		if ( function_exists( 'get_current_screen' ) ) {
			$current_screen = get_current_screen();
			if ( isset( $current_screen->post_type ) && $current_screen->post_type == 'download' ) {
				if ( ! wp_script_is( 'igd-admin' ) ) {
					Enqueue::instance()->admin_scripts( '', false );
				}

				if ( ! wp_script_is( 'igd-edd' ) ) {
					wp_enqueue_script( 'igd-edd', IGD_ASSETS . '/js/edd.js', array( 'igd-admin' ), IGD_VERSION, true );
				}

			}
		}
	}

	public function do_download( $download ) {
		$files      = edd_get_download_files( $download );
		$file_index = intval( $_GET['file'] );

		$file     = $files[ $file_index ];
		$file_url = $file['file'];

		if ( ! strpos( $file_url, 'igd-edd-download' ) ) {
			return;
		}

		$parts = parse_url( $file_url );
		parse_str( $parts['query'], $query_args );


		$id         = $query_args['id'];
		$account_id = $query_args['account_id'];

		$file      = App::instance( $account_id )->get_file_by_id( $id );
		$is_folder = igd_is_dir( $file );

		if ( $is_folder ) {
			igd_download_zip( [ $id ], '', $account_id );
		} else {
			$download_url = admin_url( 'admin-ajax.php?action=igd_download&id=' . $id . '&accountId=' . $account_id );
			wp_redirect( $download_url );
		}

		exit();
	}


	/**
	 * @return EDD|null
	 */
	public static function instance() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

}

EDD::instance();