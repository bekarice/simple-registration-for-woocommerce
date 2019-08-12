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
	 * Add hooks if particular plugins are available.
	 *
	 * @internal
	 *
	 * @since 1.0.0
	 */
	public function init_class() {

		// Jilt for WooCommerce!
		if ( function_exists( 'wc_jilt' ) ) {

			add_action( 'woocommerce_created_customer', [ $this, 'update_jilt_customer' ], 15 ); // keep this > priority 10
		}


		// WooCommerce Social Login
		if ( function_exists( 'wc_social_login' ) ) {

			// add simple registration to social login display settings
			add_filter( 'woocommerce_social_login_settings', [ $this, 'add_social_login_display_settings' ] );

			// show the social login buttons if enabled
			add_action( 'woocommerce_register_form_end', [ $this, 'render_social_login_buttons' ] );
		}
	}


	/**
	 * Update the customer record in Jilt with marketing opt in.
	 *
	 * @internal
	 *
	 * @since 1.0.1-dev.1
	 *
	 * @param int $user_id the newly created customer's ID
	 */
	public function update_jilt_customer( $user_id ) {

		// only make changes for our form submission
		if ( get_user_meta( $user_id, '_created_via_simple_registration', true ) ) {

			$integration = wc_jilt()->get_integration();
			$api         = $integration->get_api();
			$user        = get_userdata( $user_id );

			if ( $integration->is_jilt_connected() ) {

				$token = is_string( $api->get_auth_token() ) ? $api->get_auth_token() : $api->get_auth_token()->get_token();
				$url = $this->get_customers_api_endpoint( $integration ) . str_replace( '.', '%2E', urlencode( $user->user_email ) );

				$args = [
					'method'       => 'PUT',
					'accept'       => 'application/json',
					'content-type' => 'application/x-www-form-urlencoded',
					'timeout'      => 3,
					'headers'      => [
						'x-jilt-shop-domain' => wc_jilt()->get_shop_domain(),
						'Authorization'      => $api->get_auth_scheme() . ' ' . $token,
					],
					'body' => [
						'accepts_marketing' => true,
					],
				];

				$response = wp_safe_remote_request( $url, $args );

				// we can't do anything with this data yet, but save it in case for GDPR
				$this->update_local_user_data( $user_id );
			}
		}
	}


	/**
	 * Returns the customer API endpoint for Jilt.
	 *
	 * @since 1.0.1-dev.1
	 *
	 * @param \WC_Jilt_Integration $integration the Jilt integration class
	 * @return string the customers API endpoint
	 */
	private function get_customers_api_endpoint( $integration ) {

		return sprintf( '%s/shops/%s/customers/', $integration->get_api()->get_api_endpoint(), $integration->get_linked_shop_id() );
	}


	/**
	 * Update local customer meta with consent opt in info.
	 *
	 * @since 1.0.1-dev.1
	 *
	 * @param int $user_id the created customer's ID
	 */
	private function update_local_user_data( $user_id ) {

		$customer    = new \WC_Customer( $user_id );
		$button_text = isset( $_POST['wc_simple_registration_register'] ) ? wc_clean( $_POST['wc_simple_registration_register'] ) : __( 'Register', 'simple-registration-for-woocommerce' );

		$customer->update_meta_data( '_wc_jilt_accepts_marketing', true );
		$customer->update_meta_data( '_wc_jilt_consent_context', 'simple_registration_form' );
		$customer->update_meta_data( '_wc_jilt_consent_timestamp', date( 'Y-m-d\TH:i:s\Z', time() ) );
		$customer->update_meta_data( '_wc_jilt_consent_notice', $button_text );

		if ( class_exists( '\\WC_Geolocation' ) ) {
			$customer->update_meta_data( '_wc_jilt_consent_ip_address', \WC_Geolocation::get_ip_address() );
		}

		$customer->save();
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
