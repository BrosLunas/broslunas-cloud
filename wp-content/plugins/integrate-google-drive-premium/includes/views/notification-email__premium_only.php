<?php

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

if ( is_user_logged_in() ) {
	$user_name = sprintf( '<a style="text-decoration: none;color:#fff;" href="%s">%s</a>', get_edit_user_link( get_current_user_id() ), $user_name );
}

$site_link = sprintf( '<a style="text-decoration: none; color:#fff;" href="%s">%s</a>', esc_url( home_url() ), esc_html( get_bloginfo( 'name' ) ) );

// Translators: %1$s is the username, %2$s is the site link
$text = __( sprintf( '%s has downloaded the following files via %s', $user_name, $site_link ), 'integrate-google-drive' );

if ( 'upload' == $type ) {
	$text = sprintf(
	// Translators: %1$s is the username, %2$s is the site link
		__( '%1$s has uploaded the following files to your Google Drive via %2$s', 'integrate-google-drive' ), $user_name, $site_link );
} elseif ( 'delete' == $type ) {
	$text = sprintf( __(
	// Translators: %1$s is the username, %2$s is the site link
		'%1$s has deleted the following files via %2$s', 'integrate-google-drive' ), $user_name, $site_link );
} elseif ( 'search' == $type ) {
	$keyword = sanitize_text_field( $_POST['keyword'] );
	$text    = sprintf(
	// Translators: %1$s is the username, %2$s is the keyword, %3$s is the site link
		__( '%1$s has searched for %2$s on the following folders via %3$s', 'integrate-google-drive' ), $user_name, "<strong>$keyword</strong>", $site_link );
} elseif ( 'play' == $type ) {
	$text = sprintf(
	// Translators: %1$s is the username, %2$s is the site link
		__( '%1$s has played the following files via %2$s', 'integrate-google-drive' ), $user_name, $site_link );
} elseif ( 'view' == $type ) {
	$text = sprintf(
	// Translators: %1$s is the username, %2$s is the site link
		__( '%1$s has viewed the following files in your Google Drive via %2$s', 'integrate-google-drive' ), $user_name, $site_link );
} elseif ( 'download' == $type ) {
	$text = sprintf(
	// Translators: %1$s is the username, %2$s is the site link
		__( '%1$s has downloaded the following files via %2$s', 'integrate-google-drive' ),
		$user_name, $site_link
	);
}


$primary_color = igd_get_settings( 'primaryColor', '#3C82F6' );


?>

<!DOCTYPE html>
<html lang="<?php echo get_bloginfo( 'language' ); ?>">
<head>
    <meta charset="<?php echo get_bloginfo( 'charset' ); ?>">
    <title><?php echo $subject; ?></title>
</head>
<body>

<!-- Email Container -->
<table width="100%" border="0" cellspacing="0" cellpadding="0" style="font-family: Arial, 'Helvetica Neue', Helvetica, sans-serif; max-width: 600px; margin: auto; text-align: center;">

    <!-- Header -->
    <tr>
        <td style="background: <?php echo esc_attr($primary_color); ?>; padding: 20px;">
            <h2 style="color: #FFFFFF; margin: 0;"><?php echo esc_html__( 'Hi there,', 'integrate-google-drive' ); ?></h2>
            <p style="color: #FFFFFF; margin: 0;"><?php echo $text; ?></p>
        </td>
    </tr>

    <!-- File List -->
	<?php foreach ($files as $file):

		$name      = $file['name'];
		$size      = ! empty( $file['size'] ) ? $file['size'] : '';
		$view_link = ! empty( $file['webViewLink'] ) ? $file['webViewLink'] : '#';
		$thumbnail = ! empty( $file['thumbnailLink'] ) ? $file['thumbnailLink'] : igd_get_mime_icon( $file['type'] );

		?>
        <tr>
            <td style="background: #FFFFFF; padding: 15px;">
                <table width="100%" border="0" cellspacing="0" cellpadding="0">
                    <tr>
                        <!-- File Icon -->
                        <td style="padding: 5px; width: 40px;">
                            <img src="<?php echo esc_url($thumbnail);  ?>" alt="<?php echo esc_attr__( 'File Icon', 'integrate-google-drive' ); ?>" style="vertical-align: middle; height: 40px; width: 40px; border-radius: 4px;object-fit: cover;" height="40" width="40" />
                        </td>

                        <!-- File Name and Size -->
                        <td style="padding: 5px; border-bottom: 1px solid #EAEAEA; text-align: left;">
                            <a href="<?php echo esc_url( $view_link ); ?>" style="text-decoration: none; color: #333333;">
                                <h4 style="margin: 0;"><?php echo esc_html( $file['name'] ); ?></h4>
                                <p style="margin: 0; color: #999999;"><?php echo size_format( $file['size'] ); ?></p>
                            </a>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
	<?php endforeach; ?>

    <!-- Footer -->
    <tr>
        <td style="background: #EAEAEA; padding: 20px;">
            <p style="color: #333333; margin: 0;"><?php echo esc_html__( 'Best Regards,', 'integrate-google-drive' ); ?></p>
            <h3 style="color: <?php echo esc_attr($primary_color); ?>; margin: 0;"><?php bloginfo( 'name' ); ?></h3>
        </td>
    </tr>

    <!-- Additional Footer -->
    <tr>
        <td style="text-align:center; padding: 20px;">
            <p style="color: #777; margin: 0; font-size: 14px;">
				<?php echo esc_html__( 'This email has been generated from Integrate Google Drive at', 'integrate-google-drive' ) . ' '; ?>
                <a href="<?php echo esc_url( home_url() ); ?>" target="_blank" style="color: <?php echo esc_attr($primary_color); ?>; margin: 0; text-decoration: none;"><?php bloginfo( 'name' ); ?></a>.
            </p>
        </td>
    </tr>

</table>
</body>
</html>

