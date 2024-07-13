<?php

namespace IGD;

defined( 'ABSPATH' ) || exit;

class Media_Library {

	private static $instance = null;

	public function __construct() {

		// Enqueue Scripts
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
		add_action( 'wp_enqueue_media', array( $this, 'enqueue_scripts' ) );

		// Filter Attachments
		add_filter( 'pre_get_posts', [ $this, 'filter_grid_attachments_query' ] );
		add_filter( 'pre_get_posts', [ $this, 'filter_list_attachments_query' ] );

		add_filter( 'wp_prepare_attachment_for_js', [ $this, 'filter_attachment_data' ], 99, 3 );
		add_filter( 'wp_get_attachment_url', [ $this, 'filter_attachment_url' ], 999, 2 );
		add_filter( 'wp_get_attachment_image_src', [ $this, 'filter_image_src' ], 10, 4 );
		add_filter( 'wp_calculate_image_srcset_meta', [ $this, 'calculate_image_srcset_meta' ], 10, 4 );

		// add replace, restore, import, upload buttons
		add_filter( 'attachment_fields_to_edit', [ $this, 'attachment_fields_to_edit' ], 10, 2 );

		add_filter( 'igd_localize_data', [ $this, 'localize_data' ], 10, 2 );

		// Insert attachment after upload
		add_action( 'igd_upload_post_process', [ $this, 'insert_attachment' ] );

		// delete attachment after file delete
		add_action( 'igd_delete_file', [ $this, 'delete_linked_attachment' ] );

		// Delete attachment on trash detected of get_file
		add_action( 'igd_trash_detected', [ $this, 'delete_linked_attachment' ] );

		// Handle attachment delete
		add_action( 'delete_attachment', [ $this, 'delete_linked_file' ] );

		// Sync cloud files
		add_action( 'igd_sync_interval', [ $this, 'sync_attachments' ] );

		// Handle Google Drive attachment insert
		add_filter( 'image_send_to_editor', array( $this, 'media_send_to_editor' ), 10, 8 );

		// Override video shortcode
		add_filter( 'wp_video_shortcode_override', [ $this, 'override_video_shortcode' ], 100, 4 );

		// Add svg mime type
		add_filter( 'upload_mimes', array( $this, 'add_svgs_upload_mimes' ) );
		add_filter( 'wp_check_filetype_and_ext', array( $this, 'svgs_upload_check' ), 10, 4 );
		add_filter( 'wp_check_filetype_and_ext', array( $this, 'svgs_allow_svg_upload' ), 10, 4 );

		// Handle Ajax
		add_action( 'wp_ajax_igd_media_get_parent_folders', array( $this, 'get_parent_folders' ) );

		// Upload attachment to Google Drive
		add_action( 'wp_ajax_igd_media_upload_attachment', array( $this, 'upload_attachment' ) );

		// Upload files to Google Drive
		add_action( 'wp_ajax_igd_media_upload_file', array( $this, 'upload_file' ) );

		// Replace attachment
		add_action( 'wp_ajax_igd_media_replace_attachment', array( $this, 'replace_attachment' ) );

		// Restore attachment
		add_action( 'wp_ajax_igd_media_restore_attachment', array( $this, 'restore_attachment' ) );

		// Add refresh button in the media library list view
		add_action( 'restrict_manage_posts', [ $this, 'add_refresh_button' ], 10, 2 );
	}

	public function filter_grid_attachments_query( $query ) {

		if ( ! isset( $query->query_vars['post_type'] ) || $query->query_vars['post_type'] !== 'attachment' ) {
			return $query;
		}

		if ( empty( $_REQUEST['query'] ) ) {
			return $query;
		}

		$folder_id  = ! empty( $_REQUEST['query']['folder_id'] ) ? sanitize_text_field( $_REQUEST['query']['folder_id'] ) : '';
		$account_id = ! empty( $_REQUEST['query']['account_id'] ) ? sanitize_key( $_REQUEST['query']['account_id'] ) : '';
		$is_refresh = ! empty( $_REQUEST['query']['is_refresh'] ) && filter_var( $_REQUEST['query']['is_refresh'], FILTER_VALIDATE_BOOLEAN );

		$meta_query = $query->get( 'meta_query' ) ?: [];

		if ( igd_user_can_access( 'media_library' ) && ! empty( $folder_id ) ) {
			// Check if the folder files are inserted
			if ( $is_refresh || ! $this->is_folder_inserted( $folder_id, $account_id ) ) {
				$this->sync_folder_attachments( $folder_id, $account_id, $is_refresh );
			}

			// Set the meta query
			$meta_query[] = [
				'key'     => '_igd_media_folder_id',
				'value'   => $folder_id,
				'compare' => '='
			];

			$meta_query[] = [
				'key'     => '_igd_media_replace_id',
				'compare' => 'NOT EXISTS'
			];

		} else {
			$meta_query[] = [
				'relation' => 'OR',
				[
					'key'     => '_igd_media_folder_id',
					'compare' => 'NOT EXISTS',
				],
				[
					'key'     => '_igd_media_replace_id',
					'compare' => 'EXISTS',
				],
			];
		}

		$query->set( 'meta_query', $meta_query );

		return $query;
	}

