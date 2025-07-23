<?php

namespace SiteMailer\Modules\Suppressions\Rest;

use SiteMailer\Modules\Suppressions\Classes\Route_Base;
use SiteMailer\Modules\Suppressions\Database\Suppressions_Entry;
use SiteMailer\Modules\Suppressions\Database\Suppressions_Table;
use Throwable;
use WP_Error;
use WP_REST_Request;
use WP_REST_Response;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}


class Add_Suppression extends Route_Base {
	public string $path = 'add-suppression';

	public function get_methods(): array {
		return [ 'POST' ];
	}

	public function get_name(): string {
		return 'add-suppression';
	}

	/**
	 * @param WP_REST_Request $request
	 *
	 * @return WP_Error|WP_REST_Response
	 *
	 */
	public function POST( WP_REST_Request $request ) {
		try {
			$error = $this->verify_capability();

			if ( $error ) {
				return $error;
			}

			$params = $request->get_json_params();
			$email = sanitize_email( $params['email'] );
			if ( filter_var( $email, FILTER_VALIDATE_EMAIL ) ) {
				$suppression = Suppressions_Entry::get_suppressions( '`id`', [
					'email' => $email,
				] );
				if ( $suppression ) {
					return $this->respond_error_json( [
						'message' => __( 'Email already in suppressions list', 'site-mailer' ),
						'code' => 'internal_server_error',
					] );
				}
				$new_suppression = new Suppressions_Entry( [
					'data' => [
						Suppressions_Table::EMAIL => $email,
						Suppressions_Table::REASON => 'manual',
					],
				] );
				$new_suppression->create();
			}

			return $this->respond_success_json();
		} catch ( Throwable $t ) {
			return $this->respond_error_json( [
				'message' => $t->getMessage(),
				'code' => 'internal_server_error',
			] );
		}
	}
}
