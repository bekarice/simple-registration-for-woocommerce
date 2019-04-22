<?php
/**
 * Robin
 *
 * This source file is subject to the GNU General Public License v3.0
 * that is bundled with this package in the file license.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.gnu.org/licenses/gpl-3.0.html
 *
 * @package   Robin/Src
 * @author    SkyVerge
 * @category  Admin
 * @copyright Copyright (c) 2014-2018, SkyVerge, Inc.
 * @license   http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License v3.0
 */

/**
 * Returns the One True Instance of Simple Registration for WooCommerce.
 *
 * @since 1.0.0
 *
 * @return SkyVerge\WooCommerce\Simple_Registration\Plugin
 */
function wc_simple_registration() {
	return \SkyVerge\WooCommerce\Simple_Registration\Plugin::instance();
}
