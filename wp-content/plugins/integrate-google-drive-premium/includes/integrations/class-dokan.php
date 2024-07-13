<?php

namespace IGD;

defined( 'ABSPATH' ) || exit;


class Dokan {
	/**
	 * @var null
	 */
	protected static $instance = null;

	public function __construct() {
		$is_dokan_download = igd_get_settings( 'dokanDownload', true );
		$is_dokan_upload   = igd_get_settings( 'dokanUpload', false );

		if ( $is_dokan_download || $is_dokan_upload ) {
			// Add vendor dashboard settings menu for Google Drive
			add_filter( 'dokan_get_dashboard_settings_nav', [ $this, 'add_vendor_dashboard_menu' ] );

			// Add vendor dashboard settings content for Google Drive
			add_action( 'dokan_render_settings_content', [ $this, 'render_vendor_dashboard_content' ] );

			// Settings help text
			add_filter( 'dokan_dashboard_settings_helper_text', [ $this, 'settings_help_text' ], 10, 2 );

			add_filter( 'igd_localize_data', [ $this, 'localize_data' ], 10, 2 );

			// Auth state
			add_filter( 'igd_auth_state', [ $this, 'auth_state' ] );

			// Check if authorization action is set
			add_action( 'template_redirect', [ $this, 'handle_authorization' ] );

			// Enqueue scripts on vendor dashboard
			add_action( 'dokan_enqueue_scripts', [ $this, 'enqueue_scripts' ] );

		}

		if ( $is_dokan_upload ) {
			// Add uploadable type to dokan product type
			add_filter( 'dokan_product_edit_after_title', [ $this, 'add_uploadable_type' ], 20, 2 );

			// Add upload options
			add_action( 'dokan_product_edit_after_main', [ $this, 'render_uploadable_settings' ], 20, 2 );

			// Save upload options
			add_action( 'dokan_process_product_meta', [ $this, 'save_settings' ] );

			// Save upload settings
			add_action( 'wp_ajax_igd_save_dokan_upload_settings', [ $this, 'save_upload_settings' ] );
			add_action( 'wp_ajax_nopriv_igd_save_dokan_upload_settings', [ $this, 'save_upload_settings' ] );

		}

		$dokan_media_library = igd_get_settings( 'dokanMediaLibrary', false );
		if ( $dokan_media_library ) {

			// Save media library settings
			add_action( 'wp_ajax_igd_save_dokan_media_library_settings', [ $this, 'save_media_library_settings' ] );
			add_action( 'wp_ajax_nopriv_igd_save_dokan_media_library_settings', [
				$this,
				'save_media_library_settings'
			] );

			$is_vendor_media_library = get_user_meta( dokan_get_current_user_id(), '_igd_dokan_vendor_media_library', true ) !== 'no';

			if ( $is_vendor_media_library ) {
				if ( ! class_exists( 'IGD\Media_Library' ) ) {
					include_once IGD_INCLUDES . '/integrations/class-media-library.php';
				}

				add_filter( 'igd_can_access', [ $this, 'add_vendor_media_library_access' ], 10, 2 );
				add_filter( 'igd_localize_data', [ $this, 'add_media_folders' ], 10, 2 );
			}
		}

	}

	/**
	 * Update media folders settings data with vendor specific data
	 *
	 * @param $data
	 * @param $script_handle
	 *
	 * @return mixed
	 */
	public function add_media_folders( $data, $script_handle ) {

		if ( 'frontend' == $script_handle ) {
			return $data;
		}

		if ( dokan_is_seller_dashboard() || dokan_is_product_edit_page() ) {
			$media_folders = get_user_meta( dokan_get_current_user_id(), '_igd_dokan_media_folders', true );

			$data['settings']['mediaLibraryFolders'] = ! empty( $media_folders ) ? $media_folders : [];

			$data['screen']            = dokan_is_seller_dashboard() ? 'dokan_dashboard' : 'dokan_product_edit';
			$data['dokanDashboardUrl'] = dokan_get_navigation_url( 'settings/google-drive' );

		}

		return $data;
	}

	public function add_vendor_media_library_access( $can_access, $access_right ) {
		if ( 'media_library' == $access_right && dokan_is_seller_dashboard() ) {
			$can_access = true;
		}

		return $can_access;
	}

