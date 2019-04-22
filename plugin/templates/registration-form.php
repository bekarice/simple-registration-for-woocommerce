<?php
/**
 * Simple registration for WooCommerce
 *
 * This source file is subject to the GNU General Public License v3.0
 * that is bundled with this package in the file license.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.gnu.org/licenses/gpl-3.0.html
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@skyverge.com so we can send you a copy immediately.
 *
 * @author    SkyVerge
 * @copyright Copyright (c) 2019, SkyVerge, Inc.
 * @license   http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License v3.0
 */

/**
 * Renders the registration form via shortcode or widget.
 *
 * This is basically a duplicate of myaccount/form-login.php, with some exceptions:
 * - registration form is always displayed
 * - login form is not shown
 * - translations are escaped
 *
 * Note that the use of 'woocommerce' textdomain is intentional!
 *
 * @type bool $show_names whether to show name fields or not
 * @type bool $require_names whether to require name fields or not
 * @type string $button_text the button text for the submit action
 * @type bool $show_privacy_policy whether to show privacy policy text
 *
 * @version 1.0.0
 * @since 1.0.0
 */

defined( 'ABSPATH' ) or exit;

wp_enqueue_script( 'wc-password-strength-meter' );
?>

<?php do_action( 'woocommerce_before_customer_login_form' ); ?>

<div class="u-column2 col-2 simple-registration-woocommerce registration-form woocommerce">

	<form method="post" class="woocommerce-form woocommerce-form-register register" <?php do_action( 'woocommerce_register_form_tag' ); ?> >

		<?php do_action( 'woocommerce_register_form_start' ); ?>

		<?php if ( $show_names ) : ?>
			<p class="woocommerce-FormRow woocommerce-FormRow--first form-row form-row-first">
				<label for="wc_registration_fname"><?php esc_html_e( 'First name', 'simple-registration-for-woocommerce' ); ?><?php echo $require_names ? ' <span class="required">*</span>' : ''; ?></label>
				<input type="text" class="woocommerce-Input woocommerce-Input--text input-text" name="wc_registration_fname" id="wc_registration_fname" value="<?php if ( ! empty( $_POST['wc_registration_fname'] ) ) echo esc_attr( $_POST['wc_registration_fname'] ); ?>" <?php echo $require_names ? ' required' : ''; ?>/>
			</p>

			<p class="woocommerce-FormRow woocommerce-FormRow--last form-row form-row-last">
				<label for="wc_registration_lname"><?php esc_html_e( 'Last name', 'simple-registration-for-woocommerce' ); ?><?php echo $require_names ? ' <span class="required">*</span>' : ''; ?></label>
				<input type="text" class="woocommerce-Input woocommerce-Input--text input-text" name="wc_registration_lname" id="wc_registration_lname" value="<?php if ( ! empty( $_POST['wc_registration_lname'] ) ) echo esc_attr( $_POST['wc_registration_lname'] ); ?>" <?php echo $require_names ? ' required' : ''; ?>/>
			</p>
		<?php endif; ?>

		<?php if ( 'no' === get_option( 'woocommerce_registration_generate_username' ) ) : ?>

			<p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
				<label for="reg_username"><?php esc_html_e( 'Username', 'woocommerce' ); ?>&nbsp;<span class="required">*</span></label>
				<input type="text" class="woocommerce-Input woocommerce-Input--text input-text" name="username" id="reg_username" autocomplete="username" value="<?php echo ( ! empty( $_POST['username'] ) ) ? esc_attr( wp_unslash( $_POST['username'] ) ) : ''; ?>" />
			</p>

		<?php endif; ?>

		<p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
			<label for="reg_email"><?php esc_html_e( 'Email address', 'woocommerce' ); ?>&nbsp;<span class="required">*</span></label>
			<input type="email" class="woocommerce-Input woocommerce-Input--text input-text" name="email" id="reg_email" autocomplete="email" value="<?php echo ( ! empty( $_POST['email'] ) ) ? esc_attr( wp_unslash( $_POST['email'] ) ) : ''; ?>" />
		</p>

		<?php if ( 'no' === get_option( 'woocommerce_registration_generate_password' ) ) : ?>

			<p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
				<label for="reg_password"><?php esc_html_e( 'Password', 'woocommerce' ); ?>&nbsp;<span class="required">*</span></label>
				<input type="password" class="woocommerce-Input woocommerce-Input--text input-text" name="password" id="reg_password" autocomplete="new-password" />
			</p>

		<?php endif; ?>

		<?php // Spam Trap ?>
		<div style="<?php echo ( ( is_rtl() ) ? 'right' : 'left' ); ?>: -999em; position: absolute;"><label for="trap"><?php esc_html_e( 'Anti-spam', 'simple-registration-for-woocommerce' ); ?></label><input type="text" name="email_2" id="trap" tabindex="-1" autocomplete="off" /></div>

		<?php if ( ! $show_privacy_policy ) : // remove privacy policy text ?>
			<?php remove_action( 'woocommerce_register_form', 'wc_registration_privacy_policy_text', 20 ); ?>
		<?php endif; ?>

		<?php do_action( 'woocommerce_register_form' ); ?>

		<?php if ( ! $show_privacy_policy ) : // renable privacy policy text ?>
			<?php add_action( 'woocommerce_register_form', 'wc_registration_privacy_policy_text', 20 ); ?>
		<?php endif; ?>

		<p class="woocommerce-FormRow form-row">
			<?php wp_nonce_field( 'woocommerce-register', 'woocommerce-register-nonce' ); ?>
			<button type="submit" class="woocommerce-Button button" name="register" value="<?php echo esc_attr( $button_text ); ?>"><?php echo esc_html( $button_text ); ?></button>
		</p>

		<?php do_action( 'woocommerce_register_form_end' ); ?>

	</form>
</div>
