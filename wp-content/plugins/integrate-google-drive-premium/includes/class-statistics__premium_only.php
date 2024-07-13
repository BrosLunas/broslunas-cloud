<?php

namespace IGD;

class Statistics {

	private static $instance = null;

	public function __construct() {

		// Insert logs
		if ( igd_fs()->can_use_premium_code__premium_only() ) {
			add_action( 'igd_insert_log', function ( $type, $file_id, $account_id ) {

				$params = [
					'type'       => $type,
					'file_id'    => $file_id,
					'account_id' => $account_id,
				];

				$this->insert_log( $params );

			}, 10, 3 );
		}

		// Get logs - AJAX
		add_action( 'wp_ajax_igd_get_logs', array( $this, 'get_logs' ) );

		// Get events
		add_action( 'wp_ajax_igd_get_events', array( $this, 'get_events_logs' ) );

		// Clear statistics
		add_action( 'wp_ajax_igd_clear_statistics', [ $this, 'clear_statistics' ] );

		// Export statistics
		add_action( 'wp_ajax_igd_export_statistics', [ $this, 'export_statistics' ] );
	}

	public function export_statistics() {
		if ( ! igd_user_can_access( 'statistics' ) ) {
			wp_send_json_error( __( 'You do not have permission to perform this action', 'integrate-google-drive' ) );
		}

		$start_date = ! empty( $_POST['start_date'] ) ? sanitize_text_field( $_POST['start_date'] ) : '';
		$end_date   = ! empty( $_POST['end_date'] ) ? sanitize_text_field( $_POST['end_date'] ) : '';

		$end_date = $end_date . ' 23:59:59';

		//Top Items
		$data_top_items = [
			'download' => $this->get_top_items( $start_date, $end_date, 'download' ),
			'upload'   => $this->get_top_items( $start_date, $end_date, 'upload' ),
			'stream'   => $this->get_top_items( $start_date, $end_date, 'stream' ),
			'preview'  => $this->get_top_items( $start_date, $end_date, 'preview' ),
		];

		$flattened_data_top_items = [];
		foreach ( $data_top_items as $type => $items ) {
			foreach ( $items as $item ) {
				$flattened_data_top_items[] = [
					__( 'Type', 'integrate-google-drive' )      => $type,
					__( 'File Name', 'integrate-google-drive' ) => $item['file_name'],
					__( 'File ID', 'integrate-google-drive' )   => $item['file_id'],
					__( 'Date', 'integrate-google-drive' )      => date( 'Y-m-d H:i a', strtotime( $item['created_at'] ) ),
				];
			}
		}

		//Top Users
		$data_top_users = [
			'download' => $this->get_top_users( $start_date, $end_date, 'download' ),
			'upload'   => $this->get_top_users( $start_date, $end_date, 'upload' ),
			'stream'   => $this->get_top_users( $start_date, $end_date, 'stream' ),
			'preview'  => $this->get_top_users( $start_date, $end_date, 'preview' ),
		];

		$flattened_data_top_users = [];
		foreach ( $data_top_users as $type => $users ) {
			foreach ( $users as $user ) {
				$flattened_data_top_users[] = [
					__( 'Type', 'integrate-google-drive' )         => $type,
					__( 'User Name', 'integrate-google-drive' )    => $user['name'],
					__( 'User ID', 'integrate-google-drive' )      => $user['user_id'],
					__( 'Action Count', 'integrate-google-drive' ) => $user['count'],
				];
			}
		}

		//Event Logs
		$events = $this->get_events( $start_date, $end_date );

		$flattened_events = [];
		foreach ( $events as $event ) {
			$flattened_events[] = [
				__( 'Type', 'integrate-google-drive' )      => $event['type'],
				__( 'File Name', 'integrate-google-drive' ) => $event['file_name'],
				__( 'File ID', 'integrate-google-drive' )   => $event['file_id'],
				__( 'FIle Type', 'integrate-google-drive' ) => $event['file_type'],
				__( 'User Name', 'integrate-google-drive' ) => $event['username'],
				__( 'User ID', 'integrate-google-drive' )   => $event['user_id'],
				__( 'Date', 'integrate-google-drive' )      => date( 'Y-m-d H:i a', strtotime( $event['created_at'] ) ),
			];
		}

		// Generate unique file names
		$top_items_file_name  = 'top-items-' . time() . '.csv';
		$top_users_file_name  = 'top-users-' . time() . '.csv';
		$event_logs_file_name = 'event-logs-' . time() . '.csv';

		// Create temporary file paths
		$top_items_file_path  = sys_get_temp_dir() . '/' . $top_items_file_name;
		$top_users_file_path  = sys_get_temp_dir() . '/' . $top_users_file_name;
		$event_logs_file_path = sys_get_temp_dir() . '/' . $event_logs_file_name;

		// Create CSV files
		$this->create_csv_file( $top_items_file_path, $flattened_data_top_items );
		$this->create_csv_file( $top_users_file_path, $flattened_data_top_users );
		$this->create_csv_file( $event_logs_file_path, $flattened_events );

		// Create a zip file
		$zip_file_name = 'statistics-' . time() . '.zip';
		$zip_file_path = sys_get_temp_dir() . '/' . $zip_file_name;

		$zip = new \ZipArchive();

		if ( $zip->open( $zip_file_path, \ZipArchive::CREATE ) === true ) {
			$zip->addFile( $top_items_file_path, $top_items_file_name );
			$zip->addFile( $top_users_file_path, $top_users_file_name );
			$zip->addFile( $event_logs_file_path, $event_logs_file_name );
			$zip->close();
		}

		// Save the zip file in a temporary location
		$zip_file_url = 'tmp/' . $zip_file_name;
		if ( ! is_dir( 'tmp' ) ) {
			mkdir( 'tmp', 0755, true );
		}
		if ( file_exists( $zip_file_path ) ) {
			copy( $zip_file_path, $zip_file_url );
		}

		// Clean up temporary files
		unlink( $top_items_file_path );
		unlink( $top_users_file_path );
		unlink( $event_logs_file_path );
		unlink( $zip_file_path );

		// Send the zip file URL in the AJAX response
		wp_send_json_success( [
			'success' => true,
			'url'     => $zip_file_url,
		] );


		exit;
	}

