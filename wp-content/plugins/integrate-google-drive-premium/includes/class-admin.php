<?php

namespace IGD;

defined( 'ABSPATH' ) || exit;

class Admin {
	/**
	 * @var null
	 */
	protected static $instance = null;

	private $pages = [];

	public function __construct() {
		add_action( 'admin_menu', [ $this, 'admin_menu' ] );

		add_action( 'admin_notices', [ $this, 'lost_authorization_notice' ] );

		add_action( 'admin_init', [ $this, 'init_update' ] );

		// Remove admin notices from plugin pages
		add_action( 'admin_init', [ $this, 'show_review_popup' ] );

		//admin body class
		add_filter( 'admin_body_class', [ $this, 'admin_body_class' ] );

		if ( igd_fs()->can_use_premium_code__premium_only() ) {
			//Handle custom app authorization
			add_action( 'admin_init', [ $this, 'app_authorization__premium_only' ] );
		}

	}

	public function admin_body_class( $classes ) {
		$admin_pages = Admin::instance()->get_pages();

		global $current_screen;

		if ( is_object( $current_screen ) && in_array( $current_screen->id, $admin_pages ) ) {
			$key = array_search( $current_screen->id, $admin_pages );

			$classes .= ' igd-admin-page igd_' . $key . ' ';
		}

		return $classes;
	}

	public function show_review_popup() {

		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		// Rating notice
		if ( 'off' != get_option( 'igd_rating_notice' ) && 'off' != get_transient( 'igd_rating_notice_interval' ) ) {
			add_filter( 'igd_localize_data', function ( $data ) {
				$data['showReviewPopup'] = true;

				return $data;
			} );

		}
	}

	public function app_authorization__premium_only() {
		if ( isset( $_GET['action'] ) && 'integrate-google-drive-authorization' == sanitize_key( $_GET['action'] ) ) {

			// Remove 'action' from params
			unset( $_GET['action'] );

			// Decode and sanitize the 'state' parameter
			$state_url = base64_decode( sanitize_text_field( $_GET['state'] ) );

			// Validate the URL
			if ( false === filter_var( $state_url, FILTER_VALIDATE_URL ) ) {
				// Handle invalid URL
				wp_safe_redirect( home_url() );
				exit;
			}

			// Check if the URL belongs to the current website domain
			$current_domain = wp_parse_url( home_url(), PHP_URL_HOST );
			$redirect_domain = wp_parse_url( $state_url, PHP_URL_HOST );

			if ( $current_domain !== $redirect_domain ) {
				// Redirect or error handling if the domain is not the current domain
				wp_safe_redirect( home_url() );
				exit();
			}

			// Build the redirect URL
			$params = http_build_query( $_GET );
			$redirect_url = esc_url_raw( $state_url . '&' . $params );

			// Execute the redirect
			wp_redirect( $redirect_url );
			exit();
		}
	}

	public function init_update() {

		if ( current_user_can( 'manage_options' ) ) {
			include_once IGD_INCLUDES . '/class-update.php';

			$updater = Update::instance();

			if ( $updater->needs_update() ) {
				$updater->perform_updates();
			}
		}
	}

