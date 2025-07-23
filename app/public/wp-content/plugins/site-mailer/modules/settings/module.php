<?php

namespace SiteMailer\Modules\Settings;

use Exception;
use SiteMailer\Classes\Logger;
use SiteMailer\Classes\Module_Base;
use SiteMailer\Classes\Utils;
use SiteMailer\Modules\Connect\Classes\Config;
use SiteMailer\Modules\Connect\Classes\Data;
use SiteMailer\Modules\Connect\Classes\Utils as Connect_Utils;
use SiteMailer\Modules\Connect\Module as Connect;
use SiteMailer\Modules\Logs\Components\Log_Pull;
use SiteMailer\Modules\Settings\Banners\Sale_Banner;
use SiteMailer\Modules\Settings\Banners\Birthday_Banner;
use SiteMailer\Modules\Settings\Classes\Settings;
use Throwable;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Class Module
 */
class Module extends Module_Base {


	const SETTING_PREFIX = 'site_mailer_';
	const SETTING_GROUP = 'site_mailer_settings';
	const SETTING_BASE_SLUG = 'site-mailer-settings';
	const SETTING_CAPABILITY = 'manage_options';

	/**
	 * Get module name.
	 * Retrieve the module name.
	 *
	 * @access public
	 * @return string Module name.
	 */
	public function get_name(): string {
		return 'settings';
	}

	public static function component_list(): array {
		return [
			'Settings_Pointer',
		];
	}

	public static function routes_list(): array {
		return [
			'Get_Settings',
		];
	}

	public function render_top_bar() {
		?>
		<div id="site-mailer-app-top-bar"></div>
		<?php
	}

	public function render_app() {
		?>
		<?php Sale_Banner::get_banner( 'https://go.elementor.com/sm-panel-wp-dash-upgrade-panel/' ); ?>
		<?php Birthday_Banner::get_banner( 'https://go.elementor.com/sm-b-day-banner' ); ?>
		<!-- The hack required to wrap WP notifications -->
		<div class="wrap">
			<h1 style="display: none;" role="presentation"></h1>
		</div>

		<div id="site-mailer-app"></div>
		<?php
	}

	public function register_page() {
		add_submenu_page(
			'options-general.php',
			__( 'Site Mailer', 'site-mailer' ),
			__( 'Site Mailer', 'site-mailer' ),
			self::SETTING_CAPABILITY,
			self::SETTING_BASE_SLUG,
			[ $this, 'render_app' ],
			20
		);
	}

	/**
	 * @throws Throwable
	 */
	public function maybe_add_url_mismatch_notice() {
		if ( ! Utils::is_plugin_page() || Connect_Utils::is_valid_home_url() ) {
			return;
		}

		?>
		<div class="notice notice-error notice site-mailer__notice site-mailer__notice--error">
			<p>
				<b>
					<?php esc_html_e(
						'Your license key does not match your current domain, causing a mismatch.',
						'site-mailer'
					); ?>
				</b>

				<span>
					<?php esc_html_e(
						'This is most likely due to a change in the domain URL of your site (including HTTP/SSL migration).',
						'site-mailer'
					); ?>

					<button type="button"
							onclick="document.dispatchEvent( new Event( 'site-mailer/auth/url-mismatch-modal/open' ) );">
						<?php esc_html_e(
							'Fix mismatched URL',
							'site-mailer'
						); ?>
					</button>
				</span>
			</p>
		</div>
		<?php
	}

	/**
	 * Enqueue Scripts and Styles
	 */
	public function enqueue_scripts( $hook ) {
		if ( 'settings_page_site-mailer-settings' !== $hook ) {
			return;
		}

		wp_enqueue_style(
			'site-mailer-admin-fonts',
			'https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap',
			[],
			SITE_MAILER_VERSION
		);

		Utils\Assets::enqueue_app_assets( 'admin' );

		wp_localize_script(
			'admin',
			'siteMailerSettingsData',
			[
				'wpRestNonce' => wp_create_nonce( 'wp_rest' ),
				'pluginEnv' => self::get_plugin_env(),
				'planData' => Settings::get( Settings::PLAN_DATA ),
				'planScope' => Settings::get( Settings::PLAN_SCOPE ),
			]
		);
	}

	/**
	 * Get Mixpanel project Token
	 * @return string
	 */
	private static function get_plugin_env() : string {
		return apply_filters( 'site_mailer_plugin_env', 'production' );
	}

	/**
	 * @throws Exception
	 */
	public function on_connect() {
		if ( ! Connect::is_connected() ) {
			return;
		}

		$register_response = Utils::get_api_client()->make_request(
			'POST',
			'site/register'
		);

		if ( $register_response && ! is_wp_error( $register_response ) ) {
			Data::set_subscription_id( $register_response->id );

			$response = Utils::get_api_client()->make_request(
				'POST',
				'site/info'
			);
		}
		if ( ! is_wp_error( $response ) ) {
			update_option( self::SETTING_PREFIX . 'plan_data', $response );
			update_option( Settings::IS_VALID_PLAN_DATA, true );

			if ( isset( $response->scopes ) ) {
				Settings::set( Settings::PLAN_SCOPE, $response->scopes );
			}
		} else {
			Logger::error( esc_html( $response->get_error_message() ) );
			update_option( Settings::IS_VALID_PLAN_DATA, false );
		}
	}

