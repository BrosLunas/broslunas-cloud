<?php

namespace IGD;

defined( 'ABSPATH' ) || exit;

class Importer {
	/**
	 * @var null
	 */
	protected static $instance = null;

	const CHUNK_SIZE = 5 * 1024 * 1024; // 5MB

	public function __construct() {
		if ( ! function_exists( 'wp_generate_attachment_metadata' ) ) {
			require_once( ABSPATH . 'wp-admin/includes/image.php' );
			include_once( ABSPATH . 'wp-admin/includes/media.php' );
		}

		add_action( 'wp_ajax_igd_import_media', array( $this, 'import' ) );
	}

	public function import() {
		$files = ! empty( $_POST['files'] ) ? igd_sanitize_array( $_POST['files'] ) : [];

		if ( ! empty( $files ) ) {
			foreach ( $files as $file ) {
				$this->download_and_store_file_in_chunks( $file );
			}
		}

		wp_send_json_success( [
			'success' => true,
		] );
	}

	public function download_and_store_file_in_chunks( $file ) {
		$upload_dir = wp_upload_dir();

		$id         = $file['id'];
		$account_id = $file['accountId'];
		$name       = sanitize_file_name( $file['name'] );
		$type       = $file['type'];
		$extension  = igd_mime_to_ext( $type );

		// Check if the name already contains the extension
		if ( substr( strtolower( $name ), - strlen( $extension ) ) !== strtolower( $extension ) ) {
			$name .= '.' . $extension;
		}

		$file_path    = $upload_dir['path'] . '/' . $name;
		$download_url = admin_url( "admin-ajax.php?action=igd_download&id=$id&accountId=$account_id" );

		// Set a stream context with a lower timeout and larger buffer
		$context     = stream_context_create( [ 'http' => [ 'timeout' => 60 ] ] );
		$source      = fopen( $download_url, 'rb', false, $context );
		$destination = fopen( $file_path, 'wb' );

		if ( ! $source || ! $destination ) {
			return false;
		}

		// Read file in chunks and write them immediately to avoid memory exhaustion
		while ( ! feof( $source ) ) {
			$chunk = fread( $source, self::CHUNK_SIZE );
			fwrite( $destination, $chunk );
		}

		fclose( $source );
		fclose( $destination );

		// Create an attachment for the file
		$file_type = wp_check_filetype( basename( $file_path ) );

		$attachment = array(
			'guid'           => $upload_dir['url'] . '/' . basename( $file_path ),
			'post_mime_type' => $file_type['type'],
			'post_title'     => preg_replace( '/\.[^.]+$/', '', basename( $file_path ) ),
			'post_content'   => '',
			'post_status'    => 'inherit'
		);

		$attach_id = wp_insert_attachment( $attachment, $file_path );

		// You may need to include this file for the following function.
		require_once( ABSPATH . 'wp-admin/includes/image.php' );

		// Generate metadata and update the attachment.
		$attach_data = wp_generate_attachment_metadata( $attach_id, $file_path );
		wp_update_attachment_metadata( $attach_id, $attach_data );

		return $attach_id;
	}

	/**
	 * @return Importer|null
	 */
	public static function instance() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

}

Importer::instance();