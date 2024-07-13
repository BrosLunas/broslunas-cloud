<?php

/**
 * Plugin Name: Integrate Google Drive (PRO)
 * Plugin URI:  https://softlabbd.com/integrate-google-drive
 * Description: Seamless Google Drive integration for WordPress, allowing you to embed, share, play, and download documents and media files directly from Google Drive to your WordPress site.
 * Version:     1.3.94
 * Update URI: https://api.freemius.com
 * Author:      SoftLab
 * Author URI:  https://softlabbd.com/
 * Text Domain: integrate-google-drive
 * Domain Path: /languages/
 *
 * @fs_premium_only /assets/vendor/slick, /assets/js/statistics.js, /assets/vendor/chart.js, /includes/integrations/templates, /includes/integrations/woocommerce, /includes/integrations/class-tutor.php, /includes/integrations/class-fluentforms.php, /includes/integrations/class-ninjaforms.php, /includes/integrations/class-edd.php, /includes/integrations/class-acf.php, /includes/integrations/class-gravityforms.php, /includes/integrations/class-wpforms.php,  /includes/integrations/class-formidableforms.php,  /includes/integrations/class-media-library.php, /includes/integrations/media-library
 */
// don't call the file directly
if ( !defined( 'ABSPATH' ) ) {
    wp_die( __( 'You can\'t access this page', 'integrate-google-drive' ) );
}
if ( function_exists( 'igd_fs' ) ) {
    igd_fs()->set_basename( true, __FILE__ );
} else {
    // DO NOT REMOVE THIS IF, IT IS ESSENTIAL FOR THE `function_exists` CALL ABOVE TO PROPERLY WORK.
    if ( !function_exists( 'igd_fs' ) ) {
        // Create a helper function for easy SDK access.
        function igd_fs() {
            global $igd_fs;
            if ( !isset( $igd_fs ) ) {
                // Include Freemius SDK.
                require_once dirname( __FILE__ ) . '/freemius/start.php';
				class igdFsNull {
					public function can_use_premium_code__premium_only() {
						return true;
					}
					public function get_upgrade_url() {
						return '';
					}
					public function add_filter( $tag, $function_to_add, $priority = 10, $accepted_args = 1 ) {
						add_filter( $tag, $function_to_add, $priority, $accepted_args );
					}
					public function add_action( $tag, $function_to_add, $priority = 10, $accepted_args = 1 ) {
						add_action( $tag, $function_to_add, $priority, $accepted_args );
					}
				}
				
                $igd_fs = new igdFsNull();
            }
            return $igd_fs;
        }

        // Init Freemius.
        igd_fs();
        // Signal that SDK was initiated.
        do_action( 'igd_fs_loaded' );
    }
    /** define constants */
    define( 'IGD_VERSION', '1.3.94' );
    define( 'IGD_FILE', __FILE__ );
    define( 'IGD_PATH', dirname( IGD_FILE ) );
    define( 'IGD_INCLUDES', IGD_PATH . '/includes' );
    define( 'IGD_URL', plugins_url( '', IGD_FILE ) );
    define( 'IGD_ASSETS', IGD_URL . '/assets' );
    // Check min-php version
    if ( version_compare( PHP_VERSION, '7.0', '<=' ) ) {
        deactivate_plugins( plugin_basename( IGD_FILE ) );
        $notice = sprintf( 'Unsupported PHP version. %1$s requires WordPress version %2$s or greater. Please update your PHP to the latest version.', '<strong>Google Drive to WordPress</strong>', '<strong>5.6.0</strong>' );
        wp_die( $notice );
    }
    // Check min-wp version
    if ( !version_compare( get_bloginfo( 'version' ), '5.0', '>=' ) ) {
        deactivate_plugins( plugin_basename( IGD_FILE ) );
        $notice = sprintf( 'Unsupported WordPress version. %1$s requires WordPress version %2$s or greater. Please update your WordPress to the latest version.', '<strong>Google Drive to WordPress</strong>', '<strong>5.0</strong>' );
        wp_die( $notice );
    }
    //Include the base plugin file.
    include_once IGD_INCLUDES . '/base.php';
}