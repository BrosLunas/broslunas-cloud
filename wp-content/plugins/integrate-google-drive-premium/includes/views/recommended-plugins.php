<?php

if ( defined( 'ABSPATH' ) === false ) {
	exit;
}

?>
<style>
    .recommended-plugins-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 20px;
    }

    a.hide-recommended-btn {
        background: #ffa500;
        color: #fff;
        text-decoration: none;
        padding: 10px 20px;
        font-size: 1rem;
        border-radius: 4px;
    }
</style>
<?php

wp_enqueue_script( 'wp-util' );

// You may comment this out IF you're sure the function exists.
require_once ABSPATH . 'wp-admin/includes/plugin-install.php';

remove_all_filters( 'plugins_api' );

$pluginsAllowedTags = array(
	'a'       => array(
		'href'   => array(),
		'title'  => array(),
		'target' => array(),
	),
	'abbr'    => array( 'title' => array() ),
	'acronym' => array( 'title' => array() ),
	'code'    => array(),
	'pre'     => array(),
	'em'      => array(),
	'strong'  => array(),
	'ul'      => array(),
	'ol'      => array(),
	'li'      => array(),
	'p'       => array(),
	'br'      => array(),
);

$recommendedPlugins = array();

/* Radio Player */
$args = [
	'slug'   => 'radio-player',
	'fields' => [
		'short_description' => true,
		'icons'             => true,
		'reviews'           => false, // excludes all reviews
	],
];

$data = plugins_api( 'plugin_information', $args );

if ( $data && ! is_wp_error( $data ) ) {
	$recommendedPlugins['radio-player']                    = $data;
	$recommendedPlugins['radio-player']->name              = 'Radio Player – Live Shoutcast, Icecast and Any Audio Stream Player for WordPress';
	$recommendedPlugins['radio-player']->short_description = 'A simple, easy-to-use and fully customizable web Radio Player for WordPress. Radio Player is specially configured to play any MP3, Shoutcast, IceCast, Radionomy, Airtime, Live365, radio.co, and any Audio stream in your WordPress website.';
}

/* WP Radio */
$args = [
	'slug'   => 'wp-radio',
	'fields' => [
		'short_description' => true,
		'icons'             => true,
		'reviews'           => false, // excludes all reviews
	],
];

$data = plugins_api( 'plugin_information', $args );

if ( $data && ! is_wp_error( $data ) ) {
	$recommendedPlugins['wp-radio']                    = $data;
	$recommendedPlugins['wp-radio']->name              = 'WP Radio – Worldwide Online Radio Stations Directory for WordPress';
	$recommendedPlugins['wp-radio']->short_description = 'WP Radio is a worldwide online radio stations directory plugin for WordPress. You can easily create a full-featured online radio directory website with the WP Radio plugin. WP Radio has pre-included 52000+ online radio stations from around 190+ countries all over the world.';
}

/* Podcast Box */
$args = [
	'slug'   => 'podcast-box',
	'fields' => [
		'short_description' => true,
		'icons'             => true,
		'reviews'           => false, // excludes all reviews
	],
];

$data = plugins_api( 'plugin_information', $args );

if ( $data && ! is_wp_error( $data ) ) {
	$recommendedPlugins['podcast-box']                    = $data;
	$recommendedPlugins['podcast-box']->name              = 'Podcast Box – Podcast Player for WordPress';
	$recommendedPlugins['podcast-box']->short_description = 'Podcast Box is all in one solution that provides you an easy way to show and play your podcast episodes. You can also make a worldwide podcasts directory website of 5000+ podcasts included from 70+ countries.';
}

/* Dracula Dark Mode */
$args = [
	'slug'   => 'dracula-dark-mode',
	'fields' => [
		'short_description' => true,
		'icons'             => true,
		'reviews'           => false, // excludes all reviews
	],
];

$data = plugins_api( 'plugin_information', $args );

if ( $data && ! is_wp_error( $data ) ) {
	$recommendedPlugins['dracula-dark-mode']                    = $data;
	$recommendedPlugins['dracula-dark-mode']->name              = 'Dracula Dark Mode – The Revolutionary Dark Mode Plugin For WordPress';
	$recommendedPlugins['dracula-dark-mode']->short_description = 'Dracula Dark Mode is a highly customizable and easy-to-use dark mode plugin for WordPress. It offers an elegant dark mode version of your website, reducing eye strain for your visitors.';
}

