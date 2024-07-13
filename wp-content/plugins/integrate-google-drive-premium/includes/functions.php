<?php

defined( 'ABSPATH' ) || exit();

use IGD\Account;
use IGD\App;
use IGD\Files;
use IGD\Integration;
use IGD\Permissions;
use IGD\Shortcode_Builder;
use IGD\Zip;

function igd_get_breadcrumb( $folder ) {

	if ( empty( $folder ) ) {
		return [];
	}

	$folder_id  = $folder['id'] ?? '';
	$account_id = $folder['accountId'] ?? '';

	if ( ! isset( $folder['name'] ) && isset( $folder['id'] ) ) {
		$folder = App::instance( $account_id )->get_file_by_id( $folder_id );
	}

	$items = [ $folder_id => $folder['name'] ];

	if ( in_array( $folder_id, [
		'root',
		'computers',
		'shared-drives',
		'shared',
		'starred'
	] ) ) {
		return $items;
	}

	if ( ! isset( $folder['parents'] ) ) {
		$folder = App::instance( $account_id )->get_file_by_id( $folder_id );
	}

	if ( ! empty( $folder['parents'] ) ) {

		if ( in_array( 'shared-drives', $folder['parents'] ) ) {
			$items['shared-drives'] = __( 'Shared Drives', 'integrate-google-drive' );

			return array_reverse( $items );
		}

		$item  = App::instance( $account_id )->get_file_by_id( $folder['parents'][0] );
		$items = array_merge( igd_get_breadcrumb( $item ), $items );
	} elseif ( ! empty( $folder['shared'] ) ) {
		$items['shared'] = __( 'Shared with me', 'integrate-google-drive' );

		return array_reverse( $items );
	} elseif ( ! empty( $folder['computers'] ) ) {
		$items['computers'] = __( 'Computers', 'integrate-google-drive' );

		return array_reverse( $items );
	}

	return $items;
}

function igd_is_dir( $file ) {
	if ( ! isset( $file['type'] ) ) {
		return false;
	}

	if ( $file['type'] == 'application/vnd.google-apps.folder' ) {
		return true;
	}

	if ( isset( $file['shortcutDetails'] ) && $file['shortcutDetails']['targetMimeType'] == 'application/vnd.google-apps.folder' ) {
		return true;
	}

	return false;
}

function igd_is_shortcut( $type ) {
	return $type == 'application/vnd.google-apps.shortcut';
}

function igd_get_files_recursive(
	$file,
	$current_path = '',
	&$list = [
		'folders' => [],
		'files'   => [],
		'size'    => 0,
	]
) {

	if ( igd_is_dir( $file ) ) {
		$folder_path = $current_path . $file['name'] . '/';

		$list['folders'][] = $folder_path;

		$account_id = ! empty( $file['accountId'] ) ? $file['accountId'] : Account::instance()->get_active_account()['id'];

		$args = [
			'folder' => $file,
		];

		$data = App::instance( $account_id )->get_files( $args );

		if ( ! empty( $data['files'] ) ) {
			foreach ( $data['files'] as $file ) {
				igd_get_files_recursive( $file, $folder_path, $list );
			}
		}

	} else {
		$file_path = $current_path . $file['name'];

		if ( empty( $file['webContentLink'] ) ) {
			$export_as = igd_get_export_as( $file['type'] );

			$format        = reset( $export_as );
			$download_link = 'https://www.googleapis.com/drive/v3/files/' . $file['id'] . '/export?mimeType=' . urlencode( $format['mimetype'] ) . '&alt=media';
			$file_path     .= '.' . $format['extension'];
		} else {
			$download_link = 'https://www.googleapis.com/drive/v3/files/' . $file['id'] . '?alt=media';
		}

		$file['downloadLink'] = $download_link;

		$file['path']    = $file_path;
		$list['files'][] = $file;
		$list['size']    += $file['size'];
	}


	return $list;
}

function igd_file_map( $item, $account_id = null ) {

	if ( empty( $account_id ) ) {
		$account_id = Account::instance()->get_active_account()['id'];
	}

	$file = [
		'id'                           => $item->getId(),
		'name'                         => $item->getName(),
		'type'                         => $item->getMimeType(),
		'size'                         => $item->getSize(),
		'iconLink'                     => $item->getIconLink(),
		'thumbnailLink'                => $item->getThumbnailLink(),
		'webViewLink'                  => $item->getWebViewLink(),
		'webContentLink'               => $item->getWebContentLink(),
		'created'                      => $item->getCreatedTime(),
		'updated'                      => $item->getModifiedTime(),
		'description'                  => $item->getDescription(),
		'shared'                       => $item->getShared(),
		'sharedWithMeTime'             => $item->getSharedWithMeTime(),
		'extension'                    => $item->getFileExtension(),
		'resourceKey'                  => $item->getResourceKey(),
		'copyRequiresWriterPermission' => $item->getCopyRequiresWriterPermission(),
		'starred'                      => $item->getStarred(),
		'exportLinks'                  => $item->getExportLinks(),
		'accountId'                    => $account_id,
	];

	// Get parents
	$parents = $item->getParents();

	// Replace root folder id with 'root'
	if ( ! empty( $parents ) ) {
		foreach ( $parents as $key => $parent ) {
			if ( preg_match( '/^0A[A-Za-z0-9_-]+9PVA$/', $parent ) ) {
				$parents[ $key ] = 'root';
			}
		}
	}

	$file['parents'] = $parents;

	$canPreview                            = true;
	$canDownload                           = ! empty( $file['webContentLink'] ) || ! empty( $file['exportLinks'] );
	$canShare                              = false;
	$canEdit                               = false;
	$canDelete                             = $item->getOwnedByMe();
	$canTrash                              = $item->getOwnedByMe();
	$canMove                               = $item->getOwnedByMe();
	$canRename                             = $item->getOwnedByMe();
	$canChangeCopyRequiresWriterPermission = true;

	$capabilities = $item->getCapabilities();


	if ( ! empty( $capabilities ) ) {
		$canEdit                               = $capabilities->getCanEdit() && igd_is_editable( $file['type'] );
		$canShare                              = $capabilities->getCanShare();
		$canRename                             = $capabilities->getCanRename();
		$canDelete                             = $capabilities->getCanDelete();
		$canTrash                              = $capabilities->getCanTrash();
		$canMove                               = $capabilities->getCanMoveItemWithinDrive();
		$canChangeCopyRequiresWriterPermission = $capabilities->getCanChangeCopyRequiresWriterPermission();
	}

	// Permission users
	$users = [];

	$permissions = $item->getPermissions();
	if ( count( $permissions ) > 0 ) {
		foreach ( $permissions as $permission ) {
			$users[ $permission->getId() ] = [
				'type'   => $permission->getType(),
				'role'   => $permission->getRole(),
				'domain' => $permission->getDomain()
			];
		}
	}

	// Set the permissions
	$file['permissions'] = [
		'canPreview'                            => $canPreview,
		'canDownload'                           => $canDownload,
		'canEdit'                               => $canEdit,
		'canDelete'                             => $canDelete,
		'canTrash'                              => $canTrash,
		'canMove'                               => $canMove,
		'canRename'                             => $canRename,
		'canShare'                              => $canShare,
		'copyRequiresWriterPermission'          => $item->getCopyRequiresWriterPermission(),
		'canChangeCopyRequiresWriterPermission' => $canChangeCopyRequiresWriterPermission,
		'users'                                 => $users,
	];

	// Set owner
	if ( ! empty( $item->getOwners() ) ) {
		$file['owner'] = $item->getOwners()[0]['displayName'];
	}

	// Get export as
	$file['exportAs'] = igd_get_export_as( $item->getMimeType() );


	// Shortcut details
	if ( ! empty( $item->getShortcutDetails() ) ) {
		$file['shortcutDetails'] = [
			'targetId'       => $item->getShortcutDetails()->getTargetId(),
			'targetMimeType' => $item->getShortcutDetails()->getTargetMimeType(),
		];


		$original_file = App::instance( $account_id )->get_file_by_id( $file['shortcutDetails']['targetId'] );

		if ( ! empty( $original_file ) ) {
			$file['thumbnailLink'] = $original_file['thumbnailLink'];
			$file['iconLink']      = $original_file['iconLink'];
			$file['extension']     = $original_file['extension'];
			$file['exportAs']      = $original_file['exportAs'];
		}


	}

	//Meta Data
	$image_meta_data = $item->getImageMediaMetadata();
	$video_meta_data = $item->getVideoMediaMetadata();

	if ( $image_meta_data ) {
		$file['metaData'] = [
			'width'  => $image_meta_data->getWidth(),
			'height' => $image_meta_data->getHeight(),
		];
	} elseif ( $video_meta_data ) {
		$file['metaData'] = [
			'width'    => $video_meta_data->getWidth(),
			'height'   => $video_meta_data->getHeight(),
			'duration' => $video_meta_data->getDurationMillis(),
		];
	}

	return $file;
}

