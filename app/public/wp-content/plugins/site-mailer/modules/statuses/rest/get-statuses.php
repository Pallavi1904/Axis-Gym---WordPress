<?php

namespace SiteMailer\Modules\Statuses\Rest;

use SiteMailer\Modules\Statuses\Classes\Route_Base;
use SiteMailer\Modules\Statuses\Database\Status_Entry;
use SiteMailer\Modules\Statuses\Database\Statuses_Table;
use Throwable;
use WP_Error;
use WP_REST_Request;
use WP_REST_Response;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}


class Get_Statuses extends Route_Base {
	public string $path = 'get-statuses';

	public function get_methods(): array {
		return [ 'GET' ];
	}

	public function get_name(): string {
		return 'get-suppressions';
	}

	/**
	 * @param WP_REST_Request $request
	 *
	 * @return WP_Error|WP_REST_Response
	 */
	public function GET( WP_REST_Request $request ) {
		try {
			$error = $this->verify_capability();

			if ( $error ) {
				return $error;
			}

			$params = $request->get_query_params();
			$log_id = sanitize_text_field( $params['log_id'] );
			$page = sanitize_text_field( $params['page'] );
			$request_limit = sanitize_text_field( $params['limit'] );

			$where = [
				[
					'column' => Statuses_Table::LOG_ID,
					'value' => $log_id,
					'operator' => '=',
				],
			];

			//Set offset
			$offset = ( $page - 1 ) * $request_limit;

			// Set limit from 1 to 100
			$limit = max( $request_limit, 1 ) !== 1 ? min( $request_limit, 100 ) : 1;

			// Set order/default order
			$order = [ Statuses_Table::EMAIL => 'ASC' ];

			$suppressions = Status_Entry::get_statuses(
				'*',
				$where,
				$limit,
				$offset,
				'',
				$order,
			);

			$total = Status_Entry::get_statuses_count( $where );

			return $this->respond_success_json( [
				'items'  => $suppressions,
				'total' => $total[0]->count,
			]);
		} catch ( Throwable $t ) {
			return $this->respond_error_json( [
				'message' => $t->getMessage(),
				'code' => 'internal_server_error',
			] );
		}
	}
}