	public function save_upload_settings() {
		parse_str( $_POST['data'], $data );

		$upload_locations = isset( $data['upload_locations'] ) ? $data['upload_locations'] : [];
		$upload_locations = array_map( 'sanitize_text_field', $upload_locations );

		$upload_order_statuses = isset( $data['upload_order_status'] ) ? $data['upload_order_status'] : [];
		$upload_order_statuses = array_map( 'sanitize_text_field', $upload_order_statuses );

		$naming_template = isset( $data['_igd_upload_folder_name'] ) ? sanitize_text_field( $data['_igd_upload_folder_name'] ) : '';

		$file_description = isset( $data['_igd_upload_file_description'] ) ? sanitize_text_field( $data['_igd_upload_file_description'] ) : '';

		$active_account = Account::instance( dokan_get_current_user_id() )->get_active_account();

		if ( $active_account ) {
			$parent_folder = ! empty( $data['igd_upload_parent_folder'] ) ? json_decode( $data['igd_upload_parent_folder'], true ) : [];

			update_user_meta( dokan_get_current_user_id(), '_igd_dokan_upload_parent_folder', $parent_folder );
		}

		$can_delete = ! empty( $data['igd_dokan_can_delete'] ) ? sanitize_text_field( $data['igd_dokan_can_delete'] ) : 'no';

		update_user_meta( dokan_get_current_user_id(), '_igd_dokan_upload_locations', $upload_locations );
		update_user_meta( dokan_get_current_user_id(), '_igd_dokan_upload_order_statuses', $upload_order_statuses );
		update_user_meta( dokan_get_current_user_id(), '_igd_dokan_upload_folder_name', $naming_template );
		update_user_meta( dokan_get_current_user_id(), '_igd_dokan_can_delete', $can_delete );
		update_user_meta( dokan_get_current_user_id(), '_igd_upload_file_description', $file_description );

		wp_send_json_success( [
			'success' => true,
		] );

	}

	public function save_media_library_settings() {
		parse_str( $_POST['data'], $data );

		$is_vendor_media_library = ! empty( $data['igd_dokan_vendor_media_library'] ) ? 'yes' : 'no';
		$media_folders           = ! empty( $data['igd_dokan_media_folders'] ) ? json_decode( $data['igd_dokan_media_folders'], true ) : [];

		update_user_meta( dokan_get_current_user_id(), '_igd_dokan_vendor_media_library', $is_vendor_media_library );
		update_user_meta( dokan_get_current_user_id(), '_igd_dokan_media_folders', $media_folders );

		wp_send_json_success( [
			'success' => true,
		] );

	}

	public function add_uploadable_type( $post, $post_id ) {
		$uploadable = get_post_meta( $post_id, '_uploadable', true );

		?>
        <div class="dokan-form-group dokan-product-type-container show_if_subscription show_if_simple">
            <div class="content-half-part uploadable-checkbox">
                <label>
                    <input type="checkbox" <?php checked( $uploadable, 'yes' ); ?> class="_is_uploadable"
                           name="_uploadable"
                           id="_uploadable"> <?php esc_html_e( 'Uploadable', 'integrate-google-drive' ); ?>
                    <i class="fas fa-question-circle tips" aria-hidden="true"
                       data-title="<?php esc_attr_e( 'Let your customers upload files on purchase.', 'integrate-google-drive' ); ?>"></i>
                </label>
            </div>
            <div class="dokan-clearfix"></div>
        </div>
		<?php
	}