	public function admin_menu() {

		$main_menu_added = false;

		$access_rights = [
			'file_browser'      => [
				'view'          => [ 'IGD\App', 'view' ],
				'title'         => __( 'File Browser - Integrate Google Drive', 'integrate-google-drive' ),
				'submenu_title' => __( 'File Browser', 'integrate-google-drive' )
			],
			'shortcode_builder' => [
				'view'          => [ 'IGD\Shortcode_Builder', 'view' ],
				'title'         => __( 'Shortcode Builder - Integrate Google Drive', 'integrate-google-drive' ),
				'submenu_title' => __( 'Shortcode Builder', 'integrate-google-drive' )
			],
			'private_files'     => [
				'view'          => [ 'IGD\Private_Folders', 'view' ],
				'title'         => __( 'Users Private Files - Integrate Google Drive', 'integrate-google-drive' ),
				'submenu_title' => __( 'Users Private Files', 'integrate-google-drive' )
			],
			'getting_started'   => [
				'view'          => [ $this, 'render_getting_started_page' ],
				'title'         => __( 'Getting Started - Integrate Google Drive', 'integrate-google-drive' ),
				'submenu_title' => __( 'Getting Started', 'integrate-google-drive' )
			],
			'statistics'        => [
				'view'          => [ 'IGD\Statistics', 'view' ],
				'title'         => __( 'Statistics - Integrate Google Drive', 'integrate-google-drive' ),
				'submenu_title' => __( 'Statistics', 'integrate-google-drive' )
			],
			'settings'          => [
				'view'          => [ $this, 'render_settings_page' ],
				'title'         => __( 'Settings - Integrate Google Drive', 'integrate-google-drive' ),
				'submenu_title' => __( 'Settings', 'integrate-google-drive' )
			]
		];

		// Check statistics access
		if ( ! igd_fs()->can_use_premium_code__premium_only() || ! igd_get_settings( 'enableStatistics', false ) ) {
			unset( $access_rights['statistics'] );
		}


		foreach ( $access_rights as $access_right => $page_config ) {

			if ( igd_user_can_access( $access_right ) ) {
				if ( ! $main_menu_added ) {
					$this->pages[ $access_right . '_page' ] = $this->add_main_menu_page( $page_config['title'], $page_config['submenu_title'], $page_config['view'] );
					$main_menu_added                        = true;
				} else {
					$this->pages[ $access_right . '_page' ] = $this->add_submenu_page( $page_config['title'], $page_config['submenu_title'], $page_config['view'], $access_right );
				}
			}

		}

		//Recommended plugins page
		if ( empty( get_option( "igd_hide_recommended_plugins" ) ) ) {
			add_submenu_page(
				'integrate-google-drive',
				esc_html__( 'Recommended Plugins', 'integrate-google-drive' ),
				esc_html__( 'Recommended Plugins', 'integrate-google-drive' ),
				'manage_options',
				'integrate-google-drive-recommended-plugins',
				[ $this, 'render_recommended_plugins_page' ]
			);
		}

	}

	private function add_main_menu_page( $title, $submenu_title, $view ) {
		$page = add_menu_page(
			__( 'Integrate Google Drive', 'integrate-google-drive' ),
			__( 'Google Drive', 'integrate-google-drive' ),
			'read',
			'integrate-google-drive',
			$view,
			IGD_ASSETS . '/images/drive.png',
			11
		);

		add_submenu_page( 'integrate-google-drive', $title, $submenu_title, 'read', 'integrate-google-drive' );

		return $page;
	}

	private function add_submenu_page( $title, $submenu_title, $view, $slug, $priority = 90 ) {
		$slug = str_replace( '_', '-', $slug );

		return add_submenu_page( 'integrate-google-drive', $title, $submenu_title, 'read', 'integrate-google-drive-' . $slug, $view, $priority );
	}

	public function render_recommended_plugins_page() {
		include IGD_INCLUDES . '/views/recommended-plugins.php';
	}

	public function lost_authorization_notice() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$accounts = Account::instance()->get_accounts();

		if ( ! empty( $accounts ) ) {
			foreach ( $accounts as $id => $account ) {

				if ( ! empty( $account['lost'] ) ) {

					$msg = sprintf( '<div class="flex items-center"> <strong>Integrate Google Drive</strong> lost authorization for account <strong>%s</strong>. <a class="button" href="%s">Refresh</a></div>',
						$account['email'], admin_url( 'admin.php?page=integrate-google-drive-settings' ) );

					igd()->add_notice( 'error igd-lost-auth-notice', $msg );
				}
			}
		}

	}

	public function render_getting_started_page() {
		include_once IGD_INCLUDES . '/views/getting-started/index.php';
	}

	public function render_settings_page() { ?>
        <div id="igd-settings"></div>
	<?php }

	public function get_pages() {
		return array_filter( $this->pages );
	}

	/**
	 * @return Admin|null
	 */
	public static function instance() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

}

Admin::instance();