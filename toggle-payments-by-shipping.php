<?php
/**
 * Plugin Name:       Toggle Payment by Shipping
 * Plugin URI:        https://woocopilot.com/plugins/toggle-payment-by-shipping/
 * Description:       "Toggle Payment by Shipping" allows administrators to enable or disable payment methods based on product categories.
 * Version:           1.0.0
 * Requires at least: 6.5
 * Requires PHP:      7.2
 * Author:            WooCopilot
 * Author URI:        https://woocopilot.com/
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       toggle-payment-by-shipping
 * Domain Path:       /languages
 *
 * @package Toggle_Payment_By_Shipping
 */

defined( 'ABSPATH' ) || exit;

require_once plugin_dir_path( __FILE__ ) . 'includes/class-admin-toggle-payments-by-shipping.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/class-toggle-payments-by-shipping.php';

/**
 * Initializes the plugin.
 *
 * @since 1.0.0
 * @return Toggle_Payments_By_Shipping Plugin object.
 */
function toggle_payments_by_shipping() {
	return new Toggle_Payments_By_Shipping( __FILE__, '1.0.0' );
}

// Hook the initialization function.
toggle_payments_by_shipping();
