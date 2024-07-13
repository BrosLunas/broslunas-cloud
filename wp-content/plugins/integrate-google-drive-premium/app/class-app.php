<?php

namespace IGD;

defined( 'ABSPATH' ) || exit();


class App {

	public static $instance = null;

	public $client;
	public $service;
	public $account_id = null;

	public $file_fields = 'capabilities(canEdit,canRename,canDelete,canShare,canTrash,canMoveItemWithinDrive),shared,starred,sharedWithMeTime,description,fileExtension,iconLink,id,driveId,imageMediaMetadata(height,rotation,width,time),mimeType,createdTime,modifiedTime,name,ownedByMe,parents,size,thumbnailLink,trashed,videoMediaMetadata(height,width,durationMillis),webContentLink,webViewLink,exportLinks,permissions(id,type,role,domain),copyRequiresWriterPermission,shortcutDetails,resourceKey';
	public $list_fields = 'files(capabilities(canEdit,canRename,canDelete,canShare,canTrash,canMoveItemWithinDrive),shared,starred,sharedWithMeTime,description,fileExtension,iconLink,id,driveId,imageMediaMetadata(height,rotation,width,time),mimeType,createdTime,modifiedTime,name,ownedByMe,parents,size,thumbnailLink,trashed,videoMediaMetadata(height,width,durationMillis),webContentLink,webViewLink,exportLinks,permissions(id,type,role,domain),copyRequiresWriterPermission,shortcutDetails,resourceKey),nextPageToken';

	public function __construct( $account_id = null ) {
		if ( empty( $account_id ) ) {
			$account    = Account::instance()->get_active_account();
			$account_id = ! empty( $account ) ? $account['id'] : $account_id;
		}

		$this->account_id = $account_id;

		$this->client = Client::instance( $this->account_id )->get_client();

		if ( ! class_exists( 'IGDGoogle_Service_Drive' ) ) {
			require_once IGD_PATH . '/vendors/Google-sdk/src/Google/Service/Drive.php';
		}

		$this->service = new \IGDGoogle_Service_Drive( $this->client );
	}

