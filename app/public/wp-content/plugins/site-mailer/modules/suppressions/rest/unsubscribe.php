<?php

namespace SiteMailer\Modules\Suppressions\Rest;

use SiteMailer\Classes\Database\Exceptions\Missing_Table_Exception;
use SiteMailer\Modules\Settings\Module as Settings;
use SiteMailer\Modules\Suppressions\Classes\Route_Base;
use SiteMailer\Modules\Suppressions\Database\Suppressions_Entry;
use SiteMailer\Modules\Suppressions\Database\Suppressions_Table;
use WP_REST_Request;
use WP_REST_Response;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class Unsubscribe extends Route_Base {
	protected $auth = false;
	public string $path = 'unsubscribe/(?P<email>[a-zA-Z0-9=_-]+)';

	public function get_methods(): array {
		return [ 'GET', 'POST' ];
	}

	public function get_name(): string {
		return 'unsubscribe/(?P<email>[a-zA-Z0-9=_-]+)';
	}

	/**
	 * Empty GET route for mail services
	 * @return WP_REST_Response
	 */
	public function GET(): WP_REST_Response {
		return $this->respond_success_json();
	}

	/**
	 * Add user to suppressions list
	 *
	 * @param WP_REST_Request $request
	 *
	 * @return WP_REST_Response
	 * @throws Missing_Table_Exception
	 */
	public function POST( WP_REST_Request $request ): WP_REST_Response {
		if ( function_exists( 'openssl_encrypt' ) ) {
			$params = $request->get_url_params();

			$data = Settings::get_unsubscribe_encryption_data();
			$email = base64_decode( $params['email'] );
			$decoded_email = openssl_decrypt( $email, $data['cipher'], $data['key'], 0, base64_decode( $data['iv'] ) );
			if ( filter_var( $decoded_email, FILTER_VALIDATE_EMAIL ) ) {
				$new_suppression = new Suppressions_Entry([
					'data' => [
						Suppressions_Table::EMAIL => $decoded_email,
						Suppressions_Table::REASON => 'unsubscribed',
					],
				]);
				$new_suppression->create();
			}
		} else {
			Logger::error( 'the openssl extension is not installed in the environment' );
		}
		return $this->respond_success_json();
	}
}
