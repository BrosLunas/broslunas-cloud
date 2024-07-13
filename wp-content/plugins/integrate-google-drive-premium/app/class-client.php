<?php

/**
 * Handle the Google Client
 */

namespace IGD;

defined( 'ABSPATH' ) || exit();

class Client {

	private static $instance = null;

	public $client;


	private $client_id;

	private $client_secret;

	private $redirect_uri;

	public $account_id;
	public $account = [];

	public function __construct( $account_id = null ) {

		if ( ! class_exists( 'IGDGoogle_Client' ) ) {
			require_once IGD_PATH . '/vendors/Google-sdk/src/Google/autoload.php';
		}

		if ( empty( $account_id ) ) {
			$account = Account::instance()->get_active_account();
		} else {
			$account = Account::instance()->get_accounts( $account_id );
		}

		$this->account_id = ! empty( $account ) ? $account['id'] : $account_id;
		$this->account    = $account;

		$this->client_id     = apply_filters( 'igd/client_id', '116941546993-oer9vui9nfg6jp0c6cvq63rh4m11010l.apps.googleusercontent.com' );
		$this->client_secret = apply_filters( 'igd/client_secret', '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' );
		$this->redirect_uri  = apply_filters( 'igd/redirect_uri', 'https://softlabbd.com/integrate-google-drive-oauth.php' );
	}

	/**
	 * @throws \Exception
	 */
	public function get_client() {
		if ( empty( $this->client ) ) {
			$this->client = $this->start_client();
		}

		return $this->client;
	}

	/**
	 * @throws \Exception
	 */
	public function start_client() {

		try {
			$this->client = new \IGDGoogle_Client();
		} catch ( \Exception $exception ) {
			error_log( '[Integrate Google Drive - Error]: ' . sprintf( 'Couldn\'t start Google Client %s', $exception->getMessage() ) );

			return $exception;
		}

		$this->client->setApplicationName( 'Integrate Google Drive - ' . IGD_VERSION );

		$this->client->setClientId( $this->client_id );
		$this->client->setClientSecret( $this->client_secret );
		$this->client->setRedirectUri( $this->redirect_uri );
		$this->client->setApprovalPrompt( 'force' );
		$this->client->setAccessType( 'offline' );

		$state = apply_filters( 'igd_auth_state', admin_url( 'admin.php?page=integrate-google-drive&action=authorization' ) );

		$this->client->setState( base64_encode( $state ) );

		$this->client->setScopes( [
			'https://www.googleapis.com/auth/drive',
		] );

		if ( empty( $this->account ) ) {
			return $this->client;
		}

		$authorization = new Authorization( $this->account );

		if ( ! $authorization->has_access_token() ) {
			return $this->client;
		}

		$access_token = $authorization->get_access_token();

		if ( empty( $access_token ) ) {
			return $this->client;
		}

		$this->client->setAccessToken( $access_token );

		if ( ! $this->client->isAccessTokenExpired() ) {
			return $this->client;
		}

		// If we end up here, we have to refresh the token
		return $authorization->refresh_token( $this->account );
	}

	public function get_auth_url() {
		return $this->get_client()->createAuthUrl();
	}

	public function create_access_token() {

		try {
			$code = sanitize_text_field( $_GET['code'] );

			$access_token = $this->get_client()->authenticate( $code );

			$app = App::instance( $this->account_id );

			$about = $app->getService()->about->get( [ 'fields' => 'storageQuota,user' ] );


			$data = [
				'id'      => $about->getUser()->getPermissionId(),
				'name'    => $about->getUser()->getDisplayName(),
				'email'   => $about->getUser()->getEmailAddress(),
				'photo'   => $about->getUser()->getPhotoLink(),
				'storage' => [
					'usage' => $about->getStorageQuota()->getUsage(),
					'limit' => $about->getStorageQuota()->getLimit(),
				],
				'lost'    => false,
			];


			// Check if state has user_id
			$state_url   = esc_url_raw(base64_decode( sanitize_text_field($_GET['state']) ));

			$state_query = wp_parse_url( $state_url, PHP_URL_QUERY );
			parse_str( $state_query, $state );

			$user_id         = ! empty( $state['user_id'] ) ? $state['user_id'] : 'admin';
			$data['user_id'] = [ $user_id ];

			$data = Account::instance( $user_id )->update_account( $data );
			Account::instance( $user_id )->set_active_account( $data['id'] );

			$authorization = new Authorization( $data );
			$authorization->set_access_token( $access_token );

			// Remove lost authorization notice
			if ( $timestamps = wp_next_scheduled( 'igd_lost_authorization_notice', [ 'account_id' => $data['id'] ] ) ) {
				wp_unschedule_event( $timestamps, 'igd_lost_authorization_notice', [ 'account_id' => $data['id'] ] );
			}

		} catch ( \Exception $exception ) {
			error_log( 'Integrate Google Drive - Message: ' . sprintf( 'Couldn\'t generate Access Token: %s', $exception->getMessage() ) );

			return new \WP_Error( 'broke', esc_html__( 'Error communicating with API:', 'integrate-google-drive' ) . $exception->getMessage() );
		}

		return true;

	}

	public static function instance( $account_id = null ) {

		if ( is_null( self::$instance ) || self::$instance->account_id != $account_id ) {
			self::$instance = new self( $account_id );
		}

		return self::$instance;
	}

}
