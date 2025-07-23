<?php
/**
 * Plugin Name:         Ocean Social Sharing
 * Plugin URI:          https://oceanwp.org/extension/ocean-social-sharing/
 * Description:         A simple plugin to add social share buttons to your posts.
 * Version:             2.2.1
 * Author:              OceanWP
 * Author URI:          https://oceanwp.org/
 * Requires at least:   5.6
 * Tested up to:        6.8
 *
 * Text Domain: ocean-social-sharing
 * Domain Path: /languages
 *
 * @package  Ocean_Social_Sharing
 * @category Core
 * @author   OceanWP
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Returns the main instance of Ocean_Social_Sharing to prevent the need to use globals.
 *
 * @since  1.0.0
 * @return object Ocean_Social_Sharing
 */
function Ocean_Social_Sharing()
{
	return Ocean_Social_Sharing::instance();
} // End Ocean_Social_Sharing()

Ocean_Social_Sharing();

/**
 * Main Ocean_Social_Sharing Class
 *
 * @class   Ocean_Social_Sharing
 * @version 1.0.0
 * @since   1.0.0
 * @package Ocean_Social_Sharing
 */
final class Ocean_Social_Sharing
{
	/**
	 * Ocean_Social_Sharing The single instance of Ocean_Social_Sharing.
	 *
	 * @var    object
	 * @access private
	 * @since  1.0.0
	 */
	private static $_instance = null;

	/**
	 * The token.
	 *
	 * @var    string
	 * @access public
	 * @since  1.0.0
	 */
	public $token;

	/**
	 * The version number.
	 *
	 * @var    string
	 * @access public
	 * @since  1.0.0
	 */
	public $version;

	/**
	 * The plugin url.
	 *
	 * @var     string
	 * @access  public
	 */
	public $plugin_url;

	/**
	 * The plugin path.
	 *
	 * @var     string
	 * @access  public
	 */
	public $plugin_path;

	/**
	 * The plugin data.
	 *
	 * @var     array
	 * @access  public
	 */
	public $plugin_data;

	// Admin - Start
	/**
	 * The admin object.
	 *
	 * @var    object
	 * @access public
	 * @since  1.0.0
	 */
	public $admin;

	/**
	 * Constructor function.
	 *
	 * @access public
	 * @since  1.0.0
	 * @return void
	 */
	public function __construct( $widget_areas = array() )
	{
		$this->token       = 'ocean-social-sharing';
		$this->plugin_url  = plugin_dir_url(__FILE__);
		$this->plugin_path = plugin_dir_path(__FILE__);
		$this->plugin_data = get_file_data( __FILE__, array( 'Version' => 'Version' ), false );
		$this->version     = $this->plugin_data['Version'];

		define( 'OSS_VERSION', $this->version );

		register_activation_hook(__FILE__, array( $this, 'install' ));

		add_action('init', array( $this, 'oss_load_plugin_textdomain' ));

		add_filter('ocean_register_tm_strings', array( $this, 'register_tm_strings' ));

		add_action('init', array( $this, 'oss_setup' ));
	}

