<?php

namespace SiteMailer\Modules\Mailer;

use SiteMailer\Classes\Module_Base;
use SiteMailer\Modules\Mailer\Classes\Mail_Handler;
use SiteMailer\Modules\Connect\Module as Connect;
use Throwable;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Class Module
 */
class Module extends Module_Base {

	/**
	 * Get module name.
	 * Retrieve the module name.
	 * @access public
	 * @return string Module name.
	 */
	public function get_name(): string {
		return 'mailer';
	}

	public static function component_list(): array {
		return [
			'Rate_Limit_Retry',
		];
	}

	public static function routes_list(): array {
		return [
			'Resend_Mails',
			'Send_Test_Mail',
		];
	}

	/**
	 * send
	 *
	 * @param $sent
	 * @param array $email {
	 *      Array of the `wp_mail()` arguments.
	 *
	 * @type string|string[] $to Array or comma-separated list of email addresses to send message.
	 * @type string $subject Email subject.
	 * @type string $message Message contents.
	 * @type string|string[] $headers Additional headers.
	 * @type string|string[] $attachments Paths to files to attach.
	 *
	 * @return bool
	 * @throws Throwable
	 */
	public function send( $sent, array $email ) {
		// Validate and prepare email to send
		try {
			$handler = new Mail_Handler( $email, 'Normal' );
		} catch ( Throwable $t ) {
			return false;
		}

		// Send request to the external service
		try {
			$handler->send();

			return true;
		} catch ( Throwable $t ) {
			// try to send via default WP function
			return $sent;
		}
	}

	public function __construct() {
		$this->register_components();
		if ( Connect::is_connected() ) {
			add_filter( 'pre_wp_mail', [ $this, 'send' ], 10, 2 );
		}
		$this->register_routes();
	}
}
