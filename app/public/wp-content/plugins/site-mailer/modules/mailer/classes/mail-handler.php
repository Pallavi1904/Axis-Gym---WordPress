<?php

namespace SiteMailer\Modules\Mailer\Classes;

use Exception;
use SiteMailer\Classes\Database\Exceptions\Missing_Table_Exception;
use SiteMailer\Classes\Logger;
use SiteMailer\Classes\Services\Client;
use SiteMailer\Modules\Logs\Database\Log_Entry;
use SiteMailer\Modules\Logs\Database\Logs_Table;
use SiteMailer\Modules\Mailer\Components\Rate_Limit_Retry;
use SiteMailer\Modules\Settings\Classes\Settings as SettingsModule;
use SiteMailer\Modules\Settings\Module as Settings;
use SiteMailer\Modules\Statuses\Database\Status_Entry;
use SiteMailer\Modules\Statuses\Database\Statuses_Table;
use Throwable;

/**
 * The class is responsible for the send email to external service
 */
class Mail_Handler {


	const SERVICE_ENDPOINT = 'email-account/send';
	const LOG_STATUSES = [
		'pending' => 'pending',
		'failed' => 'failed',
		'not_sent' => 'not sent',
		'rate_limit' => 'rate limit',
		'not_valid' => 'not valid',
		'unsubscribed' => 'unsubscribed',
	];
	const ERROR_MSG = [
		'rate_limit' => 'Too many requests',
		'not_valid' => 'Email `To` or `message` empty',
		'unsubscribed' => 'All recipients from "To" list are unsubscribed',
		'not_sent' => 'Quota Status Guard Request Failed!: Quota exceeded',
	];

	private array $email;
	private array $initial_email;
	private string $log_id;
	private array $attachments = [];
	private string $source;
	private string $type;
	private array $unsubscribed = [];

	/**
	 * Get data from logs and try to send one more time
	 *
	 * @param array $ids
	 *
	 * @return void
	 * @throws Throwable
	 */
	public static function resend_mails( array $ids ): void {
		$escaped = implode( ',', array_map( function ( $item ) {
			return Logs_Table::db()->prepare( '%s', $item );
		}, $ids ) );
		$where = Logs_Table::table_name() . '.' . Logs_Table::API_ID . ' IN (' . $escaped . ')';
		$logs = Log_Entry::get_logs( $where );
		// TODO: Discuss and add possibility to resend mails as array with one request
		foreach ( $logs as $log ) {
			$log->to = json_decode( $log->to, true );
			$log->headers = json_decode( $log->headers, true );
			$handler = new self( (array) $log, 'Resend', 'Plugin' );
			$handler->send();
		}
	}

	/**
	 * Create and send test mail
	 *
	 * @param string $address
	 *
	 * @return void
	 * @throws Throwable
	 */
	public static function send_test_mail( string $address ): void {
		$current_timestamp = current_time( 'mysql' );
		$url = get_bloginfo( 'url' );
		/* translators: %s is the timestamp */
		$msg = '<!doctype html>
				<html xmlns="http://www.w3.org/1999/xhtml" dir="ltr" lang="en-US">
					<head>
						<title>' . __( 'Site Mailer Email Test', 'site-mailer' ) . '</title>
					</head>
					<body>
						<p style="padding: 4px 0">' . __( 'Congrats, the test email was sent successfully!', 'site-mailer' ) . '</p>
						<p style="padding: 4px 0">' . __( 'Thank you for using Site Mailer. We are here to make sure your emails actually get delivered!', 'site-mailer' ) . '</p>
						<p style="padding: 12px 0">' . __( 'The Site Mailer Team', 'site-mailer' ) . '</p>
						<p style="padding: 4px 0">' . __( 'Sent By:', 'site-mailer' ) . ' ' . $url . '</p>
						<p style="padding: 4px 0">' . __( 'Timestamp:', 'site-mailer' ) . ' ' . $current_timestamp . '</p>
					</body>
				</html>';
		$email = [
			'to' => $address,
			'subject' => __( 'Site Mailer Email Test', 'site-mailer' ),
			'message' => $msg,
			'headers' => 'Content-Type: text/html',
		];
		$handler = new self( $email, 'Test', 'Plugin' );
		$handler->send();
	}

