<?php

namespace IGD;

defined( 'ABSPATH' ) || exit;

class Integration {

	private static $instance = null;

	public function __construct() {

		// Classic editor
		if ( $this->is_active( 'classic-editor' ) ) {
			include_once IGD_INCLUDES . '/class-tinymce.php';
		}

		// Block editor
		if ( $this->is_active( 'gutenberg-editor' ) ) {
			include_once IGD_INCLUDES . '/blocks/class-blocks.php';
		}

		// Divi
		if ( $this->is_active( 'divi' ) ) {
			include_once IGD_INCLUDES . '/divi/divi.php';
		}

		add_action( 'plugins_loaded', function () {

			// Elementor
			if ( ( $this->is_active( 'elementor' ) )
			     || $this->is_active( 'elementor-form' )
			     || $this->is_active( 'metform' )
			) {
				include_once IGD_INCLUDES . '/elementor/class-elementor.php';
			}

		} );

		if ( igd_fs()->can_use_premium_code__premium_only() ) {

			// Media Library Integration
			if ( $this->is_active( 'media-library' ) ) {
				include_once IGD_INCLUDES . '/integrations/class-media-library.php';
			} else {
				add_filter( 'pre_get_posts', function ( $query ) {
					if ( ! isset( $query->query_vars['post_type'] ) || $query->query_vars['post_type'] !== 'attachment' ) {
						return $query;
					}

					$meta_query[] = [
						'relation' => 'OR',
						[
							'key'     => '_igd_media_folder_id',
							'compare' => 'NOT EXISTS',
						],
						[
							'key'     => '_igd_media_replace_id',
							'compare' => 'EXISTS',
						],
					];

					$query->set( 'meta_query', $meta_query );

					return $query;

				} );
			}

			// Load EDD integration
			if ( $this->is_active( 'edd' ) ) {
				include_once IGD_INCLUDES . '/integrations/class-edd.php';
			}

			// Load WooCommerce integration
			if ( $this->is_active( 'woocommerce' ) ) {
				add_action( 'woocommerce_loaded', function () {
					$is_download_active = igd_get_settings( 'wooCommerceDownload', true );
					$is_upload_active   = igd_get_settings( 'wooCommerceUpload', false );

					$is_dokan_download_active = $this->is_active( 'dokan' ) && igd_get_settings( 'dokanDownload', true );
					$is_dokan_upload_active   = $this->is_active( 'dokan' ) && igd_get_settings( 'dokanUpload', false );

					if ( $is_download_active || $is_dokan_download_active ) {
						include_once IGD_INCLUDES . '/integrations/woocommerce/class-woocommerce-downloads.php';
					}

					if ( $is_upload_active || $is_dokan_upload_active ) {
						include_once IGD_INCLUDES . '/integrations/woocommerce/class-woocommerce-uploads.php';
					}

					// Enqueue woocommerce scripts
					if ( $is_download_active || $is_upload_active ) {
						add_action( 'admin_enqueue_scripts', function () {

							if ( function_exists( 'get_current_screen' ) ) {
								$current_screen = get_current_screen();
								if ( isset( $current_screen->post_type ) && $current_screen->post_type == 'product' ) {
									if ( ! wp_script_is( 'igd-admin' ) ) {
										Enqueue::instance()->admin_scripts( '', false );
									}

									if ( ! wp_script_is( 'igd-woocommerce' ) ) {
										wp_enqueue_script( 'igd-woocommerce', IGD_ASSETS . '/js/woocommerce.js', array( 'igd-admin' ), IGD_VERSION, true );
									}

								}
							}

						}, 99 );
					}

				} );
			}

			// Load Dokan integration
			if ( $this->is_active( 'dokan' ) ) {
				add_action( 'dokan_loaded', function () {
					include_once IGD_INCLUDES . '/integrations/class-dokan.php';
				} );
			}

			//Tutor LMS
			if ( $this->is_active( 'tutor' ) ) {
				add_action( 'tutor_loaded', function () {
					include_once IGD_INCLUDES . '/integrations/class-tutor.php';
				} );
			}

			add_action( 'plugins_loaded', function () {

				// Load CF7 integration
				if ( $this->is_active( 'cf7' ) && defined( 'WPCF7_VERSION' ) && version_compare( WPCF7_VERSION, '5.0', '>=' ) ) {
					include_once IGD_INCLUDES . '/integrations/class-cf7.php';
				}

				// Load WPForms integration
				if ( $this->is_active( 'wpforms' ) && defined( 'WPFORMS_VERSION' ) ) {
					include_once IGD_INCLUDES . '/integrations/class-wpforms.php';
				}

				// Load Gravity Forms integration
				if ( $this->is_active( 'gravityforms' ) && class_exists( 'GFAddOn' ) ) {
					include_once IGD_INCLUDES . '/integrations/class-gravityforms.php';
				}

				// Load Fluent Forms integration
				if ( $this->is_active( 'fluentforms' ) && defined( 'FLUENTFORM' ) ) {
					include_once IGD_INCLUDES . '/integrations/class-fluentforms.php';
				}

				// Load Formidable Forms integration
				if ( $this->is_active( 'formidableforms' ) && function_exists( 'load_formidable_forms' ) ) {
					include_once IGD_INCLUDES . '/integrations/class-formidableforms.php';
				}

				// Load Ninja Forms integration
				if ( $this->is_active( 'ninjaforms' ) && function_exists( 'ninja_forms_three_table_exists' ) ) {
					include_once IGD_INCLUDES . '/integrations/class-ninjaforms.php';
				}

				// Load ACF integration
				if ( $this->is_active( 'acf' ) && class_exists( 'ACF' ) ) {
					add_action( 'acf/include_field_types', function () {
						include_once IGD_INCLUDES . '/integrations/class-acf.php';
					} );
				}


			} );


		}

	}

	/**
	 * Check if integration is active
	 *
	 * @param $key
	 *
	 * @return bool
	 */
	public function is_active( $key ) {
		$integrations = igd_get_settings( 'integrations', [
			'classic-editor',
			'gutenberg-editor',
			'elementor',
			'divi',
			'cf7',
		] );

		return in_array( $key, $integrations );
	}

	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self;
		}

		return self::$instance;
	}

}

Integration::instance();
