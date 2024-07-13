<?php
/**
 * Display Video Google Drive
 *
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$video_info = tutor_utils()->get_video_info();
$poster     = tutor_utils()->avalue_dot( 'poster', $video_info );
$poster_url = $poster ? wp_get_attachment_url( $poster ) : '';

do_action( 'tutor_lesson/single/before/video/google_drive' );


?>

<?php if ( $video_info ) : ?>
	<div class="tutor-video-player">
		<input type="hidden" id="tutor_video_tracking_information" value="<?php echo esc_attr( json_encode( $jsonData ?? null ) ); ?>">
		<div class="loading-spinner" aria-hidden="true"></div>
		<video poster="<?php echo esc_url( $poster_url ); ?>" class="tutorPlayer" playsinline controls >
			<source src="<?php echo esc_url( tutor_utils()->array_get( 'source_google_drive', $video_info ) ); ?>" type="<?php echo esc_attr( tutor_utils()->avalue_dot( 'type', $video_info ) ); ?>">
		</video>
	</div>
<?php endif; ?>

<?php do_action( 'tutor_lesson/single/after/video/google_drive' ); ?>