	public function filter_list_attachments_query( $query ) {

		if ( ! isset( $query->query_vars['post_type'] ) || $query->query_vars['post_type'] !== 'attachment' ) {
			return $query;
		}

		global $pagenow, $current_screen;

		if ( $pagenow !== 'upload.php' || ! isset( $current_screen ) || $current_screen->base !== 'upload' ) {
			return $query;
		}

		if ( empty( $_REQUEST['folder_id'] ) ) {
			return $query;
		}

		$folder_id  = base64_decode( $_REQUEST['folder_id'] );
		$account_id = ! empty( $_REQUEST['account_id'] ) ? base64_decode( $_REQUEST['account_id'] ) : '';
		$is_refresh = ! empty( $_REQUEST['is_refresh'] ) && filter_var( $_REQUEST['is_refresh'], FILTER_VALIDATE_BOOLEAN );

		$meta_query = $query->get( 'meta_query' ) ?: [];

		if ( 'media' == $folder_id ) {
			$meta_query[] = [
				'relation' => 'OR',
				[
					'key'     => '_igd_media_folder_id',
					'compare' => 'NOT EXISTS',
				],
				[
					'key'     => '_igd_media_replace_id',
					'compare' => 'EXISTS',
				],
			];
		} elseif ( 'drive' == $folder_id ) {

			$media_folders = igd_get_settings( 'mediaLibraryFolders', [] );

			if ( ! empty( $media_folders ) ) {

				$files = array_filter( $media_folders, function ( $item ) use ( $account_id ) {
					return igd_is_dir( $item ) && $item['accountId'] == $account_id;
				} );

				$file_ids = array_map( function ( $file ) {
					return $file['id'];
				}, $files );

				$meta_query[] = [
					'key'     => '_igd_media_file_id',
					'value'   => $file_ids,
					'compare' => 'IN'
				];
			}

		} else if ( igd_user_can_access( 'media_library' ) ) {

			// Check if the folder files are inserted
			if ( $is_refresh || ! $this->is_folder_inserted( $folder_id, $account_id ) ) {
				$this->sync_folder_attachments( $folder_id, $account_id, $is_refresh );
			}

			// Set the meta query
			$meta_query[] = [
				'key'     => '_igd_media_folder_id',
				'value'   => $folder_id,
				'compare' => '='
			];

			$meta_query[] = [
				'key'     => '_igd_media_replace_id',
				'compare' => 'NOT EXISTS'
			];

		} else {
			$meta_query[] = [
				'relation' => 'OR',
				[
					'key'     => '_igd_media_folder_id',
					'compare' => 'NOT EXISTS',
				],
				[
					'key'     => '_igd_media_replace_id',
					'compare' => 'EXISTS',
				],
			];
		}

		$query->set( 'meta_query', $meta_query );

		return $query;
	}

	public function add_refresh_button( $post_type, $which ) {

		if ( 'attachment' !== $post_type ) {
			return;
		}

		$scr = get_current_screen();
		if ( empty( $scr ) || $scr->base !== 'upload' ) {
			return;
		}

		$folder_id  = ! empty( $_GET['folder_id'] ) ? base64_decode( sanitize_text_field( $_GET['folder_id'] ) ) : '';
		$account_id = ! empty( $_GET['account_id'] ) ? base64_decode( sanitize_key( $_GET['account_id'] ) ) : '';

		if ( empty( $folder_id ) || empty( $account_id ) ) {
			return;
		}

		$current_url = remove_query_arg( 'is_refresh' );
		$refresh_url = add_query_arg( 'is_refresh', true, $current_url );

		?>
        <a href="<?php echo $refresh_url; ?>" class="button igd-media-refresh-button">
			<?php _e( 'Refresh', 'integrate-google-drive' ); ?>
        </a>
		<?php

	}

	public function upload_file() {

		//Check if current user can upload files
		if ( ! current_user_can( 'upload_files' ) ) {
			wp_send_json_error( 'You don\'t have permission to upload files.' );
		}

		if ( ! class_exists( 'Media_Uploader' ) ) {
			require_once( IGD_INCLUDES . '/integrations/media-library/class-media-uploader.php' );
		}

		$folder_id  = ! empty( $_REQUEST['folder_id'] ) ? sanitize_text_field( $_REQUEST['folder_id'] ) : '';
		$account_id = ! empty( $_REQUEST['account_id'] ) ? sanitize_key( $_REQUEST['account_id'] ) : '';

		$uploader = new Media_Uploader( $account_id );

		$uploaded_file = $uploader->do_file_upload( $folder_id );

		if ( ! empty( $uploaded_file['error'] ) ) {
			wp_send_json_error( $uploaded_file['error'] );
		}

		$formatted_file = Uploader::instance( $account_id )->upload_post_process( $uploaded_file );

		$attachment_id = $this->insert_attachment( $formatted_file, $folder_id );

		$attachment = wp_prepare_attachment_for_js( $attachment_id );
		if ( ! $attachment ) {
			wp_die();
		}

		wp_send_json_success( $attachment );

	}