	/**
	 * Register settings.
	 *
	 * Register settings for the plugin.
	 *
	 * @return void
	 * @throws Throwable
	 */
	public function register_settings() {
		$settings = [
			'keep_log' => [
				'type' => 'boolean',
			],
			'unsubscribe' => [
				'type' => 'boolean',
			],
			'close_post_connect_modal' => [
				'type' => 'boolean',
				'description' => _x( 'Site Mailer Close Post Connect Modal', 'Descrption for API usage. no need to translate', 'site-mailer' ),
			],
			'from_name' => [
				'type' => 'string',
				'description' => _x( 'Site Mailer From Email', 'Descrption for API usage. no need to translate', 'site-mailer' ),
			],
			'reply_to_email' => [
				'type' => 'string',
				'description' => _x( 'Site Mailer Reply Email', 'Descrption for API usage. no need to translate', 'site-mailer' ),
			],
			'sender_domain' => [
				'type' => 'string',
				'description' => _x( 'Site Mailer Sender Domain', 'Descrption for API usage. no need to translate', 'site-mailer' ),
			],
			'sender_email_prefix' => [
				'type' => 'string',
				'description' => _x( 'Site Mailer Sender Mail Prefix', 'Descrption for API usage. no need to translate', 'site-mailer' ),
			],
			'custom_domain_dns_records' => [
				'type' => 'string',
				'description' => _x( 'Site Mailer custom domain DNS records', 'Descrption for API usage. no need to translate', 'site-mailer' ),
			],
			'verification_started' => [
				'type' => 'boolean',
			],
			'registration_started' => [
				'type' => 'boolean',
			],
			'confirm_domain_settings_opened' => [
				'type' => 'boolean',
			],
			'confirm_dns_settings_added' => [
				'type' => 'boolean',
			],
			'active_step' => [
				'type' => 'boolean',
			],
			'custom_domain_verified' => [
				'type' => 'boolean',
			],
			'custom_domain_verified_alert' => [
				'type' => 'boolean',
			],
			'custom_domain_verification_start_time' => [
				'type' => 'string',
			],
			'is_valid_plan_data' => [
				'type' => 'boolean',
			],
			'hide_logs_status_refresh_infotip' => [
				'type' => 'boolean',
				'show_in_rest' => true,
			],
			'hide_walkthrough_modal' => [
				'type' => 'boolean',
			],
		];

		foreach ( $settings as $setting => $args ) {
			if ( ! isset( $args['show_in_rest'] ) ) {
				$args['show_in_rest'] = true;
			}
			register_setting( 'options', self::SETTING_PREFIX . $setting, $args );
		}
	}

	/**
	 * Get sender email
	 *
	 * @return string
	 */
	public static function get_sender_email(): string {
		$settings = Settings::get( Settings::PLAN_DATA );
		$sender_in_used = Settings::get( Settings::SENDER_EMAIL_PREFIX );
		$sender_mail = '';
		$domain_verified = 'verified' === Settings::get( Settings::CUSTOM_DOMAIN_VERIFICATION_STATUS );

		if ( ! empty( $settings ) && ! is_wp_error( $settings ) ) {
			if ( ! empty( $settings->sender->sender_in_used ) ) {
				$sender_mail = $settings->sender->sender_in_used;

				$sender = explode( '@', $sender_mail );
				if ( $domain_verified && ! empty( $sender_in_used ) && $sender[0] !== $sender_in_used ) {
					$sender_mail = $sender_in_used . '@' . $sender[1];
				}
			}
		}

		return $sender_mail;
	}

	/**
	 * Get sender from name
	 *
	 * @return string
	 */
	public static function get_sender_from_name(): string {
		return Settings::get( Settings::FROM_NAME );
	}

	/**
	 * Get sender reply to email
	 *
	 * @return string
	 */
	public static function get_sender_reply_email(): string {
		$email = Settings::get( Settings::REPLY_TO_EMAIL );

		if ( ( ! empty( $email ) && ! is_email( $email ) ) || empty( $email ) ) {
			return get_bloginfo( 'admin_email' );
		}

		return $email;
	}

	/**
	 * Get unsubscribe encryption key
	 *
	 * @return array|bool
	 */
	public static function get_unsubscribe_encryption_data(): array {
		if ( ! function_exists( 'openssl_encrypt' ) ) {
			Logger::error( 'the openssl extension is not installed in the environment' );

			return false;
		}
		$data = Settings::get( Settings::UNSUBSCRIBE_ENCRYPTION_DATA );
		if ( ! $data ) {
			$cipher = 'aes128';
			$iv_length = openssl_cipher_iv_length( $cipher );
			$iv = openssl_random_pseudo_bytes( $iv_length );
			$data = [
				'key' => wp_generate_uuid4(),
				'cipher' => $cipher,
				'iv' => base64_encode( $iv ),
			];
			update_option( Settings::UNSUBSCRIBE_ENCRYPTION_DATA, $data );
		}

		return $data;
	}

