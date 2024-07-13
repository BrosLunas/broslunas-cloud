<?php

namespace IGD;

defined( 'ABSPATH' ) || exit();

class Download {

	protected static $instance = null;
	private $client;
	private $file;
	private $file_id;
	private $account_id;
	private $mimetype;
	private $proxy;
	private $download_method = 'redirect';
	private $max_file_size_without_warning = 26214400;

	/**
	 * @throws \Exception
	 */
	public function __construct( $id, $account_id, $mimetype = 'default', $proxy = false ) {

		try {
			$file = App::instance( $account_id )->get_file_by_id( $id );
		} catch ( \Exception $e ) {
			die( __( 'Something went wrong! File may be deleted or moved to trash.', 'integrate-google-drive' ) );
		}

		$this->file       = $file;
		$this->file_id    = $id;
		$this->account_id = $account_id;
		$this->mimetype   = $mimetype;
		$this->proxy      = $proxy;

		$this->client = Client::instance( $account_id )->get_client();

		wp_using_ext_object_cache( false );
	}


	public function start_download() {
		$this->init_process();

		$this->set_download_method();

		$this->process_download();
	}

	public function init_process() {

		// Insert download log
		do_action( 'igd_insert_log', 'download', $this->file_id, $this->account_id );

		//check if shortcut file then get the original file
		if ( igd_is_shortcut( $this->file['type'] ) ) {
			$this->file = App::instance( $this->account_id )->get_file_by_id( $this->file['shortcutDetails']['targetId'] );
		}

		// get the last-modified-date of this very file
		$updated_date = $this->file['updated'];

		// get a unique hash of this file (etag)
		$etag_file = md5( $updated_date );

		// get the HTTP_IF_MODIFIED_SINCE header if set
		$if_modified_since = isset( $_SERVER['HTTP_IF_MODIFIED_SINCE'] ) ? sanitize_text_field( $_SERVER['HTTP_IF_MODIFIED_SINCE'] ) : false;

		// get the HTTP_IF_NONE_MATCH header if set (etag: unique file hash)
		$etag_header = isset( $_SERVER['HTTP_IF_NONE_MATCH'] ) ? trim( sanitize_text_field( $_SERVER['HTTP_IF_NONE_MATCH'] ) ) : false;

		header( 'Last-Modified: ' . gmdate( 'D, d M Y H:i:s', strtotime( $updated_date ) ) . ' GMT' );
		header( "Etag: {$etag_file}" );
		header( 'Expires: ' . gmdate( 'D, d M Y H:i:s', time() + 60 * 5 ) . ' GMT' );
		header( 'Cache-Control: must-revalidate' );

		if ( $if_modified_since && $etag_header && ( strpos( $if_modified_since, $etag_file ) !== false ) ) {
			header( 'HTTP/1.1 304 Not Modified' );
			exit();
		}
	}

	private function process_download() {

		if ( 'redirect' === $this->download_method ) {
			if ( 'default' === $this->mimetype ) {
				$this->redirect_to_content();
			} else {
				$this->export_content();
			}
		} else {

			if ( 'default' === $this->mimetype ) {
				$this->download_stream();
			} else {
				$this->export_content();
			}

		}

		exit();
	}

	private function download_stream() {
		$filename = $this->file['name'];
		header( 'Content-Disposition: attachment; ' . sprintf( 'filename="%s"; ', rawurlencode( $filename ) ) . sprintf( "filename*=utf-8''%s", rawurlencode( $filename ) ) );

		if ( function_exists( 'apache_setenv' ) ) {
			@apache_setenv( 'no-gzip', 1 );
		}

		@ini_set( 'zlib.output_compression', 'Off' );
		@session_write_close();

		// Stop WP from buffering
		wp_ob_end_flush_all();

		$chunk_size = min( igd_get_free_memory_available() - ( 1024 * 1024 * 5 ), 1024 * 1024 * 50 ); // Chunks of 50MB or less if memory isn't sufficient

		$access_token = json_decode( $this->client->getAccessToken() )->access_token;

		// Convert headers to string format expected by stream_context_create
		$headers = "Authorization: Bearer $access_token";

		$opts = [
			"http" => [
				"method" => "GET",
				"header" => $headers
			]
		];

		$context = stream_context_create( $opts );

		$handle = fopen( $this->get_api_url(), 'rb', false, $context );

		if ( $handle === false ) {
			return false; // Consider adding error handling here
		}

		while ( ! feof( $handle ) ) {
			echo fread( $handle, $chunk_size );
		}

		fclose( $handle );
	}

	public function export_content() {
		// Stop WP from buffering
		wp_ob_end_flush_all();

		$export_link = $this->get_export_url();

		if (
			( $this->file['size'] <= 10485760 ) &&
			( empty( $export_link ) || ! Permissions::instance( $this->account_id )->has_permission( $this->file ) || 'proxy' == $this->download_method )
		) {
			// Only use export link if publicly accessible
			$export_link = $this->get_api_url();
		} else {
			wp_redirect( $export_link );

			return;
		}

		$request     = new \IGDGoogle_Http_Request( $export_link, 'GET' );
		$httpRequest = $this->client->getAuth()->authenticatedRequest( $request );
		$headers     = $httpRequest->getResponseHeaders();

		if ( isset( $headers['location'] ) ) {
			wp_redirect( $headers['location'] );
		} else {
			foreach ( $headers as $key => $header ) {
				if ( 'transfer-encoding' === $key ) {
					continue;
				}

				if ( is_array( $header ) ) {
					header( "{$key}: " . implode( ' ', $header ) );
				} else {
					header( "{$key}: " . str_replace( "\n", ' ', $header ) );
				}
			}
		}

		echo $httpRequest->getResponseBody();
	}