	public function create_csv_file( $file_path, $data ) {
		$file = fopen( $file_path, 'w' );

		// Add header
		if ( ! empty( $data ) && is_array( $data ) ) {
			$first_row = reset( $data );
			if ( is_array( $first_row ) ) {
				fputcsv( $file, array_keys( $first_row ) );
			}
		}

		// Add data
		foreach ( $data as $row ) {
			if ( is_array( $row ) ) {
				fputcsv( $file, $row );
			}
		}

		fclose( $file );
	}

	public function clear_statistics() {

		if ( ! check_ajax_referer( 'igd', 'nonce', false ) ) {
			wp_send_json_error( [ 'message' => __( 'Invalid nonce', 'integrate-google-drive' ) ] );
		}

		if ( ! igd_user_can_access( 'statistics' ) ) {
			wp_send_json_error( __( 'You do not have permission to perform this action', 'integrate-google-drive' ) );
		}

		global $wpdb;
		$wpdb->query( "TRUNCATE TABLE {$wpdb->prefix}integrate_google_drive_logs" );

		wp_send_json_success( __( 'Statistics cleared successfully', 'integrate-google-drive' ) );
	}

	public function get_logs( $start_date = null, $end_date = null, $is_email_report = false ) {

		if ( ! igd_user_can_access( 'statistics' ) ) {
			wp_send_json_error( __( 'You do not have permission to perform this action', 'integrate-google-drive' ) );
		}

		if ( empty( $start_date ) ) {
			$start_date = ! empty( $_POST['start_date'] ) ? sanitize_text_field( $_POST['start_date'] ) : '';
		}

		if ( empty( $end_date ) ) {
			$end_date = ! empty( $_POST['end_date'] ) ? sanitize_text_field( $_POST['end_date'] ) : '';
		}

		$end_date = $end_date . ' 23:59:59';

		$data = [
			'downloads' => $this->get_top_items( $start_date, $end_date, 'download' ),
			'uploads'   => $this->get_top_items( $start_date, $end_date, 'upload' ),
			'streams'   => $this->get_top_items( $start_date, $end_date, 'stream' ),
			'previews'  => $this->get_top_items( $start_date, $end_date, 'preview' ),
			'searches'  => $this->get_top_items( $start_date, $end_date, 'search' ),

			'downloadUsers' => $this->get_top_users( $start_date, $end_date, 'download' ),
			'uploadUsers'   => $this->get_top_users( $start_date, $end_date, 'upload' ),
			'streamUsers'   => $this->get_top_users( $start_date, $end_date, 'stream' ),
			'previewUsers'  => $this->get_top_users( $start_date, $end_date, 'preview' ),
			'searchUsers'   => $this->get_top_users( $start_date, $end_date, 'search' ),

			'events' => $this->get_events( $start_date, $end_date ),
		];

		if ( $is_email_report ) {
			return $data;
		}

		wp_send_json_success( $data );
	}

	public function get_events_logs() {
		$start_date = ! empty( $_POST['start_date'] ) ? sanitize_text_field( $_POST['start_date'] ) : '';

		$end_date = ! empty( $_POST['end_date'] ) ? sanitize_text_field( $_POST['end_date'] ) : '';
		$end_date = $end_date . ' 23:59:59';

		$page     = ! empty( $_POST['page'] ) ? sanitize_text_field( $_POST['page'] ) : 2;
		$per_page = 30;
		$offset   = ( $page - 1 ) * $per_page;

		$events = $this->get_events( $start_date, $end_date, $per_page, $offset );

		$data = [
			'complete' => empty( $events ) || count( $events ) < $per_page,
			'events'   => $events,
		];

		wp_send_json_success( $data );
	}

