<?php

namespace SiteMailer\Modules\Domain\Rest;

use SiteMailer\Modules\Domain\Classes\Route_Base;
use SiteMailer\Modules\Domain\Classes\Domain_Handler;
use SiteMailer\Modules\Settings\Classes\Settings;
use SiteMailer\Modules\Settings\Module as Settings_Module;
use Throwable;
use WP_Error;
use WP_REST_Request;
use WP_REST_Response;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class Verify extends Route_Base {
	public string $path = 'verify';

	public function get_methods(): array {
		return [ 'POST' ];
	}

	public function get_name(): string {
		return 'verify';
	}

	/**
	 * @param WP_REST_Request $request
	 *
	 * @return WP_Error|WP_REST_Response
	 */
	public function POST( WP_REST_Request $request ) {
		try {
			$error = $this->verify_capability();

			if ( $error ) {
				return $error;
			}

			$data = $request->get_json_params();
			$domain = sanitize_text_field( $data['data']['domain'] );
			$prefix = sanitize_text_field( $data['data']['emailPrefix'] );
			$reply_to = Settings_Module::get_sender_reply_email();

			$response = Domain_Handler::verify_domain( [
				'domain' => $domain,
				'email_prefix' => $prefix,
				'reply_to' => $reply_to,
			] );

			if ( $response->valid ) {
				update_option( Settings::CUSTOM_DOMAIN_VERIFICATION_STATUS, 'verified' );
			} else {
				update_option( Settings::CUSTOM_DOMAIN_VERIFICATION_STATUS, 'failed' );
			}

			update_option( Settings::CUSTOM_DOMAIN_VERIFICATION_RECORDS, wp_json_encode( $response ) );

			return $this->respond_success_json( $response );

		} catch ( Throwable $t ) {
			return $this->respond_error_json( [
				'message' => $t->getMessage(),
				'code' => 'internal_server_error',
			] );
		}
	}
}
