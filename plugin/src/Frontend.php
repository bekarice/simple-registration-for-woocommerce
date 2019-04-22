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
 * Frontend class
 *
 * Loads frontend functions
 *
 * @since 1.0.0
 */
class Frontend {


	/**
	 * Frontend constructor.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {

		// make sure template files are searched for in our plugin
		add_filter( 'woocommerce_locate_template',      [ $this, 'locate_template' ], 20, 3 );
		add_filter( 'woocommerce_locate_core_template', [ $this, 'locate_template' ], 20, 3 );

		// add plugin shortcode
		add_shortcode( 'wc_registration_form', [ $this, 'render_shortcode_content' ] );

		// save name inputs if included in registration forms
		add_action( 'woocommerce_created_customer', [ $this, 'save_name_inputs' ] );
	}


	/**
	 * Locates the WooCommerce template files from our templates directory.
	 *
	 * @internal
	 *
	 * @since 1.0.0
	 *
	 * @param string $template already found template
	 * @param string $template_name searchable template name
	 * @param string $template_path template path
	 * @return string search result for the template
	 */
	public function locate_template( $template, $template_name, $template_path ) {

		// only keep looking if no custom theme template was found
		// or if a default WooCommerce template was found
		if ( ! $template || 0 === strpos( $template, WC()->plugin_path() ) ) {

			// set the path to our templates directory
			$plugin_path = untrailingslashit( wc_simple_registration()->get_plugin_path() ) . '/templates/';

			// if a template is found, make it so
			if ( is_readable( $plugin_path . $template_name ) ) {
				$template = $plugin_path . $template_name;
			}
		}

		return $template;
	}


	/**
	 * Render the registration template.
	 *
	 * @since 1.0.0
	 *
	 * @return string template HTML
	 */
	public function render_shortcode_content( $atts ) {

		// allow changing the default settings via shortcode attribute
		$show_names    = 'disabled' !== get_option( 'simple_registration_for_wc_name_fields', 'disabled' );
		$require_names = 'required' === get_option( 'simple_registration_for_wc_name_fields', 'disabled' );

		$atts = shortcode_atts( [
			'button'        => __( 'Register', 'simple-registration-for-woocommerce' ),
			'show_names'    => $show_names ? 'yes' : 'no',
			'require_names' => $require_names ? 'yes' : 'no',
		], $atts );

		ob_start();

		if ( ! is_user_logged_in() ) {

			wc_get_template( 'registration-form.php', [
				'show_names'          => 'yes' === $atts['show_names'],
				'require_names'       => 'yes' === $atts['require_names'],
				'button_text'         => $atts['button'],
				'show_privacy_policy' => 'yes' === get_option( 'simple_registration_for_wc_privacy_policy', 'yes' ),
			] );

		} else {

			$user_message = sprintf( esc_html__( 'Welcome! You can %1$sview your account here%2$s.', 'simple-registration-for-woocommerce' ), '<a href="' . esc_url( wc_get_page_permalink( 'myaccount' ) ) . '">', '</a>' );

			/**
			 * Filters the message shown to logged in users.
			 *
			 * @since 1.0.0
			 *
			 * @param string $user_message the message for logged in users.
			 */
			echo apply_filters( 'simple_registration_for_wc_logged_in_message', $user_message );
		}

		$return = ob_get_contents();
		ob_end_clean();

		return $return;
	}


	/**
	 * Save first and last name fields to customer profiles if enabled.
	 *
	 * @since 1.0.0
	 *
	 * @param int $customer_id the ID of the new user
	 */
	public function save_name_inputs( $customer_id ) {

		$user      = get_userdata( $customer_id );
		$user_data = [ 'ID' => $customer_id ];

		if ( isset( $_POST['wc_registration_fname'] ) && ! empty( $_POST['wc_registration_fname'] ) ) {

			$user_data['first_name'] = sanitize_text_field( $_POST['wc_registration_fname'] );

			// WC billing first name
			update_user_meta( $customer_id, 'billing_first_name', sanitize_text_field( $_POST['wc_registration_fname'] ) );
		}

		if ( isset( $_POST['wc_registration_lname'] ) && ! empty( $_POST['wc_registration_lname'] ) ) {

			$user_data['last_name'] = sanitize_text_field( $_POST['wc_registration_lname'] );

			// WC billing last name
			update_user_meta( $customer_id, 'billing_last_name', sanitize_text_field( $_POST['wc_registration_lname'] ) );
		}

		// set display name to first name to start
		$user_data['display_name'] = isset( $user_data['first_name'] ) ? $user_data['first_name'] : $user->user_login;

		// if we have a full name, set that as display name, and let translators adjust the name
		/* translators: Placeholders: %1$s - first or given name, %2$s - surname or last name */
		$user_data['display_name'] = isset( $user_data['first_name'], $user_data['last_name'] ) ? sprintf( _x( '%1$s %2$s', 'User full name', 'simple-registration-for-woocommerce' ), $user_data['first_name'], $user_data['last_name'] ) : $user_data['display_name'];

		wp_update_user( $user_data );
	}


}