	public function get_top_items( $start_date, $end_date, $type ) {

		global $wpdb;
		$table_name = $wpdb->prefix . 'integrate_google_drive_logs';

		$sql = $wpdb->prepare( "SELECT *, COUNT(id) as total FROM `$table_name`
                WHERE type = '%s' AND created_at BETWEEN '%s' AND '%s'
                GROUP BY file_id
                ORDER BY total DESC
                LIMIT 25
                ", $type, $start_date, $end_date );

		return $wpdb->get_results( $sql, ARRAY_A );

	}

	public function get_top_users( $start_date, $end_date, $type ) {

		global $wpdb;
		$table_name = $wpdb->prefix . 'integrate_google_drive_logs';

		$sql = $wpdb->prepare( "SELECT user_id, COUNT(id) as total FROM `$table_name`
                WHERE type = '%s' AND created_at BETWEEN '%s' AND '%s'
                GROUP BY user_id
                ORDER BY total DESC
                LIMIT 25
                ", $type, $start_date, $end_date );

		$results = $wpdb->get_results( $sql, ARRAY_A );

		$data = [];

		if ( ! empty( $results ) ) {
			foreach ( $results as $result ) {

				$gravatar = '<img src="' . IGD_ASSETS . '/images/user-icon.png" height="32px" />';

				if ( $result['user_id'] ) {
					$user = get_user_by( 'id', $result['user_id'] );
					$name = $user->user_login;

					// Gravatar
					if ( function_exists( 'get_wp_user_avatar_url' ) ) {
						$gravatar = get_wp_user_avatar( $user->user_email, 32 );
					} else {
						$gravatar = get_avatar( $user->user_email, 32 );
					}
				} else {
					$name = __( 'Guest', 'integrate-google-drive' );
				}

				$data[] = [
					'user_id' => $result['user_id'],
					'avatar'  => $gravatar,
					'name'    => $name,
					'count'   => $result['total']
				];
			}
		}

		return $data;
	}

	public function get_events( $start_date = '', $end_date = '', $per_page = 30, $offset = 0 ) {

		global $wpdb;
		$table_name = $wpdb->prefix . 'integrate_google_drive_logs';

		$limit_sql = "LIMIT $per_page OFFSET $offset";
		if ( empty( $per_page ) ) {
			$limit_sql = '';
		}

		$sql = $wpdb->prepare( "SELECT * FROM `$table_name`
                                        WHERE created_at BETWEEN '%s' AND '%s'
                                        ORDER BY created_at DESC $limit_sql ", $start_date, $end_date );

		$results = $wpdb->get_results( $sql, ARRAY_A );

		$data = [];

		if ( ! empty( $results ) ) {
			foreach ( $results as $result ) {
				$item = $result;

				if ( $result['user_id'] ) {
					$user             = get_user_by( 'id', $result['user_id'] );
					$item['username'] = $user->user_login;
				} else {
					$item['username'] = __( 'Guest', 'integrate-google-drive' );
				}

				$data[] = $item;
			}
		}

		return $data;
	}

	public function insert_log( $data = [] ) {

		if ( empty( $data['file_id'] ) ) {
			return;
		}

		$file_id = $data['file_id'];

		$type = ! empty( $data['type'] ) ? $data['type'] : 'preview';

		$account_id = ! empty( $data['account_id'] ) ? $data['account_id'] : '';

		if ( 'search' == $type ) {
			//NB: for search type treat file_id as search keyword

			$file_name = '';
			$file_type = '';
		} else {

			if ( ! empty( $data['file_name'] ) ) {
				$file_name = $data['file_name'];
				$file_type = $data['file_type'];
			} else {
				$file = App::instance( $account_id )->get_file_by_id( $file_id );

				if ( ! $file ) {
					return;
				}

				$file_name = $file['name'];
				$file_type = $file['type'];
			}
		}

		$created_at = ! empty( $data['created_at'] ) ? $data['created_at'] : current_time( 'mysql' );
		$user_id    = ! empty( $data['user_id'] ) ? $data['user_id'] : get_current_user_id();

		global $wpdb;
		$table_name = $wpdb->prefix . 'integrate_google_drive_logs';

		$wpdb->insert(
			$table_name,
			array(
				'type'       => $type,
				'user_id'    => $user_id,
				'file_id'    => $file_id,
				'file_name'  => $file_name,
				'file_type'  => $file_type,
				'account_id' => $account_id,
				'created_at' => $created_at,
			),
			array( '%s', '%d', '%s', '%s', '%s', '%s', '%s', )
		);

	}

	public static function view() { ?>
        <div id="igd-statistics" class="igd-statistics"></div>
	<?php }

	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self;
		}

		return self::$instance;
	}

}

new Statistics();