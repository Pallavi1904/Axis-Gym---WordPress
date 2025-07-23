<?php

namespace SiteMailer\Modules\Mailer\Components;

use SiteMailer\Classes\Database\Exceptions\Missing_Table_Exception;
use SiteMailer\Modules\Logs\Database\Log_Entry;
use SiteMailer\Modules\Mailer\Classes\Mail_Handler;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Class Rate_Limit_Retry
 */
class Rate_Limit_Retry {

	const JOB_PREFIX = 'site-mailer-retry_mail_send_';

	/**
	 * Get cron jobs and add actions for it
	 */
	public function __construct() {
		$cron_jobs = wp_get_ready_cron_jobs();

		foreach ( $cron_jobs as $job ) {
			$job_name = array_keys( $job )[0];

			if ( strpos( $job_name, self::JOB_PREFIX ) === 0 ) {
				add_action( $job_name, [ static::class, 'resend_email' ], 10, 2 );
			}
		}
	}

	/**
	 * Action for resend cron job
	 * use email object for prevent lose data on disabled logs
	 *
	 * @param string $log_id
	 * @param array $email Array of the `wp_mail()` arguments.
	 *
	 *
	 * @return void
	 * @throws Missing_Table_Exception
	 */
	public static function resend_email( string $log_id, array $email ): void {
		$handler = new Mail_Handler( $email, 'Resend', 'Plugin' );
		$handler->send();

		// Remove previous log
		Log_Entry::delete_logs( [ $log_id ] );
	}

	/**
	 * Add cron job for resend email
	 *
	 * @param string $log_id
	 * @param array $email Array of the `wp_mail()` arguments.
	 *
	 * @return void
	 */
	public static function schedule_resend_email( string $log_id, array $email ) {
		// Retry after 1 hour
		$time = time() + ( 60 * 60 );

		// Schedule single event and pass params to it
		wp_schedule_single_event( $time, self::JOB_PREFIX . $log_id, [ $log_id, $email ] );
	}
}