/* Reader Mode */
$args = [
	'slug'   => 'reader-mode',
	'fields' => [
		'short_description' => true,
		'icons'             => true,
		'reviews'           => false, // excludes all reviews
	],
];

$data = plugins_api( 'plugin_information', $args );
if ( $data && ! is_wp_error( $data ) ) {
	$recommendedPlugins['reader-mode']                    = $data;
	$recommendedPlugins['reader-mode']->name              = 'Reader Mode – Distraction-Free Content Reader For WordPress';
	$recommendedPlugins['reader-mode']->short_description = 'Reader Mode Plugin adds a distraction-free reading experience for users by stripping away clutter and unnecessary elements from the article or post content. To achieve better readability, accessibility, and easy operations for your readers Reader Mode can be a handy choice.';
}

/* Upload Fields for WPForms */
$args = [
	'slug'   => 'upload-fields-for-wpforms',
	'fields' => [
		'short_description' => true,
		'icons'             => true,
		'reviews'           => false, // excludes all reviews
	],
];

$data = plugins_api( 'plugin_information', $args );
if ( $data && ! is_wp_error( $data ) ) {
	$recommendedPlugins['upload-fields-for-wpforms']                    = $data;
	$recommendedPlugins['upload-fields-for-wpforms']->name              = 'Upload Fields for WPForms – Drag and Drop Multiple File Upload, Image Upload, and Google Drive Upload for WPForms';
	$recommendedPlugins['upload-fields-for-wpforms']->short_description = 'Upload Fields for WPForms is an addon plugin for WPForms that allows you to add drag and drop multiple file upload, image upload, and Google Drive upload fields to your forms.';
}

/* Hide Anything */
$args = [
	'slug'   => 'hide-anything',
	'fields' => [
		'short_description' => true,
		'icons'             => true,
		'reviews'           => false, // excludes all reviews
	],
];

$data = plugins_api( 'plugin_information', $args );
if ( $data && ! is_wp_error( $data ) ) {
	$recommendedPlugins['hide-anything']                    = $data;
	$recommendedPlugins['hide-anything']->name              = 'Hide Anything – Hide Any Element on Your WordPress Website';
	$recommendedPlugins['hide-anything']->short_description = 'If you dont know coding or want to hide any unnecessary element on your site then this plugin is for you. You can hide any element on any page by visually selection with a simple click without any coding. The user won’t see the hidden elements when they will browse the site until you make it visible again.';
}


?>
<div class="wrap recommended-plugins">
    <h2 class="recommended-plugins-header">
		<?php _e( 'Try out our recommended plugins', 'integrate-google-drive' ); ?>
        <a class="hide-recommended-btn" href="#" class=""><?php _e( 'Hide From Menu', 'integrate-google-drive' ); ?></a>
    </h2>
