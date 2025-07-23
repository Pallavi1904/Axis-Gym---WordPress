<?php

namespace SiteMailer\Modules\Reviews\Classes;

use Exception;
use SiteMailer\Classes\Logger;
use SiteMailer\Classes\Services\Client;
use SiteMailer\Modules\Logs\Database\Log_Entry;
use SiteMailer\Modules\Statuses\Database\Status_Entry;
use Throwable;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Feedback_Handler
 *
 * Class to post feedback
 */
class Feedback_Handler {

	const SERVICE_ENDPOINT = 'reviews';

	/**
	 * Send request to the service to submit the feedback.
	 *
	 * @param $params
	 *
	 * @return string
	 * @throws Exception
	 */
	public static function post_feedback( $params ) {
		$response = Client::get_instance()->make_request(
			'POST',
			self::SERVICE_ENDPOINT,
			$params
		);

		if ( empty( $response ) || is_wp_error( $response ) ) {
			throw new Exception( 'Failed to add the feedback.' );
		}

		return $response;
	}
}
