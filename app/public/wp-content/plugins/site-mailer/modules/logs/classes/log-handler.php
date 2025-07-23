<?php

namespace SiteMailer\Modules\Logs\Classes;

use Exception;
use SiteMailer\Classes\Logger;
use SiteMailer\Classes\Services\Client;
use SiteMailer\Modules\Logs\Database\Log_Entry;
use SiteMailer\Modules\Statuses\Database\Status_Entry;
use Throwable;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Log_Handler
 *
 * Class for get/update log
 */
class Log_Handler {

	const SERVICE_ENDPOINT = 'log';
	const JOB_PREFIX = 'site-mailer-retry_log_update_';
	const JOB_DAILY_HOOK = 'daily_remove_logs';

	/**
	 * Send request to the mail service for get status
	 *
	 * @param string $log_id
	 *
	 * @return string
	 * @throws Exception
	 */
	public static function get_log_status( string $log_id ): string {
		$response = Client::get_instance()->make_request(
			'GET',
			self::SERVICE_ENDPOINT . '/' . $log_id,
		);

		if ( empty( $response ) || is_wp_error( $response ) ) {
			throw new Exception( 'Cannot get response from mail service' );
		}

		if ( empty( $response->status ) ) {
			throw new Exception( 'Service does not return log status' );
		}
		return $response->status;
	}

	/**
	 * Update log, retry on failed
	 *
	 * @param string $log_id
	 * @param numeric $retry
	 *
	 * @return void
	 */
	public static function update_log( string $log_id, $retry = 1 ): void {
		try {
			$response = self::get_log_status( $log_id );
			//TODO: Add logic for webhook if needed (removed unused legacy logic)
		} catch ( Throwable $t ) {
			Logger::error( $t );

			// Retry get log status 3 times
			if ( $retry < 4 ) {
				// Retry after .5/1/1.5 hour
				$time = ( time() + ( 60 * 60 * 0.5 ) ) * $retry;

				// Schedule single event and pass params to it
				wp_schedule_single_event( $time, self::JOB_PREFIX . $log_id, [ $log_id, ++$retry ] );
			}
		}
	}

	/**
	 * Get cron jobs and add actions for it
	 * @return void
	 */
	public static function add_retry_actions() {
		$cron_jobs = wp_get_ready_cron_jobs();

		foreach ( $cron_jobs as $job ) {
			$job_name = array_keys( $job )[0];

			if ( strpos( $job_name, self::JOB_PREFIX ) === 0 ) {
				add_action( $job_name, [ static::class, 'update_log' ], 10, 3 );
			}
		}
	}

	/**
	 * Job for remove expired logs
	 * @return void
	 */
	public static function remove_expired_logs() {
		Log_Entry::delete_expired_logs();
		Status_Entry::delete_expired_statuses();
	}

	/**
	 * Register hook for daily job if no exist
	 * @return void
	 */
	public static function register_daily_job() {
		if ( ! wp_next_scheduled( self::JOB_DAILY_HOOK ) ) {
			wp_schedule_event( strtotime( 'tomorrow midnight' ), 'daily', self::JOB_DAILY_HOOK );
		}
	}

	/**
	 * Register cron job action
	 * @return void
	 */
	public static function add_daily_job() {
		add_action( 'wp', [ static::class, 'register_daily_job' ] );
		add_action( self::JOB_DAILY_HOOK, [ static::class, 'remove_expired_logs' ] );
		register_deactivation_hook( __FILE__, [ static::class, 'clear_remove_expired_daily_event' ] );
	}

	/**
	 * Clear the scheduled event upon deactivation or theme switch
	 */
	public static function clear_remove_expired_daily_event() {
		$timestamp = wp_next_scheduled( self::JOB_DAILY_HOOK );
		if ( $timestamp ) {
			wp_unschedule_event( $timestamp, self::JOB_DAILY_HOOK );
		}
	}
}
