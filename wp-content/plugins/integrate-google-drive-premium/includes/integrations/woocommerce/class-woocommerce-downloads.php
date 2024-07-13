<?php

namespace IGD;

defined( 'ABSPATH' ) || exit;

class WooCommerce_Downloads {

	/**
	 * @var null
	 */
	protected static $instance = null;

	public function __construct() {
		// Handle Downloads
		add_action( 'woocommerce_download_file_force', [ $this, 'do_download' ], 1 );
		add_action( 'woocommerce_download_file_xsendfile', [ $this, 'do_download' ], 1 );
		add_action( 'woocommerce_download_file_redirect', [ $this, 'do_download' ], 1 );

	}

	public function do_download( $file_url ) {
		if ( ! strpos( $file_url, 'igd-wc-download' ) ) {
			return;
		}

		$parts = parse_url( $file_url );
		parse_str( $parts['query'], $query_args );

		$file_id                = ! empty( $query_args['id'] ) ? $query_args['id'] : '';
		$account_id        = ! empty( $query_args['account_id'] ) ? $query_args['account_id'] : '';
		$type              = ! empty( $query_args['type'] ) ? $query_args['type'] : '';
		$redirect          = ! empty( $query_args['redirect'] );
		$create_permission = ! empty( $query_args['create_permission'] );

		$is_folder = 'application/vnd.google-apps.folder' == $type;

		if ( $redirect ) {
			$this->do_redirect($file_id, $account_id, $is_folder, $create_permission);
		} else {

			if ( $is_folder ) {
				igd_download_zip( [ $file_id ], '', $account_id );
			} else {
				$download_url = admin_url( 'admin-ajax.php?action=igd_download&id=' . $file_id . '&accountId=' . $account_id );
				wp_redirect( $download_url );
			}
		}

		exit();

	}

	/**
	 * Redirect to the content in the Google Drive instead of downloading the file
	 *
	 * @param $file_url
	 *
	 * @return void
	 */
	public function do_redirect( $file_id, $account_id, $is_folder, $create_permission ) {

		if ( $create_permission ) {
			$order_id = wc_get_order_id_by_order_key( wc_clean( wp_unslash( $_GET['order'] ) ) );
			$order    = wc_get_order( $order_id );

			if ( isset( $_GET['email'] ) ) {
				$email_address = wp_unslash( $_GET['email'] );
			} else {
				$email_address = is_a( $order, 'WC_Order' ) ? $order->get_billing_email() : null;
			}

			if ( igd_is_gmail( $email_address ) ) {

				$has_permission = get_option( 'igd_woocommerce_download_permission_' . md5( $email_address . $file_id ), false );

				if ( ! $has_permission ) {
					$service = App::instance( $account_id )->getService();

					$permission = new \IGDGoogle_Service_Drive_Permission();
					$permission->setEmailAddress( $email_address );
					$permission->setType( 'user' );
					$permission->setRole( 'reader' );

					try {
						$service->permissions->create( $file_id, $permission );
						update_option( 'igd_woocommerce_download_permission_' . md5( $email_address . $file_id ), true );
					} catch ( \Exception $e ) {
						error_log( "IGD Woocommerce download error occurred: " . $e->getMessage() );
					}


				}

			}
		}


		// Google Drive redirect
		if ( $is_folder ) {
			$redirect_url = 'https://drive.google.com/drive/folders/' . $file_id;
		} else {
			$redirect_url = 'https://drive.google.com/file/d/' . $file_id . '/view';
		}

		wp_redirect( $redirect_url );

		exit();
	}

	/**
	 * @return WooCommerce_Downloads|null
	 */
	public static function instance() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

}

WooCommerce_Downloads::instance();