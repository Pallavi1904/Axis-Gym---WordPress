<?php

namespace SiteMailer\Modules\Logs\Components;

use SiteMailer\Classes\Logger;
use SiteMailer\Classes\Utils;
use SiteMailer\Modules\Connect\Module as Connect;
use SiteMailer\Modules\Statuses\Database\Status_Entry;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Class Log_Pull
 */
class Log_Pull {
	// phpcs:disable WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
	const PULL_LOG_HOOKS = 'site_mailer_pull_logs';
	const STATUSES_REFRESH_TIMESTAMP = 'site-mailer-statuses_refresh_timestamp';

	public function __construct() {
		if ( ! Connect::is_connected() ) {
			// do nothing if not connected
			return;
		}
		// Hook into WordPress to schedule the event when the plugin is activated
		add_action( 'wp', [ $this, 'schedule_log_pull_twice_daily_event' ] );
		// Hook into plugin deactivation  to clear the scheduled event
		register_deactivation_hook( __FILE__, [ $this, 'clear_log_pull_twice_daily_event' ] );
		// Hook the scheduled event to our custom function
		add_action( self::PULL_LOG_HOOKS, [ static::class, 'pull_logs' ] );
	}

	/**
	 * Check refresh delay
	 * @return bool
	 */
	public static function check_refresh_time(): bool {
		return get_transient( self::STATUSES_REFRESH_TIMESTAMP );
	}


	/**
	 * Schedule the event if it's not already scheduled
	 */
	public function schedule_log_pull_twice_daily_event() {
		if ( ! wp_next_scheduled( self::PULL_LOG_HOOKS ) ) {
			wp_schedule_event( time(), 'twicedaily', self::PULL_LOG_HOOKS );
		}
	}

	/**
	 * Clear the scheduled event upon deactivation or theme switch
	 */
	public function clear_log_pull_twice_daily_event() {
		$timestamp = wp_next_scheduled( self::PULL_LOG_HOOKS );
		if ( $timestamp ) {
			wp_unschedule_event( $timestamp, self::PULL_LOG_HOOKS );
		}
	}

	/**
	 * The function that will be executed twice daily
	 */
	public static function pull_logs() {
		if ( ! Connect::is_connected() || self::check_refresh_time() ) {
			// do nothing if not connected or delay
			return;
		}
		set_transient( self::STATUSES_REFRESH_TIMESTAMP, true, ( 15 * 60 ) );

		$response = Utils::get_api_client()->make_request(
			'GET',
			'log',
		);

		if ( is_wp_error( $response ) ) { // Log error only on is_wp_error, empty response is correct
			Logger::error( 'Cannot get logs response from mail service' );

			return;
		}

		// sort the logs in reverse order so the latest event last
		$response = array_reverse( $response );

		foreach ( $response as $log ) {
			if ( empty( $log->emailId ) || empty( $log->status ) ) {
				continue;
			}
			// Skip pending logs
			if ( 'pending' === $log->status ) {
				continue;
			}

			if ( Status_Entry::validate_status( $log->status ) ) {
				Status_Entry::patch_status( $log->emailId, $log->to, $log->status );
			}

			// Check for opened email status
			if ( 'open' === $log->status ) {
				Status_Entry::set_opened( $log->emailId, $log->to );
			}
		}
	}
}
