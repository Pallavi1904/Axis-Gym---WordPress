<?php

namespace SiteMailer\Modules\Suppressions;

use SiteMailer\Classes\Module_Base;
use SiteMailer\Modules\Suppressions\Database\Suppressions_Table;

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
		return 'suppressions';
	}

	public static function routes_list() : array {
		return [
			'Unsubscribe',
			'Get_Suppressions',
			'Delete_Suppressions',
			'Add_Suppression',
		];
	}

	public function __construct() {
		// this make sure the database table is created and or updated when needed
		Suppressions_Table::install();

		$this->register_routes();
	}
}
