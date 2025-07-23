<?php

namespace SiteMailer\Modules\Mailer\Classes;

use SiteMailer\Modules\Suppressions\Classes\Unsubscribe_List;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * The class is responsible for email validation
 */
class Mail_Validator {
	public static ?Mail_Validator $instance = null;

	/**
	 * get_instance
	 * @return Mail_Validator |null
	 */
	public static function get_instance(): ?Mail_Validator {
		if ( ! self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Validate email object
	 *
	 * @param array $email Array of the `wp_mail()` arguments.
	 *
	 * @return array
	 */
	public function validate( array $email ): array {
		$headers = array_key_exists( 'headers', $email ) ? $this->split_headers( $email['headers'] ) : [];
		$to = $this->build_mail_list( $email['to'] );
		$cc = array_key_exists( 'cc', $headers ) ? $this->build_mail_list( $headers['cc'] ) : [];
		$bcc = array_key_exists( 'bcc', $headers ) ? $this->build_mail_list( $headers['bcc'] ) : [];

		$unsubscribed = ! empty( $to ) ? Unsubscribe_List::get_instance()->get_unsubscribed( $to, $cc, $bcc ) : [];

		$email['to'] = array_diff( $to, $unsubscribed );
		$headers['cc'] = implode( ', ', array_diff( $cc, $unsubscribed ) );
		$headers['bcc'] = implode( ', ', array_diff( $bcc, $unsubscribed ) );

		$merged_headers = [];

		foreach ( $headers as $key => $value ) {
			if ( $value ) {
				$merged_headers[] = $key . ': ' . $value;
			}
		}

		$email['headers'] = $merged_headers;

		return [
			'email' => $email,
			'to' => $to,
			'cc' => $cc,
			'bcc' => $bcc,
			'unsubscribed' => $unsubscribed,
		];
	}

	/**
	 * Validate initial email object for build log statuses list
	 *
	 * @param array $email Array of the `wp_mail()` arguments.
	 * @param array $unsubscribed Array of unsubscribed recipients.
	 *
	 * @return array $email Array of the `wp_mail()` arguments.
	 */
	public function validate_for_log_status( array $email, array $unsubscribed ): array {
		$headers = array_key_exists( 'headers', $email ) ? $this->split_headers( $email['headers'] ) : [];
		$to = $this->build_email_status_list( $email['to'], 'to', $unsubscribed );
		$cc = array_key_exists( 'cc', $headers ) ? $this->build_email_status_list( $headers['cc'], 'cc', $unsubscribed ) : [];
		$bcc = array_key_exists( 'bcc', $headers ) ? $this->build_email_status_list( $headers['bcc'], 'bcc', $unsubscribed ) : [];

		return array_merge( $bcc, $cc, $to );
	}

	/**
	 * Check if headers is an object
	 *
	 * @param mixed $value
	 *
	 * @return bool
	 */
	public function is_object( $value ): bool {
		return is_array( $value ) && ! empty( $value ) && array_keys( $value ) !== range( 0, count( $value ) - 1 );
	}

	/**
	 * Get header value
	 *
	 * @param array $headers
	 *
	 * @return array
	 */
	public function get_properties_value( array $headers ): array {
		$object_headers = [];
		foreach ( $headers as $key => $value ) {
			$item = explode( ': ', $value );
			if ( isset( $item[1] ) ) {
				$object_headers[ strtolower( $key ) ] = $item[1];
			} elseif ( strtolower( $item[0] ) !== strtolower( $key ) ) {
				$object_headers[ strtolower( $key ) ] = $item[0];
			}
		}

		return $object_headers;
	}

	/**
	 * Split headers from string to key-value array
	 *
	 * @param string|array $headers
	 *
	 * @return array
	 */
	public function split_headers( $headers ): array {
		if ( $this->is_object( $headers ) ) {
			return $this->get_properties_value( $headers );
		}

		$object_headers = [];
		$separate_lines = is_array( $headers ) ? $headers : preg_split( '/\r?\n|\r|\n/', $headers );
		if ( $separate_lines ) {
			foreach ( $separate_lines as $header ) {
				$item = explode( ': ', $header );
				if ( ! empty( $item[0] ) && ! empty( $item[1] ) ) {
					$object_headers[ strtolower( $item[0] ) ] = $item[1];
				}
			}
		}

		return $object_headers;
	}

	/**
	 * Build mail list from comma-separated string or array
	 *
	 * @param string|array|null $list
	 *
	 * @return array
	 */
	public function build_mail_list( $list = null ): array {
		$list_values = is_object( $list ) ? get_object_vars( $list ) : $list;
		$split_list = is_array( $list_values ) ? $list_values : explode( ',', $list_values );
		$mail_list = [];

		if ( $split_list ) {
			foreach ( $split_list as $item ) {
				$pattern = '/^[a-zA-Z0-9 ._\-]+ <(.+)>$/';
				$trimmed = trim( $item );
				// Check for 'Some Email <test@test.com>' format
				$formated = preg_match( $pattern, $trimmed, $matched ) && filter_var( $matched[1], FILTER_VALIDATE_EMAIL );
				if ( $formated || filter_var( $trimmed, FILTER_VALIDATE_EMAIL ) ) {
					$mail_list[] = $formated ? $matched[1] : $trimmed;
				}
			}
		}

		return $mail_list;
	}

	/**
	 * Build mail list from comma-separated string or array
	 *
	 * @param string|array $list
	 * @param string $type
	 * @param array $unsubscribed *
	 *
	 * @return array
	 */
	public function build_email_status_list( $list, string $type, array $unsubscribed = [] ): array {
		$split_list = is_array( $list ) ? $list : explode( ',', $list );
		$mail_list = [];

		if ( $split_list ) {
			foreach ( $split_list as $item ) {
				$pattern = '/^[a-zA-Z0-9 ._\-]+ <(.+)>$/';
				$trimmed = trim( $item );
				// Check for 'Some Email <test@test.com>' format
				$formated = preg_match( $pattern, $trimmed, $matched ) && filter_var( $matched[1], FILTER_VALIDATE_EMAIL );
				if ( ! ( $formated || filter_var( $trimmed, FILTER_VALIDATE_EMAIL ) ) ) {
					$status = Mail_Handler::LOG_STATUSES['not_valid'];
				} elseif ( in_array( $trimmed, $unsubscribed, true ) ) {
					$status = Mail_Handler::LOG_STATUSES['unsubscribed'];
				} else {
					$status = false;
				}

				$mail_list[ $trimmed ] = [
					'status' => $status,
					'type' => $type,
				];
			}
		}

		return $mail_list;
	}
}