function igd_drive_map( $drive, $account_id ) {
	if ( empty( $account_id ) ) {
		$account_id = Account::instance()->get_active_account()['id'];
	}

	$drive = $drive->toSimpleObject();

	$file = [
		'id'            => $drive->id,
		'name'          => $drive->name,
		'iconLink'      => $drive->backgroundImageLink,
		'thumbnailLink' => $drive->backgroundImageLink,
		'created'       => $drive->createdTime,
		'updated'       => $drive->createdTime,
		'hidden'        => $drive->hidden,
		'shared-drives' => true,
		'accountId'     => $account_id,
		'type'          => 'application/vnd.google-apps.folder',
		'parents'       => [ 'shared-drives' ],
	];

	$file['permissions'] = $drive->capabilities;

	return $file;
}

function igd_is_editable( $type ) {
	return in_array( $type, [
		'application/msword',
		'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
		'application/vnd.google-apps.document',
		'application/vnd.ms-excel',
		'application/vnd.ms-excel.sheet.macroenabled.12',
		'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
		'application/vnd.google-apps.spreadsheet',
		'application/vnd.ms-powerpoint',
		'application/vnd.openxmlformats-officedocument.presentationml.slideshow',
		'application/vnd.google-apps.presentation',
		'application/vnd.google-apps.drawing',
	] );
}

function igd_get_export_as( $type ) {
	$export_as = [];

	if ( 'application/vnd.google-apps.document' == $type ) {
		$export_as = [
			'MS Word document' => [
				'mimetype'  => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
				'extension' => 'docx',
			],

			'HTML' => [
				'mimetype'  => 'text/html',
				'extension' => 'html',
			],

			'Text' => [
				'mimetype'  => 'text/plain',
				'extension' => 'txt',
			],

			'Open Office document' => [
				'mimetype'  => 'application/vnd.oasis.opendocument.text',
				'extension' => 'odt',
			],

			'PDF' => [
				'mimetype'  => 'application/pdf',
				'extension' => 'pdf',
			],

			'ZIP' => [
				'mimetype'  => 'application/zip',
				'extension' => 'zip',
			],

		];

	} elseif ( 'application/vnd.google-apps.spreadsheet' == $type ) {
		$export_as = [
			'MS Excel document'      => [
				'mimetype'  => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
				'extension' => 'xlsx',
			],
			'Open Office sheet'      => [
				'mimetype'  => 'application/x-vnd.oasis.opendocument.spreadsheet',
				'extension' => 'ods',
			],
			'PDF'                    => [
				'mimetype'  => 'application/pdf',
				'extension' => 'pdf',
			],
			'CSV (first sheet only)' => [
				'mimetype'  => 'text/csv',
				'extension' => 'csv',
			],
			'ZIP'                    => [
				'mimetype'  => 'application/zip',
				'extension' => 'zip',
			],
		];
	} elseif ( 'application/vnd.google-apps.drawing' == $type ) {
		$export_as = [
			'JPEG' => [ 'mimetype' => 'image/jpeg', 'extension' => 'jpeg' ],
			'PNG'  => [ 'mimetype' => 'image/png', 'extension' => 'png' ],
			'SVG'  => [ 'mimetype' => 'image/svg+xml', 'extension' => 'svg' ],
			'PDF'  => [ 'mimetype' => 'application/pdf', 'extension' => 'pdf' ],
		];

	} elseif ( 'application/vnd.google-apps.presentation' == $type ) {
		$export_as = [
			'MS PowerPoint document' => [
				'mimetype'  => 'application/vnd.openxmlformats-officedocument.presentationml.presentation',
				'extension' => 'pptx',
			],
			'PDF'                    => [
				'mimetype'  => 'application/pdf',
				'extension' => 'pdf',
			],
			'Text'                   => [
				'mimetype'  => 'text/plain',
				'extension' => 'txt',
			],
		];
	} elseif ( 'application/vnd.google-apps.script' == $type ) {
		$export_as = [
			'JSON' => [
				'mimetype'  => 'application/vnd.google-apps.script+json',
				'extension' => 'json',
			],
		];
	} elseif ( 'application/vnd.google-apps.form' == $type ) {
		$export_as = [
			'ZIP' => [ 'mimetype' => 'application/zip', 'extension' => 'zip' ],
		];
	}

	return $export_as;
}

function igd_get_embed_content( $data ) {

	$items          = ! empty( $data['folders'] ) ? $data['folders'] : [];
	$show_file_name = ! empty( $data['showFileName'] );
	$embed_type     = ! empty( $data['embedType'] ) ? $data['embedType'] : 'readOnly';
	$direct_image   = ! empty( $data['directImage'] );
	$allow_popout   = ! empty( $data['allowEmbedPopout'] );
	$embed_width    = ! empty( $data['embedWidth'] ) ? $data['embedWidth'] : '100%';
	$embed_height   = ! empty( $data['embedHeight'] ) ? $data['embedHeight'] : '480px';


	$files = [];
	foreach ( $items as $item ) {

		// skip root folders
		if ( ! is_array( $item ) ) {
			continue;
		}

		if ( ! igd_is_dir( $item ) ) {
			$files[] = $item;
		} else {

			$args = [
				'folder' => $item,
			];


			$data = App::instance( $item['accountId'] )->get_files( $args );

			if ( ! empty( $data['files'] ) ) {
				foreach ( $data['files'] as $file ) {
					if ( ! igd_is_dir( $file ) ) {
						$files[] = $file;
					}
				}
			}

		}
	}

	ob_start();

	if ( ! empty( $files ) ) {
		foreach ( $files as $file ) {
			$type = $file['type'];
			$name = $file['name'];

			$is_image = strpos( $type, 'image/' ) === 0;
			$is_video = strpos( $type, 'video/' ) === 0;
			$is_audio = strpos( $type, 'audio/' ) === 0;


			if ( $show_file_name ) { ?>
                <h4 class="igd-embed-name"><?php echo esc_html( $name ); ?></h4>
			<?php }

			if ( $is_image ) {
				$embed_type = 'readOnly';
			}

			if ( empty( $file['permissions']['canEdit'] ) ) {
				$embed_type = 'readOnly';
			}

			$url = igd_get_embed_url( $file, $embed_type, $direct_image );

			if ( $direct_image && ( $is_image || $is_audio || $is_video ) ) {
				if ( $is_image ) {
					printf( '<img class="igd-embed-image" src="%s" alt="%s" width="%s" height="%s" />', esc_url( $url ), esc_attr( $name ), $embed_width, $embed_height );
				} elseif ( $is_video ) {
					echo wp_video_shortcode( [ 'src' => $url ] );
				} elseif ( $is_audio ) {
					echo wp_audio_shortcode( [ 'src' => $url ] );
				}

			} else {
				$sandbox_attrs = '';

				if ( ! $allow_popout && $embed_type = 'readOnly' ) {
					$sandbox_attrs = 'sandbox="allow-same-origin allow-scripts"';
				}

				printf( '<iframe class="igd-embed" src="%1$s" frameborder="0" scrolling="no" width="%2$s" height="%3$s" allow="autoplay" allowfullscreen="allowfullscreen" %4$s></iframe>', $url, $embed_width, $embed_height, $sandbox_attrs );
			}
		}
	}

	$content = ob_get_clean();

	return $content;

}

function igd_is_cached_folder( $folder_id, $account_id = null ) {
	$cached_folders = get_option( 'igd_cached_folders', [] );

	$is_cached = isset( $cached_folders[ $folder_id ] );

	if ( $is_cached && in_array( $folder_id, [
			'root',
			'shared-drives',
			'computers',
			'shared',
			'starred',
		] ) ) {
		$is_cached = $cached_folders[ $folder_id ]['accountId'] == $account_id;
	}

	return $is_cached;
}

function igd_update_cached_folders( $folder_id, $account_id ) {
	$cached_folders               = get_option( 'igd_cached_folders', [] );
	$cached_folders[ $folder_id ] = [
		'id'        => $folder_id,
		'accountId' => $account_id,
	];

	update_option( 'igd_cached_folders', $cached_folders );
}

