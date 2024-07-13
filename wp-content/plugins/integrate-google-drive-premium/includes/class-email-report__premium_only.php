<?php

namespace IGD;

defined( 'ABSPATH' ) || exit();

class Email_Report {
	/** @var null */
	private static $instance = null;

	/**
	 *  constructor.
	 */
	public function __construct() {
		add_filter( 'cron_schedules', array( $this, 'cron_schedules' ) );

		$enable_statistics   = igd_get_settings( 'enableStatistics', false );
		$enable_email_report = igd_get_settings( 'emailReport', false );
		$frequency           = igd_get_settings( 'emailReportFrequency', 'weekly' );

		if ( ! $enable_email_report || ! $enable_statistics ) {

			wp_clear_scheduled_hook( 'igd_monthly_report' );
			wp_clear_scheduled_hook( 'igd_weekly_report' );
			wp_clear_scheduled_hook( 'igd_daily_report' );

			return;
		}

		if ( 'monthly' == $frequency ) {
			wp_clear_scheduled_hook( 'igd_daily_report' );
			wp_clear_scheduled_hook( 'igd_weekly_report' );
		} else if ( 'weekly' == $frequency ) {
			wp_clear_scheduled_hook( 'igd_daily_report' );
			wp_clear_scheduled_hook( 'igd_monthly_report' );
		} else {
			wp_clear_scheduled_hook( 'igd_weekly_report' );
			wp_clear_scheduled_hook( 'igd_monthly_report' );
		}

		add_action( 'admin_init', [ $this, 'activate_email_reporting' ] );
		add_action( "igd_{$frequency}_report", [ $this, 'send_report' ] );
	}

	public function cron_schedules( $schedules ) {
		$schedules['monthly'] = array(
			'interval' => 2592000,
			'display'  => __( 'Once Monthly', 'integrate-google-drive' ),
		);

		return $schedules;
	}

	public function activate_email_reporting() {
		$frequency = igd_get_settings( 'emailReportFrequency', 'weekly' );

		$datetime = strtotime( "next monday 9AM", current_time( 'timestamp' ) );
		if ( 'daily' == $frequency ) {
			$datetime = strtotime( "tomorrow 9AM", current_time( 'timestamp' ) );
		} elseif ( 'monthly' == $frequency ) {
			$datetime = strtotime( "first day of next month 9AM", current_time( 'timestamp' ) );
		}

		if ( ! wp_next_scheduled( "igd_{$frequency}_report" ) ) {
			wp_schedule_event( $datetime, $frequency, "igd_{$frequency}_report" );
		}
	}

	public function send_report() {
		$enable_statistics   = igd_get_settings( 'enableStatistics', false );
		$enable_email_report = igd_get_settings( 'emailReport', false );
		$frequency           = igd_get_settings( 'emailReportFrequency', 'weekly' );

		if ( ! $enable_email_report || ! $enable_statistics ) {
			wp_clear_scheduled_hook( 'igd_monthly_report' );
			wp_clear_scheduled_hook( 'igd_weekly_report' );
			wp_clear_scheduled_hook( 'igd_daily_report' );

			return;
		}

		$length          = 7;
		$frequency_title = __( 'Weekly', 'integrate-google-drive' );

		if ( 'monthly' == $frequency ) {
			$length          = 30;
			$frequency_title = __( 'Monthly', 'integrate-google-drive' );
		} elseif ( 'daily' == $frequency ) {
			$length          = 1;
			$frequency_title = __( 'Daily', 'integrate-google-drive' );
		}

		$start_date = date( 'Y-m-d', strtotime( "-{$length} days" ) );
		$end_date   = date( 'Y-m-d' );

		$recipients = igd_get_settings( 'emailReportRecipients', get_option( 'admin_email' ) );
		$recipients = trim( $recipients, ',' );
		$recipients = apply_filters( 'igd_email_report_recipients', $recipients );

		if ( empty( $recipients ) ) {
			return;
		}

		$subject = $frequency_title . __( ' Statistics Summary of Integrate Google Drive.', 'integrate-google-drive' );

		if ( ! class_exists( 'IGD\Statistics' ) ) {
			require_once IGD_INCLUDES . '/class-statistics__premium_only.php';
		}

		$statistics = new Statistics();
		$logs       = $statistics->get_logs( $start_date, $end_date, true );

		ob_start();
		include_once IGD_INCLUDES . '/views/email-report__premium_only.php';
		$email_message = ob_get_clean();

		$headers = array(
			'Content-Type: text/html; charset=UTF-8',
			'From: ' . get_option( 'blogname' ) . ' <' . get_option( 'admin_email' ) . '>',
		);

		wp_mail( $recipients, $subject, $email_message, $headers );
	}

	public static function instance() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}

		return self::$instance;
	}
}

Email_Report::instance();