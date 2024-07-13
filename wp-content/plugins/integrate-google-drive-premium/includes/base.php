<?php

namespace IGD;

defined( 'ABSPATH' ) || exit();

final class Main {
	protected static $instance = null;

	public function __construct() {
		$this->init_auto_loader();
		$this->includes();
		$this->register_hooks();
	}

	public function includes() {
		// Core includes
		require_once IGD_INCLUDES . "/functions.php";
		require_once IGD_INCLUDES . "/class-enqueue.php";
		require_once IGD_INCLUDES . "/class-hooks.php";
		require_once IGD_INCLUDES . "/class-shortcode.php";
		require_once IGD_INCLUDES . "/class-shortcode-locations.php";

		// Ajax
		if ( igd_fs()->can_use_premium_code__premium_only() ) {
			require_once IGD_INCLUDES . "/class-ajax__premium_only.php";
		} else {
			require_once IGD_INCLUDES . "/class-ajax.php";
		}

		// Integration
		require_once IGD_INCLUDES . "/class-integration.php";

		// Pro files
		if ( igd_fs()->can_use_premium_code__premium_only() ) {
			require_once IGD_INCLUDES . "/class-notifications__premium_only.php";
			require_once IGD_INCLUDES . "/class-importer__premium_only.php";

			if ( igd_get_settings( 'enableStatistics', false ) ) {
				require_once IGD_INCLUDES . "/class-statistics__premium_only.php";

				if ( igd_get_settings( 'emailReport', false ) ) {
					require_once IGD_INCLUDES . "/class-email-report__premium_only.php";
				}
			}
		}

		// Admin includes
		if ( is_admin() ) {
			require_once IGD_INCLUDES . "/class-admin.php";
		}

	}

	public function init_auto_loader() {
		spl_autoload_register( function ( $class_name ) {
			if ( strpos( $class_name, 'IGD' ) !== false ) {
				$classes_dir = IGD_PATH . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR;
				$file_name   = strtolower( str_replace( [ 'IGD\\', '_' ], [ '', '-' ], $class_name ) );
				$file_name   = "class-$file_name.php";

				$file = $classes_dir . $file_name;

				if ( file_exists( $file ) ) {
					require_once $file;
				}
			}
		} );
	}

	private function register_hooks() {
		register_activation_hook( IGD_FILE, [ $this, 'activate' ] );
		register_deactivation_hook( IGD_FILE, [ $this, 'deactivate' ] );

		do_action( 'igd_loaded' );

		add_action( 'admin_notices', [ $this, 'print_notices' ], 15 );
		add_action( 'init', [ $this, 'localization_setup' ] );

		add_filter( 'plugin_action_links_' . plugin_basename( IGD_FILE ), [ $this, 'plugin_action_links' ] );
	}

	public function activate_deactivate( $method ) {
		if ( ! class_exists( 'IGD\Install' ) ) {
			require_once IGD_INCLUDES . "/class-install.php";
		}

		Install::$method();
	}

	public function activate() {
		$this->activate_deactivate( 'activate' );
	}

	public function deactivate() {
		$this->activate_deactivate( 'deactivate' );
	}

	public function plugin_action_links( $links ) {
		$links[] = '<a href="https://softlabbd.com/docs-category/integrate-google-drive-docs/" target="_blank">' . __( 'Docs', 'integrate-google-drive' ) . '</a>';

		return $links;
	}

	public function localization_setup() {
		load_plugin_textdomain( 'integrate-google-drive', false, dirname( plugin_basename( IGD_FILE ) ) . '/languages/' );
	}

	public function add_notice( $class, $message ) {
		$notices = get_option( sanitize_key( 'igd_notices' ), [] );
		if ( is_string( $message ) && is_string( $class ) && ! wp_list_filter( $notices, [ 'message' => $message ] ) ) {
			$notices[] = [
				'message' => $message,
				'class'   => $class,
			];

			update_option( sanitize_key( 'igd_notices' ), $notices );
		}
	}

	public function print_notices() {
		$notices = get_option( sanitize_key( 'igd_notices' ), [] );

		foreach ( $notices as $notice ) {
			$class   = ! empty( $notice['class'] ) ? esc_attr( $notice['class'] ) : 'info';
			$message = ! empty( $notice['message'] ) ? $notice['message'] : '';

			printf( '<div class="notice notice-large is-dismissible igd-admin-notice notice-%1$s">%2$s</div>', $class, $message );
		}

		update_option( sanitize_key( 'igd_notices' ), [] );
	}

	public static function instance() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}

		return self::$instance;
	}
}

if ( ! function_exists( 'igd' ) ) {
	function igd() {
		return Main::instance();
	}
}

igd();