	public function restore_attachment() {
		$attachment_id = ! empty( $_POST['attachment_id'] ) ? intval( $_POST['attachment_id'] ) : '';

		delete_post_meta( $attachment_id, '_igd_media_replace_id' );
		delete_post_meta( $attachment_id, '_igd_media_file_id' );
		delete_post_meta( $attachment_id, '_igd_media_folder_id' );
		delete_post_meta( $attachment_id, '_igd_media_account_id' );

		wp_send_json_success();
	}

	public function replace_attachment() {
		$old_attachment_id = ! empty( $_POST['old_attachment_id'] ) ? intval( $_POST['old_attachment_id'] ) : '';
		$new_attachment_id = ! empty( $_POST['new_attachment_id'] ) ? intval( $_POST['new_attachment_id'] ) : '';

		// Validate attachment IDs
		if ( ! wp_get_attachment_metadata( $old_attachment_id ) || ! wp_get_attachment_metadata( $new_attachment_id ) ) {
			wp_send_json_error( 'Invalid attachment ID(s).' );
		}

		$google_drive_file_id = get_post_meta( $new_attachment_id, '_igd_media_file_id', true );

		update_post_meta( $old_attachment_id, '_igd_media_replace_id', $google_drive_file_id );
		update_post_meta( $old_attachment_id, '_igd_media_file_id', $google_drive_file_id );

		wp_send_json_success();
	}

	public function upload_attachment() {

		// check if current user can upload files
		if ( ! current_user_can( 'upload_files' ) ) {
			wp_send_json_error( 'You don\'t have permission to upload files.' );
		}

		if ( ! class_exists( 'Media_Uploader' ) ) {
			require_once( IGD_INCLUDES . '/integrations/media-library/class-media-uploader.php' );
		}

		$attachment_ids = ! empty( $_POST['file_ids'] ) ? igd_sanitize_array( $_POST['file_ids'] ) : [];
		$folder         = ! empty( $_POST['folder'] ) ? igd_sanitize_array( $_POST['folder'] ) : [];

		$folder_id  = $folder['id'];
		$account_id = $folder['accountId'];

		$uploader = new Media_Uploader( $account_id );

		$uploaded_attachment = [];
		foreach ( $attachment_ids as $attachment_id ) {
			$uploaded_attachment[] = $uploader->do_attachment_upload( $attachment_id, $folder_id );
		}

		wp_send_json_success( $uploaded_attachment );
	}

	public function get_parent_folders() {
		if ( empty( $_POST['folder'] ) ) {
			return;
		}

		$folder = igd_sanitize_array( $_POST['folder'] );

		$folders = $this->igd_get_grouped_parent_folders( $folder );

		wp_send_json_success( $folders );
	}

	public function igd_get_grouped_parent_folders( $file, &$groupedFolders = [] ) {
		$app = App::instance( $file['accountId'] );

		// Check if file has parents
		if ( ! empty( $file['parents'] ) ) {
			foreach ( $file['parents'] as $parent_id ) {
				$parent_folder = $app->get_file_by_id( $parent_id );

				// Check if retrieved parent folder is indeed a directory
				if ( igd_is_dir( $parent_folder ) ) {

					// Initialize group for this parent if it doesn't exist
					if ( ! isset( $groupedFolders[ $parent_id ] ) ) {
						$groupedFolders[ $parent_id ] = [];
					}

					// Add to group if not already added
					if ( ! in_array( $parent_folder, $groupedFolders[ $parent_id ] ) ) {
						$groupedFolders[ $parent_id ]['folder']   = $parent_folder;
						$groupedFolders[ $parent_id ]['children'] = array_filter( igd_get_child_items( $parent_folder ), 'igd_is_dir' );
					}

					// Recursively get parents of the parent folder
					$this->igd_get_grouped_parent_folders( $parent_folder, $groupedFolders );

				}
			}
		}

		return $groupedFolders;
	}

	public function enqueue_scripts( $hook ) {

		if ( ! igd_user_can_access( 'media_library' ) ) {
			return;
		}

		// Enqueue IGD scripts
		Enqueue::instance()->admin_scripts( '', false );

		wp_enqueue_style( 'igd-media-library', IGD_ASSETS . '/css/media-library.css', array(), IGD_VERSION );

		wp_enqueue_media();
		wp_enqueue_script( 'igd-media-library', IGD_ASSETS . '/js/media-library.js', array(
			'jquery',
			'wp-element',
			'wp-util',
			'plupload',
		), IGD_VERSION, true );
	}