	/**
	 * Get files from Google Drive
	 *
	 * @return array
	 */
	public function get_files( $args = [] ) {

		$default_args = [
			'folder'      => [],
			'sort'        => [
				'sortBy'        => 'name',
				'sortDirection' => 'asc'
			],
			'from_server' => false,
			'orderBy'     => "folder,name",
			'filters'     => [],
		];

		$args = wp_parse_args( $args, $default_args );

		// Set root folder as default folder if no folder is set and no search query
		if ( empty( $args['folder'] ) && ! $this->is_search_query( $args['q'] ) ) {
			$args['folder'] = [
				'id'         => 'root',
				'accountId'  => $this->account_id,
				'pageNumber' => 1,
			];
		}

		$from_server = $args['from_server'];
		$sort        = $args['sort'];

		$folder_id         = ! empty( $args['folder'] ) ? $args['folder']['id'] : '';
		$folder_account_id = ! empty( $args['folder']['accountId'] ) ? $args['folder']['accountId'] : $this->account_id;

		if ( ! empty( $args['folder']['shortcutDetails'] ) ) {
			$folder_id = $args['folder']['shortcutDetails']['targetId'];
		}

		$limit                = ! empty( $args['limit'] ) ? $args['limit'] : 0;
		$page_number          = ! empty( $args['folder']['pageNumber'] ) ? $args['folder']['pageNumber'] : 1;
		$start_index          = $page_number > 0 ? ( $page_number - 1 ) * $limit : 0;
		$filters              = ! empty( $args['filters'] ) ? $args['filters'] : [];
		$files_number_to_show = ! empty( $args['fileNumbers'] ) ? $args['fileNumbers'] : 0;

		$data = [
			'nextPageNumber' => 0,
		];

		if ( $from_server || ! igd_is_cached_folder( $folder_id, $folder_account_id ) ) {

			if ( 'shared-drives' == $folder_id ) {
				$files = $this->get_shared_drives( $folder_account_id );
			} else {
				$params = [
					'fields'                    => $this->list_fields,
					'pageSize'                  => 300,
					'orderBy'                   => ! empty( $args['orderBy'] ) ? $args['orderBy'] : "",
					'pageToken'                 => '',
					'supportsAllDrives'         => true,
					'includeItemsFromAllDrives' => true,
					'corpora'                   => 'allDrives',
				];

				if ( ! empty( $args['q'] ) ) {
					$params['q'] = $args['q'];
				} elseif ( 'computers' == $folder_id ) {
					$params['q'] = "'me' in owners and mimeType='application/vnd.google-apps.folder' and trashed=false";
				} elseif ( 'shared' == $folder_id ) {
					$params['q'] = "sharedWithMe=true and trashed=false";
				} elseif ( 'starred' == $folder_id ) {
					$params['q'] = "starred=true and trashed=false";
				} else {
					$params['q'] = "trashed=false and '$folder_id' in parents";
				}

				$files = [];

				do {
					try {
						$response            = $this->service->files->listFiles( $params );
						$page_token          = ! empty( $response->getNextPageToken() ) ? $response->getNextPageToken() : '';
						$params['pageToken'] = $page_token;

						$items = $response->getFiles();

						if ( ! empty( $items ) ) {
							foreach ( $items as $item ) {
								$files[] = igd_file_map( $item, $folder_account_id );
							}
						}

					} catch ( \Exception $e ) {
						$data['error'] = __( 'Server error', 'integrate-google-drive' ) . ' - ' . __( 'Couldn\'t connect to the Google drive API server.', 'integrate-google-drive' );
					}
				} while ( ! empty( $page_token ) );

			}

			if ( empty( $files ) ) {
				$data['files'] = [];

				return $data;
			}

			// Filter files
			if ( igd_should_filter_files( $filters ) ) {
				$files = array_values( array_filter(
					$files,
					function ( $item ) use ( $filters ) {
						return igd_should_allow( $item, $filters );
					}
				) );
			}

			$files = $this->sort_and_insert_files( $files, $sort, $folder_id, $folder_account_id );
			$count = count( $files );

			$data['count'] = $count;

			if ( ! empty( $limit ) && ! empty( $count ) ) {
				if ( $count > $limit ) {
					$files = array_slice( $files, $start_index, $limit );
				}
			}

		} else {

			// Get files from cache
			list( $files, $count ) = Files::get( $folder_id, $folder_account_id, $sort, $start_index, $limit, $filters );

			$data['count'] = $count;
		}

		// Check limit number
		if ( ! empty( $limit ) && ! empty( $count ) ) {
			if ( $count > $limit ) {
				$data['nextPageNumber'] = $page_number + 1;
			}
		}

		// Check max number of files to show
		if ( $files_number_to_show > 0 ) {

			if ( ( $page_number * $limit ) > $files_number_to_show ) {
				$files_number_to_show   = $files_number_to_show - ( ( $page_number - 1 ) * $limit );
				$files                  = array_slice( $files, 0, $files_number_to_show );
				$data['nextPageNumber'] = 0;
			}

			if ( $count > $files_number_to_show ) {
				$data['count'] = $files_number_to_show;
			}
		}

		$data['files'] = $files;

		return $data;
	}

	public function sort_and_insert_files( $files, $sort, $folder_id, $folder_account_id ) {

		// If folder is computers, filter files without parents and not shared
		if ( 'computers' == $folder_id ) {
			$files = array_filter( $files, function ( $file ) {
				return empty( $file['parents'] ) && empty( $file['shared'] );
			} );
		}

		// Sort files
		$files = igd_sort_files( $files, $sort );

		// Reformat shortcuts
		$files = $this->reformat_shortcuts( $files );

		// Insert files to database
		if ( $folder_id ) {
			Files::set( $files, $folder_id );
			igd_update_cached_folders( $folder_id, $folder_account_id );
		}

		return $files;
	}

	/**
	 * Add iconLink, thumbnailLink and metaData to shortcuts
	 *
	 * @param $files
	 *
	 * @return mixed
	 */