function igd_mime_to_ext( $mime ) {
	$mime_map = [
		'video/3gpp2'                                                               => '3g2',
		'video/3gp'                                                                 => '3gp',
		'video/3gpp'                                                                => '3gp',
		'application/x-compressed'                                                  => '7zip',
		'audio/x-acc'                                                               => 'aac',
		'audio/ac3'                                                                 => 'ac3',
		'application/postscript'                                                    => 'ai',
		'audio/x-aiff'                                                              => 'aif',
		'audio/aiff'                                                                => 'aif',
		'audio/x-au'                                                                => 'au',
		'video/x-msvideo'                                                           => 'avi',
		'video/msvideo'                                                             => 'avi',
		'video/avi'                                                                 => 'avi',
		'application/x-troff-msvideo'                                               => 'avi',
		'application/macbinary'                                                     => 'bin',
		'application/mac-binary'                                                    => 'bin',
		'application/x-binary'                                                      => 'bin',
		'application/x-macbinary'                                                   => 'bin',
		'image/bmp'                                                                 => 'bmp',
		'image/x-bmp'                                                               => 'bmp',
		'image/x-bitmap'                                                            => 'bmp',
		'image/x-xbitmap'                                                           => 'bmp',
		'image/x-win-bitmap'                                                        => 'bmp',
		'image/x-windows-bmp'                                                       => 'bmp',
		'image/ms-bmp'                                                              => 'bmp',
		'image/x-ms-bmp'                                                            => 'bmp',
		'application/bmp'                                                           => 'bmp',
		'application/x-bmp'                                                         => 'bmp',
		'application/x-win-bitmap'                                                  => 'bmp',
		'application/cdr'                                                           => 'cdr',
		'application/coreldraw'                                                     => 'cdr',
		'application/x-cdr'                                                         => 'cdr',
		'application/x-coreldraw'                                                   => 'cdr',
		'image/cdr'                                                                 => 'cdr',
		'image/x-cdr'                                                               => 'cdr',
		'zz-application/zz-winassoc-cdr'                                            => 'cdr',
		'application/mac-compactpro'                                                => 'cpt',
		'application/pkix-crl'                                                      => 'crl',
		'application/pkcs-crl'                                                      => 'crl',
		'application/x-x509-ca-cert'                                                => 'crt',
		'application/pkix-cert'                                                     => 'crt',
		'text/css'                                                                  => 'css',
		'text/x-comma-separated-values'                                             => 'csv',
		'text/comma-separated-values'                                               => 'csv',
		'application/vnd.msexcel'                                                   => 'csv',
		'application/x-director'                                                    => 'dcr',
		'application/vnd.openxmlformats-officedocument.wordprocessingml.document'   => 'docx',
		'application/x-dvi'                                                         => 'dvi',
		'message/rfc822'                                                            => 'eml',
		'application/x-msdownload'                                                  => 'exe',
		'video/x-f4v'                                                               => 'f4v',
		'audio/x-flac'                                                              => 'flac',
		'video/x-flv'                                                               => 'flv',
		'image/gif'                                                                 => 'gif',
		'application/gpg-keys'                                                      => 'gpg',
		'application/x-gtar'                                                        => 'gtar',
		'application/x-gzip'                                                        => 'gzip',
		'application/mac-binhex40'                                                  => 'hqx',
		'application/mac-binhex'                                                    => 'hqx',
		'application/x-binhex40'                                                    => 'hqx',
		'application/x-mac-binhex40'                                                => 'hqx',
		'text/html'                                                                 => 'html',
		'image/x-icon'                                                              => 'ico',
		'image/x-ico'                                                               => 'ico',
		'image/vnd.microsoft.icon'                                                  => 'ico',
		'text/calendar'                                                             => 'ics',
		'application/java-archive'                                                  => 'jar',
		'application/x-java-application'                                            => 'jar',
		'application/x-jar'                                                         => 'jar',
		'image/jp2'                                                                 => 'jp2',
		'video/mj2'                                                                 => 'jp2',
		'image/jpx'                                                                 => 'jp2',
		'image/jpm'                                                                 => 'jp2',
		'image/jpeg'                                                                => 'jpeg',
		'image/pjpeg'                                                               => 'jpeg',
		'application/x-javascript'                                                  => 'js',
		'application/json'                                                          => 'json',
		'text/json'                                                                 => 'json',
		'application/vnd.google-earth.kml+xml'                                      => 'kml',
		'application/vnd.google-earth.kmz'                                          => 'kmz',
		'text/x-log'                                                                => 'log',
		'audio/x-m4a'                                                               => 'm4a',
		'audio/mp4'                                                                 => 'm4a',
		'application/vnd.mpegurl'                                                   => 'm4u',
		'audio/midi'                                                                => 'mid',
		'application/vnd.mif'                                                       => 'mif',
		'video/quicktime'                                                           => 'mov',
		'video/x-sgi-movie'                                                         => 'movie',
		'audio/mpeg'                                                                => 'mp3',
		'audio/mpg'                                                                 => 'mp3',
		'audio/mpeg3'                                                               => 'mp3',
		'audio/mp3'                                                                 => 'mp3',
		'video/mp4'                                                                 => 'mp4',
		'video/mpeg'                                                                => 'mpeg',
		'application/oda'                                                           => 'oda',
		'audio/ogg'                                                                 => 'ogg',
		'video/ogg'                                                                 => 'ogg',
		'application/ogg'                                                           => 'ogg',
		'font/otf'                                                                  => 'otf',
		'application/x-pkcs10'                                                      => 'p10',
		'application/pkcs10'                                                        => 'p10',
		'application/x-pkcs12'                                                      => 'p12',
		'application/x-pkcs7-signature'                                             => 'p7a',
		'application/pkcs7-mime'                                                    => 'p7c',
		'application/x-pkcs7-mime'                                                  => 'p7c',
		'application/x-pkcs7-certreqresp'                                           => 'p7r',
		'application/pkcs7-signature'                                               => 'p7s',
		'application/pdf'                                                           => 'pdf',
		'application/octet-stream'                                                  => 'pdf',
		'application/x-x509-user-cert'                                              => 'pem',
		'application/x-pem-file'                                                    => 'pem',
		'application/pgp'                                                           => 'pgp',
		'application/x-httpd-php'                                                   => 'php',
		'application/php'                                                           => 'php',
		'application/x-php'                                                         => 'php',
		'text/php'                                                                  => 'php',
		'text/x-php'                                                                => 'php',
		'application/x-httpd-php-source'                                            => 'php',
		'image/png'                                                                 => 'png',
		'image/x-png'                                                               => 'png',
		'application/powerpoint'                                                    => 'ppt',
		'application/vnd.ms-powerpoint'                                             => 'ppt',
		'application/vnd.ms-office'                                                 => 'ppt',
		'application/msword'                                                        => 'doc',
		'application/vnd.openxmlformats-officedocument.presentationml.presentation' => 'pptx',
		'application/x-photoshop'                                                   => 'psd',
		'image/vnd.adobe.photoshop'                                                 => 'psd',
		'audio/x-realaudio'                                                         => 'ra',
		'audio/x-pn-realaudio'                                                      => 'ram',
		'application/x-rar'                                                         => 'rar',
		'application/rar'                                                           => 'rar',
		'application/x-rar-compressed'                                              => 'rar',
		'audio/x-pn-realaudio-plugin'                                               => 'rpm',
		'application/x-pkcs7'                                                       => 'rsa',
		'text/rtf'                                                                  => 'rtf',
		'text/richtext'                                                             => 'rtx',
		'video/vnd.rn-realvideo'                                                    => 'rv',
		'application/x-stuffit'                                                     => 'sit',
		'application/smil'                                                          => 'smil',
		'text/srt'                                                                  => 'srt',
		'image/svg+xml'                                                             => 'svg',
		'application/x-shockwave-flash'                                             => 'swf',
		'application/x-tar'                                                         => 'tar',
		'application/x-gzip-compressed'                                             => 'tgz',
		'image/tiff'                                                                => 'tiff',
		'font/ttf'                                                                  => 'ttf',
		'text/plain'                                                                => 'txt',
		'text/x-vcard'                                                              => 'vcf',
		'application/videolan'                                                      => 'vlc',
		'text/vtt'                                                                  => 'vtt',
		'audio/x-wav'                                                               => 'wav',
		'audio/wave'                                                                => 'wav',
		'audio/wav'                                                                 => 'wav',
		'application/wbxml'                                                         => 'wbxml',
		'video/webm'                                                                => 'webm',
		'image/webp'                                                                => 'webp',
		'audio/x-ms-wma'                                                            => 'wma',
		'application/wmlc'                                                          => 'wmlc',
		'video/x-ms-wmv'                                                            => 'wmv',
		'video/x-ms-asf'                                                            => 'wmv',
		'font/woff'                                                                 => 'woff',
		'font/woff2'                                                                => 'woff2',
		'application/xhtml+xml'                                                     => 'xhtml',
		'application/excel'                                                         => 'xl',
		'application/msexcel'                                                       => 'xls',
		'application/x-msexcel'                                                     => 'xls',
		'application/x-ms-excel'                                                    => 'xls',
		'application/x-excel'                                                       => 'xls',
		'application/x-dos_ms_excel'                                                => 'xls',
		'application/xls'                                                           => 'xls',
		'application/x-xls'                                                         => 'xls',
		'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'         => 'xlsx',
		'application/vnd.ms-excel'                                                  => 'xlsx',
		'application/xml'                                                           => 'xml',
		'text/xml'                                                                  => 'xml',
		'text/xsl'                                                                  => 'xsl',
		'application/xspf+xml'                                                      => 'xspf',
		'application/x-compress'                                                    => 'z',
		'application/x-zip'                                                         => 'zip',
		'application/zip'                                                           => 'zip',
		'application/x-zip-compressed'                                              => 'zip',
		'application/s-compressed'                                                  => 'zip',
		'multipart/x-zip'                                                           => 'zip',
		'text/x-scriptzsh'                                                          => 'zsh',
	];

	return isset( $mime_map[ $mime ] ) ? $mime_map[ $mime ] : false;
}

