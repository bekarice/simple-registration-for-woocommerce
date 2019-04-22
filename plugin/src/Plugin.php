<?php
/**
 * Simple registration for WooCommerce
 *
 * This source file is subject to the GNU General Public License v3.0
 * that is bundled with this package in the file license.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.gnu.org/licenses/gpl-3.0.html
 *
 * @author    SkyVerge
 * @category  Admin
 * @copyright Copyright (c) 2019, SkyVerge, Inc.
 * @license   http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License v3.0
 */

namespace SkyVerge\WooCommerce\Simple_Registration;

defined( 'ABSPATH' ) or exit;

/**
 * Main plugin class.
 *
 * @since 1.0.0
 */
class Plugin {


	/** plugin version */
	const VERSION = '1.0.0';

	/** plugin slug */
	const ID = 'simple-registration-for-woocommerce';

	/** @var Plugin single instance of this plugin */
	protected static $instance;

	/** @var Integrations instance */
	protected $integrations;

	/** @var Admin instance */
	protected $admin;

	/** @var Frontend instance */
	protected $frontend;


	/**
	 * Plugin constructor.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {

		// add plugin links
		add_filter( 'plugin_action_links_' . plugin_basename( $this->get_file() ),[ $this, 'add_plugin_links' ] );

		$this->includes();
		$this->install();
	}


	/**
	 * Include required files.
	 *
	 * @since 1.0.0
	 */
	private function includes() {

		// make things pretty
		if ( ! is_admin() || defined( 'DOING_AJAX' ) ) {
			require_once( $this->get_plugin_path() . '/src/Frontend.php' );
			$this->frontend = new Frontend();
		}

		// and then do some stuff behind the scenes
		if ( is_admin() && ! defined( 'DOING_AJAX' ) ) {
			require_once( $this->get_plugin_path() . '/src/Admin.php' );
			$this->admin = new Admin();
		}

		// and play nice with other plugins
		require_once( $this->get_plugin_path() . '/src/Integrations.php' );
		$this->integrations = new Integrations();
	}


	/**
	 * Adds plugin action links.
	 *
	 * @since 1.0.0
	 *
	 * @param array $links all plugin links
	 * @return array all plugin links + our custom links (i.e., "Settings")
	 */
	public function add_plugin_links( $links ) {

		$plugin_links = [];
		$plugin_links['srfwc_configure'] = '<a href="' . admin_url( 'admin.php?page=wc-settings&tab=account' ) . '">' . __( 'Configure', 'simple-registration-for-woocommerce' ) . '</a>';

		return array_merge( $plugin_links, $links );
	}


	/** Helper methods ******************************************************/


	/**
	 * Returns the Admin class instance.
	 *
	 * @since 1.0.0
	 *
	 * @return Admin
	 */
	public function get_admin_instance() {
		return $this->admin;
	}


	/**
	 * Returns the Frontend class instance.
	 *
	 * @since 2.4.0
	 *
	 * @return Frontend
	 */
	public function get_frontend_instance() {
		return $this->frontend;
	}


	/**
	 * Returns the Integrations class instance.
	 *
	 * @since 1.0.0
	 *
	 * @return Integrations
	 */
	public function get_integrations_instance() {
		return $this->integrations;
	}


	/** Setup methods ******************************************************/


	/**
	 * Main Plugin instance, ensures only one instance is/can be loaded.
	 *
	 * @since 1.0.0
	 * @see simple-registration-for-woocommerce()
	 *
	 * @return Plugin
	 */
	public static function instance() {

		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}

		return self::$instance;
	}


	/**
	 * Cloning instances is forbidden due to singleton pattern.
	 *
	 * @since 1.0.0
	 */
	public function __clone() {

		/* translators: Placeholders: %s - plugin name */
		_doing_it_wrong( __FUNCTION__, sprintf( esc_html__( 'You cannot clone instances of %s.', 'simple-registration-for-woocommerce' ), 'Simple registration for WooCommerce' ), '1.0.0' );
	}


	/**
	 * Unserializing instances is forbidden due to singleton pattern.
	 *
	 * @since 1.0.0
	 */
	public function __wakeup() {

		/* translators: Placeholders: %s - plugin name */
		_doing_it_wrong( __FUNCTION__, sprintf( esc_html__( 'You cannot unserialize instances of %s.', 'simple-registration-for-woocommerce' ), 'Simple registration for WooCommerce' ), '1.0.0' );
	}


	/**
	 * Load Translations
	 *
	 * @since 1.0.0
	 */
	public function load_translation() {

		// localization
		load_plugin_textdomain( 'simple-registration-for-woocommerce', false, dirname( plugin_basename( __FILE__ ) ) . '/i18n/languages' );
	}


	/**
	 * Helper to get the plugin path.
	 *
	 * @since 1.0.0
	 *
	 * @return string the plugin path
	 */
	public function get_plugin_path() {
		return untrailingslashit( plugin_dir_path( $this->get_file() ) );
	}


	/**
	 * Gets the main plugin file.
	 *
	 * @since 1.0.0
	 *
	 * @return string
	 */
	public function get_plugin_file() {

		$slug = dirname( plugin_basename( $this->get_file() ) );
		return trailingslashit( $slug ) . $slug . '.php';
	}


	/**
	 * Helper to get the plugin file.
	 *
	 * @since 1.0.0
	 *
	 * @return string the plugin version
	 */
	public function get_file() {
		return dirname( __DIR__ ) . '/simple-registration-for-woocommerce.php';
	}


	/**
	 * Helper to get the plugin URL.
	 *
	 * @since 1.0.0
	 *
	 * @return string the plugin URL
	 */
	public function get_plugin_url() {
		return untrailingslashit( plugins_url( '/', $this->get_file() ) );
	}


	/**
	 * Helper to get the plugin version.
	 *
	 * @since 1.0.0
	 *
	 * @return string the plugin version
	 */
	public function get_version() {
		return self::VERSION;
	}


	/** Lifecycle methods ***************************************/


	/**
	 * Run every time. Used since the activation hook is not executed when updating a plugin.
	 *
	 * @since 1.0.0
	 */
	private function install() {

		// get current version to check for upgrade
		$installed_version = get_option( 'simple_registration_for_wc_version' );

		// force upgrade to 1.0.0
		if ( ! $installed_version ) {
			$this->upgrade( '1.0.0' );
		}

		// upgrade if installed version lower than plugin version
		if ( -1 === version_compare( $installed_version, self::VERSION ) ) {
			$this->upgrade( $installed_version );
		}
	}


	/**
	 * Perform any version-related changes.
	 *
	 * @since 1.0.0
	 *
	 * @param string $version the currently installed version of the plugin
	 */
	private function upgrade( $version ) {

		if ( version_compare( $version, '1.0.0', '=' ) ) {
			// install default setting
			update_option( 'simple_registration_for_wc_options', 'disabled' );
		}

		// update the installed version option
		update_option( 'simple_registration_for_wc_version', self::VERSION );
	}


}
