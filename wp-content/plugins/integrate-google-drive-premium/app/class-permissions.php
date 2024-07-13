<?php

namespace IGD;

defined( 'ABSPATH' ) || exit();

class Permissions {

	private static $instance = null;

	private $app;

	public function __construct( $account_id = null ) {

		if ( empty( $account_id ) ) {
			$account    = Account::instance()->get_active_account();
			$account_id = ! empty( $account ) ? $account['id'] : $account_id;
		}

		$this->app = App::instance( $account_id );
	}

	/**
	 * Gets the domain and type from settings.
	 *
	 * @return array
	 */
	private function get_domain_and_type() {
		$permission_domain = igd_get_settings( 'workspaceDomain' );
		$permission_type   = ! empty( $permission_domain ) ? 'domain' : 'anyone';

		return [ $permission_domain, $permission_type ];
	}

	/**
	 * Retrieves permissions from a file.
	 *
	 * @param array $file
	 *
	 * @return array
	 */
	private function get_permissions_from_file( $file = [] ) {
		$file_permissions = [];
		$users            = [];

		if ( ! empty( $file['permissions'] ) ) {
			$file_permissions = $file['permissions'];
			$users            = (array) $file_permissions['users'];
		}

		return [ $file_permissions, $users ];
	}

	/**
	 * Updates a file.
	 *
	 * @param string $account_id
	 * @param array $file
	 * @param array $users
	 *
	 * @return void
	 */
	private function update_file( $file = [], $users = [] ) {
		$file['permissions']['users'] = $users;

		// If not the full file, get the full file.
		if ( empty( $file['iconLink'] ) || empty( $file['thumbnailLink'] ) || empty( $file['webViewLink'] ) ) {
			$original_file = $this->app->get_file_by_id( $file['id'] );

			$file = array_merge( $original_file, $file );
		}

		Files::update_file(
			[ 'data' => serialize( $file ) ],
			[ 'id' => $file['id'] ]
		);
	}

	public function has_permission( $file = [], $permission_role = [ 'reader', 'writer' ], $force_update = false ) {

		list( $permission_domain, $permission_type ) = $this->get_domain_and_type();
		$users = $file['permissions']['users'] ?? [];

		if ( count( $users ) > 0 ) {
			foreach ( $users as $user ) {
				if ( ( $user['type'] == $permission_type ) && in_array( $user['role'], $permission_role ) && ( empty( $permission_domain ) || $user['domain'] == $permission_domain ) ) {
					return true;
				}
			}
		}

		if ( empty( $users ) || $force_update ) {
			if ( $this->set_permission( $file, $permission_role[0] ) ) {
				return true;
			}
		}

		// Check if the shared file
		if ( in_array( 'reader', $permission_role ) ) {
			$check_url = 'https://drive.google.com/file/d/' . $file['id'] . '/view';

			if ( ! empty( $file['resourceKey'] ) ) {
				$check_url .= "&resourcekey={$file['resourceKey']}";
			}

			$request = new \IGDGoogle_Http_Request( $check_url, 'GET' );
			$this->app->client->getIo()->setOptions( [ CURLOPT_FOLLOWLOCATION => 0 ] );
			$httpRequest = $this->app->client->getIo()->makeRequest( $request );
			curl_close( $this->app->client->getIo()->getHandler() );

			if ( 200 == $httpRequest->getResponseHttpCode() ) {
				$users['anyoneWithLink'] = [
					'domain' => $permission_domain,
					'role'   => "reader",
					'type'   => "anyone",
				];

				$this->update_file( $file, $users );

				return true;
			}
		}

		return false;
	}


	public function set_permission( $file = [], $permission_role = 'reader' ) {
		list( $permission_domain, $permission_type ) = $this->get_domain_and_type();
		list( $file_permissions, $users ) = $this->get_permissions_from_file( $file );

		$manage_permissions = igd_get_settings( 'manageSharing', true );

		if ( $manage_permissions && $file_permissions['canShare'] ) {
			$new_permission = new \IGDGoogle_Service_Drive_Permission();
			$new_permission->setType( $permission_type );
			$new_permission->setRole( $permission_role );
			$new_permission->setAllowFileDiscovery( false );

			if ( $permission_domain ) {
				$new_permission->setDomain( $permission_domain );
			}

			$params = [
				'fields'            => 'id,role,type,domain',
				'supportsAllDrives' => true,
			];

			try {
				$updated_permission = $this->app->service->permissions->create( $file['id'], $new_permission, $params );

				$users[ $updated_permission->getId() ] = [
					'type'   => $updated_permission->getType(),
					'role'   => $updated_permission->getRole(),
					'domain' => $updated_permission->getDomain(),
				];

				$this->update_file( $file, $users );

				return true;
			} catch ( \Exception $ex ) {
				error_log( 'Integrate Google Drive: Manage Permissions Error - ' . sprintf( 'API Error on line %s: %s', __LINE__, $ex->getMessage() ) );

				return false;
			}
		}

		return false;
	}


	public static function instance( $account_id = null ) {
		if ( null === self::$instance ) {
			self::$instance = new self( $account_id );
		}

		return self::$instance;
	}

}