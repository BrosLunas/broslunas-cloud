<?php

namespace IGD;

defined( 'ABSPATH' ) || exit();

class Account {

	private static $instance = null;

	public $user_id = 'admin';

	public function __construct( $user_id = null ) {
		$this->user_id = $user_id ?: $this->get_account_user_id();
	}

	/**
	 * @param $id
	 *
	 * @return array|false|mixed|null
	 */
	public function get_accounts( $id = null ) {

		$accounts = array_filter( get_option( 'igd_accounts', [] ) );

		if ( $id ) {
			return ! empty( $accounts[ $id ] ) ? $accounts[ $id ] : [];
		}

		if ( ! empty( $this->user_id ) ) {
			//accounts added by users
			$accounts = array_filter( $accounts, function ( $account ) {
				$user_ids = ! empty( $account['user_id'] ) ? $account['user_id'] : [ 'admin' ];

				return in_array( $this->user_id, $user_ids );
			} );

		} else {
			$accounts = array_filter( $accounts, function ( $account ) {
				$user_ids = ! empty( $account['user_id'] ) ? $account['user_id'] : [ 'admin' ];

				return in_array( 'admin', $user_ids );
			} );
		}

		return ! empty( $accounts ) ? $accounts : [];
	}

	/**
	 * Add new account or e previous account
	 *
	 * @param $data
	 */
	public function update_account( $data ) {
		$accounts = array_filter( get_option( 'igd_accounts', [] ) );

		if ( ! empty( $accounts[ $data['id'] ] ) ) {
			$existing_account            = $accounts[ $data['id'] ];
			$existing_account['user_id'] = array_unique( array_merge( $existing_account['user_id'], $data['user_id'] ) );
			$data                        = $existing_account;
		}

		$accounts[ $data['id'] ] = $data;

		update_option( 'igd_accounts', $accounts );
		update_option( 'igd_account_notice', false );

		return $data;
	}

	public function get_active_account() {
		$accounts = $this->get_accounts();

		$cookie = isset( $_COOKIE['igd_active_account'] ) ? $_COOKIE['igd_active_account'] : null;

		if ( ! empty( $cookie ) ) {
			$cookie = str_replace( [ "\\\"", "\/" ], [ "\"", "/" ], $cookie );

			$account = json_decode( $cookie, true );

			//check if user id is not same then remove cookie
			if ( ! empty( $this->user_id ) && ! empty( $account['user_id'] ) && ! in_array( $this->user_id, $account['user_id'] ) ) {
				setcookie( 'igd_active_account', '', time() - 3600, '/' );

				$account = @array_shift( $accounts );

				return ! empty( $account ) ? $account : [];
			}

			if ( ! empty( $account['id'] ) && empty( $accounts[ $account['id'] ] ) ) {
				setcookie( 'igd_active_account', '', time() - 3600, '/' );
			} else {
				return $account;
			}
		}

		if ( ! empty( $accounts ) ) {
			$account = @array_shift( $accounts );

			if ( ! empty( $account ) ) {
				return $account;
			}
		}

		return [];
	}

	/**
	 * @param string $account_id
	 *
	 * @return bool
	 */
	public function set_active_account( $account_id ) {
		$accounts = $this->get_accounts();

		$account = [];

		if ( ! empty( $accounts[ $account_id ] ) ) {
			$account = $accounts[ $account_id ];
			setcookie( 'igd_active_account', json_encode( $account ), time() + ( 30 * DAY_IN_SECONDS ), "/" );
		} elseif ( ! empty( $accounts ) ) {
			$account = @array_shift( $accounts );

			setcookie( 'igd_active_account', json_encode( $account ), time() + ( 30 * DAY_IN_SECONDS ), "/" );
		} else {
			setcookie( 'igd_active_account', '', time() - 3600, "/" );
		}

		return $account;
	}

	/**
	 * @param $account_id
	 *
	 * @return void
	 */
	public function delete_account( $account_id ) {
		$accounts = array_filter( get_option( 'igd_accounts', [] ) );

		$removed_account = $accounts[ $account_id ];

		// Check if account has only one user then remove account
		if ( empty( $removed_account['user_id'] ) || count( $removed_account['user_id'] ) == 1 ) {

			// Delete all the account files
			igd_delete_cache( [], $account_id );

			// Delete token
			$authorization = new Authorization( $removed_account );
			$authorization->remove_token();

			// Remove account data from saved accounts
			unset( $accounts[ $account_id ] );

		} else {
			// Remove user from account
			$removed_account['user_id'] = array_unique( array_diff( $removed_account['user_id'], [ $this->user_id ] ) );
			$accounts[ $account_id ]    = $removed_account;
		}

		$active_account = $this->get_active_account();

		// Update active account
		if ( ! empty( $active_account ) && $account_id == $active_account['id'] ) {
			if ( count( $accounts ) ) {
				self::set_active_account( array_key_first( $accounts ) );
			}
		}

		update_option( 'igd_accounts', $accounts );
	}

	public function get_account_user_id() {
		// Default user_id is 'admin'
		$user_id = 'admin';

		// Get the referer URL to determine the context
		$referer = wp_get_referer();

		// Define patterns to search in the referer that indicate we should get the current user ID
		$patterns = [
			'_dokan_edit_product_nonce', '_dokan_add_product_nonce', // Dokan plugin actions
			'/settings/google-drive/', // Shared setting page for both plugins
			'/create-course' // Tutor plugin action
		];

		// Check if any pattern matches the referer
		foreach ($patterns as $pattern) {
			if (strpos($referer, $pattern) !== false) {
				$user_id = get_current_user_id();
				break; // Exit the loop once a match is found
			}
		}

		return $user_id;
	}


	public static function instance( $user_id = null ) {
		if ( ! self::$instance || self::$instance->user_id != $user_id ) {
			self::$instance = new self( $user_id );
		}

		return self::$instance;
	}

}