	public function render_uploadable_settings( $post, $post_id ) {

		//Upload Button Text
		$upload_btn_text = get_post_meta( $post->ID, '_igd_upload_button_text', true );
		$upload_btn_text = ! empty( $upload_btn_text ) ? $upload_btn_text : __( 'Upload Documents', 'integrate-google-drive' );


		?>
        <div class="igd-dokan-upload-options dokan-edit-row dokan-clearfix show_if_uploadable">

            <div class="dokan-section-heading" data-togglehandler="dokan_uploadable_options">
                <h2><i class="fas fa-upload"
                       aria-hidden="true"></i> <?php esc_html_e( 'Uploadable Options', 'integrate-google-drive' ); ?>
                </h2>
                <p><?php esc_html_e( 'Configure your uploadable product settings', 'integrate-google-drive' ); ?></p>
                <a href="#" class="dokan-section-toggle">
                    <i class="fas fa-sort-down fa-flip-vertical" aria-hidden="true"></i>
                </a>
                <div class="dokan-clearfix"></div>
            </div>

            <div class="dokan-section-content">
                <div class="dokan-divider-top dokan-clearfix">

					<?php do_action( 'dokan_product_edit_before_sidebar' ); ?>

                    <!-- Upload to Google Drive checkbox -->
                    <div class="dokan-form-group">
						<?php dokan_post_input_box( $post_id, '_igd_upload', array( 'label' => __( 'Upload to Google Drive', 'integrate-google-drive' ) ), 'checkbox' ); ?>
                    </div>

                    <div class="show_if_igd_upload upload-box-settings  dokan-form-group dokan-clearfix">
                        <h4><?php _e( 'Google Drive Upload Settings', 'integrate-google-drive' ); ?></h4>

                        <div class="dokan-clearfix">
                            <!-- Upload Button Text -->
                            <div class="content-half-part dokan-form-group">
                                <label for="_igd_upload_button_text"
                                       class="form-label"><?php esc_html_e( 'Upload Button Text', 'integrate-google-drive' ); ?> </label>
								<?php dokan_post_input_box( $post_id, '_igd_upload_button_text', [ 'value' => $upload_btn_text ] ); ?>
                            </div>

                            <!-- Upload Description -->
                            <div class="content-half-part dokan-form-group">
                                <label for="_igd_upload_description"
                                       class="form-label"><?php esc_html_e( 'Upload Description', 'integrate-google-drive' ); ?> </label>
								<?php dokan_post_input_box( $post_id, '_igd_upload_description' ); ?>
                            </div>

                        </div>

                        <div class="dokan-clearfix">

                            <!-- Min Upload Files -->
                            <div class="content-half-part dokan-form-group">
                                <label for="_igd_upload_min_files"
                                       class="form-label"><?php esc_html_e( 'Min Upload Files', 'integrate-google-drive' ); ?> </label>

								<?php
								dokan_post_input_box( $post_id, '_igd_upload_min_files', array(
									'placeholder' => __( 'Minimum number of files', 'integrate-google-drive' ),
								) );
								?>

                                <p class="description">
									<?php esc_html_e( 'Minimum number of files required to upload. Leave blank for no limit.', 'integrate-google-drive' ); ?>
                                </p>
                            </div>

                            <!-- Max Upload Files -->
                            <div class="content-half-part dokan-form-group">
                                <label for="_igd_upload_max_files"
                                       class="form-label"><?php esc_html_e( 'Max Upload Files', 'integrate-google-drive' ); ?> </label>

								<?php
								dokan_post_input_box( $post_id, '_igd_upload_max_files', array(
									'placeholder' => __( 'Maximum number of files', 'integrate-google-drive' ),
								) );
								?>

                                <p class="description">
									<?php esc_html_e( 'Maximum number of files allowed to upload. Leave blank for no limit.', 'integrate-google-drive' ); ?>
                                </p>
                            </div>

                        </div>

                        <div class="dokan-clearfix">

                            <!-- Min File Size -->
                            <div class="content-half-part dokan-form-group">
                                <label for="_igd_upload_min_file_size"
                                       class="form-label"><?php esc_html_e( 'Min File Size', 'integrate-google-drive' ); ?> </label>

								<?php
								dokan_post_input_box( $post_id, '_igd_upload_min_file_size', array(
									'placeholder' => __( 'Min file size in MB', 'integrate-google-drive' ),
								) );
								?>

                                <p class="description">
									<?php esc_html_e( 'Minimum file size in MB. Leave blank to allow all file sizes.', 'integrate-google-drive' ); ?>
                                </p>
                            </div>

                            <!-- Max File Size -->
                            <div class="content-half-part dokan-form-group">
                                <label for="_igd_upload_max_file_size"
                                       class="form-label"><?php esc_html_e( 'Max File Size', 'integrate-google-drive' ); ?> </label>

								<?php
								dokan_post_input_box( $post_id, '_igd_upload_max_file_size', array(
									'placeholder' => __( 'Max file size in MB', 'integrate-google-drive' ),
								) );
								?>

                                <p class="description">
									<?php esc_html_e( 'Maximum file size in MB. Leave blank to allow all file sizes.', 'integrate-google-drive' ); ?>
                                </p>
                            </div>

                        </div>

                        <!-- Allowed File Types -->
                        <div class="content-half-part dokan-form-group">
                            <label for="_igd_upload_file_types"
                                   class="form-label"><?php esc_html_e( 'Allowed File Types', 'integrate-google-drive' ); ?> </label>

							<?php
							dokan_post_input_box( $post_id, '_igd_upload_file_types', array(
								'placeholder' => 'jpg, png, pdf, docx, doc, zip, rar',
							) );
							?>

                            <p class="description">
								<?php esc_html_e( 'Comma separated file extensions (e.g: png, jpg, zip). Leave blank to allow all file types.', 'integrate-google-drive' ); ?>
                            </p>

                        </div>

                    </div>
                </div>
            </div>
        </div>
		<?php
	}