</div>
<div class="wrap recommended-plugins">
    <div class="wp-list-table widefat plugin-install">
        <div class="the-list">
			<?php
			foreach ( (array) $recommendedPlugins as $plugin ) {
				if ( is_object( $plugin ) ) {
					$plugin = (array) $plugin;
				}

				// Display the group heading if there is one.
				if ( isset( $plugin['group'] ) && $plugin['group'] != $group ) {
					if ( isset( $this->groups[ $plugin['group'] ] ) ) {
						$group_name = $this->groups[ $plugin['group'] ];
						if ( isset( $plugins_group_titles[ $group_name ] ) ) {
							$group_name = $plugins_group_titles[ $group_name ];
						}
					} else {
						$group_name = $plugin['group'];
					}

					// Starting a new group, close off the divs of the last one.
					if ( ! empty( $group ) ) {
						echo '</div></div>';
					}

					echo '<div class="plugin-group"><h3>' . esc_html( $group_name ) . '</h3>';
					// Needs an extra wrapping div for nth-child selectors to work.
					echo '<div class="plugin-items">';

					$group = $plugin['group'];
				}
				$title = wp_kses( $plugin['name'], $pluginsAllowedTags );

				// Remove any HTML from the description.
				$description = strip_tags( $plugin['short_description'] );
				$version     = wp_kses( $plugin['version'], $pluginsAllowedTags );

				$name = strip_tags( $title . ' ' . $version );

				$author = wp_kses( $plugin['author'], $pluginsAllowedTags );
				if ( ! empty( $author ) ) {
					/* translators: %s: Plugin author. */
					$author = ' <cite>' . sprintf( __( 'By %s' ), $author ) . '</cite>';
				}

				$requires_php = isset( $plugin['requires_php'] ) ? $plugin['requires_php'] : null;
				$requires_wp  = isset( $plugin['requires'] ) ? $plugin['requires'] : null;

				$compatible_php = is_php_version_compatible( $requires_php );
				$compatible_wp  = is_wp_version_compatible( $requires_wp );
				$tested_wp      = ( empty( $plugin['tested'] ) || version_compare( get_bloginfo( 'version' ), $plugin['tested'], '<=' ) );

				$action_links = array();

				if ( current_user_can( 'install_plugins' ) || current_user_can( 'update_plugins' ) ) {
					$status = install_plugin_install_status( $plugin );

					switch ( $status['status'] ) {
						case 'install':
							if ( $status['url'] ) {
								if ( $compatible_php && $compatible_wp ) {
									$action_links[] = sprintf(
										'<a class="install-now button" data-slug="%s" href="%s" aria-label="%s" data-name="%s">%s</a>',
										esc_attr( $plugin['slug'] ),
										esc_url( $status['url'] ),
										/* translators: %s: Plugin name and version. */
										esc_attr( sprintf( _x( 'Install %s now', 'plugin' ), $name ) ),
										esc_attr( $name ),
										__( 'Install Now' )
									);
								} else {
									$action_links[] = sprintf(
										'<button type="button" class="button button-disabled" disabled="disabled">%s</button>',
										_x( 'Cannot Install', 'plugin' )
									);
								}
							}
							break;

						case 'update_available':
							if ( $status['url'] ) {
								if ( $compatible_php && $compatible_wp ) {
									$action_links[] = sprintf(
										'<a class="update-now button aria-button-if-js" data-plugin="%s" data-slug="%s" href="%s" aria-label="%s" data-name="%s">%s</a>',
										esc_attr( $status['file'] ),
										esc_attr( $plugin['slug'] ),
										esc_url( $status['url'] ),
										/* translators: %s: Plugin name and version. */
										esc_attr( sprintf( _x( 'Update %s now', 'plugin' ), $name ) ),
										esc_attr( $name ),
										__( 'Update Now' )
									);
								} else {
									$action_links[] = sprintf(
										'<button type="button" class="button button-disabled" disabled="disabled">%s</button>',
										_x( 'Cannot Update', 'plugin' )
									);
								}
							}
							break;

						case 'latest_installed':
						case 'newer_installed':
							if ( is_plugin_active( $status['file'] ) ) {
								$action_links[] = sprintf(
									'<button type="button" class="button button-disabled" disabled="disabled">%s</button>',
									_x( 'Active', 'plugin' )
								);
							} elseif ( current_user_can( 'activate_plugin', $status['file'] ) ) {
								$button_text = __( 'Activate' );
								/* translators: %s: Plugin name. */
								$button_label = _x( 'Activate %s', 'plugin' );
								$activate_url = add_query_arg(
									array(
										'_wpnonce' => wp_create_nonce( 'activate-plugin_' . $status['file'] ),
										'action'   => 'activate',
										'plugin'   => $status['file'],
									),
									network_admin_url( 'plugins.php' )
								);

								if ( is_network_admin() ) {
									$button_text = __( 'Network Activate' );
									/* translators: %s: Plugin name. */
									$button_label = _x( 'Network Activate %s', 'plugin' );
									$activate_url = add_query_arg( array( 'networkwide' => 1 ), $activate_url );
								}

								$action_links[] = sprintf(
									'<a href="%1$s" class="button activate-now" aria-label="%2$s">%3$s</a>',
									esc_url( $activate_url ),
									esc_attr( sprintf( $button_label, $plugin['name'] ) ),
									$button_text
								);
							} else {
								$action_links[] = sprintf(
									'<button type="button" class="button button-disabled" disabled="disabled">%s</button>',
									_x( 'Installed', 'plugin' )
								);
							}
							break;
					}
				}

				$details_link = self_admin_url(
					'plugin-install.php?tab=plugin-information&amp;plugin=' . $plugin['slug'] .
					'&amp;TB_iframe=true&amp;width=600&amp;height=550'
				);

				$action_links[] = sprintf(
					'<a href="%s" class="thickbox open-plugin-details-modal" aria-label="%s" data-title="%s">%s</a>',
					esc_url( $details_link ),
					/* translators: %s: Plugin name and version. */
					esc_attr( sprintf( __( 'More information about %s' ), $name ) ),
					esc_attr( $name ),
					__( 'More Details' )
				);

				if ( ! empty( $plugin['icons']['svg'] ) ) {
					$plugin_icon_url = $plugin['icons']['svg'];
				} elseif ( ! empty( $plugin['icons']['2x'] ) ) {
					$plugin_icon_url = $plugin['icons']['2x'];
				} elseif ( ! empty( $plugin['icons']['1x'] ) ) {
					$plugin_icon_url = $plugin['icons']['1x'];
				} else {
					$plugin_icon_url = $plugin['icons']['default'];
				}

				/**
				 * Filters the install action links for a plugin.
				 *
				 * @param string[] $action_links An array of plugin action links. Defaults are links to Details and Install Now.
				 * @param array $plugin The plugin currently being listed.
				 *
				 * @since 2.7.0
				 *
				 */
				$action_links = apply_filters( 'plugin_install_action_links', $action_links, $plugin );

				$last_updated_timestamp = strtotime( $plugin['last_updated'] );
				?>
                <div class="plugin-card plugin-card-<?php echo sanitize_html_class( $plugin['slug'] ); ?>">
					<?php
					if ( ! $compatible_php || ! $compatible_wp ) {
						echo '<div class="notice inline notice-error notice-alt"><p>';
						if ( ! $compatible_php && ! $compatible_wp ) {
							_e( 'This plugin doesn&#8217;t work with your versions of WordPress and PHP.' );
							if ( current_user_can( 'update_core' ) && current_user_can( 'update_php' ) ) {
								printf(
								/* translators: 1: URL to WordPress Updates screen, 2: URL to Update PHP page. */
									' ' . __( '<a href="%1$s">Please update WordPress</a>, and then <a href="%2$s">learn more about updating PHP</a>.' ),
									self_admin_url( 'update-core.php' ),
									esc_url( wp_get_update_php_url() )
								);
								wp_update_php_annotation( '</p><p><em>', '</em>' );
							} elseif ( current_user_can( 'update_core' ) ) {
								printf(
								/* translators: %s: URL to WordPress Updates screen. */
									' ' . __( '<a href="%s">Please update WordPress</a>.' ),
									self_admin_url( 'update-core.php' )
								);
							} elseif ( current_user_can( 'update_php' ) ) {
								printf(
								/* translators: %s: URL to Update PHP page. */
									' ' . __( '<a href="%s">Learn more about updating PHP</a>.' ),
									esc_url( wp_get_update_php_url() )
								);
								wp_update_php_annotation( '</p><p><em>', '</em>' );
							}
						} elseif ( ! $compatible_wp ) {
							_e( 'This plugin doesn&#8217;t work with your version of WordPress.' );
							if ( current_user_can( 'update_core' ) ) {
								printf(
								/* translators: %s: URL to WordPress Updates screen. */
									' ' . __( '<a href="%s">Please update WordPress</a>.' ),
									self_admin_url( 'update-core.php' )
								);
							}
						} elseif ( ! $compatible_php ) {
							_e( 'This plugin doesn&#8217;t work with your version of PHP.' );
							if ( current_user_can( 'update_php' ) ) {
								printf(
								/* translators: %s: URL to Update PHP page. */
									' ' . __( '<a href="%s">Learn more about updating PHP</a>.' ),
									esc_url( wp_get_update_php_url() )
								);
								wp_update_php_annotation( '</p><p><em>', '</em>' );
							}
						}
						echo '</p></div>';
					}
					?>
                    <div class="plugin-card-top">
                        <div class="name column-name">
                            <h3>
                                <a href="<?php echo esc_url( $details_link ); ?>"
                                   class="thickbox open-plugin-details-modal">
									<?php echo esc_attr( $title ); ?>
                                    <img src="<?php echo esc_attr( $plugin_icon_url ); ?>" class="plugin-icon" alt=""/>
                                </a>
                            </h3>
                        </div>
                        <div class="action-links">
							<?php
							if ( $action_links ) {
								echo '<ul class="plugin-action-buttons"><li>' . implode( '</li><li>', $action_links ) . '</li></ul>';
							}
							?>
                        </div>
                        <div class="desc column-description">
                            <p><?php echo esc_html( $description ); ?></p>
                            <p class="authors"><?php echo wp_kses( $author, $pluginsAllowedTags ); ?></p>
                        </div>
                    </div>
                    <div class="plugin-card-bottom">
                        <div class="vers column-rating">
							<?php
							wp_star_rating(
								array(
									'rating' => $plugin['rating'],
									'type'   => 'percent',
									'number' => $plugin['num_ratings'],
								)
							);
							?>
                            <span class="num-ratings"
                                  aria-hidden="true">(<?php echo number_format_i18n( $plugin['num_ratings'] ); ?>)</span>
                        </div>
                        <div class="column-updated">
                            <strong><?php _e( 'Last Updated:' ); ?></strong>
							<?php
							/* translators: %s: Human-readable time difference. */
							printf( __( '%s ago' ), human_time_diff( $last_updated_timestamp ) );
							?>
                        </div>
                        <div class="column-downloaded">
							<?php
							if ( $plugin['active_installs'] >= 1000000 ) {
								$active_installs_millions = floor( $plugin['active_installs'] / 1000000 );
								$active_installs_text     = sprintf(
								/* translators: %s: Number of millions. */
									_nx( '%s+ Million', '%s+ Million', $active_installs_millions, 'Active plugin installations' ),
									number_format_i18n( $active_installs_millions )
								);
							} elseif ( 0 == $plugin['active_installs'] ) {
								$active_installs_text = _x( 'Less Than 10', 'Active plugin installations' );
							} else {
								$active_installs_text = number_format_i18n( $plugin['active_installs'] ) . '+';
							}
							/* translators: %s: Number of installations. */
							printf( __( '%s Active Installations' ), $active_installs_text );
							?>
                        </div>
                        <div class="column-compatibility">
							<?php
							if ( ! $tested_wp ) {
								echo '<span class="compatibility-untested">' . __( 'Untested with your version of WordPress' ) . '</span>';
							} elseif ( ! $compatible_wp ) {
								echo '<span class="compatibility-incompatible">' . __( '<strong>Incompatible</strong> with your version of WordPress' ) . '</span>';
							} else {
								echo '<span class="compatibility-compatible">' . __( '<strong>Compatible</strong> with your version of WordPress' ) . '</span>';
							}
							?>
                        </div>
                    </div>
                </div>
				<?php
			} ?>
        </div>
    </div>
    <div id="hide-recommeded-plugins" style="display:none;"
         title="<?php _e( 'Are you sure?', 'integrate-google-drive' ); ?>">
        <p><?php _e( "If you hide the recommended plugins page from your menu, it won't appear there again. Are you sure you'd like to do it?", 'integrate-google-drive' ); ?></p>
    </div>

