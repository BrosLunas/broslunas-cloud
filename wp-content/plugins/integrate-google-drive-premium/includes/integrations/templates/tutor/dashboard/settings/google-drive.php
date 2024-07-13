<?php
/**
 * Profile
 *
 * @package Tutor\Templates
 * @subpackage Dashboard\Settings
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 1.6.2
 */

use IGD\Account;

$user = wp_get_current_user();


?>

<div class="tutor-fs-5 tutor-fw-medium tutor-mb-24"><?php esc_html_e( 'Settings', 'integrate-google-drive' ); ?></div>


<div class="tutor-dashboard-setting-google-drive tutor-dashboard-content-inner">

	<div class="tutor-mb-32">
		<?php tutor_load_template( 'dashboard.settings.nav-bar', array( 'active_setting_nav' => 'google-drive' ) ); ?>
		<div
			class="tutor-fs-6 tutor-fw-medium tutor-color-black tutor-mt-32"><?php esc_html_e( 'Google Drive Accounts', 'integrate-google-drive' ); ?></div>
	</div>

	<?php

	$accounts = Account::instance( $user->ID )->get_accounts();

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

		<?php } ?>

		<button class="igd-btn add-account-btn"
		        onclick="window.open(igd.authUrl, 'newwindow', 'width=550,height=600');">
			<img src="<?php echo IGD_ASSETS; ?>/images/google-icon.png"/>
			<span><?php echo __( "Add new account", 'integrate-google-drive' ); ?></span>
		</button>

	<?php } else { ?>

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

</div>
