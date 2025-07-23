<?php

namespace SiteMailer\Modules\WhatsNew\Components;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

use Elementor\WPNotificationsPackage\V120\Notifications as Notifications_SDK;

class Notificator {
	private ?Notifications_SDK $notificator = null;

	public function get_notifications_by_conditions( $force_request = false ) {
		return $this->notificator->get_notifications_by_conditions( $force_request );
	}

	public function __construct() {
		require_once SITE_MAILER_PATH . '/vendor/autoload.php';

		$this->notificator = new Notifications_SDK([
			'app_name' => 'site-mailer',
			'app_version' => SITE_MAILER_VERSION,
			'short_app_name' => 'esm',
			'app_data' => [
				'plugin_basename' => plugin_basename( SITE_MAILER_FILE ),
			],
		]);
	}
}
