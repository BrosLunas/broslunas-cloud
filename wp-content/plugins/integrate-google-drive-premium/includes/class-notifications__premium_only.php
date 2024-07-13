<?php

namespace IGD;

class Notifications {

	private static $instance = null;

	private $files;
	private $type;
	private $notifications;

	public function __construct() {
		add_action( 'wp_ajax_igd_notification', [ $this, 'process_notification' ] );
		add_action( 'wp_ajax_nopriv_igd_notification', [ $this, 'process_notification' ] );

		//handle view and download notification
		add_action( 'wp_ajax_igd_send_view_download_notification', [ $this, 'process_view_download_notification' ] );
		add_action( 'wp_ajax_nopriv_igd_send_view_download_notification', [
			$this,
			'process_view_download_notification'
		] );
	}

	public function process_view_download_notification() {
		$file_id = ! empty( $_POST['id'] ) ? sanitize_text_field( $_POST['id'] ) : '';

		if ( empty( $file_id ) ) {
			wp_send_json_error();
		}

		$account_id = ! empty( $_POST['accountId'] ) ? sanitize_text_field( $_POST['accountId'] ) : '';
		$file       = App::instance( $account_id )->get_file_by_id( $file_id );

		if ( ! $file ) {
			wp_send_json_error();
		}

		$this->files = [ $file ];

		$type       = ! empty( $_POST['type'] ) ? sanitize_text_field( $_POST['type'] ) : '';
		$this->type = $type;

		$notification_email             = ! empty( $_POST['notificationEmail'] ) ? trim( strip_tags( ( $_POST['notificationEmail'] ) ) ) : '%admin_email%';
		$skip_current_user_notification = ! empty( $_POST['skipCurrentUserNotification'] ) ? filter_var( $_POST['skipCurrentUserNotification'], FILTER_VALIDATE_BOOLEAN ) : '';

		$this->notifications = [
			'notificationEmail'           => $notification_email,
			'skipCurrentUserNotification' => $skip_current_user_notification
		];

		$this->process_notification( true );

	}

	public function process_notification( $is_direct = false ) {

		// sanitize inputs
		if ( ! $is_direct ) {
			$this->sanitize_inputs();
		}

		// check if inputs are empty
		if ( empty( $this->files ) || empty( $this->type ) || empty( $this->notifications ) ) {
			wp_send_json_error();
		}

		// get recipients
		$recipient_arr = $this->get_recipients();

		// construct subject
		$subject = $this->construct_subject();

		// prepare message
		$message = $this->prepare_message();

		// prepare headers
		$headers = $this->prepare_headers();

		// send email
		$this->send_email( $recipient_arr, $subject, $message, $headers );

		wp_send_json_success();
	}

	private function sanitize_inputs() {
		$files         = ! empty( $_POST['files'] ) ? igd_sanitize_array( $_POST['files'] ) : [];
		$type          = ! empty( $_POST['type'] ) ? sanitize_text_field( $_POST['type'] ) : '';
		$notifications = ! empty( $_POST['notifications'] ) ? igd_sanitize_array( $_POST['notifications'] ) : [];

		$this->files         = $files;
		$this->type          = $type;
		$this->notifications = $notifications;
	}

	private function get_recipients() {
		$recipient_str = $this->notifications['notificationEmail'];
		$recipient_arr = array_map( 'trim', explode( ',', $recipient_str ) );

		// replace placeholders with actual emails
		$recipient_arr = $this->replace_email_placeholders( $recipient_arr );

		$skip_current_user = ! empty( $this->notifications['skipCurrentUserNotification'] );

		if ( $skip_current_user ) {
			$user_id = get_current_user_id();
			if ( $user_id ) {
				$user_email = get_user_by( 'id', $user_id )->user_email;

				$recipient_arr = array_diff( $recipient_arr, [ $user_email ] );
			}
		}

		// unique emails
		return array_unique( $recipient_arr );
	}

	private function replace_email_placeholders( $recipient_arr ) {
		$user_id = get_current_user_id();

		// Admin email
		if ( false !== $admin_email_key = array_search( '%admin_email%', $recipient_arr ) ) {
			unset( $recipient_arr[ $admin_email_key ] );
			$recipient_arr[] = get_option( 'admin_email' );
		}

		// User email
		if ( false !== $user_email_key = array_search( '%user_email%', $recipient_arr ) ) {
			unset( $recipient_arr[ $user_email_key ] );
			if ( $user_id ) {
				$recipient_arr[] = get_user_by( 'id', $user_id )->user_email;
			}
		}

		// Linked users
		if ( $linked_users_key = array_search( '%linked_user_email%', $recipient_arr ) ) {
			unset( $recipient_arr[ $linked_users_key ] );
			$linked_emails = $this->get_linked_user_emails();
			$recipient_arr = array_merge( $recipient_arr, $linked_emails );
		}

		return $recipient_arr;
	}