function igd_get_child_items( $folder ) {
	$args = [
		'folder' => $folder,
	];

	$app = App::instance( $folder['accountId'] );

	$data = $app->get_files( $args );

	if ( ! empty( $data['error'] ) ) {
		error_log( 'Integrate Google Drive - Error: ' . $data['error'] );

		return [];
	}

	return ! empty( $data['files'] ) ? $data['files'] : [];
}

function igd_get_all_child_folders( $folder ) {

	$folders = array_filter( igd_get_child_items( $folder ), function ( $file ) {
		return igd_is_dir( $file );
	} );

	$list = [];

	if ( ! empty( $folders ) ) {
		foreach ( $folders as $folder_item ) {
			$list[]        = $folder_item;
			$child_folders = igd_get_all_child_folders( $folder_item );
			$list          = array_merge( $list, $child_folders );
		}
	}

	return $list;
}

function igd_get_all_child_files( $folder ) {

	$items = igd_get_child_items( $folder );

	$list = [];

	if ( ! empty( $items ) ) {
		foreach ( $items as $item ) {

			if ( igd_is_dir( $item ) ) {
				$child_files = igd_get_all_child_files( $item );
				$list        = array_merge( $list, $child_files );
				continue;
			}

			$list[] = $item;
		}
	}

	return $list;
}

function igd_get_all_parent_folders( $file ) {
	$list = [];

	$app = App::instance( $file['accountId'] );

	// Check if file has parents
	if ( ! empty( $file['parents'] ) ) {
		foreach ( $file['parents'] as $parent_id ) {
			$parent_folder = $app->get_file_by_id( $parent_id );

			// Check if retrieved parent folder is indeed a directory
			if ( igd_is_dir( $parent_folder ) ) {
				$list[] = $parent_folder;

				// Recursively get parents of the parent folder
				$parent_folders = igd_get_all_parent_folders( $parent_folder );
				$list           = array_merge( $list, $parent_folders );
			}
		}
	}

	return $list;
}

function igd_get_scheduled_interval( $hook ) {
	$schedule  = wp_get_schedule( $hook );
	$schedules = wp_get_schedules();

	return ! empty( $schedules[ $schedule ] ) ? $schedules[ $schedule ]['interval'] : false;
}

function igd_get_shortcodes_array() {
	$shortcodes = Shortcode_Builder::instance()->get_shortcodes();

	$formatted = [];

	if ( ! empty( $shortcodes ) ) {
		foreach ( $shortcodes as $shortcode ) {

			$formatted[ $shortcode->id ] = $shortcode->title;
		}
	}

	return $formatted;
}

function igd_download_zip( $file_ids, $request_id = '', $account_id = '' ) {

	$files = [];

	if ( ! empty( $file_ids ) ) {
		$app = App::instance( $account_id );

		foreach ( $file_ids as $file_id ) {
			do_action( 'igd_insert_log', 'download', $file_id, $account_id );
			$files[] = $app->get_file_by_id( $file_id );
		}
	}

	Zip::instance( $files, $request_id )->do_zip();
	exit();
}

function igd_get_free_memory_available() {
	$memory_limit = igd_return_bytes( ini_get( 'memory_limit' ) );

	if ( $memory_limit < 0 ) {
		if ( defined( 'WP_MEMORY_LIMIT' ) ) {
			$memory_limit = igd_return_bytes( WP_MEMORY_LIMIT );
		} else {
			$memory_limit = 1024 * 1024 * 92; // Return 92MB if we can't get any reading on memory limits
		}
	}

	$memory_usage = memory_get_usage( true );

	$free_memory = $memory_limit - $memory_usage;

	if ( $free_memory < ( 1024 * 1024 * 10 ) ) {
		// Return a minimum of 10MB available
		return 1024 * 1024 * 10;
	}

	return $free_memory;
}

function igd_set_time_limit( $limit ) {
	if ( function_exists( 'set_time_limit' ) && false === strpos( ini_get( 'disable_functions' ), 'set_time_limit' ) && ! ini_get( 'safe_mode' ) ) {
		@set_time_limit( $limit );
	}
}

function igd_return_bytes( $size_str ) {
	if ( empty( $size_str ) ) {
		return $size_str;
	}

	$unit = substr( $size_str, - 1 );
	if ( ( 'B' === $unit || 'b' === $unit ) && ( ! ctype_digit( substr( $size_str, - 2 ) ) ) ) {
		$unit = substr( $size_str, - 2, 1 );
	}

	switch ( $unit ) {
		case 'M':
		case 'm':
			return (int) $size_str * 1048576;

		case 'K':
		case 'k':
			return (int) $size_str * 1024;

		case 'G':
		case 'g':
			return (int) $size_str * 1073741824;

		default:
			return $size_str;
	}
}

function igd_get_settings( $key = null, $default = null ) {
	$settings = (array) get_option( 'igd_settings', [] );

	if ( ! isset( $settings['emailReportRecipients'] ) ) {
		$settings['emailReportRecipients'] = get_option( 'admin_email' );
	}

	if ( empty( $settings ) && ! empty( $default ) ) {
		return $default;
	}

	if ( empty( $key ) ) {
		return ! empty( $settings ) ? $settings : [];
	}

	return isset( $settings[ $key ] ) ? $settings[ $key ] : $default;
}

function igd_get_embed_url( $file, $embed_type = 'readOnly', $direct_image = false, $is_preview = false, $popout = false, $download = true ) {
	$id         = $file['id'];
	$account_id = $file['accountId'];
	$type       = isset( $file['type'] ) ? $file['type'] : '';

	$is_editable       = in_array( $embed_type, [ 'editable', 'fullEditable' ] );
	$editable_arguemts = $is_editable && $embed_type === 'fullEditable' ? 'edit?usp=drivesdk&rm=embedded&embedded=true' : 'edit?usp=drivesdk&rm=minimal&embedded=true';

	$permissions = Permissions::instance( $account_id );
	if ( ! $permissions->has_permission( $file ) ) {
		$permissions->set_permission( $file );
	}

	if ( $is_preview || $popout ) {
		$url = "https://drive.google.com/file/d/{$id}/preview?rm=minimal";
	} else {
		$doc_types = [
			'doc'          => [
				'application/msword',
				'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
				'application/vnd.google-apps.document'
			],
			'sheet'        => [
				'application/vnd.ms-excel',
				'application/vnd.ms-excel.sheet.macroenabled.12',
				'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
				'application/vnd.google-apps.spreadsheet'
			],
			'presentation' => [
				'application/vnd.ms-powerpoint',
				'application/vnd.openxmlformats-officedocument.presentationml.slideshow',
				'application/vnd.google-apps.presentation'
			],
			'drawing'      => [ 'application/vnd.google-apps.drawing' ],
			'form'         => [ 'application/vnd.google-apps.form' ],
		];

		$type_to_path = [
			'doc'          => 'document',
			'sheet'        => 'spreadsheets',
			'presentation' => 'presentation',
			'drawing'      => 'drawings',
			'form'         => 'forms'
		];

		$found = false;
		foreach ( $doc_types as $doc_type => $types ) {
			if ( in_array( $type, $types ) ) {
				$found     = true;
				$arguments = $is_editable ? $editable_arguemts : 'preview?rm=minimal';

				$url = "https://docs.google.com/{$type_to_path[$doc_type]}/d/{$id}/{$arguments}";

				if ( $doc_type === 'doc' || $doc_type === 'sheet' || $doc_type === 'presentation' ) {
					if ( ! $is_editable ) {
						$url = "https://drive.google.com/file/d/{$id}/preview?rm=minimal";
					}
				}

				break;
			}
		}

		if ( ! $found ) {

			$is_media = strpos( $type, 'image/' ) === 0 || preg_match( '/^(audio|video)\//', $type );

			if ( $type === 'application/vnd.google-apps.folder' ) {
				$url = "https://drive.google.com/open?id={$id}";
			} elseif ( $direct_image && $is_media ) {
				if ( strpos( $type, 'image/' ) === 0 ) {

					$url = igd_get_thumbnail_url( $file, 'full' );

				} elseif ( preg_match( '/^(audio|video)\//', $type ) ) {
					$ext = strpos( $type, 'audio/' ) === 0 ? '.mp3' : '.mp4';

					$url = admin_url( "admin-ajax.php?action=igd_stream&id={$id}&account_id={$account_id}&ext={$ext}" );
				}
			} else {
				$arguments = $is_editable ? $editable_arguemts : 'preview?rm=minimal';
				$url       = "https://drive.google.com/file/d/$id/$arguments";
			}
		}
	}

	if ( ! empty( $file['resourceKey'] ) ) {
		$url .= "&resourcekey={$file['resourceKey']}";
	}

	return $url;
}

function igd_is_public_file( $file ) {
	if ( isset( $file['permissions'] ) &&
	     isset( $file['permissions']['users'] ) &&
	     isset( $file['permissions']['users']['anyoneWithLink'] ) ) {
		$role = $file['permissions']['users']['anyoneWithLink']['role'];

		return $role === 'reader' || $role === 'writer';
	}

	return false;
}

