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
 * Admin class.
 *
 * @since 1.0.0
 */
class Admin {


	/**
	 * Admin constructor.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {

		// add our settings to WooCommerce > Settings > Account
		add_filter( 'woocommerce_get_settings_account', [ $this, 'add_settings' ] );
	}


	/**
	 * Add our plugin settings under WooCommerce > Settings > Accounts.
	 *
	 * @since 1.0.0
	 *
	 * @param array $settings the WooCommerce account settings
	 * @return array updated settings
	 */
	public function add_settings( $settings ) {

		$updated_settings = [];
		$new_settings     = [

			[
				'title' => __( 'Simple registration', 'simple-registration-for-woocommerce' ),
				'type'  => 'title',
				'id'    => 'simple_registration_for_wc_options',
				'desc'  => __( 'Determine which fields are shown on simple registration forms.', 'simple-registration-for-woocommerce' ),
			],
			[
				'name'     => __( 'Show name fields', 'simple-registration-for-woocommerce' ),
				'desc'     => __( 'Determines whether these fields are shown and required.', 'simple-registration-for-woocommerce' ),
				'id'       => 'simple_registration_for_wc_name_fields',
				'css'      => 'min-width:150px;',
				'default'  => 'disabled',
				'type'     => 'select',
				'options'  => [
					'disabled' => __( 'Do not show first and last name fields', 'simple-registration-for-woocommerce' ),
					'enabled'  => __( 'Show optional first and last name fields', 'simple-registration-for-woocommerce' ),
					'required' => __( 'Require first and last name fields', 'simple-registration-for-woocommerce' ),
				],
				'desc_tip' => true,
			],
			[
				'name'     => __( 'Show privacy text', 'simple-registration-for-woocommerce' ),
				'desc'     => __( 'Enable to show privacy policy text after this form.', 'simple-registration-for-woocommerce' ),
				'id'       => 'simple_registration_for_wc_privacy_policy',
				'default'  => 'yes',
				'type'     => 'checkbox',
			],
			[
				'type' => 'sectionend',
				'id'   => 'simple_registration_for_wc_options',
			],
		];

		// merge the existing settings into our new array
		foreach ( $settings as $setting ) {

			$updated_settings[] = $setting;

			if ( isset( $setting['id'] ) && 'account_registration_options' === $setting['id'] && 'sectionend' === $setting['type'] ) {
				$updated_settings = array_merge( $updated_settings, $new_settings );
			}
		}

		return $updated_settings;
	}


}
