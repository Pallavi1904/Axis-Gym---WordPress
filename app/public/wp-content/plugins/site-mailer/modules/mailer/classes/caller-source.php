<?php

namespace SiteMailer\Modules\Mailer\Classes;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}


class Caller_Source {

	/**
	 * get_caller_source
	 *
	 * Method to get source info where wp-mail function was called
	 *
	 * @return string
	 */
	public static function get_caller_source(): string {
		$caller_source = '';

		$backtrace_history = debug_backtrace( DEBUG_BACKTRACE_IGNORE_ARGS );

		foreach ( $backtrace_history as $call ) {
			if ( ! empty( $call['function'] ) && 'wp_mail' === $call['function'] && ! empty( $call['file'] ) ) {
				$caller_source = self::get_caller_source_by_file( $call['file'] );

				break;
			}
		}

		return $caller_source;
	}

	/**
	 * get_caller_source_by_file
	 *
	 * Run existing wp function for getting source
	 * @param string $file
	 *
	 * @return string
	 */
	private static function get_caller_source_by_file( string $file ): string {
		$methods = [
			'get_plugin_name_by_file',
			'get_mu_plugin_name_by_file',
			'get_theme_name_by_file',
			'get_wp_core_name_by_file',
		];

		foreach ( $methods as $method ) {
			$caller_source = self::$method( $file );
			if ( ! empty( $caller_source ) ) {
				return $caller_source;
			}
		}

		return esc_html__( 'Unknown', 'site-mailer' );
	}

	/**
	 * get_plugin_name_by_file
	 *
	 * Check if it's a plugin was call wp-mail
	 * @param string $file
	 *
	 * @return string
	 */
	private static function get_plugin_name_by_file( string $file ): string {
		if ( ! function_exists( 'get_plugin_data' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}
		$plugin_data = get_plugin_data( $file );

		return $plugin_data['Name'];
	}

	/**
	 * get_mu_plugin_name_by_file
	 *
	 * Check if it's a mu-plugin was call wp-mail
	 * @param string $file
	 *
	 * @return string
	 */
	private static function get_mu_plugin_name_by_file( string $file ): string {
		if ( ! function_exists( 'get_mu_plugins' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}

		$mu_plugins = get_mu_plugins();
		$plugin_data = $mu_plugins[ plugin_basename( $file ) ] ?? [];

		return $plugin_data['Name'] ?? '';
	}

	/**
	 * get_theme_name_by_file
	 *
	 * Check if it's a theme was call wp-mail
	 * @param string $file
	 *
	 * @return string
	 */
	private static function get_theme_name_by_file( string $file ): string {
		$theme_data = wp_get_theme();

		if ( $theme_data->exists() && $theme_data->get_stylesheet_directory() === dirname( $file ) ) {
			return $theme_data->get( 'Name' );
		}

		return '';
	}

	/**
	 * get_wp_core_name_by_file
	 *
	 * Check if wp-mail was called from wp core
	 * @param string $file
	 *
	 * @return string
	 */
	private static function get_wp_core_name_by_file( string $file ): string {
		$core_directories = [
			ABSPATH . WPINC,
			ABSPATH . 'wp-admin',
		];

		foreach ( $core_directories as $core_directory ) {
			if ( 0 === strpos( $file, $core_directory ) ) {
				return esc_html__( 'Core', 'site-mailer' );
			}
		}

		return '';
	}
}