	private function get_linked_user_emails() {
		global $wpdb;
		$linked_emails = [];

		//select files with the different parents
		$uniqueParentFiles = [];
		$uniqueParents     = [];

		foreach ( $this->files as $file ) {
			if ( ! empty( $file['parents'] ) ) {
				$parent = $file['parents'][0]; // Assuming each file only has one parent
				if ( ! isset( $uniqueParents[ $parent ] ) ) {
					$uniqueParents[ $parent ] = true;
					$uniqueParentFiles[]      = $file;
				}
			}
		}

		foreach ( $uniqueParentFiles as $file ) {
			$parent_folders = igd_get_all_parent_folders( $file );

			$meta_query = [
				'relation' => 'OR',
				[
					'key'     => $wpdb->prefix . 'folders',
					'value'   => '"' . $file['id'] . '"',
					'compare' => 'LIKE',
				],
			];

			if ( ! empty( $parent_folders ) ) {
				foreach ( $parent_folders as $parent_folder ) {
					$meta_query[] = [
						'key'     => $wpdb->prefix . 'folders',
						'value'   => '"' . $parent_folder['id'] . '"',
						'compare' => 'LIKE',
					];
				}
			}

			$linked_users = get_users( [ 'meta_query' => $meta_query ] );

			if ( ! empty( $linked_users ) ) {
				foreach ( $linked_users as $linked_user ) {
					$linked_emails[] = $linked_user->user_email;
				}
			}
		}

		return $linked_emails;
	}

	private function get_user_name() {
		$user_id = get_current_user_id();

		return $user_id ? get_user_by( 'id', $user_id )->user_login : __( 'An anonymous user', 'integrate-google-drive' );
	}

	private function get_file_name() {
		return ( count( $this->files ) == 1 ) ? $this->files[0]['name'] : __( 'file', 'integrate-google-drive' );
	}

	private function construct_subject() {
		// get username
		$user_name = $this->get_user_name();

		$ext = $this->get_file_name();

		switch ( $this->type ) {

			case 'upload':
				$upload_folder_id   = ! empty( $this->files[0]['parents'] ) ? $this->files[0]['parents'][0] : '';
				$account_id         = $this->files[0]['accountId'];
				$upload_folder_name = ! empty( $upload_folder_id ) ? App::instance( $account_id )->get_file_by_id( $upload_folder_id )['name'] : '';

				if ( count( $this->files ) > 1 ) {
					/* translators: %1$s: number of files, %2$s: folder name */
					$ext = sprintf( __( '%1$s files to %2$s.', 'integrate-google-drive' ), count( $this->files ), $upload_folder_name );
				} else {
					/* translators: %1$s: file name, %2$s: folder name */
					$ext = sprintf( __( '%1$s file to %2$s.', 'integrate-google-drive' ), $this->get_file_name(), $upload_folder_name );
				}

				/* translators: %1$s: user name, %2$s: file name */

				return sprintf( __( '%1$s uploaded %2$s', 'integrate-google-drive' ), $user_name, $ext );

			case 'delete':

				if ( count( $this->files ) > 1 ) {
					/* translators: %1$s: number of files */
					$ext = sprintf( __( '(%1$s) file(s)', 'integrate-google-drive' ), count( $this->files ) );
				}

				/* translators: %1$s: user name, %2$s: file name */

				return sprintf( __( '%1$s deleted %2$s', 'integrate-google-drive' ), $user_name, $ext );

			case 'search':
				$ext = sanitize_text_field( $_POST['keyword'] );

				/* translators: %1$s: user name, %2$s: file name */

				return sprintf( __( '%1$s searched for %2$s', 'integrate-google-drive' ), $user_name, $ext );

			case 'play':

				/* translators: %1$s: user name, %2$s: file name */
				return sprintf( __( '%1$s played %2$s', 'integrate-google-drive' ), $user_name, $ext );

			case 'view':

				/* translators: %1$s: user name, %2$s: file name */
				return sprintf( __( '%1$s viewed %2$s', 'integrate-google-drive' ), $user_name, $ext );

			default:
				if ( count( $this->files ) > 1 ) {
					/* translators: %1$s: number of files */
					$ext = sprintf( __( '(%1$s) file(s)', 'integrate-google-drive' ), count( $this->files ) );
				}

				/* translators: %1$s: user name, %2$s: file name */

				return sprintf( __( '%1$s downloaded %2$s', 'integrate-google-drive' ), $user_name, $ext );
		}
	}

	private function prepare_message() {
		ob_start();

		$subject   = $this->construct_subject();
		$user_name = $this->get_user_name();
		$type      = $this->type;
		$files     = $this->files;

		include_once IGD_INCLUDES . '/views/notification-email__premium_only.php';

		return ob_get_clean();
	}

	private function prepare_headers() {
		return [
			'Content-Type: text/html; charset=UTF-8',
			'From: ' . get_bloginfo( 'name' ) . ' <' . get_bloginfo( 'admin_email' ) . '>'
		];
	}

	private function send_email( $to, $subject, $message, $headers ) {
		if ( ! empty( $to ) ) {
			wp_mail( $to, $subject, $message, $headers );
		}
	}

	public static function view() { ?>
        <div id="igd-notifications" class="igd-notifications"></div>
	<?php }

	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self;
		}

		return self::$instance;
	}

}

Notifications::instance();