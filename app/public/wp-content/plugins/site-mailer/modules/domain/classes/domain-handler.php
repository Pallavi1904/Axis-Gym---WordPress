<?php

namespace SiteMailer\Modules\Domain\Classes;

use Exception;
use SiteMailer\Classes\Services\Client;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * The class is responsible for the registering and
 * unregistering the external domain.
 */
class Domain_Handler {
	const REGISTER_ENDPOINT = 'domain/register';
	const VERIFY_ENDPOINT = 'domain/verify';
	const DELETE_ENDPOINT = 'domain';

	/**
	 * send request to register custom domain to the api service
	 *
	 * @param $data
	 *
	 * @return object
	 * @throws Exception
	 */
	public static function register_domain( $data ) {
		$response = Client::get_instance()->make_request(
			'POST',
			self::REGISTER_ENDPOINT,
			[
				'domain_url'   => $data['domain'],
				'email_prefix' => $data['emailPrefix'],
			],
			[],
			true
		);

		if ( is_wp_error( $response ) ) {
			throw new Exception( esc_html( $response->get_error_message() ) );
		}

		return $response;
	}

	/**
	 * send request to verify custom domain status to the api service
	 *
	 * @param $data
	 *
	 * @return object
	 * @throws Exception
	 */
	public static function verify_domain( $data ): object {
		$response = Client::get_instance()->make_request(
			'POST',
			self::VERIFY_ENDPOINT,
			[
				'domain_url'   => $data['domain'],
				'email_prefix' => $data['email_prefix'],
				'reply_to' => $data['reply_to'],
			],
			[],
			true
		);

		if ( is_wp_error( $response ) ) {
			throw new Exception( esc_html( $response->get_error_message() ) );
		}

		return $response;
	}

	/**
	 * send request to delete custom domain to the api service
	 *
	 * @return object
	 * @throws Exception
	 */
	public static function delete_domain() {
		$response = Client::get_instance()->make_request(
			'DELETE',
			self::DELETE_ENDPOINT,
			[],
			[],
			true
		);

		if ( is_wp_error( $response ) ) {
			throw new Exception( esc_html( $response->get_error_message() ) );
		}

		if ( $response ) {
			$response = [
				'data' => 'Domain deleted successfully',
				'code' => 204,
			];
		}

		return $response;
	}
}