	/**
	 * Save settings
	 *
	 * @param $post_id
	 */
	public function save_settings( $post_id ) {

		$uploadable = ! empty( $_POST['_uploadable'] ) ? 'yes' : 'no';

		$upload_button_text = ! empty( $_POST['_igd_upload_button_text'] ) ? sanitize_text_field( $_POST['_igd_upload_button_text'] ) : 'Upload Documents';

		$description = ! empty( $_POST['_igd_upload_description'] ) ? sanitize_text_field( $_POST['_igd_upload_description'] ) : '';

		$upload_to_google_drive = ! empty( $_POST['_igd_upload'] ) ? sanitize_text_field( $_POST['_igd_upload'] ) : 'no';

		//allowed file types
		$allowed_file_types = ! empty( $_POST['_igd_upload_file_types'] ) ? sanitize_text_field( $_POST['_igd_upload_file_types'] ) : '';

		// max file size
		$max_file_size = ! empty( $_POST['_igd_upload_max_file_size'] ) ? sanitize_text_field( $_POST['_igd_upload_max_file_size'] ) : '';
		$min_file_size = ! empty( $_POST['_igd_upload_min_file_size'] ) ? sanitize_text_field( $_POST['_igd_upload_min_file_size'] ) : '';

		// max upload files
		$max_upload_files = ! empty( $_POST['_igd_upload_max_files'] ) ? sanitize_text_field( $_POST['_igd_upload_max_files'] ) : '';
		$min_upload_files = ! empty( $_POST['_igd_upload_min_files'] ) ? sanitize_text_field( $_POST['_igd_upload_min_files'] ) : '';

		update_post_meta( $post_id, '_igd_upload_button_text', $upload_button_text );
		update_post_meta( $post_id, '_igd_upload_description', $description );
		update_post_meta( $post_id, '_igd_upload', $upload_to_google_drive );
		update_post_meta( $post_id, '_uploadable', $uploadable );
		update_post_meta( $post_id, '_igd_upload_file_types', $allowed_file_types );
		update_post_meta( $post_id, '_igd_upload_max_file_size', $max_file_size );
		update_post_meta( $post_id, '_igd_upload_min_file_size', $min_file_size );
		update_post_meta( $post_id, '_igd_upload_max_files', $max_upload_files );
		update_post_meta( $post_id, '_igd_upload_min_files', $min_upload_files );
	}

	/**
	 * Check if authorization action is set
	 */
	public function handle_authorization() {
		global $wp;

		if ( empty( $wp->query_vars['settings'] ) || 'google-drive' !== $wp->query_vars['settings'] ) {
			return;
		}

		if ( empty( $_GET['action'] ) || 'igd-dokan-authorization' !== $_GET['action'] ) {
			return;
		}

		//check if vendor is logged in
		if ( ! is_user_logged_in() ) {
			return;
		}

		$client = Client::instance();

		$client->create_access_token();

		$redirect = dokan_get_navigation_url( 'settings/google-drive' );

		echo '<script type="text/javascript">window.opener.parent.location.href = "' . $redirect . '"; window.close();</script>';
		die();

	}

	/**
	 * Auth state
	 *
	 * @param $state
	 *
	 * @return string
	 */
	public function auth_state( $state ) {

		if ( dokan_is_seller_dashboard() || dokan_is_product_edit_page() ) {
			$state = dokan_get_navigation_url() . 'settings/google-drive?action=igd-dokan-authorization&user_id=' . dokan_get_current_user_id();
		}

		return $state;
	}

