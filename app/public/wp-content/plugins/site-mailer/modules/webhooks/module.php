<?php

namespace SiteMailer\Modules\Webhooks;

use SiteMailer\Classes\Module_Base;

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
		return 'webhooks';
	}

	public static function component_list() : array {
		return [];
	}

	public static function routes_list() : array {
		return [
			'Update_Log',
		];
	}

	public function __construct() {
		$this->register_components( self::component_list() );
		$this->register_routes();
	}
}