	/**
	 * Get unsubscribe endpoint
	 *
	 * @param $to string
	 *
	 * @return string
	 */
	private function get_unsubscribe_endpoint( string $to ): string {
		$blog_id = get_current_blog_id();
		$data = Settings::get_unsubscribe_encryption_data();
		$email = openssl_encrypt( $to, $data['cipher'], $data['key'], 0, base64_decode( $data['iv'] ) );
		$path = 'site-mailer/v1/suppressions/unsubscribe/' . base64_encode( $email );

		return get_rest_url( $blog_id, $path );
	}

	/**
	 * Add data from settings to email, remove unsubscribed from list of recipients
	 *
	 * @return void
	 * @throws Exception
	 */
	private function prepare_data() {
		$reply_to = Settings::get_sender_reply_email();

		if ( SettingsModule::get( SettingsModule::UNSUBSCRIBE ) ) {
			$unsubscribe = [];
			if ( function_exists( 'openssl_encrypt' ) ) {
				foreach ( $this->email['to'] as $item ) {
					$unsubscribe[ $item ] = [
						'List-Unsubscribe-Post' => 'List-Unsubscribe=One-Click',
						'List-Unsubscribe' => '<mailto:' . $reply_to . '>, <' . $this->get_unsubscribe_endpoint( $item ) . '>',
					];
				}
				$this->email['headers'][] = 'Unsubscribe: ' . wp_json_encode( $unsubscribe );
			} else {
				Logger::error( 'the openssl extension is not installed in the environment' );
			}
		}

		$this->email['to'] = implode( ', ', $this->email['to'] );
		$this->email = array_merge( $this->email, [
			'from_name' => Settings::get_sender_from_name(),
			'reply_to' => $reply_to,
		] );
	}

