<?php

namespace SiteMailer\Modules\Domain\Rest;

use SiteMailer\Modules\Domain\Classes\Route_Base;
use SiteMailer\Modules\Domain\Classes\Domain_Handler;
use SiteMailer\Modules\Settings\Classes\Settings;
use Throwable;
use WP_Error;
use WP_REST_Request;
use WP_REST_Response;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class Register extends Route_Base {
	public string $path = 'register';

	public function get_methods(): array {
		return [ 'POST' ];
	}

	public function get_name(): string {
		return 'register';
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

			$response = Domain_Handler::register_domain( [
				'domain' => $domain,
				'emailPrefix' => $prefix,
			] );

			update_option( Settings::SENDER_DOMAIN, $domain );
			update_option( Settings::SENDER_EMAIL_PREFIX, $prefix );

			if ( isset( $response->message ) && 'Domain already registered with this site' === $response->message ) {
				update_option( Settings::CUSTOM_DOMAIN_DNS_RECORDS, wp_json_encode( $response->domain ) );

				return $this->respond_success_json( $response->domain );
			} else {
				update_option( Settings::CUSTOM_DOMAIN_DNS_RECORDS, wp_json_encode( $response ) );

				return $this->respond_success_json( $response );
			}
		} catch ( Throwable $t ) {
			return $this->respond_error_json( [
				'message' => $t->getMessage(),
				'code' => 'internal_server_error',
			] );
		}
	}
}
