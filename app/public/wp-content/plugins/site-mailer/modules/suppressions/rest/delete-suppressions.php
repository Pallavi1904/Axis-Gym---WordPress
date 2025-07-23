<?php

namespace SiteMailer\Modules\Suppressions\Rest;

use SiteMailer\Modules\Suppressions\Classes\Route_Base;
use SiteMailer\Modules\Suppressions\Database\Suppressions_Entry;
use Throwable;
use WP_Error;
use WP_REST_Request;
use WP_REST_Response;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}


class Delete_Suppressions extends Route_Base {
	public string $path = 'delete-suppressions';

	public function get_methods(): array {
		return [ 'DELETE' ];
	}

	public function get_name(): string {
		return 'delete-suppressions';
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
			Suppressions_Entry::delete_suppressions( $ids );

			return $this->respond_success_json();
		} catch ( Throwable $t ) {
			return $this->respond_error_json( [
				'message' => $t->getMessage(),
				'code' => 'internal_server_error',
			] );
		}
	}
}