function igd_get_thumbnail_url( $file, $size, $custom_size = [] ) {
	$id            = $file['id'];
	$iconLink      = $file['iconLink'];
	$thumbnailLink = ! empty( $file['thumbnailLink'] ) ? $file['thumbnailLink'] : false;
	$account_id    = ! empty( $file['accountId'] ) ? $file['accountId'] : '';

	$w = ! empty( $custom_size['w'] ) ? $custom_size['w'] : 256;
	$h = ! empty( $custom_size['h'] ) ? $custom_size['h'] : 256;

	$thumb = str_replace( '/16/', "/$w/", $iconLink );

	if ( $thumbnailLink ) {
		if ( igd_is_public_file( $file ) ) {
			switch ( $size ) {
				case 'small':
				case 'custom':
					$thumb = "https://drive.google.com/thumbnail?id=$id&sz=w$w-h$h";
					break;
				case 'medium':
					$thumb = "https://drive.google.com/thumbnail?id=$id&sz=w600-h400";
					break;
				case 'large':
					$thumb = "https://drive.google.com/thumbnail?id=$id&sz=w1024-h768";
					break;
				case 'full':
					$thumb = "https://drive.google.com/thumbnail?id=$id&sz=w2048";
					break;
				default:
					$thumb = "https://drive.google.com/thumbnail?id=$id&sz=w300-h300";
			}
		} else {

			// get new thumbnail link for non-public files
			if ( strpos( $thumbnailLink, 'google.com' ) === false ) {

				$from_server = false;

				$transient = get_transient( 'igd_latest_fetch_' . $id );
				if ( ! $transient ) {
					$from_server = true;
					set_transient( 'igd_latest_fetch_' . $id, true, 60 * MINUTE_IN_SECONDS );
				}

				$file          = App::instance( $account_id )->get_file_by_id( $id, $from_server );
				$thumbnailLink = $file['thumbnailLink'] ?? '';

			}

			switch ( $size ) {
				case 'custom':
					$thumb = str_replace( '=s220', "={$w}-h{$h}", $thumbnailLink );
					break;
				case 'small':
					$thumb = str_replace( '=s220', '=w300-h300', $thumbnailLink );
					break;
				case 'medium':
					$thumb = str_replace( '=s220', '=h600-nu', $thumbnailLink );
					break;
				case 'large':
					$thumb = str_replace( '=s220', '=w1024-h768-p-k-nu', $thumbnailLink );
					break;
				case 'full':
					$thumb = str_replace( '=s220', '', $thumbnailLink );
					break;
				default:
					$thumb = str_replace( '=s220', '=w200-h190-p-k-nu', $thumbnailLink );
			}

		}
	}

	return $thumb;
}

function igd_get_mime_type( $mime, $returnGroup = false ) {

	$mimes = [

		'text' => [
			'application/vnd.oasis.opendocument.text' => 'Text',
			'text/plain'                              => 'Text',
		],

		'file'  => [
			'text/html'                        => 'HTML',
			'text/php'                         => 'PHP',
			'x-httpd-php'                      => 'PHP',
			'text/css'                         => 'CSS',
			'text/js'                          => 'JavaScript',
			'application/javascript'           => 'JavaScript',
			'application/json'                 => 'JSON',
			'application/xml'                  => 'XML',
			'application/x-shockwave-flash'    => 'SWF',
			'video/x-flv'                      => 'FLV',
			'application/vnd.google-apps.file' => 'File',
		],

		// images
		'image' => [
			'application/vnd.google-apps.photo' => 'Photo',
			'image/png'                         => 'PNG',
			'image/jpeg'                        => 'JPEG',
			'image/jpg'                         => 'JPG',
			'image/gif'                         => 'GIF',
			'image/bmp'                         => 'BMP',
			'image/vnd.microsoft.icon'          => 'ICO',
			'image/tiff'                        => 'TIFF',
			'image/tif'                         => 'TIF',
			'image/svg+xml'                     => 'SVG',
		],

		// archives
		'zip'   => [
			'application/zip'                   => 'ZIP',
			'application/x-rar-compressed'      => 'RAR',
			'application/x-msdownload'          => 'EXE',
			'application/vnd.ms-cab-compressed' => 'CAB',
		],

		// audio/video
		'audio' => [
			'audio/mpeg'                        => 'MP3',
			'video/quicktime'                   => 'QT',
			'application/vnd.google-apps.audio' => 'Audio',
			'audio/x-m4a'                       => 'Audio',
			'audio/mp4'                         => 'Audio',
			'audio/ogg'                         => 'Audio',
			'audio/wav'                         => 'Audio',
			'audio/webm'                        => 'Audio',
		],

		'video' => [
			'application/vnd.google-apps.video' => 'Video',
			'video/x-flv'                       => 'Video',
			'video/mp4'                         => 'Video',
			'video/webm'                        => 'Video',
			'video/ogg'                         => 'Video',
			'application/x-mpegURL'             => 'Video',
			'video/MP2T'                        => 'Video',
			'video/3gpp'                        => 'Video',
			'video/quicktime'                   => 'Video',
			'video/x-msvideo'                   => 'Video',
			'video/x-ms-wmv'                    => 'Video',
		],

		// adobe
		'pdf'   => [
			'application/pdf' => 'PDF',
		],

		// ms office
		'word'  => [
			'application/msword' => 'MS Word',
		],

		'doc' => [
			'application/vnd.google-apps.document' => 'Google Docs',
		],

		'excel' => [
			'application/vnd.ms-excel'                                          => 'Excel',
			'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' => 'Excel',
		],

		'presentation' => [
			'application/vnd.google-apps.presentation'        => 'Slide',
			'application/vnd.oasis.opendocument.presentation' => 'Presentation'
		],

		'powerpoint' => [
			'application/vnd.ms-powerpoint' => 'Powerpoint',
		],

		'form' => [
			'application/vnd.google-apps.form' => 'Form',
		],

		'folder' => [
			'application/vnd.google-apps.folder' => 'Folder',
		],

		'drawing' => [
			'application/vnd.google-apps.drawing' => 'Drawing',
		],

		'script' => [
			'application/vnd.google-apps.script' => 'Script',
		],

		'sites' => [
			'application/vnd.google-apps.sites' => 'Sites',
		],

		'spreadsheet' => [
			'application/vnd.google-apps.spreadsheet'        => 'Spreadsheet',
			'application/vnd.oasis.opendocument.spreadsheet' => 'Spreadsheet',
		],
	];

	$file_type  = 'File';
	$group_type = 'file';

	foreach ( $mimes as $group => $types ) {
		if ( array_key_exists( $mime, $types ) ) {
			$file_type  = $types[ $mime ];
			$group_type = $group;
			break;
		}
	}

	return $returnGroup ? $group_type : $file_type;

}

function igd_get_mime_icon( $mime ) {
	$mime_type = igd_get_mime_type( $mime, true );

	return IGD_ASSETS . "/images/icons/$mime_type.png";
}

function igd_should_allow( $item, $filters = [] ) {
	$is_dir = igd_is_dir( $item );

	if ( ! $is_dir && isset( $filters['showFiles'] ) && empty( $filters['showFiles'] ) ) {
		return false;
	}

	if ( $is_dir && isset( $filters['showFolders'] ) && empty( $filters['showFolders'] ) ) {
		return false;
	}

	$extension = ! empty( $item['extension'] ) ? $item['extension'] : '';
	$name      = ! empty( $item['name'] ) ? $item['name'] : '';

	// Extensions
	if ( ! $is_dir ) {
		if ( ! empty( $filters['allowAllExtensions'] ) ) {
			if ( ! empty( $filters['allowExceptExtensions'] ) ) {
				$exceptExtensions = array_map( 'trim', explode( ',', $filters['allowExceptExtensions'] ) );

				if ( in_array( $extension, $exceptExtensions ) ) {
					return false;
				}
			}
		} else {
			if ( ! empty( $filters['allowExtensions'] ) ) {
				$allowedExtensions = array_map( 'trim', explode( ',', $filters['allowExtensions'] ) );

				if ( ! in_array( $extension, $allowedExtensions ) ) {
					return false;
				}
			}
		}
	}

	// Names
	if ( ! empty( $filters['nameFilterOptions'] ) ) {
		$nameFilterOptions = $filters['nameFilterOptions'];

		if ( in_array( 'files', $nameFilterOptions ) && ! in_array( 'folders', $nameFilterOptions ) && $is_dir ) {
			return true;
		}

		if ( in_array( 'folders', $nameFilterOptions ) && ! in_array( 'files', $nameFilterOptions ) && ! $is_dir ) {
			return true;
		}
	}

	if ( ! empty( $filters['allowAllNames'] ) ) {
		if ( ! empty( $filters['allowExceptNames'] ) ) {
			$exceptPatterns = array_map( 'trim', explode( ',', $filters['allowExceptNames'] ) );

			$match = false;
			foreach ( $exceptPatterns as $pattern ) {
				if ( fnmatch( strtolower( $pattern ), strtolower( $name ) ) ) {
					$match = true;
					break;
				}
			}

			if ( $match ) {
				return false;
			}
		}
	} else {
		if ( ! empty( $filters['allowNames'] ) ) {
			$allowedPatterns = array_map( 'trim', explode( ',', $filters['allowNames'] ) );

			foreach ( $allowedPatterns as $pattern ) {
				if ( ! fnmatch( strtolower( $pattern ), strtolower( $name ) ) ) {
					return false;
				}
			}
		}
	}

	return true;
}

