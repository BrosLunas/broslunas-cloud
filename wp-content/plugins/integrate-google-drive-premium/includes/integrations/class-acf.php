<?php

namespace IGD;

class ACF extends \acf_field {

	private static $instance = null;

	public function __construct() {

		$this->name = 'integrate_google_drive_field';

		$this->label = __( 'Google Drive Files', 'integrate-google-drive' );

		$this->category = __( 'Integrate Google Drive', 'integrate-google-drive' );

		parent::__construct();

		// show data in vendor product edit form.
		add_action( 'dokan_product_edit_after_product_tags', array( $this, 'acf_dokan_show_on_edit_page' ), 99, 2 );

	}

	public function acf_dokan_show_on_edit_page( $post, $post_id ) {
		$acfs = $this->acf_dokan_get_custom_fields();

		foreach ( $acfs as $key => $acf ) {

			foreach ( $acf as $key => $value ) {

				if ( $value['type'] != 'integrate_google_drive_field' || ! $value['vendor_edit'] ) {
					continue;
				}

				// Check Vendor edit allowed from custom field settings.
				if ( $value['vendor_edit'] ) {
					$custom_field = get_post_meta( $post_id, $value['name'], true );

					if ( ! empty( $custom_field ) ) {
						$custom_field = json_decode( $custom_field, true );
					}

					$label    = sanitize_text_field( $value['label'] );
					$name     = sanitize_text_field( $value['name'] );
					$type     = sanitize_text_field( $value['type'] );
					$required = $value['required']; ?>

                    <div class="dokan-form-group">
                        <div class="acf-label">
                            <label for="<?php echo $name; ?>" class="form-label"><?php echo $label; ?></label>
                        </div>

                        <div class="acf-input">
							<?php

							acf_render_field(
								[
									'type'     => $type,
									'name'     => $name,
									'value'    => $custom_field,
									'required' => $required,
								] );

							?>
                        </div>
                    </div>
					<?php
				}
			}
		}
	}

	public function acf_dokan_get_custom_fields() {
		$fields          = acf_get_raw_field_groups();
		$products_fields = array();
		foreach ( $fields as $key => $value ) {
			foreach ( $value['location'] as $locations ) {
				foreach ( $locations as $location ) {
					if ( $location['param'] == 'post_type' && $location['value'] == 'product' && $location['operator'] == '==' ) {
						$products_fields[] = $value;
					}
				}
			}
		}
		$acfs = array();
		foreach ( $products_fields as $key => $value ) {
			$acfs[] = acf_get_raw_fields( $value['ID'] );
		}

		return $acfs;
	}


	public function render_field( $field ) {

		if ( ! wp_script_is( 'igd-admin', 'registered' ) ) {
			Enqueue::instance()->admin_scripts( '', false );
		}

		if ( ! wp_script_is( 'igd-acf', 'registered' ) ) {
			wp_enqueue_script( 'igd-acf', IGD_ASSETS . '/js/acf.js', [ 'igd-admin' ], IGD_VERSION, true );
		}

		?>
        <div class="igd-acf-field">
			<?php

			acf_hidden_input(
				[
					'name'      => $field['name'],
					'value'     => empty( $field['value'] ) ? '' : json_encode( $field['value'] ),
					'data-name' => 'id',
				]
			);

			$files = $field['value'];

			?>
            <table class="igd-items-table wp-list-table widefat striped">
                <thead>
                <th style="width: 18px;"></th>
                <th><?php esc_html_e( 'Name', 'integrate-google-drive' ); ?></th>
                <th><?php esc_html_e( 'File ID', 'integrate-google-drive' ); ?></th>
                <th style="width: 220px;"><?php esc_html_e( 'Actions', 'integrate-google-drive' ); ?></th>
                </thead>

                <tbody>
				<?php
				if ( ! empty( $files ) ) {
					foreach ( $files as $file ) { ?>
                        <tr>
                            <td><img class="file-icon" src="<?php echo esc_url_raw( $file['icon_link'] ); ?>"/></td>
                            <td class="file-name"
                                style="max-width: 220px;overflow: hidden;white-space: nowrap;text-overflow: ellipsis;display: block;"><?php echo esc_html( $file['name'] ); ?></td>
                            <td class="file-id"><?php echo esc_html( $file['id'] ); ?></td>
                            <td class="file-actions">
                                <a href="<?php echo esc_url_raw( $file['view_link'] ); ?>" target="_blank"
                                   class="button file-view"><?php esc_html_e( 'View', 'integrate-google-drive' ); ?></a>
                                <a href="<?php echo esc_url_raw( $file['download_link'] ); ?>"
                                   class="button file-download"><?php esc_html_e( 'Download', 'integrate-google-drive' ); ?></a>
                                <a href="#" class="button button-link-delete file-remove"
                                   data-id="<?php echo esc_attr( $file['id'] ) ?>"><?php esc_html_e( 'Remove', 'integrate-google-drive' ); ?></a>
                            </td>
                        </tr>
					<?php }
				} else {
					printf( '<tr class="empty-row"><td></td><td colspan="3">%s</td></tr>', __( 'No Files Added', 'integrate-google-drive' ) );
				}
				?>
                </tbody>
            </table>

            <button type="button" class="button button-secondary igd-acf-button">
                <img src="<?php echo IGD_ASSETS; ?>/images/drive.png" width="20"/>
                <span><?php echo __( 'Add File', 'integrate-google-drive' ) ?></span>
            </button>
        </div>
		<?php
	}

	public function load_value( $value, $post_id, $field ) {
		if ( empty( $value ) ) {
			return [];
		}

		return json_decode( $value, true );
	}

	public function update_value( $value, $post_id, $field ) {

		if ( ! is_array( $value ) ) {
			$entries = json_decode( wp_unslash( $value ), true );
		} else {
			$entries = $value;
		}


		if ( empty( $entries ) ) {
			return [];
		}

		return json_encode( $entries );
	}

	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self;
		}

		return self::$instance;
	}

}

ACF::instance();