	public function get_api_url() {
		if ( 'default' !== $this->mimetype ) {
			return 'https://www.googleapis.com/drive/v3/files/' . $this->file['id'] . '/export?alt=media&mimeType=' . $this->mimetype;
		}

		return 'https://www.googleapis.com/drive/v3/files/' . $this->file['id'] . '?alt=media';
	}

	public function set_download_method() {

		if ( $this->proxy ) {
			return $this->download_method = 'proxy';
		}

		$copy_disabled = $this->file['copyRequiresWriterPermission'];

		if ( $copy_disabled ) {
			return $this->download_method = 'proxy';
		}

		// Is file already shared ?
		$is_shared = Permissions::instance( $this->account_id )->has_permission( $this->file );

		if ( $is_shared ) {
			return $this->download_method = 'redirect';
		}

		// File permissions
		$file_permissions = (array) $this->file['permissions'];

		// Can the sharing permissions of the file be updated via the plugin?
		$manage_permissions     = igd_get_settings( 'manageSharing', true );
		$can_update_permissions = $manage_permissions && $file_permissions['canShare'];

		if ( ! $can_update_permissions ) {
			return $this->download_method = 'proxy';
		}

		// Update the Sharing Permissions
		$is_sharing_permission_updated = Permissions::instance( $this->account_id )->set_permission( $this->file );

		if ( ! $is_sharing_permission_updated ) {
			return $this->download_method = 'proxy';
		}

		return $this->download_method = 'redirect';

	}

	public function redirect_to_content() {

		if ( $this->file['size'] < $this->max_file_size_without_warning ) {
			wp_redirect( $this->get_content_url() );

			exit();
		}

		// Download larger files via export link if possible, otherwise start streaming
		if ( $this->should_redirect_for_large_file() ) {
			$this->save_redirect_for_large_file();
			$this->redirect_for_large_file( $this->get_content_url() );
		} else {
			$this->download_method = 'proxy';
			$this->process_download();
		}

		exit;

	}


	public function get_content_url() {

		if ( 'default' == $this->mimetype && ! empty( $this->file['webContentLink'] ) ) {
			return $this->file['webContentLink'] . '&userIp=' . igd_get_user_ip();
		}

		return $this->get_export_url() . '&userIp=' . igd_get_user_ip();

	}

	public function get_export_url() {
		if ( ! empty( $this->file['exportLinks'][ $this->mimetype ] ) ) {
			return $this->file['exportLinks'][ $this->mimetype ];
		}

		return false;
	}

	private function should_redirect_for_large_file() {
		// Unclear what the exact limit for automated queries is.
		// Higher values will likely cause download problems.
		$downloads_per_hour = 0;

		$latest_downloads = get_site_option( 'igd_download_list', [] );
		$hour_ago         = strtotime( '-1 hour' );

		foreach ( $latest_downloads as $i => $time ) {
			if ( $time < $hour_ago ) {
				unset( $latest_downloads[ $i ] );
			}
		}

		return count( $latest_downloads ) < $downloads_per_hour;
	}

	private function save_redirect_for_large_file() {
		$latest_downloads   = get_site_option( 'igd_download_list', [] );
		$latest_downloads[] = time();

		update_site_option( 'igd_download_list', $latest_downloads );
	}

	private function redirect_for_large_file( $web_url ) {

		// Redirect to final download url if it is still cached.
		$download_url = get_transient( 'igd_download_' . $this->file['id'] );
		if ( ! empty( $download_url ) ) {
			header( 'Location: ' . $download_url );

			return true;
		}

		// Add Resources key to give permission to access the item
		if ( ! empty( $this->file['resourceKey'] ) ) {
			$headers['X-Goog-Drive-Resource-Keys'] = $this->file['id'] . '/' . $this->file['resourceKey'];
		}

		$web_url = str_replace( 'drive.google.com/uc?id=', 'drive.usercontent.google.com/download?id=', $web_url );

		$options = [
			'headers' => [
				'user-agent' => 'IGD ' . IGD_VERSION,
			],
		];

		$response = wp_remote_get( $web_url, $options );
		if ( ! empty( $response ) && ! is_wp_error( $response ) ) {
			$response_body = wp_remote_retrieve_body( $response );
			$headers       = wp_remote_retrieve_headers( $response );
		} else {
			$this->download_method = 'proxy';
			$this->process_download();

			exit;
		}

		// If location is found, set in cache and redirect user to url
		if ( isset( $headers['location'] ) ) {
			set_transient( 'igd_download_' . $this->file['id'], $headers['location'], MINUTE_IN_SECONDS * 2 );

			header( 'Location: ' . $headers['location'] );

			return true;
		}

		// If no location is found, try find the download url in the body and load that url instead
		preg_match_all( '/ type="hidden" name="(?<name>.*?)" value="(?<value>.*?)"/m', $response_body, $params, PREG_SET_ORDER, 0 );

		$found_redirect = false;
		$download_url   = 'https://drive.usercontent.google.com/download?id=' . $this->file['id'];
		foreach ( $params as $param ) {
			if ( 'id' === $param['name'] ) {
				continue;
			}

			if ( 'uuid' === $param['name'] ) {
				$found_redirect = true;
			}

			$download_url .= '&' . $param['name'] . '=' . $param['value'];
		}

		if ( $found_redirect ) {
			header( 'Location: ' . $download_url );

			return true;
		}

		// If nothing works, fallback to proxy method
		$this->download_method = 'proxy';
		$this->process_download();

		exit;
	}

	public static function instance( $id, $account_id, $mimetype = 'default', $proxy = false ) {

		if ( is_null( self::$instance ) ) {
			self::$instance = new self( $id, $account_id, $mimetype, $proxy );
		}

		return self::$instance;
	}

}