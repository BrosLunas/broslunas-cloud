<?php

use IGD\Shortcode_Builder;

defined( 'ABSPATH' ) || exit;


?>

<!DOCTYPE html>
<html xmlns:v="urn:schemas-microsoft-com:vml" xmlns:o="urn:schemas-microsoft-com:office:office" lang="en" class="os-html">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo esc_html($subject); ?></title>

    <style>
        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            padding: 0;
        }

        a[x-apple-data-detectors] {
            color: inherit !important;
            text-decoration: inherit !important;
        }

        #MessageViewBody a {
            color: inherit;
            text-decoration: none;
        }

        p {
            line-height: inherit
        }

        .desktop_hide,
        .desktop_hide table {
            mso-hide: all;
            display: none;
            max-height: 0px;
            overflow: hidden;
        }

        .image_block img + div {
            display: none;
        }

        @media (max-width: 720px) {
            .desktop_hide table.icons-inner {
                display: inline-block !important;
            }

            .icons-inner {
                text-align: center;
            }

            .icons-inner td {
                margin: 0 auto;
            }

            .mobile_hide {
                display: none;
            }

            .row-content {
                width: 100% !important;
            }

            .stack .column {
                width: 100%;
                display: block;
            }

            .mobile_hide {
                min-height: 0;
                max-height: 0;
                max-width: 0;
                overflow: hidden;
                font-size: 0px;
            }

            .desktop_hide,
            .desktop_hide table {
                display: table !important;
                max-height: none !important;
            }

            .row-2 .column-1 .block-1.paragraph_block td.pad > div {
                text-align: center !important;
            }

            .row-2 .column-1 {
                padding: 5px 25px 20px !important;
            }
        }
    </style>
</head>

<body style="background-color: #f7f7f7; margin: 0; padding: 0; -webkit-text-size-adjust: none; text-size-adjust: none;"
      class="os-host os-theme-dark os-host-resize-disabled os-host-scrollbar-horizontal-hidden os-host-transition os-host-overflow os-host-overflow-y"
      data-new-gr-c-s-check-loaded="14.1119.0" data-gr-ext-installed="">

<div class="os-resize-observer-host observed">
    <div class="os-resize-observer" style="left: 0px; right: auto;"></div>
</div>

