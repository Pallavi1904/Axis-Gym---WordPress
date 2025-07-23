<?php

namespace SiteMailer\Modules\Webhooks\Rest;

use SiteMailer\Modules\Connect\Module as Connect;
use SiteMailer\Modules\Logs\Classes\Log_Handler;
use SiteMailer\Modules\Webhooks\Classes\Route_Base;
use WP_REST_Request;
use WP_REST_Response;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class Update_Log extends Route_Base {
	protected $auth = false;
	public string $path = 'update-log';

	public function get_methods(): array {
		return [ 'PATCH' ];
	}

	public function get_name(): string {
		return 'update-log';
	}

	/**
	 * @param WP_REST_Request $request
	 *
	 * @return WP_REST_Response
	 *
	 * @headers {
	 *     "Content-Type": "application/json"
	 * }
	 *
	 * @body {
	 *     require string $log_id
	 * }
	 */
	public function PATCH( WP_REST_Request $request ): WP_REST_Response {
		$params = $request->get_json_params();

		if ( ! empty( $params['log_id'] ) && Connect::is_connected() ) {
			Log_Handler::update_log( $params['log_id'] );
		}

		return new WP_REST_Response( null, 204 );
	}
}