function igd_delete_cache( $folder_ids = [], $account_id = false ) {

	// Check if running on cron
	if ( defined( 'DOING_CRON' ) && DOING_CRON ) {
		$interval = igd_get_settings( 'syncInterval', 'never' );

		if ( 'never' != $interval ) {
			$syncType   = igd_get_settings( 'syncType', 'all' );
			$folder_ids = [];

			if ( $syncType == 'selected' ) {
				$folders    = igd_get_settings( 'syncFolders', [] );
				$folder_ids = array_column( $folders, 'id' );
			}
		}
	}

	if ( ! $account_id ) {
		$active_account = Account::instance()->get_active_account();
		$account_id     = ! empty( $active_account ) ? $active_account['id'] : null;
	}

	if ( ! empty( $folder_ids ) ) {
		$cached_folders    = get_option( 'igd_cached_folders', [] );
		$folders_to_delete = [];

		foreach ( $folder_ids as $folder_id ) {
			if ( ! empty( $cached_folders[ $folder_id ] ) ) {
				unset( $cached_folders[ $folder_id ] );
				$folders_to_delete[] = $folder_id;
			}
		}

		// Update the option after the loop
		update_option( 'igd_cached_folders', $cached_folders );

		// Delete files of all folders in a single operation
		if ( ! empty( $folders_to_delete ) ) {
			Files::delete_folder_files( $folders_to_delete );
		}
	} else {
		delete_option( 'igd_cached_folders' );
		Files::delete_account_files( $account_id );
	}

}

function igd_color_brightness( $hex, $steps ) {

	// return if not hex color
	if ( ! preg_match( '/^#([a-f0-9]{3}){1,2}$/i', $hex ) ) {
		return $hex;
	}

	// Steps should be between -255 and 255. Negative = darker, positive = lighter
	$steps = max( - 255, min( 255, $steps ) );

	// Normalize into a six character long hex string
	$hex = str_replace( '#', '', $hex );
	if ( strlen( $hex ) == 3 ) {
		$hex = str_repeat( substr( $hex, 0, 1 ), 2 ) . str_repeat( substr( $hex, 1, 1 ), 2 ) . str_repeat( substr( $hex, 2, 1 ), 2 );
	}

	// Split into three parts: R, G and B
	$color_parts = str_split( $hex, 2 );
	$return      = '#';

	foreach ( $color_parts as $color ) {
		$color  = hexdec( $color ); // Convert to decimal
		$color  = max( 0, min( 255, $color + $steps ) ); // Adjust color
		$return .= str_pad( dechex( $color ), 2, '0', STR_PAD_LEFT ); // Make two char hex code
	}

	return $return;
}

function igd_hex2rgba( $color, $opacity = false ) {

	$default = 'rgb(0,0,0)';

	//Return default if no color provided
	if ( empty( $color ) ) {
		return $default;
	}

	//Sanitize $color if "#" is provided
	if ( $color[0] == '#' ) {
		$color = substr( $color, 1 );
	}

	//Check if color has 6 or 3 characters and get values
	if ( strlen( $color ) == 6 ) {
		$hex = array( $color[0] . $color[1], $color[2] . $color[3], $color[4] . $color[5] );
	} elseif ( strlen( $color ) == 3 ) {
		$hex = array( $color[0] . $color[0], $color[1] . $color[1], $color[2] . $color[2] );
	} else {
		return $default;
	}

	//Convert hexadec to rgb
	$rgb = array_map( 'hexdec', $hex );

	//Check if opacity is set(rgba or rgb)
	if ( $opacity ) {
		if ( abs( $opacity ) > 1 ) {
			$opacity = 1.0;
		}
		$output = 'rgba(' . implode( ",", $rgb ) . ',' . $opacity . ')';
	} else {
		$output = 'rgb(' . implode( ",", $rgb ) . ')';
	}

	//Return rgb(a) color string
	return $output;
}

function igd_get_user_gravatar( $user_id, $size = 32 ) {
	$user = get_user_by( 'id', $user_id );

	if ( function_exists( 'get_wp_user_avatar' ) ) {
		$gravatar = get_wp_user_avatar( $user->user_email, $size );
	} else {
		$gravatar = get_avatar( $user->user_email, $size );
	}

	if ( empty( $gravatar ) ) {
		$gravatar = sprintf( '<img src="%s/images/user-icon.png" height="%s" />', IGD_ASSETS, $size );
	}

	return $gravatar;
}

function igd_get_user_ip() {
	if ( ! empty( $_SERVER['HTTP_CLIENT_IP'] ) ) {
		//check ip from share internet
		$ip = $_SERVER['HTTP_CLIENT_IP'];
	} elseif ( ! empty( $_SERVER['HTTP_X_FORWARDED_FOR'] ) ) {
		//to check ip is pass from proxy
		$ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
	} else {
		$ip = $_SERVER['REMOTE_ADDR'];
	}

	return $ip;
}

if ( ! function_exists( 'tutor_get_template' ) ) {
	function tutor_get_template( $template = null, $tutor_pro = false ) {
		if ( ! $template ) {
			return false;
		}

		$template = str_replace( '.', DIRECTORY_SEPARATOR, $template );

		/**
		 * Get template first from child-theme if exists
		 * If child theme not exists, then get template from parent theme
		 */
		$template_location = trailingslashit( get_stylesheet_directory() ) . "tutor/{$template}.php";
		if ( ! file_exists( $template_location ) ) {
			$template_location = trailingslashit( get_template_directory() ) . "tutor/{$template}.php";
		}
		$file_in_theme = $template_location;
		if ( ! file_exists( $template_location ) ) {
			$template_location = trailingslashit( tutor()->path ) . "templates/{$template}.php";

			if ( $tutor_pro && function_exists( 'tutor_pro' ) ) {
				$pro_template_location = trailingslashit( tutor_pro()->path ) . "templates/{$template}.php";
				if ( file_exists( $pro_template_location ) ) {
					$template_location = trailingslashit( tutor_pro()->path ) . "templates/{$template}.php";
				}
			}

			//Integrate Google Drive Templates
			if ( ! file_exists( $template_location ) ) {
				$template_location = trailingslashit( IGD_INCLUDES ) . "integrations/templates/tutor/{$template}.php";
			}

			if ( ! file_exists( $template_location ) ) {
				$warning_msg = __( 'The file you are trying to load does not exist in your theme or Tutor LMS plugin location. If you are extending the Tutor LMS plugin, please create a php file here: ', 'integrate-google-drive' );
				$warning_msg = $warning_msg . "<code>$file_in_theme</code>";
				$warning_msg = apply_filters( 'tutor_not_found_template_warning_msg', $warning_msg );
				echo wp_kses( $warning_msg, array( 'code' => true ) );
				?>
				<?php
			}
		}

		return apply_filters( 'tutor_get_template_path', $template_location, $template );
	}

}

function igd_sanitize_array( $array ) {
	foreach ( $array as $key => &$value ) {
		if ( is_array( $value ) ) {
			$value = igd_sanitize_array( $value );
		} else {
			if ( in_array( $value, [ 'true', 'false' ] ) ) {
				$value = filter_var( $value, FILTER_VALIDATE_BOOLEAN );
			} elseif ( is_numeric( $value ) ) {
				if ( strpos( $value, '.' ) !== false ) {
					$value = floatval( $value );
				} elseif ( filter_var( $value, FILTER_VALIDATE_INT ) !== false && $value <= PHP_INT_MAX ) {
					$value = intval( $value );
				} else {
					// Keep large integers or non-integer values as string
					$value = $value;
				}
			} else {
				$value = wp_kses_post( $value );
			}
		}
	}

	return $array;
}

function igd_should_filter_files( $filters ) {
	return ( ! empty( $filters['allowExtensions'] ) && empty( $filters['allowAllExtensions'] ) )
	       || ( ! empty( $filters['allowExceptExtensions'] ) && ! empty( $filters['allowAllExtensions'] ) )
	       || ( ! empty( $filters['nameFilterOptions'] ) && ( ! empty( $filters['allowNames'] ) && empty( $filters['allowAllNames'] ) ) || ( ! empty( $filters['allowExceptNames'] ) && ! empty( $filters['allowAllNames'] ) ) )
	       || ( isset( $filters['showFiles'] ) && empty( $filters['showFiles'] ) )
	       || ( isset( $filters['showFolders'] ) && empty( $filters['showFolders'] ) )
	       || ! empty( $filters['isGallery'] )
	       || ! empty( $filters['isMedia'] );
}

