<?php

namespace SiteMailer\Modules\Statuses;

use SiteMailer\Classes\Module_Base;
use SiteMailer\Modules\Statuses\Database\Statuses_Table;

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
		return 'statuses';
	}

	public static function routes_list() : array {
		return [
			'Get_Stats',
			'Get_Statuses',
		];
	}

	public function __construct() {
		// this make sure the database table is created and or updated when needed
		Statuses_Table::install();

		$this->register_routes();
	}
}
