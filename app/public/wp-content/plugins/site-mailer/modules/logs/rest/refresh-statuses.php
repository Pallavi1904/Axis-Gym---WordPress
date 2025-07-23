<?php

namespace SiteMailer\Modules\Logs\Rest;

use SiteMailer\Modules\Logs\Classes\Route_Base;
use SiteMailer\Modules\Logs\Components\Log_Pull;
use Throwable;
use WP_Error;
use WP_REST_Response;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}


class Refresh_Statuses extends Route_Base {
	public string $path = 'refresh-statuses';

	public function get_methods(): array {
		return [ 'GET' ];
	}

	public function get_name(): string {
		return 'refresh-statuses';
	}

	/**
	 * Get fresh statuses for log
	 * @return WP_Error|WP_REST_Response
	 */
	public function GET() {
		try {
			$error = $this->verify_capability();

			if ( $error ) {
				return $error;
			}
			Log_Pull::pull_logs();
			return $this->respond_success_json();
		} catch ( Throwable $t ) {
			return $this->respond_error_json( [
				'message' => $t->getMessage(),
				'code' => 'internal_server_error',
			] );
		}
	}
}
