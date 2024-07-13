<?php

use IGD\Account;

defined( 'ABSPATH' ) || exit;

/**
 * Dokan Google Drive Settings  Template
 *
 * @since   1.1.86
 *
 * @package integrate-google-drive
 */

do_action( 'igd_dokan_settings_before_form', $current_user, $profile_info );

$is_dokan_upload = igd_get_settings( 'dokanUpload' );

$dokan_media_library = igd_get_settings( 'dokanMediaLibrary' );


?>

<div class="dokan-igd-settings">
    <h2 id="vendor-dashboard-google-drive-settings-error"></h2>

    <div class="igd-dokan-settings-header">
        <h2><?php esc_html_e( 'Google Drive Accounts', 'integrate-google-drive' ); ?></h2>

        <button class="igd-btn add-account-btn"
                onclick="window.open(igd.authUrl, 'newwindow', 'width=550,height=600');">
            <img src="<?php echo IGD_ASSETS; ?>/images/google-icon.png"/>
            <span><?php _e( 'Add new account', 'integrate-google-drive' ); ?></span>

        </button>
    </div>

	<?php

	$accounts = Account::instance( dokan_get_current_user_id() )->get_accounts();

	if ( count( $accounts ) > 0 ) {
		foreach ( $accounts as $key => $account ) {
			$id    = $account['id'];
			$name  = $account['name'];
			$photo = $account['photo'];
			$email = $account['email'];
			$lost  = $account['lost'];
			?>
            <div class="igd-account-item"
                 data-id="<?php echo $id; ?>">
                <img src="<?php echo $photo; ?>"/>
                <div class="igd-account-item-info">
                    <span class="account-name"><?php echo $name; ?></span>
                    <span class="account-email"><?php echo $email; ?></span>
                </div>

                <div class="igd-account-item-action">
					<?php if ( $lost ) { ?>
                        <button class="igd-btn btn-primary"
                                onclick="window.open(igd.authUrl, 'newwindow', 'width=550,height=600');">
                            <i class="dashicons dashicons-update"></i>
                            <span><?php echo __( "Refresh", "integrate-google-drive" ); ?></span>
                        </button>
					<?php } ?>
                    <button class="igd-btn btn-danger remove-account"
                    >
                        <i class="dashicons dashicons-trash"></i>
                        <span><?php echo __( 'Remove', 'integrate-google-drive' ); ?></span>
                    </button>
                </div>
            </div>
			<?php
		}
	} else { ?>

        <div class="no-account-placeholder">
            <img src="<?php echo IGD_ASSETS; ?>/images/file-browser/no-account-placeholder.svg" alt="No Accounts"/>
            <span
                    class="placeholder-heading"><?php echo __( "You didn't link any account yet.", 'integrate-google-drive' ); ?></span>
            <span
                    class="placeholder-description"><?php echo __( "Please link to a Google Drive account to continue.", 'integrate-google-drive' ); ?></span>

            <button class="igd-btn add-account-btn"
                    onclick="window.open(igd.authUrl, 'newwindow', 'width=550,height=600');">
                <img src="<?php echo IGD_ASSETS; ?>/images/google-icon.png"/>
                <span><?php echo __( "Sign in with Google", 'integrate-google-drive' ); ?></span>
            </button>
        </div>

	<?php } ?>

    <!-- Media Library Settings -->
	<?php if ( $dokan_media_library ) {
		$is_vendor_media_library = get_user_meta( dokan_get_current_user_id(), '_igd_dokan_vendor_media_library', true ) != 'no';

		$media_folders = get_user_meta( dokan_get_current_user_id(), '_igd_dokan_media_folders', true );

		?>

        <div class="igd-dokan-settings-header igd-media-library-settings-header"
             id="igd-dokan-media-library-settings-header">
            <h2><?php esc_html_e( 'Media Library Settings', 'integrate-google-drive' ); ?></h2>

            <button class="igd-btn btn-primary save-media-library-settings">
                <i class="fas fa-save"></i>
                <span><?php esc_html_e( 'Save Settings', 'integrate-google-drive' ); ?></span>
            </button>
        </div>

        <div class="dokan-ajax-response dokan-igd-ajax-response dokan-igd-media-library-ajax-response">
            <div class="dokan-alert dokan-alert-success">
                <p><?php esc_html_e( 'Settings saved successfully', 'integrate-google-drive' ); ?></p>
            </div>
        </div>

        <form class="dokan-form-horizontal" method="post" id="igd-dokan-media-library-settings">

            <!-- Enable Media Library-->
            <div class="dokan-form-group">
                <label class="dokan-w3 dokan-control-label dokan-text-left" for="igd_media_library">
					<?php esc_html_e( 'Media Library', 'integrate-google-drive' ); ?>
                </label>

                <div class="dokan-w9 dokan-text-left">
                    <label>
                        <input type="checkbox" name="igd_dokan_vendor_media_library"
                               value="1" <?php checked( $is_vendor_media_library ); ?> />
						<?php esc_html_e( 'Enable Google Drive as Media Library', 'integrate-google-drive' ); ?>
                    </label>

                    <p class="description">
						<?php esc_html_e( 'Connect your Google Drive to WordPress media library, So you can use Google Drive files like images and downloads directly in your products.', 'integrate-google-drive' ); ?>
                    </p>
                </div>
            </div>

            <!-- Media Library Folders-->
            <div class="dokan-form-group">
                <label class="dokan-w3 dokan-control-label dokan-text-left" for="igd_media_library">
					<?php esc_html_e( 'Media Library Folders', 'integrate-google-drive' ); ?>
                </label>

                <div class="dokan-w9 dokan-text-left">
                    <input type="hidden" name="igd_dokan_media_folders" id="igd_dokan_media_folders"
                           value="<?php echo esc_attr( json_encode( $media_folders ) ); ?>"/>

                    <div class="media-folders-wrap">
                        <div class="media-folders"><?php

							if ( ! empty( $media_folders ) ) {
								foreach ( $media_folders as $folder ) {
									?>
                                    <div class="folder-item">
                                        <img src="https://drive-thirdparty.googleusercontent.com/16/type/application/vnd.google-apps.folder">
										<?php echo $folder['name']; ?>
                                    </div>
									<?php
								}
							}

							?></div>

                        <button class="igd-btn btn-info" type="button" id="igd-dokan-select-media-folders">
                            <i class="dashicons dashicons-open-folder"></i><span>
                                <?php echo ! empty( $media_folders ) ? esc_html__( 'Update Folders', 'integrate-google-drive' ) : esc_html__( 'Select Folders', 'integrate-google-drive' ); ?>
                            </span>
                        </button>

                    </div>

                    <p class="description">
						<?php esc_html_e( 'Select the Google Drive folders that you want to use in the Media Library.', 'integrate-google-drive' ); ?>
                    </p>
                </div>

            </div>
        </form>
	<?php } ?>


    <!-- Upload Settings -->
	<?php if ( $is_dokan_upload ) { ?>
        <div class="igd-dokan-settings-header igd-upload-settings-header">
            <h2> <?php esc_html_e( 'Upload Settings', 'integrate-google-drive' ); ?></h2>

            <button class="igd-btn btn-primary save-upload-settings">
                <i class="fas fa-save"></i>
                <span><?php esc_html_e( 'Save Settings', 'integrate-google-drive' ); ?></span>
            </button>
        </div>

        <div class="dokan-ajax-response dokan-igd-ajax-response dokan-igd-upload-ajax-response">
            <div class="dokan-alert dokan-alert-success">
                <p><?php esc_html_e( 'Settings saved successfully', 'integrate-google-drive' ); ?></p>
            </div>
        </div>


        <form class="dokan-form-horizontal" method="post" id="igd-dokan-upload-settings">

            <!-- Upload Box Locations -->
            <div class="dokan-form-group">
                <label class="dokan-w3 dokan-control-label dokan-text-left" for="igd_upload_folder">
					<?php esc_html_e( 'Upload Box Locations', 'integrate-google-drive' ); ?>
                </label>

                <div class="dokan-w9 dokan-text-left">

                    <ul class="wc-radios">
						<?php

						$upload_location_options = array(
							"product"        => __( "Product Page", "integrate-google-drive" ),
							"cart"           => __( "Cart Page", "integrate-google-drive" ),
							"checkout"       => __( "Checkout Page", "integrate-google-drive" ),
							"order-received" => __( "Order Received Page", "integrate-google-drive" ),
							"my-account"     => __( "My Account Page", "integrate-google-drive" ),
						);

						$upload_locations = get_user_meta( dokan_get_current_user_id(), '_igd_dokan_upload_locations', true );
						$upload_locations = is_array( $upload_locations ) ? $upload_locations : array(
							'checkout',
							'order-received',
							'my-account'
						);

						foreach ( $upload_location_options as $key => $label ) { ?>
                            <li>
                                <label>
                                    <input type="checkbox" name="upload_locations[]"
                                           value="<?php echo esc_attr( $key ); ?>" <?php checked( in_array( $key, $upload_locations ), true ); ?> />
									<?php echo esc_html( $label ); ?>
                                </label>
                            </li>
							<?php
						}

						?>
                    </ul>

                    <p class="description">
						<?php esc_html_e( 'Select the pages where you want to show the upload box.', 'integrate-google-drive' ); ?>
                    </p>

                </div>
            </div>

            <!-- Order Statues -->
            <div class="dokan-form-group">
                <label class="dokan-w3 dokan-control-label dokan-text-left" for="igd_upload_folder">
					<?php esc_html_e( 'Show When Order Status is', 'integrate-google-drive' ); ?> </label>

                <div class="dokan-w9 dokan-text-left">
                    <ul class="wc-radios">
						<?php

						//Order Status
						$order_statuses = wc_get_order_statuses();

						$order_status = get_user_meta( dokan_get_current_user_id(), '_igd_dokan_upload_order_statuses', true );
						$order_status = $order_status ?: array(
							'wc-pending',
							'wc-processing'
						);

						foreach ( $order_statuses as $key => $value ) { ?>
                            <li>
                                <label>
                                    <input type="checkbox" name="upload_order_status[]"
                                           value="<?php echo esc_attr( $key ); ?>" <?php checked( in_array( $key, $order_status ), true ); ?> />
									<?php echo esc_html( $value ); ?>
                                </label>
                            </li>
							<?php
						}

						?>
                    </ul>

                    <p class="description">
						<?php esc_html_e( 'Select the order status when the upload field will be shown to the customer.', 'integrate-google-drive' ); ?>
                        <br/>
						<?php
						/* translators: %1$s: <strong> tag, %2$s: </strong> tag */
						printf( esc_html__( 'This option will only work on the %1$s Order Received %2$s and %1$s My Account %2$s page.', 'integrate-google-drive' ), '<strong>', '</strong>' ); ?>
                    </p>

                </div>
            </div>

            <!-- Enable File Description -->
            <div class="dokan-form-group">
                <label class="dokan-w3 dokan-control-label dokan-text-left" for="upload_file_description">
					<?php esc_html_e( 'Enable File Description', 'integrate-google-drive' ); ?> </label>

                <div class="dokan-w9 dokan-text-left">
                    <label>
                        <input type="checkbox" name="_igd_upload_file_description"
                               id="upload_file_description"
                               value="1" <?php checked( get_user_meta( dokan_get_current_user_id(), '_igd_upload_file_description', true ) ); ?> />
						<?php esc_html_e( 'Enable file description field for the uploaded files.', 'integrate-google-drive' ); ?>
                    </label>

                    <p class="description">
                        <?php esc_html_e( 'Allow users to add a description to the uploaded files.', 'integrate-google-drive' ); ?>
                    </p>
                </div>
            </div>

            <!-- Upload Folder Name -->
            <div class="dokan-form-group upload-folder-name-field">
                <label for="_igd_upload_folder_name"
                       class="dokan-w3 dokan-control-label dokan-text-left"><?php esc_html_e( 'Folder Naming Template', 'integrate-google-drive' ); ?> </label>

                <div class="dokan-w9 dokan-text-left">
                    <input type="text" name="_igd_upload_folder_name" id="_igd_upload_folder_name"
                           value="<?php
					       //Upload Folder Name
					       $upload_folder_name = get_user_meta( dokan_get_current_user_id(), '_igd_dokan_upload_folder_name', true );
					       $upload_folder_name = ! empty( $upload_folder_name ) ? $upload_folder_name : 'Order - %wc_order_id% - %wc_product_name% (%user_email%)';

					       echo $upload_folder_name;
					       ?>" class="dokan-w12">

                    <p class="description">
						<?php esc_html_e( 'Unique folder name for the uploaded files. A new folder will be created in the parent folder with this name.', 'integrate-google-drive' ); ?>
                    </p>

                    <h4 class="folder-naming-template"><?php esc_html_e( 'Available Placeholders (Click to insert) : ', 'integrate-google-drive' ); ?></h4>
                    <p class="description">
                    <span class="variables">
                    <span class="variable">%wc_order_id%</span>
                    <span class="variable">%wc_order_date%</span>
                    <span class="variable">%wc_product_id%</span>
                    <span class="variable">%wc_product_name%</span>
                    <span class="variable">%user_id%</span>
                    <span class="variable">%user_email%</span>
                    <span class="variable">%user_first_name%</span>
                    <span class="variable">%user_last_name%</span>
                    <span class="variable">%user_display_name%</span>
                    <span class="variable">%user_login%</span>
                    <span class="variable">%user_role%</span>
                    <span class="variable">%user_meta_{meta_key}%</span>
                    <span class="variable">%date%</span>
                    <span class="variable">%time%</span>
                    <span class="variable">%unique_id%</span>
                </span>
                    </p>
                </div>

            </div>

            <!-- Parent Folder -->
            <div class="dokan-form-group">
                <label class="dokan-w3 dokan-control-label dokan-text-left" for="igd_upload_folder">
					<?php esc_html_e( 'Upload Parent Folder', 'integrate-google-drive' ); ?> </label>

                <div class="dokan-w9 dokan-text-left">
					<?php

					// Parent Folder
					$parent_folder     = get_user_meta( dokan_get_current_user_id(), '_igd_dokan_upload_parent_folder', true );
					$active_account_id = igd_get_active_account_id( dokan_get_current_user_id() );

					if ( empty( $parent_folder ) && ! empty( $active_account_id ) ) {
						$parent_folder = [
							'id'        => 'root',
							'accountId' => $active_account_id,
							'name'      => 'My Drive',
						];
					}

					if ( ! empty( $active_account_id ) ) { ?>
                        <input type="hidden" name="igd_upload_parent_folder" id="igd_upload_parent_folder"
                               value="<?php echo esc_attr( json_encode( $parent_folder ) ); ?>"/>

                        <div class="parent-folder">

                        <span class="parent-folder-account">
                            <?php
                            $accounts = Account::instance( dokan_get_current_user_id() )->get_accounts();

                            if ( ! empty( $accounts[ $parent_folder['accountId'] ] ) ) {
	                            echo $accounts[ $parent_folder['accountId'] ]['email'];
                            }

                            ?>
                        </span>

                            <div class="parent-folder-item">

								<?php

								if ( ! empty( $parent_folder['iconLink'] ) ) {
									echo '<img src="' . $parent_folder['iconLink'] . '"/>';
								} else {
									echo '<i class="dashicons dashicons-category"></i>';
								}

								?>

                                <span class="parent-folder-name"><?php echo $parent_folder['name']; ?></span>
                            </div>

                            <button type="button"
                                    class="button button-primary select-parent-folder"
                                    id="igd-wc-select-parent-folder">
								<?php ! empty( $parent_folder ) ? _e( 'Change Parent Folder', 'integrate-google-drive' ) : _e( 'Select Parent Folder', 'integrate-google-drive' ); ?>
                            </button>

                        </div>
					<?php } else { ?>

                        <div class="dokan-error">
							<?php esc_html_e( 'You didn\'t link any Google account yet. Please link a Google account to select the upload folder', 'integrate-google-drive' ); ?>
                            <a href="<?php echo dokan_get_navigation_url( 'settings/google-drive' ); ?>"><?php esc_html_e( 'Link a Google account →', 'integrate-google-drive' ); ?></a>
                        </div>

					<?php } ?>

                    <p class="description"><?php _e( 'Select the parent folder where the new folder will be created.', 'integrate-google-drive' ); ?></p>

                </div>
            </div>


        </form>
	<?php } ?>


</div>

<?php
/**
 * @since 1.1.86
 */
do_action( 'igd_dokan_settings_after_form', $current_user, $profile_info ); ?>
