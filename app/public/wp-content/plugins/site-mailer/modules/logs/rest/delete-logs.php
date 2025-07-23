<?php

namespace SiteMailer\Modules\Logs\Rest;

use SiteMailer\Modules\Logs\Classes\Route_Base;
use SiteMailer\Modules\Logs\Database\Log_Entry;
use SiteMailer\Modules\Statuses\Database\Status_Entry;
use Throwable;
use WP_Error;
use WP_REST_Request;
use WP_REST_Response;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}


class Delete_Logs extends Route_Base {
	public string $path = 'delete-logs';

	public function get_methods(): array {
		return [ 'DELETE' ];
	}

	public function get_name(): string {
		return 'delete-logs';
	}

	/**
	 * @param WP_REST_Request $request
	 *
	 * @return WP_Error|WP_REST_Response
	 *
	 */
	public function DELETE( WP_REST_Request $request ) {
		try {
			$error = $this->verify_capability();

			if ( $error ) {
				return $error;
			}

			$ids = $request->get_json_params();

			Log_Entry::delete_logs( $ids );
			Status_Entry::delete_statuses( $ids );

			return $this->respond_success_json();
		} catch ( Throwable $t ) {
			return $this->respond_error_json( [
				'message' => $t->getMessage(),
				'code' => 'internal_server_error',
			] );
		}
	}
}