/**
 * The function checks if the current user has the required role or if the user is in the list of users with access
 * The access rights are defined in the settings page
 *
 * @param $access_right
 *
 * @return bool
 */
function igd_user_can_access( $access_right ) {

	if ( ! function_exists( 'wp_get_current_user' ) ) {
		include_once( ABSPATH . "wp-includes/pluggable.php" );
	}

	if ( ! is_user_logged_in() ) {
		return false;
	}

	$current_user = wp_get_current_user();

	if ( ! is_object( $current_user ) ) {
		return false;
	}

	$access_users = igd_get_settings( "access" . str_replace( ' ', '', ucwords( str_replace( '_', ' ', $access_right ) ) ) . "Users", [ 'administrator' ] );

	$can_access = ! empty( array_intersect( $current_user->roles, $access_users ) ) || in_array( $current_user->ID, $access_users ) || ( is_multisite() && is_super_admin() );

	// Check if privateFoldersInAdminDashboard is enabled
	if ( 'file_browser' == $access_right ) {
		$private_folders_in_admin_dashboard = igd_get_settings( 'privateFoldersInAdminDashboard', false );

		if ( $private_folders_in_admin_dashboard ) {
			$folders = get_user_option( 'folders', get_current_user_id() );

			$can_access = $can_access || ! empty( $folders );
		}
	}

	// Check if media library integration is enabled
	if ( $can_access && 'media_library' == $access_right ) {
		$can_access = Integration::instance()->is_active( 'media-library' );
	}

	return apply_filters( 'igd_can_access', $can_access, $access_right );
}

function igd_get_user_access_data() {
	$access_users = igd_get_settings( "accessFileBrowserUsers", [] );
	$data         = [];

	if ( in_array( 'administrator', $access_users ) ) {
		$key = array_search( 'administrator', $access_users );
		unset( $access_users[ $key ] );
	}

	if ( ! empty( $access_users ) ) {

		if ( ! function_exists( 'wp_get_current_user' ) ) {
			include_once( ABSPATH . "wp-includes/pluggable.php" );
		}

		$current_user = wp_get_current_user();

		// Get assigned folders for current user
		if ( $current_user ) {
			$user_folders = igd_get_settings( 'userFolders', [] );

			if ( in_array( $current_user->ID, $access_users ) && isset( $user_folders[ $current_user->ID ] ) ) {
				$folders = $user_folders[ $current_user->ID ];
			} elseif ( ! empty( array_intersect( $current_user->roles, $access_users ) ) ) {
				$folders = [];
				foreach ( $access_users as $role ) {
					if ( in_array( $role, $current_user->roles ) ) {
						$u_folders = ! empty( $user_folders[ $role ] ) ? $user_folders[ $role ] : [];
						$folders   = array_merge( $folders, $u_folders );
					}
				}
			}
		}
	}


	// Check if privateFoldersInAdminDashboard is enabled
	if ( ! current_user_can( 'administrator' ) ) {
		$private_folders_in_admin_dashboard = igd_get_settings( 'privateFoldersInAdminDashboard', false );

		if ( $private_folders_in_admin_dashboard ) {
			$private_folders = get_user_option( 'folders', get_current_user_id() );

			// if not empty $private_folders, merge private folders with assigned folders
			if ( ! empty( $private_folders ) ) {
				$folders = ! empty( $folders ) ? array_merge( $folders, $private_folders ) : $private_folders;
			}
		}
	}


	if ( ! empty( $folders ) ) {

		$is_single_folder = count( array_unique( wp_list_pluck( $folders, 'id' ) ) ) == 1;

		// Get files from the single folder
		if ( $is_single_folder ) {

			$folder    = $folders[0];
			$folder_id = $folder['id'];

			$data['initParentFolder'] = $folder;

			$args = [
				'folder'      => $folder,
				'from_server' => true,
				'limit'       => 100,
			];

			$transient = get_transient( 'igd_latest_fetch_' . $folder_id );
			if ( $transient ) {
				$args['from_server'] = false;
			} else {
				set_transient( 'igd_latest_fetch_' . $folder_id, true, 60 * MINUTE_IN_SECONDS );
			}

			// Fetch files
			$account_id = ! empty( $folder['accountId'] ) ? $folder['accountId'] : '';
			$files_data = App::instance( $account_id )->get_files( $args );

			if ( isset( $files_data['files'] ) ) {
				$data['initFolders'] = $files_data['files'];
			}

			// Update the arguments for the next iteration
			$pageNumber                             = ! empty( $files_data['nextPageNumber'] ) ? $files_data['nextPageNumber'] : 0;
			$data['initParentFolder']['pageNumber'] = $pageNumber;

		} else {
			$data['initFolders'] = $folders;
		}
	}

	return $data;
}

function igd_contains_tags( $type = '', $template = '' ) {
	// Define tags
	$user_tags = [
		'%user_login%',
		'%user_email%',
		'%first_name%',
		'%last_name%',
		'%display_name%',
		'%user_id%',
		'%user_role%',
		'%user_meta_{key}%'
	];

	$post_tags = [
		'%post_id%',
		'%post_title%',
		'%post_slug%',
		'%post_author%',
		'%post_date%',
		'%post_modified%',
		'%post_type%',
		'%post_status%',
		'%post_category%',
		'%post_tags%',
		'%post_meta_{key}%'
	];

	$woocommerce_tags = [
		'%wc_product_name%',
		'%wc_product_id%',
		'%wc_product_sku%',
		'%wc_product_slug%',
		'%wc_product_price%',
		'%wc_product_sale_price%',
		'%wc_product_regular_price%',
		'%wc_product_tags%',
		'%wc_product_type%',
		'%wc_product_status%',
		'%wc_product_meta_{key}%',
	];

	$post_tags = array_merge( $post_tags, $woocommerce_tags );  // Merge post tags and WooCommerce tags

	if ( $type == 'user' ) {
		return array_reduce( $user_tags, function ( $carry, $item ) use ( $template ) {
				return $carry || strpos( $template, $item ) !== false;
			}, false ) || strpos( $template, '%user_meta_' ) !== false;
	} elseif ( $type == 'post' ) {
		return array_reduce( $post_tags, function ( $carry, $item ) use ( $template ) {
				return $carry || strpos( $template, $item ) !== false;
			}, false ) || strpos( $template, '%post_meta_' ) !== false;
	} elseif ( $type == 'woocommerce' ) {
		return array_reduce( $woocommerce_tags, function ( $carry, $item ) use ( $template ) {
			return $carry || strpos( $template, $item ) !== false;
		}, false );
	} elseif ( $type == 'field' ) {
		return strpos( $template, '%field_' ) !== false;
	} elseif ( $type == 'field_id' ) {
		return strpos( $template, '%field_id_' ) !== false;
	}

	return false;

}

