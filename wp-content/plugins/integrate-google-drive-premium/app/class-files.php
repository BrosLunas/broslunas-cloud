<?php

namespace IGD;

defined( 'ABSPATH' ) || exit();


class Files {

	public static function get_table() {
		global $wpdb;

		return $wpdb->prefix . 'integrate_google_drive_files';
	}

	public static function get( $parent_id, $account_id, $sort, $start_index, $limit = '', $filters = [] ) {
		global $wpdb;

		$table = self::get_table();

		$where = [];

		if ( 'computers' == $parent_id ) {
			$where['is_computers'] = 1;
		} elseif ( 'shared-drives' == $parent_id ) {
			$where['is_shared_drive'] = 1;
		} elseif ( 'shared' == $parent_id ) {
			$where['is_shared_with_me'] = 1;
		} elseif ( 'starred' == $parent_id ) {
			$where['is_starred'] = 1;
		} else {
			$where['parent_id'] = $parent_id;
		}

		$where['account_id'] = $account_id;

		$where_placeholders = '';
		$where_values       = [];

		if ( empty( $sort ) ) {
			$sort = [ 'sortBy' => 'name', 'sortDirection' => 'asc' ];
		}

		$order_by = "ORDER BY (type = 'application/vnd.google-apps.folder') DESC ";

		$order_by .= 'random' == $sort['sortBy'] ? ", RAND() " : ", {$sort['sortBy']} " . strtoupper( $sort['sortDirection'] );

		if ( ! empty( $limit ) ) {
			$order_by .= " LIMIT $start_index, $limit";
		}

		foreach ( $where as $key => $value ) {
			$where_placeholders .= " AND $key=%s";
			$where_values[]     = $value;
		}

		// Filters
		$needs_filter = igd_should_filter_files( $filters );

		if ( $needs_filter ) {

			// Show or Hide Folders/Files based on filters
			if ( isset( $filters['showFiles'] ) && empty( $filters['showFiles'] ) ) {
				$where_placeholders .= " AND type = 'application/vnd.google-apps.folder'";
			}

			if ( isset( $filters['showFolders'] ) && empty( $filters['showFolders'] ) ) {
				$where_placeholders .= " AND type <> 'application/vnd.google-apps.folder'";
			}

			// Handle Extension Filters
			if ( ! empty( $filters['allowAllExtensions'] ) ) {
				if ( ! empty( $filters['allowExceptExtensions'] ) ) {
					$extensions         = explode( ',', $filters['allowExceptExtensions'] );
					$where_placeholders .= " AND (extension NOT IN (" . implode( ', ', array_fill( 0, count( $extensions ), '%s' ) ) . ") OR type = 'application/vnd.google-apps.folder' )";
					$where_values       = array_merge( $where_values, $extensions );
				}
			} else {
				if ( ! empty( $filters['allowExtensions'] ) ) {
					$extensions         = explode( ',', $filters['allowExtensions'] );
					$where_placeholders .= " AND (extension IN (" . implode( ', ', array_fill( 0, count( $extensions ), '%s' ) ) . ") OR type = 'application/vnd.google-apps.folder' )";
					$where_values       = array_merge( $where_values, $extensions );
				}
			}

			// Handle Name Filters
			$nameFilterOptions = $filters['nameFilterOptions'] ?? [];

			if ( in_array( 'files', $nameFilterOptions ) || in_array( 'folders', $nameFilterOptions ) ) {

				if ( ! empty( $filters['allowAllNames'] ) ) {

					if ( ! empty( $filters['allowExceptNames'] ) ) {
						$names = array_map( 'trim', explode( ',', $filters['allowExceptNames'] ) );

						foreach ( $names as $name ) {
							$name = str_replace( '*', '%', $name );  // replace '*' with '%'
							$name = str_replace( '?', '_', $name );  // replace '?' with '_'

							if ( in_array( 'files', $nameFilterOptions ) && ! in_array( 'folders', $nameFilterOptions ) ) {
								$where_placeholders .= " AND (type = 'application/vnd.google-apps.folder' OR name NOT LIKE %s)";
							} elseif ( in_array( 'folders', $nameFilterOptions ) && ! in_array( 'files', $nameFilterOptions ) ) {
								$where_placeholders .= " AND (type <> 'application/vnd.google-apps.folder' OR name NOT LIKE %s)";
							} else {
								$where_placeholders .= " AND name NOT LIKE %s";
							}

							$where_values[] = $name;
						}
					}

				} else {
					if ( ! empty( $filters['allowNames'] ) ) {
						$names = array_map( 'trim', explode( ',', $filters['allowNames'] ) );

						foreach ( $names as $name ) {
							$name = str_replace( '*', '%', $name );  // replace '*' with '%'
							$name = str_replace( '?', '_', $name );  // replace '?' with '_'

							if ( in_array( 'files', $nameFilterOptions ) && ! in_array( 'folders', $nameFilterOptions ) ) {
								$where_placeholders .= " AND (type = 'application/vnd.google-apps.folder' OR name LIKE %s)";
							} elseif ( in_array( 'folders', $nameFilterOptions ) && ! in_array( 'files', $nameFilterOptions ) ) {
								$where_placeholders .= " AND (type <> 'application/vnd.google-apps.folder' OR name LIKE %s)";
							} else {
								$where_placeholders .= " AND name LIKE %s";
							}

							$where_values[] = $name;
						}
					}
				}
			}

			// Handle Gallery
			if ( ! empty( $filters['isGallery'] ) ) {
				$where_placeholders .= " AND (type LIKE 'image/%' OR type LIKE 'video/%' OR type = 'application/vnd.google-apps.folder')";
			}

			// Handle Media
			if ( ! empty( $filters['isMedia'] ) ) {
				$where_placeholders .= " AND (type LIKE 'audio/%' OR type LIKE 'video/%' OR type = 'application/vnd.google-apps.folder')";
			}
		}

		// Create a SQL query to  fetch the count of total files applied filters
		$count_sql = "SELECT COUNT(*) FROM `$table` WHERE 1 $where_placeholders";
		$count     = $wpdb->get_var( $wpdb->prepare( $count_sql, $where_values ) );

		if ( ! $count ) {
			$count = false;
		}

		// Create the final SQL
		$sql = $wpdb->prepare( "SELECT data FROM `$table` WHERE 1 $where_placeholders $order_by", $where_values );

		$items = $wpdb->get_results( $sql, ARRAY_A );

		if ( empty( $items ) ) {
			$count = 0;
		}

		$files = [];

		if ( ! empty( $items ) ) {
			foreach ( $items as $item ) {
				$files[] = unserialize( $item['data'] );
			}
		}

		return [ $files, $count ];
	}

