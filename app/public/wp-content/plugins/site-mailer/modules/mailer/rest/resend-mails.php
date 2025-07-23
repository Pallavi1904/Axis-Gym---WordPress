<?php

namespace SiteMailer\Modules\Mailer\Rest;

use SiteMailer\Modules\Mailer\Classes\Mail_Handler;
use SiteMailer\Modules\Mailer\Classes\Route_Base;
use SiteMailer\Modules\Logs\Database\Log_Entry;
use Throwable;
use WP_Error;
use WP_REST_Request;
use WP_REST_Response;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}


class Resend_Mails extends Route_Base {
	public string $path = 'resend-mails';

	public function get_methods(): array {
		return [ 'POST' ];
	}

	public function get_name(): string {
		return 'resend-mails';
	}

	/**
	 * @param WP_REST_Request $request
	 *
	 * @return WP_Error|WP_REST_Response
	 *
	 * @query {
	 *     require numeric 0 < $limit < 100
	 *     require numeric $page
	 *     string $orderBy
	 *     string $order
	 * }
	 */
	public function POST( WP_REST_Request $request ) {
		try {
			$error = $this->verify_capability();

			if ( $error ) {
				return $error;
			}

			$ids = $request->get_json_params();
			Mail_Handler::resend_mails( $ids );

			return $this->respond_success_json();
		} catch ( Throwable $t ) {
			return $this->respond_error_json( [
				'message' => $t->getMessage(),
				'code' => 'internal_server_error',
			] );
		}
	}
}