	/**
	 * Enqueue scripts on vendor dashboard
	 */
	public function enqueue_scripts() {
		// Check if dokan vendor dashboard
		if ( ! dokan_is_seller_dashboard() ) {
			return;
		}

		if ( ! wp_script_is( 'igd-admin', 'registered' ) ) {
			Enqueue::instance()->admin_scripts( '', false );
		}

		wp_enqueue_script( 'igd-woocommerce', IGD_ASSETS . '/js/woocommerce.js', array( 'igd-admin' ), IGD_VERSION, true );
		wp_enqueue_script( 'igd-dokan', IGD_ASSETS . '/js/dokan.js', [
			'igd-woocommerce',
			'igd-admin'
		], IGD_VERSION, true );

	}

	/**
	 * Localize data
	 *
	 * @param $data
	 *
	 * @return mixed
	 */
	public function localize_data( $data, $script_handle ) {

		if ( 'frontend' == $script_handle ) {
			return $data;
		}

		if ( dokan_is_seller_dashboard() || dokan_is_product_edit_page() ) {
			$data['authUrl']       = Client::instance()->get_auth_url();
			$data['accounts']      = base64_encode( json_encode( Account::instance( dokan_get_current_user_id() )->get_accounts() ) );
			$data['activeAccount'] = base64_encode( json_encode( Account::instance( dokan_get_current_user_id() )->get_active_account() ) );
		}

		return $data;
	}

	/**
	 * Enqueue scripts on vendor dashboard
	 */

	/**
	 * Add vendor dashboard menu for Google Drive
	 *
	 * @param $urls
	 *
	 * @return mixed
	 */
	public function add_vendor_dashboard_menu( $urls ) {
		$urls['google-drive'] = array(
			'title' => __( 'Google Drive', 'integrate-google-drive' ),
			'icon'  => '<i class="fab fa-google-drive"></i>',
			'url'   => dokan_get_navigation_url( 'settings/google-drive' ),
			'pos'   => 100,
		);

		return $urls;
	}

	/**
	 * Render vendor dashboard content for Google Drive
	 *
	 * @param $current_section
	 */

	public function render_vendor_dashboard_content( $query_vars ) {
		$current_section = isset( $query_vars['settings'] ) ? $query_vars['settings'] : '';

		if ( 'google-drive' === $current_section ) {
			$current_user = dokan_get_current_user_id();
			$profile_info = dokan_get_store_info( dokan_get_current_user_id() );

			include_once IGD_INCLUDES . '/integrations/templates/dokan-settings.php';
		}

	}

	/**
	 * Settings help text
	 *
	 * @param $help_text
	 * @param $section
	 *
	 * @return string
	 */
	public function settings_help_text( $help_text, $section ) {

		if ( 'google-drive' === $section ) {
			$is_dokan_download      = igd_get_settings( 'dokanDownload', true );
			$is_dokan_upload        = igd_get_settings( 'dokanUpload', false );
			$is_dokan_media_library = igd_get_settings( 'dokanMediaLibrary', false );

			if ( $is_dokan_media_library ) {
				$help_text = __( 'Integrate your Google Drive with your store to use product images and download files directly from your Drive', 'integrate-google-drive' );
				if ( $is_dokan_upload ) {
					$help_text .= '<br />' . __( 'And customers can also upload files to your Google Drive after making a purchase.', 'integrate-google-drive' );
				}
			} elseif ( $is_dokan_download && ! $is_dokan_upload ) {
				$help_text = __( 'Connect your Google Drive account with your store to use product images and download able files from Google Drive.', 'integrate-google-drive' );
			} elseif ( $is_dokan_upload && ! $is_dokan_download ) {
				$help_text = __( 'Connect your Google Drive account with your store to let customers upload files to your Google Drive account on purchase.', 'integrate-google-drive' );
			} elseif ( $is_dokan_upload && $is_dokan_download ) {
				$help_text = __( 'Connect your Google Drive account with your store to use product images and download able files from your Google Drive and let customers upload files to your Google Drive on product purchase.', 'integrate-google-drive' );
			}
		}

		return $help_text;
	}


	/**
	 * @return Dokan|null
	 */
	public static function instance() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

}

Dokan::instance();