	/**
	 * get_mail_attachments
	 *
	 * Get file content and path info from tmp file
	 *
	 * TODO add store file if needed
	 */
	private function get_mail_attachments(): void {
		if ( array_key_exists( 'attachments', $this->email ) && ! empty( $this->email['attachments'] ) ) {
			foreach ( $this->email['attachments'] as $attachment ) {
				$file = file_get_contents( $attachment ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents -- Reason: Using file_get_contents for get an file from instance.
				$pathinfo = pathinfo( $attachment );
				$this->attachments[] = [
					'file' => base64_encode( $file ),
					// phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_encode -- Reason: Encoding data for REST call.
					'basename' => $pathinfo['basename'],
					'type' => mime_content_type( $attachment ),
				];
			}
		}
	}

	/**
	 * Check 'To' and 'message' for empty or wrong
	 * @throws Throwable
	 */
	private function check_mail() {
		$data = Mail_Validator::get_instance()->validate( $this->email );
		$this->email = $data['email'];
		$this->unsubscribed = $data['unsubscribed'];
		if ( count( $data['unsubscribed'] ) && empty( $this->email['to'] ) && empty( array_diff( $data['to'], $data['unsubscribed'] ) ) ) {
			$this->error_handler( self::ERROR_MSG['unsubscribed'] );
		}

		if ( empty( $this->email['to'] ) || empty( $this->email['message'] ) ) {
			$this->error_handler( self::ERROR_MSG['not_valid'] );
		}
	}

	/**
	 * Sanitize email body
	 *
	 * @param string $html
	 *
	 * @return string
	 */
	public static function sanitize_html( string $html ): string {
		// Remove JavaScript-style comments, malformed comment endings, and JavaScript protocol URLs
		$html = preg_replace( [
			'/--!>\s*/',                      // Remove `--!>`
			'/\bjavascript\s*:[^"\'>]+/i',     // Remove `javascript:` URLs
			'/\s+on[a-zA-Z]+\s*=\s*["\'][^"\']*["\']/i', // Remove event handlers (onClick, onMouseOver, etc.)
		], '', $html );

		// Remove disallowed tags (script, iframe, object, etc.)
		return preg_replace( '/<\s*(script|iframe|object|embed|form|input)[^>]*>.*?<\/\s*\1\s*>/is', '', $html );
	}


	/**
	 * Create and save log entry
	 *
	 * @param string|null $status
	 *
	 * @return void
	 * @throws Missing_Table_Exception
	 */
	private function write_log( string $status = null ) {
		$keep_log = SettingsModule::get( SettingsModule::KEEP_LOG );
		$datetime_wp = current_time( 'mysql' );
		$mail_list = Mail_Validator::get_instance()->validate_for_log_status( $this->initial_email, $this->unsubscribed );

		foreach ( $mail_list as $email => $item ) {
			$status_item = new Status_Entry( [
				'data' => [
					Statuses_Table::LOG_ID => $this->log_id,
					Statuses_Table::EMAIL => $email,
					Statuses_Table::STATUS => $item['status'] ? $item['status'] : ( $status ?? self::LOG_STATUSES['pending'] ),
					Statuses_Table::TYPE => $item['type'],
					Statuses_Table::CREATED_AT => $datetime_wp,
					Statuses_Table::UPDATED_AT => $datetime_wp,
				],
			] );
			$status_item->create();
		}

		$required = [
			Logs_Table::API_ID => $this->log_id,
			Logs_Table::SUBJECT => self::sanitize_html( $this->email['subject'] ),
			// possible array of strings
			Logs_Table::TO => wp_json_encode( $this->initial_email['to'] ),
			// possible array of strings
			Logs_Table::HEADERS => wp_json_encode( array_key_exists( 'headers', $this->initial_email ) ? $this->initial_email['headers'] : '' ),
			Logs_Table::SOURCE => $this->source,
			Logs_Table::CREATED_AT => $datetime_wp,
			Logs_Table::UPDATED_AT => $datetime_wp,
		];
		$on_keep = $keep_log ? [
			Logs_Table::MESSAGE => self::sanitize_html( $this->email['message'] ),
		] : [];
		$log = new Log_Entry( [ 'data' => array_merge( $required, $on_keep ) ] );
		$log->create();
	}

	/**
	 * Handle send email error
	 *
	 * @param string $error
	 *
	 * @throws Missing_Table_Exception
	 * @throws Exception
	 */
	private function error_handler( string $error ) {
		switch ( $error ) {
			case self::ERROR_MSG['not_sent']:
				$status = self::LOG_STATUSES['not_sent'];
				break;
			case self::ERROR_MSG['rate_limit']:
				$status = self::LOG_STATUSES['rate_limit'];
				Rate_Limit_Retry::schedule_resend_email( $this->log_id, $this->email );
				break;
			case self::ERROR_MSG['not_valid']:
				$status = self::LOG_STATUSES['not_valid'];
				break;
			case self::ERROR_MSG['unsubscribed']:
				$status = self::LOG_STATUSES['unsubscribed'];
				break;
			default:
				$status = self::LOG_STATUSES['failed'];
				break;
		}

		$this->write_log( $status );

		// Don't throw an error on 'rate_limit'
		if ( self::ERROR_MSG['rate_limit'] !== $error ) {
			throw new Exception( esc_html( $error ) );
		}
	}

	/**
	 * send request with mail to the api service
	 *
	 * Send mail to the external service
	 *
	 * @return void
	 * @throws Missing_Table_Exception
	 * @throws Exception
	 */
	public function send() {
		$response = Client::get_instance()->make_request(
			'POST',
			self::SERVICE_ENDPOINT,
			[
				'email' => $this->email,
				'attachments' => $this->attachments,
				'from' => Settings::get_sender_email(),
				'custom_args' => [
					'email_id' => $this->log_id,
					'source' => $this->source,
					'type' => $this->type,
					'status' => self::LOG_STATUSES['pending'],
				],
			],
			[],
			true
		);
		if ( is_wp_error( $response ) ) {
			$error = $response->get_error_message();
			$this->error_handler( $error );
		}

		$this->write_log();
	}

	/**
	 *
	 * @param array $email Array of the `wp_mail()` arguments.
	 * @param string $type Normal|Resend|Test
	 * @param string|null $source
	 *
	 * @throws Throwable
	 */
	public function __construct( array $email, string $type, string $source = null ) {
		$this->log_id = wp_generate_uuid4();
		$this->source = $source ?? Caller_Source::get_caller_source();
		$this->type = $type;
		$this->initial_email = $email;
		$this->email = $email;
		$this->check_mail();
		$this->prepare_data();
		$this->get_mail_attachments();
	}
}
