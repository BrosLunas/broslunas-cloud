<?php

$logs = [

	'v.1.3.94' => [
		'date'        => '2024-04-21',
		'new'         => [
			'Added video playback thumbnail for video items in the gallery.',
			'Added option to display selected folder files for download/view links module.',
		],

		'enhancement' => [
			'Improved overall performance and security.',
		],

		'remove'      => [
			'Removed Gallery module from the free version.',
			'Removed Contact Form 7 integration from the free version.',
		],
	],

	'v.1.3.93' => [
		'date' => '2024-04-04',
		'fix'  => [
			'Fixed file browser not working in mobile',
		],
	],

	'v.1.3.92' => [
		'date'        => '2024-03-15',
		'new'         => [
			'Added description field for the uploader module files.',
		],
		'fix'         => [
			'Fixed media player not playing properly.',
			'Fixed large file download scan warning issue.',
			'Fixed statistics data not exporting properly.',
			'Fixed Google Drive media attachment not displaying in gallery.',
		],
		'enhancement' => [
			'Improved media library integration.',
			'Improved overall performance and security.',
		],
	],

	'v.1.3.9' => [
		'date'   => '2024-03-15',
		'update' => [
			'Improved overall performance and security.',
		],
	],

	'v.1.3.8' => [
		'date'        => '2024-03-01',
		'new'         => [
			'Added option to rename the files based on the form field values.',
			'Added setting to only allow specific folders in the media library.',
			'Added Google Drive ACF integration for the Dokan products.',
			'Added Media Library integration with Dokan.',
			'Added option to display the gallery and slider image overlay description always and on hover.',
			'Added option to enable/disable the auto-save settings.',
			'Added multiple files lazy load options (Pagination, Load More button, Scroll) for file browser and gallery shortcode module.',
			'Added files lazy load settings for admin file browser.',
		],
		'fix'         => [
			'Fixed gallery thumbnail not loading issue.',
			'Fixed large file download not completing issue.',
			'Fixed large video file not playing issue.',
			'Fixed elementor module builder white screen issue.',
			'Fixed Gravity form not submitting when has multiple Google Drive upload fields.',
			'Fixed %queue_index% file rename placeholder not working issue.',
			'Fixed file uploader not showing uploaded files on the WooCommerce product page.',
			'Fixed shortcode modules throws PHP errors for deleted files.',
			'Fixed data export/import not working issue.',
			'Fixed shortcode modules small font-size issue.',
			'Fixed reset settings not working.',
		],
		'enhancement' => [
			'Improved media library integration with Google Drive.',
			'Improved secure video playback and prevent unauthorized video downloads.',
			'Improved notification email template.',
			'Improved overall performance and security.',
		]
	],

	'v.1.3.7' => [
		'date' => '2024-01-14',
		'fix'  => [
			'Fixed media player not working issue.',
		],
	],

	'v.1.3.6' => [
		'date'        => '2023-12-27',
		'new'         => [
			'Added Media Library Integration.',
			'Added description to the slider carousel lightbox preview.',
			'Added option to enable/disable folder download.',
		],
		'fix'         => [
			'Fixed Ninja Forms Google Drive uploader extension filter not working properly.',
		],
		'enhancement' => [
			'Improved file import to the media library.',
		]
	],

	'v.1.3.4' => [
		'date' => '2023-12-03',
		'new'  => [
			'Added user role based private folder creation on user registration.',
			'Added sort option for slider module.',
			'Added random sort option for shortcode module files.',
		],
		'fix'  => [
			'Fixed uploader browse files button not working properly on IOS devices.',
			'Fixed search module not working properly.',
		],

	],

	'v.1.3.3' => [
		'date' => '2023-11-17',
		'fix'  => [
			'Fixed security issue',
			'Translations issue',
		],
	],


];


?>

<div id="what-new" class="getting-started-content content-what-new">
    <div class="content-heading">
        <h2><?php esc_html_e( 'Exploring the Latest Updates', 'integrate-google-drive' ); ?></h2>
        <p><?php esc_html_e( 'Dive Into the Recent Change Logs for Fresh Insights', 'integrate-google-drive' ); ?></p>
    </div>

	<?php
	$i = 0;
	foreach ( $logs as $v => $log ) { ?>
        <div class="log <?php echo esc_attr( $i == 0 ? 'active' : '' ); ?>">
            <div class="log-header">
                <span class="log-version"><?php echo esc_html( $v ); ?></span>
                <span class="log-date">(<?php echo esc_html( $log['date'] ); ?>)</span>

                <i class="<?php echo esc_attr( $i == 0 ? 'dashicons-arrow-up-alt2' : 'dashicons-arrow-down-alt2' ); ?> dashicons "></i>
            </div>

            <div class="log-body">
				<?php

				if ( ! empty( $log['new'] ) ) {
					printf( '<div class="log-section new"><h3>%s</h3>', __( 'New Features', 'integrate-google-drive' ) );
					foreach ( $log['new'] as $item ) {
						echo '<div class="log-item log-item-new"><i class="dashicons dashicons-plus-alt2"></i> <span>' . $item . '</span></div>';
					}
					echo '</div>';
				}


				if ( ! empty( $log['fix'] ) ) {
					printf( '<div class="log-section fix"><h3>%s</h3>', __( 'Bug Fixes', 'integrate-google-drive' ) );
					foreach ( $log['fix'] as $item ) {
						echo '<div class="log-item log-item-fix"><i class="dashicons dashicons-saved"></i> <span>' . $item . '</span></div>';
					}
					echo '</div>';
				}

				if ( ! empty( $log['enhancement'] ) ) {
					printf( '<div class="log-section enhancement"><h3>%s</h3>', __( 'Improvements', 'integrate-google-drive' ) );
					foreach ( $log['enhancement'] as $item ) {
						echo '<div class="log-item log-item-enhancement"><i class="dashicons dashicons-star-filled"></i> <span>' . $item . '</span></div>';
					}
					echo '</div>';
				}

				if ( ! empty( $log['remove'] ) ) {
					printf( '<div class="log-section remove"><h3>%s</h3>', __( 'Removes', 'integrate-google-drive' ) );
					foreach ( $log['remove'] as $item ) {
						echo '<div class="log-item log-item-remove"><i class="dashicons dashicons-trash"></i> <span>' . $item . '</span></div>';
					}
					echo '</div>';
				}


				?>
            </div>

        </div>
		<?php
		$i ++;
	} ?>


</div>


<script>
    jQuery(document).ready(function ($) {
        $('.log-header').on('click', function () {
            $(this).next('.log-body').slideToggle();
            $(this).find('i').toggleClass('dashicons-arrow-down-alt2 dashicons-arrow-up-alt2');
            $(this).parent().toggleClass('active');
        });
    });
</script>