	public function calculate_image_srcset_meta( $image_meta, $size_array, $image_src, $attachment_id ) {
		$file_id = get_post_meta( $attachment_id, '_igd_media_file_id', true );

		if ( ! empty( $file_id ) ) {
			if ( empty( $image_meta['sizes']['full']['width'] ) ) {

				$account_id = get_post_meta( $attachment_id, '_igd_media_account_id', true );
				$file       = App::instance( $account_id )->get_file_by_id( $file_id );

				$link = igd_get_embed_url( $file, 'readOnly', true );

				$image_meta['sizes']['full']['width']  = $image_meta['width'] ?? 60;
				$image_meta['sizes']['full']['height'] = $image_meta['height'] ?? 60;
				$image_meta['sizes']['full']['file']   = $link;
			}
		}

		return $image_meta;
	}

	public function attachment_fields_to_edit( $form_fields, $post ) {

		if ( ! igd_user_can_access( 'media_library' ) ) {
			return $form_fields;
		}

		$post_id = $post->ID;

		$file_id = get_post_meta( $post_id, '_igd_media_file_id', true );


		if ( empty( $file_id ) ) {
			// Replace with Google Drive
			$form_fields['igd_media_replace'] = array(
				'label' => '',
				'input' => 'html',
				'html'  => '<button type="button" class="button igd-media-action igd-media-action-replace" data-attachment_id="' . $post_id . '"><i class="dashicons dashicons-update-alt"></i>' . __( 'Replace', 'integrate-google-drive' ) . '</button>',
				'helps' => __( 'Replace the file with a Google Drive file', 'integrate-google-drive' ),
			);

			// Upload to google drive
			$form_fields['igd_media_upload'] = array(
				'label' => '',
				'input' => 'html',
				'html'  => '<button type="button" class="button igd-media-action igd-media-action-upload" data-attachment_id="' . $post_id . '"><i class="dashicons dashicons-upload"></i>' . __( 'Upload', 'integrate-google-drive' ) . '</button>',
				'helps' => __( 'Upload the file to Google Drive', 'integrate-google-drive' ),
			);
		} else {

			// Restore to original
			$replace_id = get_post_meta( $post_id, '_igd_media_replace_id', true );
			if ( ! empty( $replace_id ) ) {
				$form_fields['igd_media_restore'] = array(
					'label' => '',
					'input' => 'html',
					'html'  => '<button type="button" class="button igd-media-action igd-media-action-restore" data-attachment_id="' . $post_id . '"><i class="dashicons dashicons-image-rotate"></i>' . __( 'Restore', 'integrate-google-drive' ) . '</button>',
					'helps' => __( 'Restore the file to original media library file', 'integrate-google-drive' ),
				);
			}

			// Import to media library
			if ( empty( $replace_id ) ) {
				$form_fields['igd_media_import'] = array(
					'label' => '',
					'input' => 'html',
					'html'  => '<button type="button" class="button igd-media-action igd-media-action-import" data-attachment_id="' . $post_id . '"><i class="dashicons dashicons-download"></i>' . __( 'Import', 'integrate-google-drive' ) . '</button>',
					'helps' => __( 'Import the file to media library', 'integrate-google-drive' ),
				);
			}
		}

		return $form_fields;
	}

