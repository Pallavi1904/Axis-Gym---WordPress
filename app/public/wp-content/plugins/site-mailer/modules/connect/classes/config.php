<?php

namespace SiteMailer\Modules\Connect\Classes;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Class Config
 */
class Config {
	const APP_NAME = 'site-mailer';
	const APP_PREFIX = 'site_mailer';
	const APP_REST_NAMESPACE = 'site_mailer';
	const BASE_URL = 'https://my.elementor.com/connect';
	const ADMIN_PAGE = 'site-mailer-settings';
	const APP_TYPE = 'app_mailer';
	const SCOPES = 'openid offline_access share_usage_data';
	const STATE_NONCE = 'site_mailer_auth_nonce';
	const CONNECT_MODE = 'site';
}
