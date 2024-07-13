<?php

/**
 * Handle google account authorization
 */

namespace IGD;

defined( 'ABSPATH' ) || exit();


class Authorization {

	private $account_id;
	private $is_valid = true;
	private $token_key = 'igd_tokens';
	private $client;

	/**
	 * @param $account
	 */
	public function __construct( $account ) {
		$this->account_id = $account['id'];
	}

	/**
	 * @return \Exception|false|\IGDGoogle_Client
	 * @throws \Exception
	 */
	public function get_client() {
		if ( empty( $this->client ) ) {
			$this->client = Client::instance( $this->account_id )->get_client();
		}

		return $this->client;
	}

	/**
	 * @return false|mixed
	 */
	public function get_access_token() {
		$tokens = get_option( $this->token_key, [] );

		return ! empty( $tokens[ $this->account_id ] ) ? $tokens[ $this->account_id ] : false;
	}

	/**
	 * @param $access_token
	 *
	 * @return mixed
	 */
	public function set_access_token( $access_token ) {
		$tokens = get_option( $this->token_key, [] );

		$tokens[ $this->account_id ] = trim( $access_token );

		update_option( $this->token_key, $tokens );

		return $access_token;
	}

	/**
	 * Check if the authorization is still valid
	 *
	 * @return bool
	 */
	public function is_valid() {
		return $this->is_valid;
	}

	/**
	 * @param $valid
	 *
	 * @return void
	 */
	public function set_is_valid( $valid = true ) {
		$this->is_valid = $valid;
	}

	/**
	 * @return bool
	 */
	public function has_access_token() {
		if ( ! $this->is_valid() ) {
			return false;
		}

		$access_token = $this->get_access_token();

		return ! empty( $access_token );
	}

	/**
	 * @return void
	 */
	public function remove_token() {
		$tokens = get_option( $this->token_key, [] );

		unset( $tokens[ $this->account_id ] );

		update_option( $this->token_key, $tokens );
	}

	/**
	 * Refresh the access token
	 */
	public function refresh_token( $account ) {

		// Stop if we need to get a new AccessToken but somehow ended up without a refresh token
		$refresh_token = $this->get_client()->getRefreshToken();

		if ( empty( $refresh_token ) ) {
			error_log( '[Integrate Google Drive]: No Refresh Token found during the renewing of the current token. We will stop the authorization completely.' );

			$this->set_is_valid( false );
			$this->revoke_token();

			return false;
		}

		// Refresh token
		try {
			$this->get_client()->refreshToken( $refresh_token );

			// Store the new token
			$new_access_token = $this->get_client()->getAccessToken();
			$this->set_access_token( $new_access_token );

			// Remove lost authorization notice
			if ( $timestamps = wp_next_scheduled( 'igd_lost_authorization_notice', [ 'account_id' => $account['id'] ] ) ) {
				wp_unschedule_event( $timestamps, 'igd_lost_authorization_notice', [ 'account_id' => $account['id'] ] );

				// Update lost info
				$account['is_lost'] = false;
				Account::instance()->update_account( $account );
			}


		} catch ( \Exception $exception ) {
			$this->set_is_valid( false );

			if ( ! wp_next_scheduled( 'igd_lost_authorization_notice', [ 'account_id' => $account['id'] ] ) ) {
				wp_schedule_event( time(), 'daily', 'igd_lost_authorization_notice', [ 'account_id' => $account['id'] ] );

				// Update lost info
				$account['is_lost'] = true;
				Account::instance()->update_account( $account );
			}

			error_log( 'Integrate Google Drive - Message: ' . sprintf( 'Cannot refresh the authorization token %s', $exception->getMessage() ) );
		}

		return $this->get_client();
	}

	/**
	 * Delete account
	 *
	 * @return bool
	 */
	public function revoke_token() {
		error_log( 'Integrate Google Drive - Message: ' . 'Lost authorization' );

		try {
			$this->get_client()->revokeToken();
		} catch ( \Exception $exception ) {
			error_log( 'Integrate Google Drive - Error: ' . $exception->getMessage() );
		}

		// Delete the account completely
		Account::instance()->delete_account( $this->account_id );

		return true;
	}
}