	/**
	 * Refresh plan data when the settings page is loaded.
	 *
	 * @return void
	 * @throws Throwable
	 */
	public static function refresh_plan_data() {
		$response = Utils::get_api_client()->make_request(
			'POST',
			'site/info'
		);

		if ( ! is_wp_error( $response ) ) {
			update_option( self::SETTING_PREFIX . 'plan_data', $response );
			update_option( Settings::IS_VALID_PLAN_DATA, true );
		} else {
			Logger::error( esc_html( $response->get_error_message() ) );
			update_option( Settings::IS_VALID_PLAN_DATA, false );
		}
	}

	public static function clear_settings_cache() {
		wp_cache_delete( Settings::KEEP_LOG, 'option' );
		wp_cache_delete( Settings::UNSUBSCRIBE, 'option' );
		wp_cache_delete( Settings::CLOSE_POST_CONNECT_MODAL, 'option' );
		wp_cache_delete( Settings::PLAN_DATA, 'option' );
		wp_cache_delete( Settings::SENDER_EMAIL_PREFIX, 'option' );
		wp_cache_delete( Settings::FROM_NAME, 'option' );
		wp_cache_delete( Settings::REPLY_TO_EMAIL, 'option' );
		wp_cache_delete( Settings::CUSTOM_DOMAIN_DNS_RECORDS, 'option' );
		wp_cache_delete( Settings::CUSTOM_DOMAIN_VERIFICATION_STATUS, 'option' );
		wp_cache_delete( Settings::CUSTOM_DOMAIN_VERIFICATION_RECORDS, 'option' );
		wp_cache_delete( Settings::CUSTOM_DOMAIN_VERIFICATION_START_TIME, 'option' );
		wp_cache_delete( Config::APP_PREFIX . Data::SUBSCRIPTION_ID, 'option' );
		wp_cache_delete( Config::APP_PREFIX . Data::ACCESS_TOKEN, 'option' );
		wp_cache_delete( Config::APP_PREFIX . Data::CLIENT_ID, 'option' );
		wp_cache_delete( Config::APP_PREFIX . Data::HOME_URL, 'option' );
		wp_cache_delete( Config::APP_PREFIX . Data::OPTION_OWNER_USER_ID, 'option' );
		wp_cache_delete( Settings::IS_VALID_PLAN_DATA, 'option' );
	}

	/**
	 * Get all plugin settings data
	 * @return array
	 * @throws Throwable
	 */
	public static function get_plugin_settings(): array {
		self::refresh_plan_data();
		self::clear_settings_cache();

		return [
			'isConnected' => Connect::is_connected(),
			'isUrlMismatch' => ! Connect_Utils::is_valid_home_url(),
			'subscriptionId' => Data::get_subscription_id(),
			'keepLog' => Settings::get( Settings::KEEP_LOG ),
			'unsubscribe' => Settings::get( Settings::UNSUBSCRIBE ),
			'isDevelopment' => defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG,
			'siteUrl' => wp_parse_url( get_site_url(), PHP_URL_HOST ),
			'closePostConnectModal' => Settings::get( Settings::CLOSE_POST_CONNECT_MODAL ),
			'senderEmail' => self::get_sender_email() ?? '',
			'fromName' => self::get_sender_from_name(),
			'replyToEmail' => self::get_sender_reply_email(),
			'settings' => Settings::get( Settings::PLAN_DATA ),
			'customDomainDNSRecords' => Settings::get( Settings::CUSTOM_DOMAIN_DNS_RECORDS ),
			'verificationStatus' => Settings::get( Settings::CUSTOM_DOMAIN_VERIFICATION_STATUS ),
			'verificationRecords' => Settings::get( Settings::CUSTOM_DOMAIN_VERIFICATION_RECORDS ),
			'verificationStartTime' => Settings::get( Settings::CUSTOM_DOMAIN_VERIFICATION_START_TIME ),
			//TODO: check use of this
			'isValidPlanData' => Settings::get( Settings::IS_VALID_PLAN_DATA ),
			'isRTL' => is_rtl(),
			'lastLogRefresh' => Log_Pull::check_refresh_time(),
		];
	}

	public function __construct() {
		$this->register_routes();
		$this->register_components( self::component_list() );

		add_action( 'current_screen', function () {
			add_action( 'admin_notices', [ $this, 'maybe_add_url_mismatch_notice' ] );
		} );

		add_action( 'admin_menu', [ $this, 'register_page' ] );
		add_action( 'in_admin_header', [ $this, 'render_top_bar' ] );
		add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_scripts' ] );
		add_action( 'on_connect_' . Config::APP_PREFIX . '_connected', [ $this, 'on_connect' ] );
		add_action( 'rest_api_init', [ $this, 'register_settings' ] );
	}
}