	/**
	 * Main Ocean_Social_Sharing Instance
	 *
	 * Ensures only one instance of Ocean_Social_Sharing is loaded or can be loaded.
	 *
	 * @since  1.0.0
	 * @static
	 * @see    Ocean_Social_Sharing()
	 * @return Main Ocean_Social_Sharing instance
	 */
	public static function instance()
	{
		if (is_null(self::$_instance) ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	} // End instance()

	/**
	 * Load the localisation file.
	 *
	 * @access public
	 * @since  1.0.0
	 * @return void
	 */
	public function oss_load_plugin_textdomain()
	{
		load_plugin_textdomain('ocean-social-sharing', false, dirname(plugin_basename(__FILE__)) . '/languages');
	}

	/**
	 * Cloning is forbidden.
	 *
	 * @since 1.0.0
	 */
	public function __clone()
	{
		_doing_it_wrong(__FUNCTION__, __('Cheatin&#8217; huh?'), '1.0.0');
	}

	/**
	 * Unserializing instances of this class is forbidden.
	 *
	 * @since 1.0.0
	 */
	public function __wakeup()
	{
		_doing_it_wrong(__FUNCTION__, __('Cheatin&#8217; huh?'), '1.0.0');
	}

	/**
	 * Installation.
	 * Runs on activation. Logs the version number and assigns a notice message to a WordPress option.
	 *
	 * @access public
	 * @since  1.0.0
	 * @return void
	 */
	public function install()
	{
		$this->_log_version_number();
	}

	/**
	 * Log the plugin version number.
	 *
	 * @access private
	 * @since  1.0.0
	 * @return void
	 */
	private function _log_version_number()
	{
		// Log the version number.
		update_option($this->token . '-version', $this->version);
	}

	/**
	 * Register translation strings
	 */
	public static function register_tm_strings( $strings )
	{

		$strings['oss_social_share_heading'] = 'Please Share This';

		return $strings;

	}

	/**
	 * Setup all the things.
	 * Only executes if OceanWP or a child theme using OceanWP as a parent is active and the extension specific filter returns true.
	 *
	 * @return void
	 */
	public function oss_setup()
	{
		$theme = wp_get_theme();

		if ('OceanWP' == $theme->name || 'oceanwp' == $theme->template ) {
			include_once $this->plugin_path . '/includes/helpers.php';
			add_action('customize_preview_init', array( $this, 'customize_preview_js' ));
			add_filter( 'ocean_customize_options_data', array( $this, 'register_customize_options') );
			add_action('wp_enqueue_scripts', array( $this, 'get_scripts' ), 999);
			add_action('ocean_before_single_post_content', array( $this, 'before_content' ));
			add_action('ocean_social_share', array( $this, 'after_content' ));
			add_filter('ocean_head_css', array( $this, 'head_css' ));
			add_filter( 'oe_theme_panels', array( $this, 'oe_theme_panels' ) );

			$theme_version = $theme->version;

			$current_theme_version = $theme_version;

			if ( get_template_directory() == get_stylesheet_directory() ) {
				$current_theme_version  = $theme_version;
			} else {
				$parent = wp_get_theme()->parent();
				if ( ! empty( $parent) ) {
					$current_theme_version = $parent->Version;
				}
			}

			if ( version_compare( $current_theme_version, '3.6.1', '<=' ) ) {

				$is_ocean_extra_active = class_exists( 'Ocean_Extra' );
				$is_ocean_extra_version_valid = defined( 'OE_VERSION' ) && version_compare( OE_VERSION, '2.3.1', '<=' );

				if ( ! $is_ocean_extra_active || $is_ocean_extra_version_valid ) {
					include_once $this->plugin_path . '/includes/update-message.php';
				}
			}
		}
	}

	/**
	 * Register customizer options
	 */
	public function register_customize_options($options) {

		if ( OCEAN_EXTRA_ACTIVE
			&& class_exists( 'Ocean_Extra_Theme_Panel' ) ) {

			if ( empty( Ocean_Extra_Theme_Panel::get_setting( 'ocean_social_sharing_panel' ) ) ) {
				return $options;
			}

		}

		include_once $this->plugin_path . '/includes/options.php';

		$options['ocean_social_sharing_settings'] = oss_customizer_options();

		return $options;
	}

	/**
	 * Binds JS handlers to make Theme Customizer preview reload changes asynchronously.
	 *
	 * @since 1.0.0
	 */
	public function customize_preview_js()
	{
		wp_enqueue_script('oss-customizer', plugins_url('/assets/js/customizer.min.js', __FILE__), array( 'customize-preview' ), '1.1', true);
	}

	/**
	 * Enqueue scripts.
	 *
	 * @since 1.0.0
	 */
	public function get_scripts()
	{

		// Load main stylesheet
		wp_enqueue_style('oss-social-share-style', plugins_url('/assets/css/style.min.css', __FILE__));

		// Load main script
		wp_enqueue_script('oss-social-share-script', plugins_url('/assets/js/social.js', __FILE__), array( 'jquery' ), $this->version, true);

		// If rtl
		if (is_RTL() ) {
			wp_enqueue_style('oss-social-share-rtl', plugins_url('/assets/css/rtl.css', __FILE__));
		}

	}

	/**
	 * Social sharing links before content
	 *
	 * @since 1.0.8
	 */
	public function before_content()
	{

		// Return if after content
		if ('after' == get_theme_mod('oss_social_share_position', 'after') ) {
			return;
		} ?>

		<div class="entry-share-wrap"><?php $this->social_share(); ?></div>

		<?php
	}

	/**
	 * Social sharing links after content
	 *
	 * @since 1.0.8
	 */
	public function after_content()
	{

		// Return if before content
		if ('before' == get_theme_mod('oss_social_share_position', 'after') ) {
			return;
		}

		$this->social_share();

	}

	/**
	 * Social sharing links template
	 *
	 * @since 1.0.0
	 */
	public function social_share()
	{

		$file       = $this->plugin_path . 'template/social-share.php';
		$theme_file = get_stylesheet_directory() . '/templates/extra/social-share.php';

		if (file_exists($theme_file) ) {
			$file = $theme_file;
		}

		if (file_exists($file) ) {
			include $file;
		}

	}

	/**
	 * Add css in head tag.
	 *
	 * @since 1.0.0
	 */
	public function head_css( $output )
	{

		// Global vars
		$sharing_border_radius = get_theme_mod('oss_social_share_style_border_radius');
		$sharing_borders       = get_theme_mod('oss_sharing_borders_color');
		$sharing_icons_bg      = get_theme_mod('oss_sharing_icons_bg');
		$sharing_icons_color   = get_theme_mod('oss_sharing_icons_color');

		// Define css var
		$css = '';

		// Add border radius
		if (! empty($sharing_border_radius) ) {
			$css .= '.entry-share ul li a{border-radius:' . $sharing_border_radius . ';}';
		}

		// Add border color
		if (! empty($sharing_borders) ) {
			$css .= '.entry-share.minimal ul li a{border-color:' . $sharing_borders . ';}';
		}

		// Add icon background
		if (! empty($sharing_icons_bg) ) {
			$css .= '.entry-share.minimal ul li a{background-color:' . $sharing_icons_bg . ';}';
		}

		// Add icon color
		if (! empty($sharing_icons_color) ) {
			$css .= '.entry-share.minimal ul li a{color:' . $sharing_icons_color . ';}';
			$css .= '.entry-share.minimal ul li a .oss-icon{fill:' . $sharing_icons_color . ';}';
		}

		// Return CSS
		if (! empty($css) ) {
			$output .= '/* Social Sharing CSS */' . $css;
		}

		// Return output css
		return $output;

	}

	/**
	 * Add social sharing switcher.
	 *
	 * @since  1.0.0
	 */
	public function oe_theme_panels( $panels ) {

		$panels['ocean_social_sharing_panel'] = [
			'label' => esc_html__('Social Sharing', 'ocean-social-sharing'),
		];

		// Return panels list
		return $panels;
	}

} // End Class

// --------------------------------------------------------------------------------
// region Freemius
// --------------------------------------------------------------------------------

if (! function_exists('ocean_social_sharing_fs') ) {
	// Create a helper function for easy SDK access.
	function ocean_social_sharing_fs()
	{
		global $ocean_social_sharing_fs;

		if (! isset($ocean_social_sharing_fs) ) {
			$ocean_social_sharing_fs = OceanWP_EDD_Addon_Migration::instance('ocean_social_sharing_fs')->init_sdk(
				array(
				'id'              => '3807',
				'slug'            => 'ocean-social-sharing',
				'public_key'      => 'pk_0aa6121ff893b29efa9a58d6c0ad5',
				'is_premium'      => false,
				'is_premium_only' => false,
				'has_paid_plans'  => false,
				)
			);
		}

		return $ocean_social_sharing_fs;
	}

	function ocean_social_sharing_fs_addon_init()
	{
		if (class_exists('Ocean_Extra') ) {
			OceanWP_EDD_Addon_Migration::instance('ocean_social_sharing_fs')->init();
		}
	}

	if (0 == did_action('owp_fs_loaded') ) {
		// Init add-on only after parent theme was loaded.
		add_action('owp_fs_loaded', 'ocean_social_sharing_fs_addon_init', 15);
	} else {
		if (class_exists('Ocean_Extra') ) {
			/**
			 * This makes sure that if the theme was already loaded
			 * before the plugin, it will run Freemius right away.
			 *
			 * This is crucial for the plugin's activation hook.
			 */
			ocean_social_sharing_fs_addon_init();
		}
	}
}

// endregion
