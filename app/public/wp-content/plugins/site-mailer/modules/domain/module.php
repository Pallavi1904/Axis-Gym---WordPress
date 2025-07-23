<?php

namespace SiteMailer\Modules\Domain;

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
		return 'domain';
	}

	public static function routes_list() : array {
		return [
			'Register',
			'Verify',
			'Delete',
		];
	}

	public function __construct() {
		$this->register_routes();
	}
}