<div class="os-padding">
    <div class="os-viewport os-viewport-native-scrollbars-invisible" style="overflow-y: scroll;">
        <div class="os-content" style="padding: 0px; height: 100%; width: 100%;">
            <table class="nl-container" width="100%" border="0" cellpadding="0" cellspacing="0" role="presentation"
                   style="mso-table-lspace: 0pt; mso-table-rspace: 0pt; background-color: #f7f7f7;">
                <tbody>
                <tr>
                    <td>

                        <!-- Header Space -->
                        <table class="row row-1" align="center" width="100%" border="0" cellpadding="0" cellspacing="0"
                               role="presentation" style="mso-table-lspace: 0pt; mso-table-rspace: 0pt;">
                            <tbody>
                            <tr>
                                <td>
                                    <table class="row-content stack" align="center" border="0" cellpadding="0"
                                           cellspacing="0" role="presentation"
                                           style="mso-table-lspace: 0pt; mso-table-rspace: 0pt; border-radius: 0; color: #000; width: 700px; margin: 0 auto;"
                                           width="700">
                                        <tbody>
                                        <tr>
                                            <td class="column column-1" width="100%"
                                                style="mso-table-lspace: 0pt; mso-table-rspace: 0pt; font-weight: 400; text-align: left; padding-bottom: 5px; padding-top: 5px; vertical-align: top; border-top: 0px; border-right: 0px; border-bottom: 0px; border-left: 0px;">
                                                <div class="spacer_block block-1"
                                                     style="height:15px;line-height:15px;font-size:1px;">
                                                </div>
                                            </td>
                                        </tr>
                                        </tbody>
                                    </table>
                                </td>
                            </tr>
                            </tbody>
                        </table>

                        <!-- Header -->
                        <table class="row row-2" align="center" width="100%" border="0" cellpadding="0" cellspacing="0"
                               role="presentation" style="mso-table-lspace: 0pt; mso-table-rspace: 0pt;">
                            <tbody>
                            <tr>
                                <td>
                                    <table class="row-content stack" align="center" border="0" cellpadding="0"
                                           cellspacing="0" role="presentation"
                                           style="mso-table-lspace: 0pt; mso-table-rspace: 0pt; border-radius: 0; color: #000; background-color: #4f5aba; background-image: url('https://d1oco4z2z1fhwp.cloudfront.net/templates/default/7826/Header-bg.png'); background-repeat: no-repeat; background-size: cover; width: 700px; margin: 0 auto;"
                                           width="700">
                                        <tbody>
                                        <tr>
                                            <td class="column column-1" width="100%"
                                                style="mso-table-lspace: 0pt; mso-table-rspace: 0pt; font-weight: 400; text-align: left; padding-bottom: 15px; padding-left: 25px; padding-right: 30px; padding-top: 15px; vertical-align: middle; border-top: 0px; border-right: 0px; border-bottom: 0px; border-left: 0px;">
                                                <table class="paragraph_block block-1" width="100%" border="0"
                                                       cellpadding="0" cellspacing="0" role="presentation"
                                                       style="mso-table-lspace: 0pt; mso-table-rspace: 0pt; word-break: break-word;">
                                                    <tbody>
                                                    <tr>
                                                        <td class="pad">
                                                            <div style="color:#ffffff;direction:ltr;font-family:Inter, sans-serif;font-size:24px;font-weight:700;letter-spacing:0px;line-height:120%;text-align:center;mso-line-height-alt:28.799999999999997px;">
                                                                <p style="margin: 0;"><?php esc_html_e( 'Client Photo Proof Selection', 'integrate-google-drive' ); ?></p>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                    </tbody>
                                                </table>
                                            </td>
                                        </tr>
                                        </tbody>
                                    </table>
                                </td>
                            </tr>
                            </tbody>
                        </table>

                        <!-- Content -->
                        <table class="row row-3" align="center" width="100%" border="0" cellpadding="0" cellspacing="0"
                               role="presentation" style="mso-table-lspace: 0pt; mso-table-rspace: 0pt;">
                            <tbody>
                            <tr>
                                <td>
                                    <table class="row-content stack" align="center" border="0" cellpadding="0"
                                           cellspacing="0" role="presentation"
                                           style="mso-table-lspace: 0pt; mso-table-rspace: 0pt; background-color: #fff; color: #000; width: 700px; margin: 0 auto;"
                                           width="700">
                                        <tbody>
                                        <tr>
                                            <td class="column column-1" width="100%"
                                                style="mso-table-lspace: 0pt; mso-table-rspace: 0pt; font-weight: 400; text-align: left; padding-bottom: 35px; padding-left: 25px; padding-right: 25px; padding-top: 35px; vertical-align: top; border-top: 0px; border-right: 0px; border-bottom: 0px; border-left: 0px;">
                                                <table class="paragraph_block block-1" width="100%" border="0"
                                                       cellpadding="0" cellspacing="0" role="presentation"
                                                       style="mso-table-lspace: 0pt; mso-table-rspace: 0pt; word-break: break-word;">
                                                    <tbody>
                                                    <tr>
                                                        <td class="pad" style="padding-top:30px;">
                                                            <div style="color:#201f42;direction:ltr;font-family:Inter, sans-serif;font-size:18px;font-weight:400;letter-spacing:0px;line-height:180%;text-align:left;mso-line-height-alt:32.4px;">
                                                                <p style="margin: 0; margin-bottom: 0px;">
																	<?php printf( esc_html__( 'Hello Admin,', 'integrate-google-drive' ) ); ?>
                                                                </p>

                                                                <p style="margin: 0;">
                                                                    <br> <?php esc_html_e( 'You have a new photo selection from: ', 'integrate-google-drive' ); ?>

																	<?php if ( is_user_logged_in() ) {
																		$user       = wp_get_current_user();
																		$user_name  = $user->display_name;
																		$user_email = $user->user_email;

																		?>

                                                                        <a href="<?php echo esc_url( get_edit_user_link( get_current_user_id() ) ); ?>"
                                                                           style="text-decoration: underline; color: #201f42;"
                                                                           rel="noopener"><strong><?php echo esc_html( $user_name ); ?></strong>
                                                                            (<?php echo esc_html( $user_email ); ?>)</a>

																	<?php } else {
																		esc_html_e( 'one of your client', 'integrate-google-drive' );
																	} ?>

																	<?php

																	$shortcode_id = ! empty( $_POST['shortcode_id'] ) ? sanitize_text_field( $_POST['shortcode_id'] ) : '';
																	$shortcode_title = ! empty( $shortcode_id ) ? Shortcode_Builder::instance()->get_shortcode( $shortcode_id )->title : '';

																	printf( esc_html__(
																	// translators: %s is the shortcode title
																		'for the %s gallery.', 'integrate-google-drive' ), $shortcode_title );

																	?>

																	<?php

																	$referrer     = wp_get_referer();

																	if ( url_to_postid( $referrer ) ) {

																		$post = get_post( url_to_postid( $referrer ) );

																		$post_title = $post->post_title;
																		$page_link  = get_permalink( $post->ID );

																		printf( esc_html__(
                                                                                // translators: %1$s is the post title
                                                                                'on the %s page.', 'integrate-google-drive' ), "<a href='$page_link'>$post_title</a>" );
																	}

																	?>
                                                                </p>

																<?php if ( ! empty( $message ) ) { ?>
                                                                    <p style="margin-bottom:0;">
                                                                        <strong><?php esc_html_e( 'Client Message:', 'integrate-google-drive' ); ?></strong>
                                                                        <br>
																		<?php echo esc_html( $message ); ?>
                                                                    </p>
																<?php } ?>


                                                                <p style="margin-bottom:0;">
                                                                    <br>
                                                                    <strong>Selected Images
                                                                        (<?php echo count( $selected ); ?>):</strong>


                                                                    <a href="<?php ;

																	// Create the AJAX URL
																	$base_ajax_url = admin_url( 'admin-ajax.php' );
                                                                    // Map the selected items for the 'filedata' parameter
                                                                    $fileData = array_map(function($item) {
	                                                                    return [
		                                                                    'id'   => $item['id'],
		                                                                    'name' => $item['name'],
	                                                                    ];
                                                                    }, $selected);

                                                                    // Build the query data array
                                                                    $queryData = [
	                                                                    'action'   => 'igd_photo_proof_download',
	                                                                    'filedata' => $fileData,
                                                                        'nonce'    => wp_create_nonce( 'igd_photo_proof_download' ),
                                                                    ];

                                                                    // Generate the full AJAX URL
                                                                    $ajax_url = add_query_arg($queryData, $base_ajax_url);

																	echo esc_url_raw( $ajax_url ); ?>"
                                                                       style="background-color: #4F5ABA; color: white; padding: 8px 12px; text-align: center; text-decoration: none; display: inline-block; border-radius: 4px;float: right;font-size: 15px;line-height: 1;"><?php esc_html_e('Download CSV', 'integrate-google-drive'); ?></a>
                                                                </p>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                    </tbody>
                                                </table>
                                            </td>
                                        </tr>
                                        </tbody>
                                    </table>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <table class="row-content stack" align="center" border="0" cellpadding="0"
                                           cellspacing="0" role="presentation"
                                           style="mso-table-lspace: 0pt; mso-table-rspace: 0pt; background-color: #fff; color: #000; width: 700px;
                                           margin: 0 auto;
                                           padding: 0 20px;
                                           border-collapse: separate;
    border-spacing: 0 10px;
                                           "
                                           width="700">
                                        <thead>
                                        <tr>
                                            <th><?php esc_html_e('Image', 'integrate-google-drive'); ?></th>
                                            <th style="text-align: left; width: 50%;"><?php esc_html_e('Title', 'integrate-google-drive'); ?></th>
                                            <th><?php esc_html_e('Link', 'integrate-google-drive'); ?></th>
                                        </tr>
                                        </thead>
                                        <tbody>

										<?php foreach ( $selected as $file ) {
											$icon_url = igd_get_thumbnail_url( $file, 'custom', [
												'w' => 64,
												'h' => 64
											] );
											$file_url = sprintf( 'https://drive.google.com/file/d/%1$s/view', $file['id'] );
											?>
                                            <tr>
                                                <td style="text-align: center;">
                                                    <img src="<?php echo esc_url( $icon_url ); ?>" width="50"
                                                         height="50">
                                                </td>
                                                <td style="text-align: left;">
													<?php echo esc_html( $file['name'] ); ?>
                                                </td>
                                                <td style="text-align: center;">
                                                    <a href="<?php echo esc_url_raw( $file_url ); ?>"
                                                       style="background-color: #4CAF50; color: white; padding: 8px 12px; text-align: center; text-decoration: none; display: inline-block; border-radius: 4px;"><?php esc_html_e('View Image', 'integrate-google-drive'); ?></a>
                                                </td>
                                            </tr>
										<?php } ?>
                                        <!-- End of example row -->
                                        </tbody>
                                    </table>
                                </td>
                            </tr>
                            </tbody>
                        </table>

                        <!-- Footer -->
                        <table class="row row-4" align="center" width="100%" border="0" cellpadding="0" cellspacing="0"
                               role="presentation"
                               style="margin-top: 20px;margin-bottom: 20px; mso-table-lspace: 0pt; mso-table-rspace: 0pt;">
                            <tbody>
                            <tr>
                                <td style="font-family: Inter, sans-serif; font-size: 15px; color: #9d9d9d; vertical-align: middle; text-align: center;">
                                    <a href="<?php echo esc_url( home_url() ); ?>"
                                       target="_blank"
                                       style="color: #9d9d9d; text-decoration: none;">
										<?php printf(
                                                // translators: %s is the site name
                                                __( 'This email has been generated by Integrate Google Drive plugin at %s', 'integrate-google-drive' ), get_bloginfo( 'name' ) ); ?></a>
                                </td>
                            </tr>
                            </tbody>
                        </table>
                    </td>
                </tr>
                </tbody>
            </table><!-- End -->
        </div>
    </div>
</div>

</body>
</html>