	/**
	 * Set files
	 *
	 * @param $files
	 * @param $folder_id
	 *
	 * @return void
	 */
	public static function set( $files, $folder_id = '' ) {

		if ( ! empty( $files ) ) {
			foreach ( $files as $file ) {
				self::add_file( $file, $folder_id );
			}
		}
	}

	/**
	 * Get cached file by ID
	 *
	 * @param $id
	 *
	 * @return false|mixed
	 */
	public static function get_file_by_id( $id ) {
		global $wpdb;

		$table = self::get_table();

		$sql  = $wpdb->prepare( "SELECT data FROM `$table` WHERE id = %s", $id );
		$item = $wpdb->get_row( $sql, ARRAY_A );

		return ! empty( $item['data'] ) ? unserialize( $item['data'] ) : false;
	}

	public static function get_file_by_name( $name, $folder_id = '' ) {
		global $wpdb;

		$table = self::get_table();

		$sql = $wpdb->prepare( "SELECT data FROM `$table` WHERE name = %s AND parent_id = %s", $name, $folder_id );

		$item = $wpdb->get_row( $sql, ARRAY_A );

		return ! empty( $item['data'] ) ? unserialize( $item['data'] ) : false;
	}

	/**
	 * @param $file
	 * @param $folder
	 *
	 * @return void
	 */
	public static function add_file( $file, $folder_id = '' ) {

		if ( $folder_id && ! empty( $file['parents'] ) && $folder_id != $file['parents'][0] ) {
			$folder_id = $file['parents'][0];
		} elseif ( ! $folder_id && ! empty( $file['parents'] ) ) {
			$folder_id = $file['parents'][0];
		}

		global $wpdb;

		$table = self::get_table();


		$sql = "REPLACE INTO `$table` (id, name, size, parent_id, account_id, type, extension, data, created, updated, is_computers, is_shared_with_me, is_starred, is_shared_drive) 
		VALUES (%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%d,%d,%d,%d )";

		$is_computers      = 'computers' == $folder_id;
		$is_shared_with_me = 'shared' == $folder_id || ! empty( $file['sharedWithMeTime'] );
		$is_starred        = 'starred' == $folder_id || ! empty( $file['starred'] );
		$is_shared_drive   = ! empty( $file['shared-drives'] );
		$parent_id         = ! empty( $file['parents'] ) ? $file['parents'][0] : '';
		$extension         = ! empty( $file['extension'] ) ? $file['extension'] : '';

		$type = $file['type'];
		if ( ! empty( $file['shortcutDetails'] ) ) {
			$type = $file['shortcutDetails']['targetMimeType'];
		}

		$size    = ! empty( $file['size'] ) ? $file['size'] : '';
		$updated = ! empty( $file['updated'] ) ? $file['updated'] : '';

		$values = [
			$file['id'],
			$file['name'],
			$size,
			$parent_id,
			$file['accountId'],
			$type,
			$extension,
			serialize( $file ),
			$file['created'],
			$updated,
			$is_computers,
			$is_shared_with_me,
			$is_starred,
			$is_shared_drive,
		];

		$wpdb->query( $wpdb->prepare( $sql, $values ) );
	}

	/**
	 * @return void
	 */
	public static function delete_account_files( $account_id ) {
		global $wpdb;

		$table = self::get_table();

		$where = [
			'account_id' => $account_id,
		];

		$where_format = [ '%s' ];

		if ( ! empty( $folder_id ) ) {
			$where['parent_id'] = $folder_id;
			$where_format[]     = '%s';
		}

		$wpdb->delete( $table, $where, $where_format );
	}

	public static function delete_folder_files( $folder_ids ) {
		global $wpdb;

		// If it's a single ID, put it in an array
		if ( ! is_array( $folder_ids ) ) {
			$folder_ids = array( $folder_ids );
		}

		// Create a string for the placeholders
		$placeholders = implode( ', ', array_fill( 0, count( $folder_ids ), '%s' ) );

		// Prepare the query
		$query = $wpdb->prepare( "DELETE FROM " . self::get_table() . " WHERE parent_id IN ($placeholders)", $folder_ids );

		// Execute the query
		$wpdb->query( $query );
	}

	/**
	 * @param $data
	 * @param $where
	 * @param $format
	 * @param $where_format
	 *
	 * @return void
	 */
	public static function update_file( $data, $where, $format = [], $where_format = [] ) {
		global $wpdb;

		$table = self::get_table();

		$wpdb->update( $table, $data, $where, $format, $where_format );

	}

	/**
	 * @param $where
	 * @param $where_format
	 *
	 * @return void
	 */
	public static function delete( $where, $where_format = null ) {
		global $wpdb;

		$table = self::get_table();

		$wpdb->delete( $table, $where, $where_format );
	}

}