	public function sync_folder_attachments( $folder_id, $account_id, $is_refresh = false ) {

		// Insert the folder files
		$files_query = App::instance( $account_id )->get_files( [
			'folder'  => [
				'id'        => $folder_id,
				'accountId' => $account_id,
			],
			'refresh' => true,
		] );

		$files = $files_query['files'];

		if ( ! empty( $files ) ) {

			foreach ( $files as $file ) {

				// If the attachment is already exists or the file is a folder, skip it
				if ( $this->is_attachment_exists( $file['id'] ) || igd_is_dir( $file ) ) {
					continue;
				}

				$this->insert_attachment( $file, $folder_id );
			}

			// Delete the attachment that linked files are not in the folder anymore
			if ( $is_refresh ) {
				global $wpdb;

				// Prepare file IDs
				$file_ids     = wp_list_pluck( $files, 'id' );
				$prepared_ids = implode( ',', array_map( function ( $id ) use ( $wpdb ) {
					return $wpdb->prepare( '%s', $id );
				}, $file_ids ) );

				// SQL to get post IDs
				$sql_query = $wpdb->prepare(
					"SELECT pm1.post_id
						    FROM $wpdb->postmeta pm1
						    INNER JOIN $wpdb->postmeta pm2 ON pm1.post_id = pm2.post_id
						    WHERE pm1.meta_key = '_igd_media_file_id' AND pm1.meta_value NOT IN ($prepared_ids)
						    AND pm2.meta_key = '_igd_media_folder_id' AND pm2.meta_value = %s",
					$folder_id
				);

				$post_ids = $wpdb->get_col( $sql_query );

				if ( ! empty( $post_ids ) ) {
					// Prepare a string of comma-separated post IDs
					$post_ids_str = implode( ',', array_map( 'intval', $post_ids ) );

					// Single query to delete posts and postmeta
					$wpdb->query( "DELETE FROM $wpdb->posts WHERE ID IN ($post_ids_str)" );
					$wpdb->query( "DELETE FROM $wpdb->postmeta WHERE post_id IN ($post_ids_str)" );
				}

			}
		} else {
			global $wpdb;

			$query = $wpdb->prepare( "
				    DELETE p, pm
				    FROM $wpdb->posts p
				    JOIN $wpdb->postmeta pm ON p.ID = pm.post_id
				    WHERE p.ID IN (
				        SELECT * FROM (
				            SELECT pm1.post_id
				            FROM $wpdb->postmeta pm1
				            INNER JOIN $wpdb->postmeta pm2 ON pm1.post_id = pm2.post_id
				            WHERE pm1.meta_key = '_igd_media_folder_id' AND pm1.meta_value = %s
				        ) AS temp_table
				    )
				", $folder_id );

			$wpdb->query( $query );

		}

		$this->update_inserted_folder( $folder_id, $account_id );
	}

	public function filter_image_src( $image, $attachment_id, $size, $icon ) {
		$file_id = get_post_meta( $attachment_id, '_igd_media_file_id', true );

		if ( is_array( $image ) && ! empty( $file_id ) ) {
			$account_id    = get_post_meta( $attachment_id, '_igd_media_account_id', true );
			$file          = App::instance( $account_id )->get_file_by_id( $file_id );
			$has_thumbnail = ! empty( $file['thumbnailLink'] );
			$post_url      = igd_get_embed_url( $file, 'readOnly', true );

			if ( $size === 'full' ) {
				$image[0] = $post_url;
			} else {
				$thumb_url = igd_get_thumbnail_url( $file, 'custom', [
					'w' => $has_thumbnail ? $image[1] : 64,
					'h' => $has_thumbnail ? $image[2] : 64,
				] );

				$image[0] = $thumb_url;
			}
		}

		return $image;
	}

	public function filter_attachment_data( $response, $attachment, $meta ) {
		$file_id = get_post_meta( $attachment->ID, '_igd_media_file_id', true );

		if ( ! empty( $file_id ) ) {

			$account_id = get_post_meta( $attachment->ID, '_igd_media_account_id', true );
			$file       = App::instance( $account_id )->get_file_by_id( $file_id );

			if ( empty( $file ) ) {
				return $response;
			}

			$replace_id = get_post_meta( $attachment->ID, '_igd_media_replace_id', true );
			if ( ! empty( $replace_id ) ) {
				$response['igd_media_replace_id'] = $replace_id;
			}

			$response['google_drive_file'] = $file;

			$has_thumbnail = ! empty( $file['thumbnailLink'] );

			$response['url'] = igd_get_embed_url( $file, 'readOnly', true );

			$attached_file        = get_post_meta( $attachment->ID, '_wp_attached_file', true );
			$response['filename'] = basename( $attached_file );

			if ( empty( $replace_id ) && ! empty( $meta['sizes'] ) && is_array( $meta['sizes'] ) ) {
				$sizes      = [];
				$upload_dir = wp_upload_dir();

				if ( ! empty( $meta['file'] ) ) {
					if ( file_exists( $upload_dir['basedir'] . '/' . $meta['file'] ) ) {
						unlink( $upload_dir['basedir'] . '/' . $meta['file'] );
					}
				}

				foreach ( $meta['sizes'] as $size => $size_info ) {

					if ( ! empty( $meta['file'] ) ) {
						$dir = dirname( $meta['file'] );

						if ( file_exists( $upload_dir['basedir'] . '/' . $dir . '/' . $size_info['file'] ) ) {
							unlink( $upload_dir['basedir'] . '/' . $dir . '/' . $size_info['file'] );
						}
					}

					if ( $size === 'full' ) {
						continue;
					}

					$thumb_url = igd_get_thumbnail_url( $file, 'custom', [
						'w' => $has_thumbnail ? $size_info['width'] : 64,
						'h' => $has_thumbnail ? $size_info['height'] : 64,
					] );

					if ( $size_info['width'] > 1 ) {
						$size_info['file'] = $thumb_url;
						$size_info['url']  = $thumb_url;
						$sizes[ $size ]    = $size_info;
					}

				}

				// Get full size
				$thumb_url = igd_get_thumbnail_url( $file, 'full' );

				$size_info           = array();
				$size_info['width']  = $meta['width'] ?? 100;
				$size_info['height'] = $meta['height'] ?? 100;

				$size_info['file'] = $thumb_url;
				$size_info['url']  = $thumb_url;
				$sizes['full']     = $size_info;

				$response['sizes'] = $sizes;

			} else {
				$sizes             = $this->render_metadata( $attachment->ID, $file );
				$response['sizes'] = $sizes;
			}

		} else {
			$response['igd_media_replace_id'] = false;
			$response['google_drive_file']    = false;
		}

		return $response;
	}

	public function filter_attachment_url( $url, $post_id ) {
		$file_id = get_post_meta( $post_id, '_igd_media_file_id', true );

		if ( ! empty( $file_id ) ) {
			$account_id = get_post_meta( $post_id, '_igd_media_account_id', true );
			$file       = App::instance( $account_id )->get_file_by_id( $file_id );

			if ( ! empty( $file ) ) {
				$url = igd_get_embed_url( $file, 'readOnly', true );
			}
		}

		return $url;
	}

	public function insert_attachment( $file, $folder_id = false ) {

		if ( empty( $folder_id ) ) {
			$folder_id = $file['parents'][0];
		}

		$name = html_entity_decode( $file['name'], ENT_COMPAT, 'UTF-8' );

		$upload_path = wp_upload_dir();
		$info        = pathinfo( $name );

		$link = igd_get_embed_url( $file, 'readOnly', true );

		$attachment = array(
			'guid'           => $link,
			'post_mime_type' => $file['type'],
			'post_title'     => $info['filename'],
			'post_author'    => get_current_user_id(),
			'post_type'      => 'attachment',
			'post_status'    => 'inherit'
		);

		$attachment_id = wp_insert_post( $attachment );

		$attached = trim( $upload_path['subdir'], '/' ) . '/' . $name;

		update_post_meta( $attachment_id, '_igd_media_folder_id', $folder_id );
		update_post_meta( $attachment_id, '_igd_media_file_id', $file['id'] );
		update_post_meta( $attachment_id, '_igd_media_account_id', $file['accountId'] );

		update_post_meta( $attachment_id, '_wp_attached_file', $attached );

		$meta = array();
		if ( strpos( $file['type'], 'image' ) !== false ) {

			if ( isset( $file['metaData']['width'] ) && isset( $file['metaData']['height'] ) ) {
				$meta['width']  = $file['metaData']['width'];
				$meta['height'] = $file['metaData']['height'];
			} else {
				list( $width, $height ) = igd_get_image_size( $link );

				$meta['width']  = $width;
				$meta['height'] = $height;
			}

			$meta['file'] = $attached;
		}

		if ( isset( $file['size'] ) ) {
			$meta['filesize'] = $file['size'];
		}

		$sizes         = $this->render_metadata( $attachment_id, $file );
		$meta['sizes'] = $sizes;

		update_post_meta( $attachment_id, '_wp_attachment_metadata', $meta );

		return $attachment_id;

	}

	public function is_folder_inserted( $folder_id, $account_id = null ) {
		$inserted_folders = get_option( 'igd_media_inserted_folders', [] );

		$is_inserted = isset( $inserted_folders[ $folder_id ] );

		if ( $is_inserted && in_array( $folder_id, [ 'root', 'shared-drives', 'computers', 'shared', 'starred', ] ) ) {

			$is_inserted = $inserted_folders[ $folder_id ]['accountId'] == $account_id;
		}

		return $is_inserted;
	}

	public function update_inserted_folder( $folder_id, $account_id ) {
		$inserted_folders               = get_option( 'igd_media_inserted_folders', [] );
		$inserted_folders[ $folder_id ] = [
			'id'        => $folder_id,
			'accountId' => $account_id,
		];

		update_option( 'igd_media_inserted_folders', $inserted_folders );
	}

	public function render_metadata( $attachment_id, $file ) {
		$has_thumbnail = ! empty( $file['thumbnailLink'] );
		$sizes         = array();

		$thumbnail_size_w = intval( get_option( 'thumbnail_size_w' ) );
		$thumbnail_size_h = intval( get_option( 'thumbnail_size_h' ) );
		$medium_size_w    = intval( get_option( 'medium_size_w' ) );
		$medium_size_h    = intval( get_option( 'medium_size_h' ) );
		$large_size_w     = intval( get_option( 'large_size_w' ) );
		$large_size_h     = intval( get_option( 'large_size_h' ) );

		if ( ! empty( $thumbnail_size_w ) && ! empty( $thumbnail_size_h ) ) {
			$size_info = array();

			$size_info['width']  = $has_thumbnail ? $thumbnail_size_w : 64;
			$size_info['height'] = $has_thumbnail ? $thumbnail_size_h : 64;

			$thumbnail = igd_get_thumbnail_url( $file, 'custom', [
				'w' => $thumbnail_size_w,
				'h' => $thumbnail_size_h,
			] );

			$size_info['file'] = $thumbnail;
			$size_info['url']  = $thumbnail;

			$sizes['thumbnail'] = $size_info;
		}

		if ( ! empty( $medium_size_w ) && ! empty( $medium_size_h ) ) {
			$size_info           = array();
			$size_info['width']  = $medium_size_w;
			$size_info['height'] = $medium_size_h;

			$thumbnail = igd_get_thumbnail_url( $file, 'custom', [
				'w' => $has_thumbnail ? $medium_size_w : 64,
				'h' => $has_thumbnail ? $medium_size_h : 64,
			] );

			$size_info['file'] = $thumbnail;
			$size_info['url']  = $thumbnail;

			$sizes['medium'] = $size_info;
		}

		if ( ! empty( $large_size_w ) && ! empty( $large_size_h ) ) {
			$size_info           = array();
			$size_info['width']  = $large_size_w;
			$size_info['height'] = $large_size_h;

			$thumbnail = igd_get_thumbnail_url( $file, 'custom', [
				'w' => $has_thumbnail ? $large_size_w : 64,
				'h' => $has_thumbnail ? $large_size_h : 64,
			] );

			$size_info['file'] = $thumbnail;
			$size_info['url']  = $thumbnail;

			$sizes['large'] = $size_info;
		}

		$thumb_url = igd_get_thumbnail_url( $file, 'full' );

		$size_info           = array();
		$size_info['width']  = $file['metaData']['width'] ?? 100;
		$size_info['height'] = $file['metaData']['height'] ?? 100;

		$size_info['file'] = $thumb_url;
		$size_info['url']  = $thumb_url;

		$sizes['full'] = $size_info;

		return $sizes;
	}

	public function add_svgs_upload_mimes( $mimes = array() ) {
		$mimes['svg']  = 'image/svg+xml';
		$mimes['svgz'] = 'image/svg+xml';
		$mimes['xlsm'] = 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet';

		return $mimes;
	}

	public function svgs_upload_check( $checked, $file, $filename, $mimes ) {
		if ( ! $checked['type'] ) {
			$check_filetype  = wp_check_filetype( $filename, $mimes );
			$ext             = $check_filetype['ext'];
			$type            = $check_filetype['type'];
			$proper_filename = $filename;

			if ( $type && 0 === strpos( $type, 'image/' ) && $ext !== 'svg' ) {
				$ext  = false;
				$type = false;
			}

			$checked = compact( 'ext', 'type', 'proper_filename' );
		}

		return $checked;
	}

	public function svgs_allow_svg_upload( $data, $file, $filename, $mimes ) {
		global $wp_version;
		if ( $wp_version !== '4.7.1' || $wp_version !== '4.7.2' ) {
			return $data;
		}

		$filetype = wp_check_filetype( $filename, $mimes );

		return array(
			'ext'             => $filetype['ext'],
			'type'            => $filetype['type'],
			'proper_filename' => $data['proper_filename']
		);
	}

	public function override_video_shortcode( $html, $atts, $content, $instance ) {
		$url = '';

		if ( ! empty( $atts['src'] ) ) {
			$url = $atts['src'];
		} elseif ( ! empty( $atts['mp4'] ) ) {
			$url = $atts['mp4'];
		}

		if ( strpos( $url, 'drive.google.com/uc?id' ) !== false ) {

			$parts = parse_url( $url );
			parse_str( $parts['query'], $query );

			if ( $url !== '' && isset( $query['id'] ) && $query['id'] !== '' ) {
				return '<iframe src="https://drive.google.com/file/d/' . $query['id'] . '/preview" width="' . $atts['width'] . '" height="' . $atts['height'] . '"></iframe>';
			}
		}

		return $html;
	}

	public function media_send_to_editor( $html, $id, $caption, $title, $align, $url, $size, $alt = '' ) {
		$post = get_post( $id );

		if ( in_array( $post->post_mime_type, array( 'image/jpg', 'image/png', 'image/jpeg', 'image/webp' ) ) ) {
			$file_id = get_post_meta( $id, '_igd_media_file_id', true );

			if ( ! empty( $file_id ) ) {
				$doc = new \DOMDocument();
				libxml_use_internal_errors( true );
				$sousce = mb_convert_encoding( $html, 'HTML-ENTITIES', 'UTF-8' );
				$doc->loadHTML( $sousce );
				$tags = $doc->getElementsByTagName( 'img' );
				if ( $tags->length > 0 ) {
					if ( ! empty( $tags ) ) {
						$width = $tags->item( 0 )->getAttribute( 'width' );

						$account_id = get_post_meta( $id, '_igd_media_account_id', true );
						$file       = App::instance( $account_id )->get_file_by_id( $file_id );

						$thumb_url = igd_get_embed_url( $file, $width, true );

						$tags->item( 0 )->setAttribute( 'src', $thumb_url );
					}
				}
				$html = $doc->saveHTML();
			}
		}

		return $html;
	}

	public function sync_attachments() {
		$syncType = igd_get_settings( 'syncType', 'all' );

		// Group folder by accountId
		$grouped_folders = [];

		if ( $syncType == 'selected' ) {
			$folders = igd_get_settings( 'syncFolders', [] );

			foreach ( $folders as $folder ) {
				$grouped_folders[ $folder['accountId'] ][] = $folder['id'];
			}

		} else {

			global $wpdb;

			$query = "SELECT DISTINCT pm2.meta_value AS folder_id, pm1.meta_value AS account_id
          FROM $wpdb->postmeta pm1
          INNER JOIN $wpdb->postmeta pm2 ON pm1.post_id = pm2.post_id
          WHERE pm1.meta_key = '_igd_media_account_id'
          AND pm2.meta_key = '_igd_media_folder_id'
          AND pm1.meta_value != '' 
          AND pm2.meta_value != ''";

			$folders = $wpdb->get_results( $query, ARRAY_A );

			$grouped_folders = array();
			foreach ( $folders as $folder ) {
				$grouped_folders[ $folder['account_id'] ][] = $folder['folder_id'];
			}

		}

		foreach ( $grouped_folders as $account_id => $folder_ids ) {
			foreach ( $folder_ids as $folder_id ) {
				$this->sync_folder_attachments( $folder_id, $account_id, true );
			}
		}

	}

	public function delete_linked_file( $attachment_id ) {
		$file_delete_enabled = igd_get_settings( 'deleteMediaCloudFile', false );

		if ( ! $file_delete_enabled ) {
			return;
		}

		$file_id = get_post_meta( $attachment_id, '_igd_media_file_id', true );

		if ( ! $file_id ) {
			return;
		}

		$cached_file = Files::get_file_by_id( $file_id );

		if ( ! $cached_file ) {
			return;
		}

		$account_id = get_post_meta( $attachment_id, '_igd_media_account_id', true );

		do_action( 'igd_insert_log', 'delete', $file_id, $account_id );

		Files::delete( [ 'id' => $file_id ] );

		try {

			$meta_data = new \IGDGoogle_Service_Drive_DriveFile( [
				'trashed' => true
			] );

			$service = App::instance( $account_id )->getService();

			$service->files->update( $file_id, $meta_data );

		} catch ( \Exception $e ) {
			error_log( 'Error while trashing file: ' . $e->getMessage() );
		}

	}

	/**
	 * Delete attachment and its associated postmeta on Google Drive file delete
	 *
	 * @param $file_id
	 *
	 * @return void
	 */
	public function delete_linked_attachment( $file_id ) {
		global $wpdb;

		// Query to delete the post and its associated postmeta
		$query = $wpdb->prepare( "
					    DELETE p, pm
					    FROM $wpdb->posts p
					    JOIN $wpdb->postmeta pm ON p.ID = pm.post_id
					    WHERE p.ID IN (
					        SELECT post_id 
					        FROM (
					            SELECT post_id 
					            FROM $wpdb->postmeta 
					            WHERE meta_key = '_igd_media_file_id' 
					            AND meta_value = %s
					        ) AS temp
					    )
					", $file_id );

		$wpdb->query( $query );
	}

	public function localize_data( $data, $script_handle ) {

		if ( 'frontend' == $script_handle ) {
			return $data;
		}

		global $pagenow;

		$data['pagenow'] = $pagenow;

		// check if media library page and list view
		if ( 'upload.php' == $pagenow ) {

			if ( ! empty( $_GET['folder_id'] ) && ! empty( $_GET['account_id'] ) ) {
				$account_id = base64_decode( sanitize_key( $_GET['account_id'] ) );
				$folder_id  = base64_decode( sanitize_text_field( $_GET['folder_id'] ) );

				if ( ! in_array( $folder_id, [ 'media', 'drive' ] ) ) {
					$folder = App::instance( $account_id )->get_file_by_id( $folder_id );

					if ( ! empty( $folder ) ) {
						$data['activeFolder'] = $folder;
					}
				}
			}

			// Add auth url on the media library page
			$data['authUrl'] = Client::instance()->get_auth_url();

		}

		return $data;
	}

	public function is_attachment_exists( $file_id ) {
		global $wpdb;

		$query = $wpdb->prepare( "SELECT post_id FROM $wpdb->postmeta WHERE meta_key = '_igd_media_file_id' AND meta_value = %s", $file_id );

		return $wpdb->get_var( $query );
	}

	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self;
		}

		return self::$instance;
	}

}

Media_Library::instance();