<?php

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );


$title = __( 'Weekly Statistics Summary', 'integrate-google-drive' );

if ( 'monthly' == $frequency ) {
	$title = __( 'Monthly Statistics Summary', 'integrate-google-drive' );
} elseif ( 'daily' == $frequency ) {
	$title = __( 'Daily Statistics Summary', 'integrate-google-drive' );
}


?>

<div style="background-color:#ECECEC;color:#595959;font-family:HelveticaNeue,Roboto,sans-serif;font-size:15px"
     class="email-wrap">

    <!-- Header Space -->
    <table align="center" border="0" cellpadding="0" cellspacing="0" role="presentation" style="width:100%;">
        <tbody>
        <tr>
            <td>
                <div style="Margin:0px auto;max-width:600px;">
                    <table align="center" border="0" cellpadding="0" cellspacing="0" role="presentation"
                           style="width:100%;">
                        <tbody>
                        <tr>
                            <td style="direction:ltr;font-size:0px;padding:20px 0;padding-bottom:0;text-align:center;vertical-align:top;">
                                <div class="mj-column-per-100 outlook-group-fix"
                                     style="font-size:13px;text-align:left;direction:ltr;display:inline-block;vertical-align:top;width:100%;">
                                    <table border="0" cellpadding="0" cellspacing="0" role="presentation"
                                           style="vertical-align:top;" width="100%">
                                        <tbody>
                                        <tr>
                                            <td style="font-size:0px;word-break:break-word;">
                                                <div style="height:25px;">&nbsp;</div>
                                            </td>
                                        </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </td>
                        </tr>
                        </tbody>
                    </table>
                </div>
            </td>
        </tr>
        </tbody>
    </table>

    <div class="email-body"
         style="padding:15px;box-shadow: 1px 4px 11px 0px rgb(0 0 0 / 15%);background: #ffffff;background-color: #ffffff;margin: 0px auto;max-width: 600px;">

        <!-- Header -->
        <table width="100%" border="0" align="center" style="margin-bottom: 15px;">
            <tbody>

            <tr>
                <td height="25"
                    style="background:#2FB44B;padding:15px 5%;font-size:1px;border-collapse:collapse;margin:0">
                    <p style="margin-top:0;margin-bottom:0;text-align: center">
                        <span style="color:#fff;font-size:16px;font-family:inherit;font-weight:bold;text-transform:uppercase;text-decoration:initial;line-height:40px;letter-spacing:normal"> <?php echo $title; ?></span>
                    </p>
                </td>
            </tr>
            </tbody>
        </table>

		<?php

		$tops = [
			'downloads' => [
				'data'  => $logs['downloads'],
				'title' => __( 'Top Downloads', 'integrate-google-drive' ),
			],
			'uploads'   => [
				'data'  => $logs['uploads'],
				'title' => __( 'Top Uploads', 'integrate-google-drive' ),
			],
			'streams'   => [
				'data'  => $logs['streams'],
				'title' => __( 'Top Streams', 'integrate-google-drive' ),
			],
			'previews'  => [
				'data'  => $logs['previews'],
				'title' => __( 'Top Previews', 'integrate-google-drive' ),
			],
		];

		?>

		<?php foreach ( $tops as $top ) {
			if ( ! empty( $top['data'] ) ) {
				?>
                <div class="top-table-wrap" style="margin-bottom: 30px;">
                    <!-- Table Title -->
                    <table>
                        <tr>
                            <td data-text="Titles" data-font="Primary" valign="middle"
                                style="font-family: Poppins, sans-serif; color: #2FB44B; font-size: 20px; line-height: 20px; font-weight: 600; letter-spacing: 0px; padding: 10px 0;"
                                contenteditable="true" data-gramm="false">
								<?php echo $top['title']; ?>
                            </td>
                        </tr>
                    </table>

                    <!-- Table Values -->
                    <table cellpadding="0" cellspacing="0" border="1" width="100%"
                           style="width:100%;font-family:HelveticaNeue,Roboto,sans-serif;font-size:15px; border-collapse:collapse">
                        <thead>
                        <tr>
                            <th style="padding:10px;text-align:left;font-weight:normal;color:#333;background:#f5f5f5;border:1px solid #e0e0e0;">
                                <u></u><b><?php _e( 'File', 'integrate-google-drive' ); ?></b><u></u>
                            </th>
                            <th style="padding:10px;text-align:left;font-weight:normal;color:#333;background:#f5f5f5;border:1px solid #e0e0e0;">
                                <u></u><b><?php _e( 'Count', 'integrate-google-drive' ); ?></b><u></u>
                            </th>
                        </tr>
                        </thead>
                        <tbody>
						<?php
						foreach ( $top['data'] as $key => $item ) {
							$is_odd = $key % 2;
							?>
                            <tr style="background:<?php echo $is_odd ? '#f5f5f5' : '#fff'; ?>;">
                                <td style="padding:8px;border:1px solid #e0e0e0;" valign="middle">
                                    <a style="text-decoration: none;"
                                       href="https://drive.google.com/file/d/<?php echo $item['id']; ?>/view?usp=drivesdk"
                                       target="_blank">
                                        <span style="text-decoration: none;color: #333;"
                                              class="sl"><?php echo $key + 1; ?>.</span>
                                        <img width="18" src="<?php echo igd_get_mime_icon( $item['file_type'] ); ?>"/>
                                        <span style="vertical-align: top;"><?php echo $item['file_name']; ?></span>
                                    </a>
                                </td>
                                <td style="padding:8px;border:1px solid #e0e0e0;">
									<?php echo $item['total']; ?>
                                </td>
                            </tr>
						<?php } ?>
                        </tbody>
                    </table>
                </div>
				<?php
			}
		}

		?>

		<?php if ( ! empty( $logs['events'] ) ) { ?>
            <div class="top-table-wrap" style="margin-bottom: 30px;">
                <!-- Table Title -->
                <table>
                    <tr>
                        <td data-text="Titles" data-font="Primary" valign="middle"
                            style="font-family: Poppins, sans-serif; color: #2FB44B; font-size: 20px; line-height: 20px; font-weight: 600; letter-spacing: 0px; padding: 10px 0;"
                            contenteditable="true" data-gramm="false">
							<?php _e( 'Latest Activities', 'integrate-google-drive' ); ?>
                        </td>
                    </tr>
                </table>

                <!-- Table Values -->
                <table cellpadding="0" cellspacing="0"
                       style="width:100%;font-family:HelveticaNeue,Roboto,sans-serif;font-size:15px;border-collapse: collapse;">
                    <tbody>
					<?php

					//$events = array_slice( $logs['events'], 0, 15 );
					$events = $logs['events'];

					foreach ( $events as $key => $item ) {
						$user_icon = igd_get_user_gravatar( $item['user_id'], 18 );

						$icon      = sprintf( '<img src="%s/images/statistics/%s.svg" alt="%s"/>', IGD_ASSETS, $item['type'], $item['type'] );
						$user_text = ! empty( $item['user_id'] ) ? sprintf( '<a style="vertical-align:top;text-decoration: none;color: #2FB44B;font-weight: 600;" href="%s/user-edit.php?user_id=%s" target="_blank">%s</a>', admin_url(), $item['user_id'], $item['username'] ) : __( 'A visitor', 'integrate-google-drive' );
						$file_text = sprintf( '<a style="font-weight:400;vertical-align:top;text-decoration: none;" href="https://drive.google.com/file/d/%s/view?usp=drivesdk" target="_blank">%s</a>', $item['file_id'], $item['file_name'] );

						$is_odd = $key % 2 == 0;

						?>
                        <tr style="background: <?php echo $is_odd ? '#f5f5f5' : '#fff'; ?>;border:1px solid #e0e0e0;">
                            <td style="padding:8px;border:1px solid #e0e0e0;" valign="middle">
                                <span style="text-decoration: none;color: #333;vertical-align:top;"
                                      class="sl"><?php echo $key + 1; ?>.</span>
                            </td>
                            <td style="padding:8px;border:1px solid #e0e0e0;" valign="middle">

								<?php echo $user_icon; ?>

								<?php echo $user_text; ?>
                                <span style="color: #3D3D3D;vertical-align:top;"><?php echo $item['type']; ?>ed the file</span>
								<?php echo $file_text; ?>
                            </td>
                            <td style="text-align:center;line-height:20px;padding:8px;border:1px solid #e0e0e0;color:#555;font-size: 14px;min-width: 90px;"
                                valign="middle">
								<?php echo date( 'Y-m-d', strtotime( $item['created_at'] ) ); ?>
                                <br>
								<?php echo date( 'H:i a', strtotime( $item['created_at'] ) ); ?>
                            </td>
                        </tr>
					<?php } ?>
                    </tbody>
                </table>

                <!-- Add a styled button to view all-->
                <table border="0" cellpadding="0" cellspacing="0" role="presentation" align="center"
                       style="border-collapse:separate;width:300px;line-height:100%; margin-top: 30px;">
                    <tbody>
                    <tr>
                        <td align="center" bgcolor="#5e6ebf" role="presentation"
                            style="border:none;border-radius:3px;cursor:auto;padding:10px 25px;background:#2FB44B;"
                            valign="middle">
                            <a
                                    href="<?php echo admin_url( 'admin.php?page=integrate-google-drive-statistics' ); ?>"
                                    style="background:#2FB44B;color:#ffffff;font-family:'Helvetica Neue', Helvetica, Arial, sans-serif;font-size:17px;font-weight:bold;line-height:120%;Margin:0;text-decoration:none;text-transform:none;"
                                    target="_blank"> <?php _e( 'View All Logs', 'integrate-google-drive' ); ?> </a></td>
                    </tr>
                    </tbody>
                </table>


            </div>
		<?php } ?>

    </div>


    <div style="font-family:'Helvetica Neue',Helvetica,Arial,sans-serif;box-sizing:border-box;font-size:14px;width:100%;clear:both;color:#999;margin:0;padding:20px">
        <table width="100%"
               style="font-family:'Helvetica Neue',Helvetica,Arial,sans-serif;box-sizing:border-box;font-size:14px;margin:0">
            <tbody>
            <tr style="font-family:'Helvetica Neue',Helvetica,Arial,sans-serif;box-sizing:border-box;font-size:14px;margin:0">
                <td align="center" valign="top"
                    style="font-family:'Helvetica Neue',Helvetica,Arial,sans-serif;box-sizing:border-box;font-size:12px;vertical-align:top;color:#999;text-align:center;margin:0;padding:0 0 20px">
                    <p>
						<?php _e( 'This email has been generated from Integrate Google Drive at', 'integrate-google-drive' ); ?>
                        <a href="<?php echo home_url(); ?>"><?php bloginfo( 'name' ) ?></a>.
                    </p>
                </td>
            </tr>
            </tbody>
        </table>
    </div>

</div>