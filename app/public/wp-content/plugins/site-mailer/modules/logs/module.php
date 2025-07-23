<?php

namespace SiteMailer\Modules\Logs;

use SiteMailer\Classes\Module_Base;
use SiteMailer\Modules\Logs\Classes\Log_Handler;
use SiteMailer\Modules\Logs\Database\Logs_Table;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Class Module
 */
class Module extends Module_Base {

	/**
	 * Get module name.
	 * Retrieve the module name.
	 * @access public
	 * @return string Module name.
	 */
	public function get_name(): string {
		return 'logs';
	}

	public static function component_list(): array {
		return [
			'Log_Pull',
		];
	}


	public static function routes_list() : array {
		return [
			'Get_Logs',
			'Delete_Logs',
			'Refresh_Statuses',
		];
	}

	public function __construct() {
		$this->register_components();

		// this make sure the database table is created and or updated when needed
		Logs_Table::install();

		// Add actions for retry update logs
		Log_Handler::add_retry_actions();

		// Add cron daily cron jobs for remove expired logs
		Log_Handler::add_daily_job();

		$this->register_routes();
	}
}
