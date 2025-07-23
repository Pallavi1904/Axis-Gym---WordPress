<?php

namespace SiteMailer\Modules\Domain\Rest;

use SiteMailer\Modules\Domain\Classes\Route_Base;
use SiteMailer\Modules\Domain\Classes\Domain_Handler;
use SiteMailer\Modules\Settings\Classes\Settings;
use Throwable;
use WP_Error;
use WP_REST_Response;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class Delete extends Route_Base {
	public string $path = 'delete';

	public function get_methods(): array {
		return [ 'POST' ];
	}

	public function get_name(): string {
		return 'delete';
	}

	/**
	 * @return WP_Error|WP_REST_Response
	 */
	public function POST() {
		try {
			$error = $this->verify_capability();

			if ( $error ) {
				return $error;
			}

			$response = Domain_Handler::delete_domain();

			delete_option( Settings::SENDER_DOMAIN );
			delete_option( Settings::SENDER_EMAIL_PREFIX );
			delete_option( Settings::CUSTOM_DOMAIN_DNS_RECORDS );
			delete_option( Settings::CUSTOM_DOMAIN_VERIFICATION_STATUS );
			delete_option( Settings::CUSTOM_DOMAIN_VERIFICATION_RECORDS );

			return $this->respond_success_json( $response );
		} catch ( Throwable $t ) {
			return $this->respond_error_json( [
				'message' => $t->getMessage(),
				'code' => 'internal_server_error',
			] );
		}
	}
}