function igd_replace_template_tags( $data, $extra_tag_values = [] ) {

	$name_template = ! empty( $data['name'] ) ? $data['name'] : '%user_login% (%user_email%)';

	$date      = date( 'Y-m-d' );
	$time      = date( 'H:i' );
	$unique_id = uniqid();

	$search = [
		'%date%',
		'%time%',
		'%unique_id%',
	];

	$replace = [
		$date,
		$time,
		$unique_id,
	];

	$name = str_replace( $search, $replace, $name_template );

	// Handle form data
	if ( ! empty( $data['form'] ) ) {
		$form = $data['form'];

		$search = array_merge( $search, [
			'%form_title%',
			'%form_id%',
			'%entry_id%',
		] );

		$replace = array_merge( $replace, [
			$form['form_title'],
			$form['form_id'] ?? '',
			! empty( $form['entry_id'] ) ? $form['entry_id'] : '',
		] );

		$name = str_replace( $search, $replace, $name );

	}

	// Handle file data
	if ( ! empty( $data['file'] ) ) {
		$file = $data['file'];

		$search = array_merge( $search, [
			'%file_name%',
			'%file_extension%',
			'%queue_index%',
		] );

		$replace = array_merge( $replace, [
			$file['file_name'],
			$file['file_extension'],
			$file['queue_index'],
		] );

		$name = str_replace( $search, $replace, $name );

	}

	// Handle post data
	if ( ! empty( $data['post'] ) ) {
		$post = $data['post'];

		$post_id       = $post->ID;
		$post_title    = $post->post_title;
		$post_slug     = $post->post_name;
		$post_author   = get_the_author_meta( 'display_name', $post->post_author );
		$post_date     = $post->post_date;
		$post_modified = $post->post_modified;
		$post_type     = $post->post_type;
		$post_status   = $post->post_status;

		$post_categories = get_the_category( $post_id );
		if ( ! is_wp_error( $post_categories ) && ! empty( $post_categories ) ) {
			$post_categories = implode( ', ', wp_list_pluck( $post_categories, 'name' ) );
		} else {
			$post_categories = '';
		}

		$post_tags = get_the_tags( $post_id );
		if ( ! is_wp_error( $post_tags ) && ! empty( $post_tags ) ) {
			$post_tags = implode( ', ', wp_list_pluck( $post_tags, 'name' ) );
		} else {
			$post_tags = '';
		}

		$search = array_merge( $search, [
			'%post_id%',
			'%post_title%',
			'%post_slug%',
			'%post_author%',
			'%post_date%',
			'%post_modified%',
			'%post_type%',
			'%post_status%',
			'%post_category%',
			'%post_tags%',
		] );

		$replace = array_merge( $replace, [
			$post_id,
			$post_title,
			$post_slug,
			$post_author,
			$post_date,
			$post_modified,
			$post_type,
			$post_status,
			$post_categories,
			$post_tags,
		] );

		$name = str_replace( $search, $replace, $name );

		//Check if %post_meta_{key}% is in the name template
		if ( preg_match_all( '/%post_meta_(.*?)%/', $name, $matches ) ) {
			foreach ( $matches[1] as $meta_key ) {
				$meta_key_trimmed = trim( $meta_key );
				$meta_value       = get_post_meta( $post_id, $meta_key_trimmed, true );

				$name = str_replace( '%post_meta_' . $meta_key_trimmed . '%', $meta_value, $name );
			}
		}

	}

	// Handle user data
	if ( ! empty( $data['user'] ) ) {
		$user = $data['user'];


		$user_login   = $user->user_login;
		$user_email   = $user->user_email;
		$display_name = $user->display_name;
		$first_name   = $user->first_name;
		$last_name    = $user->last_name;
		$user_role    = ! empty( $user->roles ) ? implode( ', ', $user->roles ) : '';

		$search = array_merge( $search, [
			'%user_id%',
			'%user_login%',
			'%user_email%',
			'%display_name%',
			'%first_name%',
			'%last_name%',
			'%user_role%',
		] );

		$replace = array_merge( $replace, [
			$user->ID,
			$user_login,
			$user_email,
			$display_name,
			$first_name,
			$last_name,
			$user_role,
		] );

		$name = str_replace( $search, $replace, $name );

		$user_id = $user->ID;

		//Check if %user_meta_{key}% is in the name template
		if ( preg_match_all( '/%user_meta_(.*?)%/', $name_template, $matches ) ) {
			foreach ( $matches[1] as $meta_key ) {
				$meta_key_trimmed = trim( $meta_key );
				$meta_value       = get_user_meta( $user_id, $meta_key_trimmed, true );

				$name_template = str_replace( '%user_meta_' . $meta_key_trimmed . '%', $meta_value, $name_template );
			}

			$name = $name_template;
		}


	}

	// Handle wc order data
	if ( ! empty( $data['wc_order'] ) ) {
		$order = $data['wc_order'];

		$order_id = $order->get_id();

		$order_date = $order->get_date_created()->date( 'Y-m-d' );

		$search = array_merge( $search, [
			'%wc_order_id%',
			'%wc_order_date%',
		] );

		$replace = array_merge( $replace, [
			$order_id,
			$order_date,
		] );

		$name = str_replace( $search, $replace, $name );

		//Check if %wc_order_meta_{key}% is in the name template
		if ( preg_match_all( '/%wc_order_meta_(.*?)%/', $name, $matches ) ) {

			foreach ( $matches[1] as $meta_key ) {
				$meta_key_trimmed = trim( $meta_key );
				$meta_value       = get_post_meta( $order_id, $meta_key_trimmed, true );

				$name = str_replace( '%wc_order_meta_' . $meta_key_trimmed . '%', $meta_value, $name );
			}

		}


	}

	// Handle wc product data
	if ( ! empty( $data['wc_product'] ) ) {
		$product = $data['wc_product'];

		$product_id            = $product->get_id();
		$product_name          = $product->get_name();
		$product_sku           = $product->get_sku();
		$product_slug          = $product->get_slug();
		$product_price         = $product->get_price();
		$product_sale_price    = $product->get_sale_price();
		$product_regular_price = $product->get_regular_price();
		$product_type          = $product->get_type();
		$product_status        = $product->get_status();

		$product_category_ids = $product->get_category_ids();
		$product_tag_ids      = $product->get_tag_ids();

		$product_categories = array();
		foreach ( $product_category_ids as $category_id ) {
			$term = get_term( $category_id, 'product_cat' );
			if ( ! is_wp_error( $term ) && $term ) {
				$product_categories[] = $term->name;
			}
		}

		$product_tags = array();
		foreach ( $product_tag_ids as $tag_id ) {
			$term = get_term( $tag_id, 'product_tag' );
			if ( ! is_wp_error( $term ) && $term ) {
				$product_tags[] = $term->name;
			}
		}

		$product_categories = implode( ', ', $product_categories );
		$product_tags       = implode( ', ', $product_tags );

		$search = array_merge( $search, [
			'%wc_product_id%',
			'%wc_product_name%',
			'%wc_product_sku%',
			'%wc_product_slug%',
			'%wc_product_price%',
			'%wc_product_sale_price%',
			'%wc_product_regular_price%',
			'%wc_product_categories%',
			'%wc_product_tags%',
			'%wc_product_type%',
			'%wc_product_status%',
		] );

		$replace = array_merge( $replace, [
			$product_id,
			$product_name,
			$product_sku,
			$product_slug,
			$product_price,
			$product_sale_price,
			$product_regular_price,
			$product_categories,
			$product_tags,
			$product_type,
			$product_status,
		] );

		$name = str_replace( $search, $replace, $name );

		//Check if %wc_product_meta_{key}% is in the name template
		if ( preg_match_all( '/%wc_product_meta_(.*?)%/', $name, $matches ) ) {
			foreach ( $matches[1] as $meta_key ) {
				$meta_key_trimmed = trim( $meta_key );
				$meta_value       = get_post_meta( $product_id, $meta_key_trimmed, true );

				$name = str_replace( '%wc_product_meta_' . $meta_key_trimmed . '%', $meta_value, $name );
			}

		}

	}

	// Handle extra tag values
	if ( ! empty( $extra_tag_values ) ) {
		$name = str_replace( array_keys( $extra_tag_values ), array_values( $extra_tag_values ), $name );
	}

	return $name;
}

function igd_is_gmail( $email ) {
	// Check if it's a valid email
	if ( filter_var( $email, FILTER_VALIDATE_EMAIL ) ) {
		// Split the email at '@' and check the domain
		$parts = explode( '@', $email );
		if ( count( $parts ) === 2 && $parts[1] === 'gmail.com' ) {
			return true; // It's a valid Gmail address
		}
	}

	return false; // It's either not a valid email or not a Gmail address
}

function igd_sort_files( $files, $sort ) {

	if ( empty( $sort ) ) {
		$sort = [ 'sortBy' => 'name', 'sortDirection' => 'asc' ];
	}

	$sort_by        = $sort['sortBy'];
	$sort_direction = $sort['sortDirection'] === 'asc' ? SORT_ASC : SORT_DESC;

	$is_random = 'random' == $sort_by;

	// Initializing sorting arrays
	$sort_array           = [];
	$sort_array_secondary = [];

	// Populating sorting arrays and adding isFolder attribute to files
	foreach ( $files as $key => $file ) {

		if ( empty( $file[ $sort_by ] ) ) {
			$sort_array_secondary[ $key ] = 0;
			$file[ $sort_by ]             = '';
		}

		$files[ $key ]['isFolder'] = igd_is_dir( $file );
		$sort_array[ $key ]        = $files[ $key ]['isFolder'];

		if ( ! $is_random ) {
			// Convert date to timestamp if needed
			$sort_array_secondary[ $key ] = in_array( $sort_by, [
				'created',
				'updated'
			] ) ? strtotime( $file[ $sort_by ] ) : $file[ $sort_by ];
		}
	}

	if ( $is_random ) {
		shuffle( $files );
	} else {
		array_multisort( $sort_array, SORT_DESC, $sort_array_secondary, $sort_direction, SORT_NATURAL | SORT_FLAG_CASE, $files );
	}

	return $files;
}

function igd_get_active_account_id( $user_id = null ) {
	$active_account = Account::instance( $user_id )->get_active_account();

	return ! empty( $active_account ) ? $active_account['id'] : null;
}

function igd_get_image_size( $url, $referer = '' ) {
	// Set headers
	$headers = array( 'Range: bytes=0-131072' );
	if ( ! empty( $referer ) ) {
		array_push( $headers, 'Referer: ' . $referer );
	}

	// Get remote image
	$ch = curl_init();
	curl_setopt( $ch, CURLOPT_URL, $url );
	curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1 );
	curl_setopt( $ch, CURLOPT_FOLLOWLOCATION, 1 );
	$data        = curl_exec( $ch );
	$http_status = curl_getinfo( $ch, CURLINFO_HTTP_CODE );
	$curl_errno  = curl_errno( $ch );
	curl_close( $ch );

	// Get network stauts
	if ( (int) $http_status !== 200 ) {
		return array( 0, 0 );
	}

	// Process image
	$image = imagecreatefromstring( $data );
	$dims  = array( imagesx( $image ), imagesy( $image ) );
	imagedestroy( $image );

	return $dims;
}