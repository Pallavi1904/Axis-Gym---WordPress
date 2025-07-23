<?php

namespace SiteMailer\Modules\Settings\Classes;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class Settings {
	public const SENDER_DOMAIN = 'site_mailer_sender_domain';
	public const SENDER_EMAIL_PREFIX = 'site_mailer_sender_email_prefix';
	public const CUSTOM_DOMAIN_DNS_RECORDS = 'site_mailer_custom_domain_dns_records';
	public const CUSTOM_DOMAIN_VERIFICATION_STATUS = 'site_mailer_verification_status';
	public const CUSTOM_DOMAIN_VERIFICATION_RECORDS = 'site_mailer_verification_records';
	public const KEEP_LOG = 'site_mailer_keep_log';
	public const UNSUBSCRIBE = 'site_mailer_unsubscribe';
	public const UNSUBSCRIBE_ENCRYPTION_DATA = 'site_mailer_unsubscribe_encryption_data';
	public const PLAN_DATA = 'site_mailer_plan_data';
	public const CLOSE_POST_CONNECT_MODAL = 'site_mailer_close_post_connect_modal';
	public const IS_VALID_PLAN_DATA = 'site_mailer_is_valid_plan_data';
	public const CUSTOM_DOMAIN_VERIFICATION_START_TIME = 'site_mailer_custom_domain_verification_start_time';
	public const FROM_NAME = 'site_mailer_from_name';
	public const REPLY_TO_EMAIL = 'site_mailer_reply_to_email';
	public const PLAN_SCOPE = 'site_mailer_plan_scope';

	/**
	 * Returns plugin settings data by option name typecasted to an appropriate data type.
	 *
	 * @param string $option_name
	 * @return mixed
	 */
	public static function get( string $option_name ) {
		$data = get_option( $option_name );

		switch ( $option_name ) {
			case self::SENDER_DOMAIN:
			case self::CUSTOM_DOMAIN_VERIFICATION_RECORDS:
				return json_decode( $data );

			case self::CUSTOM_DOMAIN_VERIFICATION_STATUS:
				if ( ! $data ) {
					return 'not-started';
				}

				return $data;

			case self::PLAN_DATA:
				if ( ! $data ) {
					return [
						'plan'   => [
							'quota' => [
								'allowed' => 0,
								'used'    => 0,
							],
							'name'  => 'None',
						],
						'sender' => [
							'sender_in_used' => '',
						],
						'failed' => true,
					];
				}

				return $data;
			case self::FROM_NAME:
				if ( ! $data ) {
					return 'Site Mailer';
				}

				return $data;

			default:
				return $data;
		}
	}

	/**
	 * Update the settings data by option name.
	 *
	 * @param string $option_name
	 * @param $value
	 *
	 * @return bool
	 */
	public static function set( string $option_name, $value ) : bool {
		return update_option( $option_name, $value, false );
	}
}