	public function reformat_shortcuts( $files ) {
		array_walk( $files, function ( &$file ) {
			if ( ! empty( $file['shortcutDetails'] ) && ! igd_is_dir( $file ) ) {
				$original_file = $this->get_file_by_id( $file['shortcutDetails']['targetId'] );

				$file['iconLink']      = $original_file['iconLink'];
				$file['thumbnailLink'] = $original_file['thumbnailLink'];

				if ( ! empty( $original_file['metaData'] ) ) {
					$file['metaData'] = $original_file['metaData'];
				}

			}
		} );

		return $files;
	}

	public function get_shared_drives( $folder_account_id = null ) {

		$params = [
			'fields'    => 'kind,nextPageToken,drives(kind,id,name,capabilities,backgroundImageFile,backgroundImageLink,createdTime,hidden)',
			'pageSize'  => 100,
			'pageToken' => '',
		];

		// Get all files in folder
		$files = [];

		do {
			try {
				$response            = $this->service->drives->listDrives( $params );
				$items               = $response->getDrives();
				$page_token          = ! empty( $response->getNextPageToken() ) ? $response->getNextPageToken() : '';
				$params['pageToken'] = $page_token;

				if ( ! empty( $items ) ) {

					foreach ( $items as $drive ) {
						$file = igd_drive_map( $drive, $folder_account_id );

						$files[] = $file;
					}
				}

			} catch ( \Exception $ex ) {
				error_log( $ex->getMessage() );

				return [];
			}
		} while ( ! empty( $page_token ) );


		return $files;

	}

	public function get_search_files( $keyword, $folders = [], $sort = [], $full_text_search = true ) {
		$keyword = str_replace( [ "\'", '\"' ], [ "'", '"' ], $keyword );

		$files = [];

		$look_in_to = [];
		if ( ! empty( $folders ) ) {

			foreach ( $folders as $key => $folder ) {

				if ( in_array( $folder['id'], [
					'root',
					'computers',
					'shared-drives',
					'shared',
					'starred'
				] ) ) {
					continue;
				}


				// Skip if shared drives
				if ( ! empty( $folder['parents'] ) && in_array( 'shared-drives', $folder['parents'] ) ) {
					continue;
				}

				// Skip if not a folder
				if ( ! igd_is_dir( $folder ) ) {
					continue;
				}

				// Get target ID from shortcut folder
				if ( ! empty( $folder['shortcutDetails'] ) ) {
					$folder_id       = $folder['shortcutDetails']['targetId'];
					$folder          = $this->get_file_by_id( $folder_id );
					$folders[ $key ] = $folder;
				}

				$look_in_to[] = $folder['id'];

				$child_folders     = igd_get_all_child_folders( $folder );
				$child_folders_ids = wp_list_pluck( $child_folders, 'id' );
				$look_in_to        = array_merge( $look_in_to, $child_folders_ids );
			}
		}

		// Filter files if files parents is in look_in_to
		$args = array(
			'fields'      => $this->list_fields,
			'pageSize'    => 1000,
			'orderBy'     => "",
			'q'           => "fullText contains '{$keyword}' and trashed = false",
			'from_server' => true,
			'sort'        => [
				'sortBy'        => 'name',
				'sortDirection' => 'asc'
			],
		);

		if ( ! empty( $sort ) ) {
			$args['sort'] = $sort;
		}

		if ( ! $full_text_search ) {
			$args['q']       = "name contains '{$keyword}' and trashed = false";
			$args['orderBy'] = 'folder,name'; // Order by not supported in fullText search
		}

		$data  = $this->get_files( $args );
		$files = array_merge( $files, $data['files'] ?? [] );

		if ( ! empty( $look_in_to ) ) {
			$files = array_filter( $files, function ( $file ) use ( $look_in_to ) {
				return ! empty( $file['parents'] ) && in_array( $file['parents'][0], $look_in_to );
			} );
		}

		// Insert log
		do_action( 'igd_insert_log', 'search', $keyword, $this->account_id );

		return array_values( $files );

	}

	public function is_search_query( $args ) {
		if ( empty( $args['q'] ) ) {
			return false;
		}

		$keyword = $args['q'];

		if ( strpos( $keyword, 'fullText contains' ) !== false ) {
			return true;
		}

		if ( strpos( $keyword, 'name contains' ) !== false ) {
			return true;
		}

		return false;
	}

