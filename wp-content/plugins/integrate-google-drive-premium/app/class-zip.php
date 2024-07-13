<?php

namespace IGD;

use ZipStream\Option\Archive;
use ZipStream\Option\File;
use ZipStream\ZipStream;

defined( 'ABSPATH' ) || exit();

class Zip {
	/**
	 * @var null
	 */
	protected static $instance = null;

	/**
	 * Google API Client
	 *
	 * @var \Exception|false|\IGDGoogle_Client|mixed
	 */
	private $client;

	/**
	 * Download files
	 *
	 * @var
	 */
	private $files;
	private $zip_handler;
	private $file_name;

	private $request_id;
	private $action;
	private $status;
	private $downloaded = 0;
	private $total = 0;

	public function __construct( $files, $request_id ) {
		$this->files      = $files;
		$this->request_id = $request_id;

		$account_id = ! empty( $files[0]['accountId'] ) ? $files[0]['accountId'] : Account::instance()->get_active_account()['id'];

		$this->client = Client::instance( $account_id )->get_client();

		if ( ! class_exists( 'ZipStream' ) ) {
			require_once IGD_PATH . '/vendors/ZipStream/vendor/autoload.php';
		}
	}

	/**
	 * Start ZIP Download Proces
	 */
	public function do_zip() {
		$this->start();
		$this->create_zip_handler();
		$this->list_files();
		$this->end();

		exit();
	}

	public function start() {
		ignore_user_abort( false );

		$this->file_name = "drive-download-" . time() . ".zip";

		$this->status = esc_html__( 'Preparing Files...', 'integrate-google-drive' );
		$this->set_progress();

		// Stop WP from buffering
		wp_ob_end_flush_all();
	}

	public function create_zip_handler() {

		$options = new Archive();
		$options->setSendHttpHeaders( true );
		$options->setFlushOutput( true );
		$options->setContentType( 'application/octet-stream' );
		header( 'X-Accel-Buffering: no' );

		// create a new zip-stream object
		$this->zip_handler = new ZipStream( $this->file_name, $options );

		$this->action = 'indexing';
		$this->set_progress();
	}

	public function list_files() {
		$files   = [];
		$folders = [];

		if ( ! empty( $this->files ) ) {
			foreach ( $this->files as $file ) {
				$list = igd_get_files_recursive( $file );

				$files       = array_merge( $files, $list['files'] );
				$folders     = array_merge( $folders, $list['folders'] );
				$this->total += $list['size'];
			}
		}

		//Add folders
		$this->add_folders( $folders );

		//Add files
		$this->add_files( $files );
	}

	public function add_folders( $folders ) {
		if ( ! empty( $folders ) ) {
			foreach ( $folders as $folder ) {
				$this->zip_handler->addFile( $folder, '' );
			}
		}
	}

	public function add_files( $files ) {

		if ( ! empty( $files ) ) {
			foreach ( $files as $key => $file ) {
				$this->add_file_to_zip( $file );

				$this->action = 'downloading';
				$this->status = esc_html__( 'Downloading Files...', 'integrate-google-drive' );
				$this->set_progress();
			}
		}
	}

	public function add_file_to_zip( $file ) {
		@set_time_limit( 0 );

		$download_stream = fopen( 'php://temp/maxmemory:' . ( 5 * 1024 * 1024 ), 'r+' );

		$request = new \IGDGoogle_Http_Request( $file['downloadLink'], 'GET' );

		$this->client->getIo()->setOptions( [
			CURLOPT_FOLLOWLOCATION => true,
			CURLOPT_RETURNTRANSFER => false,
			CURLOPT_FILE           => $download_stream,
			CURLOPT_HEADER         => false,
			CURLOPT_CONNECTTIMEOUT => 900,
			CURLOPT_TIMEOUT        => 900,
		] );

		try {
			$this->client->getAuth()->authenticatedRequest( $request );
			curl_close( $this->client->getIo()->getHandler() );
		} catch ( \Exception $exception ) {
			fclose( $download_stream );
			error_log( 'Integrate Google Drive - Error: ' . sprintf( 'API Error on line %s: %s', __LINE__, $exception->getMessage() ) );

			return;
		}

		rewind( $download_stream );

		$this->downloaded += $file['size'];

		$fileOptions = new File();

		if ( ! empty( $file['updated'] ) ) {
			$date = new \DateTime();
			$date->setTimestamp( strtotime( $file['updated'] ) );
			$fileOptions->setTime( $date );
		}

		if ( ! empty( $file['description'] ) ) {
			$fileOptions->setComment( (string) $file['description'] );
		}

		try {
			$this->zip_handler->addFileFromStream( trim( $file['path'], '/' ), $download_stream, $fileOptions );

		} catch ( \Exception $exception ) {
			error_log( 'Integrate Google Drive - Error: ' . sprintf( 'Error creating ZIP file %s: %s', __LINE__, $exception->getMessage() ) );

			$this->action = 'failed';
			$this->status = esc_html__( 'Error creating ZIP file', 'integrate-google-drive' );
			$this->set_progress();

			exit();
		}

		fclose( $download_stream );
	}

	public function end() {
		$this->zip_handler->finish();

		delete_transient( 'igd_download_status_' . $this->request_id );
	}

	public function set_progress() {
		$status = [
			'downloaded' => $this->downloaded,
			'total'      => $this->total,
			'status'     => $this->status,
			'action'     => $this->action,
		];

		// Update progress
		return set_transient( 'igd_download_status_' . $this->request_id, $status, HOUR_IN_SECONDS );
	}

	/**
	 * @return Zip|null
	 */
	public static function instance( $files, $request_id ) {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self( $files, $request_id );
		}

		return self::$instance;
	}

}