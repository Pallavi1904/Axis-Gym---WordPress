<?php

namespace SiteMailer\Modules\Statuses\Rest;

use SiteMailer\Modules\Logs\Database\Log_Entry;
use SiteMailer\Modules\Statuses\Classes\Route_Base;
use SiteMailer\Modules\Statuses\Database\Status_Entry;
use Throwable;
use WP_Error;
use WP_REST_Request;
use WP_REST_Response;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class Get_Stats extends Route_Base {
	public string $path = 'get-stats';

	public function get_methods(): array {
		return [ 'GET' ];
	}

	public function get_name(): string {
		return 'get-stats';
	}

	/**
	 * @param WP_REST_Request $request
	 *
	 * @return WP_Error|WP_REST_Response
	 *
	 * @query {
	 *     Date $period
	 * }
	 */
	public function GET( WP_REST_Request $request ) {
		try {
			$error = $this->verify_capability();

			if ( $error ) {
				return $error;
			}

			$params = $request->get_query_params();
			$period = sanitize_text_field( $params['period'] );

			// Add period
			$where = $period ? [
				[
					'column' => 'created_at',
					'value' => $period,
					'operator' => '>',
					'relation_after' => 'AND',
				],
				[
					'column' => 'status',
					'value' => 'not sent',
					'operator' => '<>',
				],
			] : '1';

			$stats = Status_Entry::get_stats( $where );

			/** @deprecated Will be deleted in a future */
			$log_stats = Log_Entry::get_logs_stats( $where );
			$merged = array_merge_recursive( (array) $stats[0], (array) $log_stats[0] );

			$result = array_map( function ( $item ) {
				return is_array( $item ) ? array_sum( $item ) : $item;
			}, $merged );

			return $this->respond_success_json( $result );
		} catch ( Throwable $t ) {
			return $this->respond_error_json( [
				'message' => $t->getMessage(),
				'code' => 'internal_server_error',
			] );
		}
	}
}