	/**
	 * Get file item by file id
	 *
	 * @param $id
	 *
	 * @return array|false|mixed|void
	 */
	public function get_file_by_id( $id, $from_server = false ) {

		// Get cache file
		if ( ! $from_server ) {
			$file = Files::get_file_by_id( $id );
		}


		// If no cache file then get file from server
		if ( empty( $file ) || $from_server ) {
			try {
				$item = $this->service->files->get( $id, [
					'supportsAllDrives' => true,
					'fields'            => $this->file_fields,
				] );

				// Skip errors if folder is not found
				if ( ! is_object( $item ) || ! method_exists( $item, 'getId' ) || $item->trashed ) {
					do_action( 'igd_trash_detected', $id, $this->account_id );

					return false;
				}

				$file = igd_file_map( $item, $this->account_id );

				// Add file to cache
				Files::add_file( $file );

			} catch ( \Exception $e ) {
				return false;
			}
		}

		return $file;
	}

	public function get_file_by_name( $name, $parent_folder = '', $from_server = false ) {
		$folder_id = isset( $parent_folder['id'] ) ? $parent_folder['id'] : $parent_folder;

		$file = ! $from_server ? Files::get_file_by_name( $name, $folder_id ) : null;

		if ( empty( $file ) || $from_server ) {
			$args = [
				'fields'   => $this->list_fields,
				'pageSize' => 1,
				'q'        => "name = '{$name}' and trashed = false ",
			];

			if ( ! empty( $folder_id ) ) {
				$args['q'] .= " and '{$folder_id}' in parents";
			}

			try {
				$response = $this->service->files->listFiles( $args );

				if ( ! method_exists( $response, 'getFiles' ) ) {
					return false;
				}

				$files = $response->getFiles();
			} catch ( \Exception $e ) {
				return false;
			}

			if ( empty( $files ) ) {
				return false;
			}

			$item = $files[0];

			// Check if file is in trash
			if ( $item->trashed ) {
				do_action( 'igd_trash_detected', $item->id, $this->account_id );

				return false;
			}

			$file = igd_file_map( $item, $this->account_id );

			Files::add_file( $file );
		}

		return $file;
	}


	/**
	 * Create new folder
	 *
	 * @param $folder_name
	 * @param $parent_folder array | string
	 *
	 * @return array
	 */
	public function new_folder( $folder_name, $parent_id ) {

		if ( empty( $parent_id ) ) {
			$parent_id = 'root';
		}

		$params = [
			'fields'            => $this->file_fields,
			'supportsAllDrives' => true,
		];

		$request = $this->getService()->files->create( new \IGDGoogle_Service_Drive_DriveFile( [
			'name'     => $folder_name,
			'parents'  => [ $parent_id ],
			'mimeType' => 'application/vnd.google-apps.folder'
		] ), $params );

		// add new folder to cache
		$item = igd_file_map( $request, $this->account_id );

		Files::add_file( $item, $parent_id );

		// Insert log
		do_action( 'igd_insert_log', 'folder', $item['id'], $this->account_id );

		return $item;
	}

	/**
	 * Move Files
	 *
	 * @param $file_ids
	 * @param $new_parent_id
	 *
	 * @return string|void
	 */
	public function move_file( $file_ids, $new_parent_id = null ) {

		if ( empty( $new_parent_id ) ) {
			$new_parent_id = 'root';
		}

		try {

			$emptyFileMetadata = new \IGDGoogle_Service_Drive_DriveFile();

			if ( ! empty( $file_ids ) ) {
				foreach ( $file_ids as $file_id ) {
					// Retrieve the existing parents to remove
					$file = $this->get_file_by_id( $file_id );

					$previousParents = join( ',', $file['parents'] );

					// Move the file to the new folder
					$file = $this->service->files->update( $file_id, $emptyFileMetadata, array(
						'addParents'    => $new_parent_id,
						'removeParents' => $previousParents,
						'fields'        => $this->file_fields,
					) );

					//Update cached file
					if ( $file->getId() ) {
						Files::update_file(
							[
								'parent_id' => $new_parent_id,
								'data'      => serialize( igd_file_map( $file, $this->account_id ) ),
							],
							[ 'id' => $file_id ]
						);
					}

					// Insert log
					do_action( 'igd_insert_log', 'move', $file_id, $this->account_id );
				}
			}

		} catch ( \Exception $e ) {
			return "An error occurred: " . $e->getMessage();
		}
	}

