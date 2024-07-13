<?php

namespace IGD;

defined( 'ABSPATH' ) || exit;

class Stream {

	protected static $instance = null;
	private $client;
	private $file;
	private $account_id;

	public function __construct( $file ) {
		$this->file       = $file;
		$this->client     = Client::instance( $file['accountId'] )->get_client();
		$this->account_id = ! empty( $file['accountId'] ) ? $file['accountId'] : '';

		wp_using_ext_object_cache( false );
	}

	public function stream_content() {
		if ( function_exists( 'apache_setenv' ) ) {
			@apache_setenv( 'no-gzip', 1 );
		}

		@ini_set( 'zlib.output_compression', 'Off' );
		@session_write_close();
		wp_ob_end_flush_all();

		igd_set_time_limit( 0 );

		$fileSize = $this->file['size']; // Assuming you have the file size
		$start    = 0;
		$end      = $fileSize - 1;

		$access_token = json_decode( $this->client->getAccessToken() )->access_token;

		// Initial headers
		$headers = [
			"Authorization: Bearer $access_token",
		];

		// Check for the HTTP_RANGE header
		if ( isset( $_SERVER['HTTP_RANGE'] ) ) {
			$range = $_SERVER['HTTP_RANGE'];
			$range = preg_replace( '/^bytes=/', '', $range );
			list( $start, $end ) = explode( '-', $range, 2 );
			$start = max( 0, $start );
			$end   = $end ? min( $end, $fileSize - 1 ) : $fileSize - 1;
			header( 'HTTP/1.1 206 Partial Content' );
			header( "Content-Range: bytes $start-$end/$fileSize" );
		} else {
			header( 'HTTP/1.1 200 OK' );
		}

		header( "Content-Type: " . $this->file['type'] );
		header( "Content-Length: " . ( $end - $start + 1 ) );
		header( "Accept-Ranges: bytes" );

		// Using cURL to handle the Range request
		$ch = curl_init();
		curl_setopt( $ch, CURLOPT_URL, $this->get_api_url() );
		curl_setopt( $ch, CURLOPT_RANGE, "$start-$end" );
		curl_setopt( $ch, CURLOPT_HTTPHEADER, $headers );
		curl_setopt( $ch, CURLOPT_RETURNTRANSFER, false ); // Directly output the data
		curl_setopt( $ch, CURLOPT_BINARYTRANSFER, true ); // For binary data
		curl_setopt( $ch, CURLOPT_HEADER, false );

		// Execute the request
		curl_exec( $ch );
		$statusCode = curl_getinfo( $ch, CURLINFO_HTTP_CODE );

		// Close the cURL session
		curl_close( $ch );

		if ( $statusCode != 206 && $statusCode != 200 ) {
			// Handle error; HTTP request failed
			header( 'HTTP/1.1 500 Internal Server Error' );

			return false;
		}
	}

	public function get_api_url() {
		return 'https://www.googleapis.com/drive/v3/files/' . $this->file['id'] . '?alt=media';
	}

	/**
	 * Returns an instance of this class.
	 *
	 * @param array $file File information array.
	 *
	 * @return Stream|null The instance of the class.
	 */
	public static function instance( $file ) {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self( $file );
		}

		return self::$instance;
	}
}
