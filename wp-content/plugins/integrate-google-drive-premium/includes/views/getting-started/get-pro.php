<?php

$features = [
	[
		'title' => __( 'Easy setup', 'integrate-google-drive' ),
		'pro'   => 0,
	],
	[
		'title' => __( 'Google Drive Files Management', 'integrate-google-drive' ),
		'pro'   => 0,
	],
	[
		'title' => __( 'Embed Document', 'integrate-google-drive' ),
		'pro'   => 0,
	],
	[
		'title' => __( 'File View Links', 'integrate-google-drive' ),
		'pro'   => 0,
	],
	[
		'title' => __( 'File Download Links', 'integrate-google-drive' ),
		'pro'   => 0,
	],
	[
		'title' => __( 'Shortcode Builder', 'integrate-google-drive' ),
		'pro'   => 0,
	],
	[
		'title' => __( 'File Browser - Module', 'integrate-google-drive' ),
		'pro'   => true,
	],
	[
		'title' => __( 'File Uploader - Module', 'integrate-google-drive' ),
		'pro'   => true,
	],
	[
		'title' => __( 'Media Player - Module', 'integrate-google-drive' ),
		'pro'   => true,
	],
	[
		'title' => __( 'Gallery - Module', 'integrate-google-drive' ),
		'pro'   => 0,
	],
	[
		'title' => __( 'File Search - Module', 'integrate-google-drive' ),
		'pro'   => true,
	],
	[
		'title' => __( 'Import to Media Library', 'integrate-google-drive' ),
		'pro'   => true,
	],
	[
		'title' => __( 'User Private Files & Folders', 'integrate-google-drive' ),
		'pro'   => true,
	],
	[
		'title' => __( 'Multiple Google Accounts', 'integrate-google-drive' ),
		'pro'   => true,
	],
	[
		'title' => __( 'Use Own Google App', 'integrate-google-drive' ),
		'pro'   => true,
	],
	[
		'title' => __( 'Statistics', 'integrate-google-drive' ),
		'pro'   => true,
	],
	[
		'title' => __( 'Email Notification', 'integrate-google-drive' ),
		'pro'   => true,
	],
	[
		'title' => __( 'Classic Editor Integration', 'integrate-google-drive' ),
		'pro'   => 0,
	],
	[
		'title' => __( 'Gutenberg Integration', 'integrate-google-drive' ),
		'pro'   => 0,
	],
	[
		'title' => __( 'Elementor Integration', 'integrate-google-drive' ),
		'pro'   => 0,
	],
	[
		'title' => __( 'Media Library Integration', 'integrate-google-drive' ),
		'pro'   => true,
	],
	[
		'title' => __( 'WooCommerce Integration', 'integrate-google-drive' ),
		'pro'   => true,
	],
	[
		'title' => __( 'Tutor LMS Integration', 'integrate-google-drive' ),
		'pro'   => true,
	],
	[
		'title' => __( 'Easy Digital Downloads Integration', 'integrate-google-drive' ),
		'pro'   => true,
	],
	[
		'title' => __( 'Contact Form 7 Integration', 'integrate-google-drive' ),
		'pro'   => true,
	],
	[
		'title' => __( 'WPForms Integration', 'integrate-google-drive' ),
		'pro'   => true,
	],
	[
		'title' => __( 'Gravity Forms Integration', 'integrate-google-drive' ),
		'pro'   => true,
	],
	[
		'title' => __( 'Fluent Forms Integration', 'integrate-google-drive' ),
		'pro'   => true,
	],
	[
		'title' => __( 'Ninja Forms Integration', 'integrate-google-drive' ),
		'pro'   => true,
	],
	[
		'title' => __( 'Formidable Forms Integration', 'integrate-google-drive' ),
		'pro'   => true,
	],
];

?>

<div id="get-pro" class="getting-started-content content-get-pro">
    <div class="content-heading">
        <h2><?php  esc_html_e('Unlock the full power of the Google Drive integration with WordPress','integrate-google-drive') ?></h2>
        <p><?php  esc_html_e('The amazing PRO features will make your Google Drive integration even more efficient','integrate-google-drive') ?>.</p>
    </div>

    <div class="content-heading free-vs-pro">
        <h2>
            <span><?php esc_html_e('Free', 'integrate-google-drive'); ?></span>
		    <?php esc_html_e('vs', 'integrate-google-drive'); ?>
            <span><?php esc_html_e('Pro', 'integrate-google-drive'); ?></span>
        </h2>
    </div>

    <div class="features-list">
        <div class="list-header">
            <div class="feature-title"><?php esc_html_e('Feature List','integrate-google-drive'); ?></div>
            <div class="feature-free"><?php esc_html_e('Free','integrate-google-drive'); ?></div>
            <div class="feature-pro"><?php esc_html_e('Pro','integrate-google-drive'); ?></div>
        </div>

		<?php foreach ( $features as $feature ) : ?>
            <div class="feature">
                <div class="feature-title"><?php echo $feature['title']; ?></div>
                <div class="feature-free">
					<?php if ( $feature['pro'] ) : ?>
                        <i class="dashicons dashicons-no-alt"></i>
					<?php else : ?>
                        <i class="dashicons dashicons-saved"></i>
					<?php endif; ?>
                </div>
                <div class="feature-pro">
                    <i class="dashicons dashicons-saved"></i>
                </div>
            </div>
		<?php endforeach; ?>

    </div>

    <div class="get-pro-cta">
        <div class="cta-content">
            <h2><?php esc_html_e('Don\'t waste time, get the PRO version now!','integrate-google-drive'); ?></h2>
            <p><?php esc_html_e('Upgrade to the PRO version of the plugin and unlock all the amazing Google Drive Integration features for
                your website.','integrate-google-drive'); ?></p>
        </div>

        <div class="cta-btn">
            <a href="<?php echo igd_fs()->get_upgrade_url(); ?>" class="igd-btn btn-primary"><?php esc_html_e('Upgrade Now','integrate-google-drive'); ?></a>
        </div>

    </div>

    <div class="demo-cta">
        <div class="cta-content">
            <h2><?php esc_html_e('Want to try live demo, before purchase?','integrate-google-drive'); ?></h2>
            <p><?php esc_html_e('You can try our instant ready-made demo. The demo allows you to experiment with all the functionality of
                the plugins on both Front-End and Back-End. Feel free to explore the possibilities and limits of our
                plugins to see if it fits your requirements!','integrate-google-drive'); ?></p>
        </div>

        <div class="cta-btn">
            <a href="https://demo.softlabbd.com/?product=integrate-google-drive" class="igd-btn btn-primary"
               target="_blank"
            ><?php esc_html_e('Try Live Demo','integrate-google-drive'); ?></a>
        </div>

    </div>

</div>