	/**
	 * Rename file
	 *
	 * @param $name
	 * @param $file_id
	 *
	 * @return \IGDGoogle_Http_Request|\IGDGoogle_Service_Drive_DriveFile|string
	 */
	public function rename( $name, $file_id ) {
		try {

			$file_meta_data = new \IGDGoogle_Service_Drive_DriveFile();
			$file_meta_data->setName( $name );

			// Move the file to the new folder
			$file = $this->service->files->update( $file_id, $file_meta_data, array(
				'fields' => $this->file_fields,
			) );

			// Update cached file
			if ( $file->getId() ) {
				Files::update_file( [
					'name' => $name,
					'data' => serialize( igd_file_map( $file, $this->account_id ) ),
				], [ 'id' => $file_id ] );
			}

			// Insert log
			do_action( 'igd_insert_log', 'rename', $file_id, $this->account_id );

			return $file;
		} catch ( \Exception $e ) {
			return "An error occurred: " . $e->getMessage();
		}
	}

	/**
	 * Rename multiple files on form submit
	 *
	 * @param $files
	 *
	 * @return array|string
	 */
	public function rename_files( $files ) {

		try {
			$this->client->setUseBatch( true );
			$batch = new \IGDGoogle_Http_Batch( $this->client );

			foreach ( $files as $file ) {
				$name    = $file['name'];
				$file_id = $file['id'];


				$file_met_data = new \IGDGoogle_Service_Drive_DriveFile();
				$file_met_data->setName( $name );

				// Move the file to the new folder
				$batch->add( $this->service->files->update( $file_id, $file_met_data, array( 'fields' => $this->file_fields, ) ) );

			}

			$batch_result = $batch->execute();
			$this->client->setUseBatch( false );

			$renamed_files = [];
			foreach ( $batch_result as $file ) {
				if ( ! empty( $file->getId() ) ) {
					$file = igd_file_map( $file, $this->account_id );
					Files::update_file( [
						'name' => $file['name'],
						'data' => serialize( $file ),
					], [ 'id' => $file['id'] ] );

					$renamed_files[] = $file;
				}
			}

			return $renamed_files;

		} catch ( \Exception $e ) {
			return "An error occurred: " . $e->getMessage();
		}

	}

	public function update_description( $file_id, $description ) {
		try {

			$file = new \IGDGoogle_Service_Drive_DriveFile();
			$file->setDescription( $description );

			// Move the file to the new folder
			$update_file = $this->service->files->update( $file_id, $file, array(
				'fields' => $this->file_fields,
			) );

			// Update cached file
			if ( $update_file->getId() ) {
				Files::update_file( [
					'data' => serialize( igd_file_map( $update_file, $this->account_id ) ),
				], [ 'id' => $file_id ] );
			}

			// Insert log
			do_action( 'igd_insert_log', 'description', $file_id, $this->account_id );

			return $update_file;
		} catch ( \Exception $e ) {
			return "An error occurred: " . $e->getMessage();
		}
	}

	public function copy( $files, $parent_id = null ) {

		try {
			$this->client->setUseBatch( true );

			$batch          = new \IGDGoogle_Http_Batch( $this->client );
			$file_meta_data = new \IGDGoogle_Service_Drive_DriveFile();

			foreach ( $files as $file ) {

				$file_meta_data->setName( 'Copy of ' . $file['name'] );

				if ( ! empty( $parent_id ) ) {
					$file_meta_data->setParents( [ $parent_id ] );
				}

				$batch->add( $this->service->files->copy( $file['id'], $file_meta_data, [ 'fields' => $this->file_fields ] ) );
			}

			$batch_result = $batch->execute();

			$copied_files = [];
			foreach ( $batch_result as $file ) {
				if ( ! empty( $file->getId() ) ) {
					$file = igd_file_map( $file, $this->account_id );
					Files::add_file( $file );

					$copied_files[] = $file;


					// Insert log
					do_action( 'igd_insert_log', 'copy', $file['id'], $this->account_id );
				}
			}

			$this->client->setUseBatch( false );

			return $copied_files;

		} catch ( \Exception $e ) {
			$this->client->setUseBatch( false );

			return "An error occurred: " . $e->getMessage();
		}
	}

