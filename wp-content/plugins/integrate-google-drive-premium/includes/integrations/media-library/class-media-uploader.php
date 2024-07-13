<?php

namespace IGD;

defined( 'ABSPATH' ) || exit();

class Media_Uploader {

	private static $instance = null;

	public $client;
	public $app;
	public $account_id;

	public function __construct( $account_id = null ) {
		$this->account_id = $account_id;
		$this->client     = Client::instance( $account_id )->get_client();
		$this->app        = App::instance( $account_id );
	}

	public function do_attachment_upload( $attachment_id, $folder_id ) {
		$name        = get_the_title( $attachment_id );
		$file_type   = get_post_mime_type( $attachment_id );
		$file_path   = get_attached_file( $attachment_id );
		$description = get_post_field( 'post_content', $attachment_id );

		$file_size = 0;

		if ( ! file_exists( $file_path ) ) {
			// Get metadata for the attachment
			$metadata = wp_get_attachment_metadata( $attachment_id );

			if ( $metadata && isset( $metadata['sizes'] ) ) {
				foreach ( $metadata['sizes'] as $size => $size_info ) {
					$file_path = dirname( $file_path ) . '/' . $size_info['file'];

					if ( file_exists( $file_path ) ) {
						$file_size = filesize( $file_path );
						break;
					}
				}
			} else {
				return false;
			}
		}

		try {
			$file_size = filesize( $file_path );

			// Get Resume URI
			$url = Uploader::instance( $this->account_id )->get_resume_url( [
				'name'        => $name,
				'description' => $description,
				'size'        => $file_size,
				'type'        => $file_type,
				'folderId'    => $folder_id,
			] );

			// Create a media file upload to represent our upload process
			$chunkSizeBytes = 50 * 1024 * 1024;

			// Upload the file in chunks
			$handle   = fopen( $file_path, 'rb' );
			$position = 0; // Keep track of the current position in the file

			while ( ! feof( $handle ) ) {
				set_time_limit( 60 );
				$chunk     = fread( $handle, $chunkSizeBytes );
				$chunkSize = strlen( $chunk ); // Size of the chunk we read

				$session = curl_init( $url ); // Initialize a cURL session with the resume URL

				// Set the necessary options for the cURL request
				curl_setopt( $session, CURLOPT_CUSTOMREQUEST, 'PUT' );
				curl_setopt( $session, CURLOPT_POSTFIELDS, $chunk );
				curl_setopt( $session, CURLOPT_RETURNTRANSFER, true );
				curl_setopt( $session, CURLOPT_HTTPHEADER, [
					'Content-Type: ' . $file_type,
					'Content-Length: ' . $chunkSize,
					'Content-Range: bytes ' . $position . '-' . ( $position + $chunkSize - 1 ) . '/' . $file_size,
				] );

				// Execute the cURL request
				$response = curl_exec( $session );
				$httpCode = curl_getinfo( $session, CURLINFO_HTTP_CODE );

				if ( $httpCode < 200 || $httpCode > 299 ) {
					// Handle error, the upload failed
					error_log( 'Failed to upload chunk: HTTP ' . $httpCode . ' - ' . curl_error( $session ) );
					fclose( $handle );
					curl_close( $session );

					return false;
				}

				// Update the position for the next chunk
				$position += $chunkSize;

				curl_close( $session ); // Close the cURL session
			}

			fclose( $handle ); // Close the file handle


			// Check if the response contains the file details
			if ( $response ) {
				return json_decode( $response, true );
			}

		} catch ( \Exception $ex ) {
			error_log( $ex->getMessage() );

			return false;
		}

		return true;
	}

