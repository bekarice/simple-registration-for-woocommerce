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
 * Integrations class.
 *
 * @since 1.0.0
 */
class Integrations {


	/**
	 * Integrations constructor.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {

		// load this class after Social Login
		add_action( 'plugins_loaded', [ $this, 'init_class' ], 11 );
	}


	/**
	 * Load this class if Social Login is available.
	 *
	 * @since 1.0.0
	 */
	public function init_class() {

		if ( function_exists( 'wc_social_login' ) ) {

			// add simple registration to social login display settings
			add_filter( 'woocommerce_social_login_settings', [ $this, 'add_social_login_display_settings' ] );

			// show the social login buttons if enabled
			add_action( 'woocommerce_register_form_end', [ $this, 'render_social_login_buttons' ] );
		}
	}


	/**
	 * Add our shortcode to social login display settings.
	 *
	 * @since 1.0.0
	 *
	 * @param array $settings original settings
	 * @return array updated settings
	 */
	public function add_social_login_display_settings( $settings ) {

		foreach ( $settings as $k => $setting ) {

			if ( isset( $setting['id'], $setting['options'] ) && 'wc_social_login_display' === $setting['id'] ) {

				// add an option for simple registration forms
				$settings[ $k ]['options']['wc_simple_registration'] = __( 'Registration shortcodes', 'simple-registration-for-woocommerce' );
			}
		}

		return $settings;
	}


	/**
	 * Whether social login buttons are displayed on simple registration shortcode.
	 *
	 * @since 1.0.0
	 *
	 * @param string $handle the display location
	 * @return bool true if shown
	 */
	public function is_displayed_on( $handle = 'wc_simple_registration' ) {

		/** this filter is documented in woocommerce-social-login/includes/frontend/class-wc-social-login-frontend.php */
		return in_array( $handle, apply_filters( 'wc_social_login_display', (array) get_option( 'wc_social_login_display', [] ) ) );
	}


	/**
	 * Render social login buttons in shortcodes if enabled.
	 *
	 * @since 1.0.0
	 */
	public function render_social_login_buttons() {

		// determine if buttons are already shown on the account page
		$buttons_shown = is_account_page() && $this->is_displayed_on( 'my_account' );

		if ( $this->is_displayed_on( 'wc_simple_registration' ) && ! $buttons_shown ) {
			woocommerce_social_login_buttons( wc_get_page_permalink( 'myaccount' ) );
		}
	}


}