	public function copy_folder( $folder, $parent_id ) {

		if ( empty( $folder ) || empty( $parent_id ) ) {
			return false;
		}


		$args = [
			'folder' => $folder,
		];

		$data  = $this->get_files( $args );
		$files = ! empty( $data['files'] ) ? $data['files'] : [];

		if ( empty( $files ) ) {
			return false;
		}

		$batch          = new \IGDGoogle_Http_Batch( $this->client );
		$batch_requests = 0;
		$this->client->setUseBatch( true );


		foreach ( $files as $file ) {

			if ( igd_is_dir( $file ) ) {
				//Create new folder in parent folder
				$new_folder = new \IGDGoogle_Service_Drive_DriveFile();
				$new_folder->setName( $file['name'] );
				$new_folder->setMimeType( 'application/vnd.google-apps.folder' );
				$new_folder->setParents( [ $parent_id ] );

				$batch->add( $this->service->files->create( $new_folder, [
					'fields'            => $this->file_fields,
					'supportsAllDrives' => true
				] ), $file['id'] );
			} else {
				// Copy file to new folder
				$new_file = new \IGDGoogle_Service_Drive_DriveFile();
				$new_file->setName( $file['name'] );
				$new_file->setParents( [ $parent_id ] );

				$batch->add( $this->service->files->copy( $file['id'], $new_file, [
					'fields'            => $this->file_fields,
					'supportsAllDrives' => true
				] ), $file['id'] );
			}

			++ $batch_requests;
		}

		// Execute the Batch Call
		try {
			usleep( 20000 * $batch_requests );
			@set_time_limit( 30 );

			$batch_result = $batch->execute();
		} catch ( \Exception $ex ) {
			error_log( '[Integrate Google Drive Message]: ' . sprintf( 'API Error on line %s: %s', __LINE__, $ex->getMessage() ) );

			return false;
		}

		$this->client->setUseBatch( false );

		foreach ( $batch_result as $key => $file ) {

			$file = igd_file_map( $file, $this->account_id );
			Files::add_file( $file );

			if ( igd_is_dir( $file ) ) {
				$original_id   = str_replace( 'response-', '', $key );
				$original_file = array_filter( $files, function ( $item ) use ( $original_id ) {
					return $item['id'] == $original_id;
				} );
				$original_file = array_shift( $original_file );
				$new_id        = $file['id'];

				$this->copy_folder( $original_file, $new_id );
			}
		}
	}

	/**
	 * Delete files
	 *
	 * @param $file_ids
	 *
	 * @return string|void
	 */
	public function delete( $file_ids ) {
		try {
			$this->client->setUseBatch( true );

			$batch = new \IGDGoogle_Http_Batch( $this->client );

			foreach ( $file_ids as $file_id ) {
				Files::delete( [ 'id' => $file_id ] );

				do_action( 'igd_insert_log', 'delete', $file_id, $this->account_id );
				do_action( 'igd_delete_file', $file_id, $this->account_id );

				$meta_data = new \IGDGoogle_Service_Drive_DriveFile( [
					'trashed' => true,
				] );

				$batch->add( $this->service->files->update( $file_id, $meta_data ) );
			}

			$batch->execute();

		} catch ( \Exception $e ) {
			return "An error occurred: " . $e->getMessage();
		}
	}


	/**
	 * Google Drive Service Instance
	 *
	 * @return \IGDGoogle_Service_Drive
	 */
	public function getService() {
		return $this->service;
	}

	/**
	 * Render File Browser
	 */
	public static function view() { ?>
        <div id="igd-app" class="igd-app"></div>
		<?php
	}

	public static function instance( $account_id = null ) {
		if ( is_null( self::$instance ) || self::$instance->account_id != $account_id ) {
			self::$instance = new self( $account_id );
		}

		return self::$instance;
	}

}