	public function do_file_upload( $folder_id ) {

		$error_messages = [
			1                     => esc_html__( 'The uploaded file exceeds the upload_max_filesize directive in php.ini', 'integrate-google-drive' ),
			2                     => esc_html__( 'The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form', 'integrate-google-drive' ),
			3                     => esc_html__( 'The uploaded file was only partially uploaded', 'integrate-google-drive' ),
			4                     => esc_html__( 'No file was uploaded', 'integrate-google-drive' ),
			6                     => esc_html__( 'Missing a temporary folder', 'integrate-google-drive' ),
			7                     => esc_html__( 'Failed to write file to disk', 'integrate-google-drive' ),
			8                     => esc_html__( 'A PHP extension stopped the file upload', 'integrate-google-drive' ),
			'post_max_size'       => esc_html__( 'The uploaded file exceeds the post_max_size directive in php.ini', 'integrate-google-drive' ),
			'max_file_size'       => esc_html__( 'File is too big', 'integrate-google-drive' ),
			'min_file_size'       => esc_html__( 'File is too small', 'integrate-google-drive' ),
			'accept_file_types'   => esc_html__( 'Filetype not allowed', 'integrate-google-drive' ),
			'max_number_of_files' => esc_html__( 'Maximum number of files exceeded', 'integrate-google-drive' ),
			'max_width'           => esc_html__( 'Image exceeds maximum width', 'integrate-google-drive' ),
			'min_width'           => esc_html__( 'Image requires a minimum width', 'integrate-google-drive' ),
			'max_height'          => esc_html__( 'Image exceeds maximum height', 'integrate-google-drive' ),
			'min_height'          => esc_html__( 'Image requires a minimum height', 'integrate-google-drive' ),
		];

		if ( empty( $_FILES ) || empty( $_FILES['async-upload'] ) ) {
			return [
				'error' => esc_html__( 'No files to upload', 'integrate-google-drive' ),
			];
		}

		$file = $_FILES['async-upload'];

		if ( $file['error'] ) {
			return [
				'error' => $error_messages[ $file['error'] ] ?? esc_html__( 'Unknown error', 'integrate-google-drive' ),
			];
		}

		$file_path = $file['tmp_name'];
		$file_name = $file['name'];
		$file_type = $file['type'];
		$file_size = $file['size'];

		$chunkSizeBytes = 50 * 1024 * 1024;

		// Get Resume URI
		$url = Uploader::instance( $this->account_id )->get_resume_url( [
			'name'     => $file_name,
			'size'     => $file_size,
			'type'     => $file_type,
			'folderId' => $folder_id,
		] );

		// Upload the file in chunks
		$handle   = fopen( $file_path, 'rb' );
		$position = 0; // Keep track of the current position in the file

		while ( ! feof( $handle ) ) {
			set_time_limit( 60 );
			$chunk     = fread( $handle, $chunkSizeBytes );
			$chunkSize = strlen( $chunk ); // Size of the chunk we read

			$session = curl_init( $url ); // Initialize a cURL session with the resume URL

			// Set the necessary options for the cURL request
			curl_setopt( $session, CURLOPT_CUSTOMREQUEST, 'PUT' );
			curl_setopt( $session, CURLOPT_POSTFIELDS, $chunk );
			curl_setopt( $session, CURLOPT_RETURNTRANSFER, true );
			curl_setopt( $session, CURLOPT_HTTPHEADER, [
				'Content-Type: ' . $file_type,
				'Content-Length: ' . $chunkSize,
				'Content-Range: bytes ' . $position . '-' . ( $position + $chunkSize - 1 ) . '/' . $file_size,
			] );

			// Execute the cURL request
			$response = curl_exec( $session );
			$httpCode = curl_getinfo( $session, CURLINFO_HTTP_CODE );

			if ( $httpCode < 200 || $httpCode > 299 ) {
				// Handle error, the upload failed
				error_log( 'Failed to upload chunk: HTTP ' . $httpCode . ' - ' . curl_error( $session ) );
				fclose( $handle );
				curl_close( $session );

				return false;
			}

			// Update the position for the next chunk
			$position += $chunkSize;

			curl_close( $session ); // Close the cURL session
		}

		fclose( $handle ); // Close the file handle

		// return teh file if the response contains the file details
		if ( $response ) {
			return json_decode( $response, true );
		}

	}

	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self;
		}

		return self::$instance;
	}

}