</div>
<script>
    (function ($) {
        "use strict";
        $(document).ready(function () {
            $('a.hide-recommended-btn').on('click', function (event) {
                event.preventDefault();

                Swal.fire({
                    title: wp.i18n.__('Are you sure?', "integrate-google-drive"),
                    text: wp.i18n.__('If you hide the recommended plugins page from your menu, it won\'t appear there again. Are you sure you\'d like to do it?', "integrate-google-drive"),
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: wp.i18n.__('Yes, hide it!', "integrate-google-drive"),
                    cancelButtonText: wp.i18n.__('No, cancel!', "integrate-google-drive"),
                    reverseButtons: true,
                }).then((result) => {
                    if (result.value) {

                        wp.ajax.send('igd_hide_recommended_plugins', {
                            data: {
                                nonce: igd.nonce,
                            },
                            success: function () {
                                Swal.fire({
                                    title: wp.i18n.__('Done!', "integrate-google-drive"),
                                    text: wp.i18n.__('The recommended plugins page has been hidden from your menu.', "integrate-google-drive"),
                                    icon: 'success',
                                    showConfirmButton: false,
                                    timer: 2500
                                });

                                setTimeout(function () {
                                    window.location = '<?php echo admin_url() ?>/admin.php?page=integrate-google-drive-settings';
                                }, 2500);

                            },
                            error: function (error) {
                                console.log(error);
                            },
                        });
                    }
                });

            });
        });
    })(jQuery);
</script>

