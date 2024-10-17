<?php

defined( 'ABSPATH' ) || exit;


/**
 * Class Toggle_Payments_By_Shipping
 *
 * This class handles the functionality of toggling payment methods based on shipping regions
 * in a WooCommerce store. It initializes constants, sets up hooks for actions, and modifies
 * available payment gateways based on defined settings.
 *
 * @since 1.0.0
 */
class Toggle_Payments_By_Shipping {

	/**
	 * The file path of the plugin.
	 *
	 * @var string
	 * @since 1.0.0
	 */

	public string $file;

	/**
	 * The version number of the plugin.
	 *
	 * @var string
	 * @since 1.0.0
	 */

	public string $version;

	/**
	 * Toggle_Payments_By_Shipping constructor.
	 *
	 * Initializes the class and defines constants and hooks.
	 *
	 * @param string $file The file path of the plugin.
	 * @param string $version The version number of the plugin (default is "1.0.0").
	 *
	 * @since 1.0.0
	 */
	public function __construct( $file, $version = '1.0.0' ) {
		$this->file    = $file;
		$this->version = $version;
		$this->define_constants();
		$this->inithooks();

		register_activation_hook( $file, array( $this, 'activate' ) );
		register_deactivation_hook( $file, array( $this, 'deactivate' ) );
	}

	/**
	 * Define constants for the plugin.
	 *
	 * This method sets up constants used throughout the plugin, including version,
	 * file path, plugin directory, URL, and basename.
	 *
	 * @since 1.0.0
	 */
	public function define_constants() {
		define( 'TPBS_VERSION', $this->version );
		define( 'TPBS_FILE', $this->file );
		define( 'TPBS_PLUGIN_DIR', plugin_dir_path( $this->file ) );
		define( 'TPBS_PLUGIN_URL', plugin_dir_url( $this->file ) );
		define( 'TPBS_PLUGIN_BASENAME', plugin_basename( $this->file ) );
	}

	/**
	 * Initialize hooks for the plugin.
	 *
	 * This method adds action hooks for initializing the plugin and loading
	 * the plugin's text domain for translations.
	 *
	 * @since 1.0.0
	 */
	public function inithooks() {
		add_action( 'init', array( $this, 'init' ) );
		add_action( 'plugins_loaded', array( $this, 'load_plugin_textdomain' ) );
	}

	/**
	 * Activation hook for the plugin.
	 *
	 * This method can be used to set up any necessary actions upon plugin activation.
	 *
	 * @since 1.0.0
	 */
	public function activate() {
		// Activation logic here if needed.
	}

	/**
	 * Deactivation hook for the plugin.
	 *
	 * This method can be used to clean up actions or settings upon plugin deactivation.
	 *
	 * @since 1.0.0
	 */
	public function deactivate() {
		// Deactivation logic here if needed.
	}

	/**
	 * Initialize the plugin functionality.
	 *
	 * This method creates an instance of the admin class and adds the filter to modify
	 * available payment gateways based on shipping regions.
	 *
	 * @since 1.0.0
	 */
	public function init() {
		new Toggle_Payments_By_Shipping_Admin();
		add_filter( 'woocommerce_available_payment_gateways', array( $this, 'shipping_checkout_payment' ) );

		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_custom_scripts' ) );
		// Uncomment the following line to enqueue custom JavaScript for frontend.
		// add_action('wp_enqueue_scripts', array($this, 'enqueue_custom_js'));.

		// Hook to handle AJAX for logged-in users.
		add_action( 'wp_ajax_get_shipping_state_data', array( $this, 'get_shipping_state_data' ) );

		// Hook to handle AJAX for guests or logged-out users.
		add_action( 'wp_ajax_nopriv_get_shipping_state_data', array( $this, 'get_shipping_state_data' ) );
	}

	/**
	 * Enqueues custom JavaScript for the checkout page.
	 *
	 * This function enqueues the `custom-checkout.js` file and localizes it with an AJAX URL
	 * and a nonce for security during AJAX requests.
	 *
	 * @since 1.0.0
	 */
	public function enqueue_custom_scripts() {
		wp_enqueue_script( 'tpbc_custom_scripts', TPBS_PLUGIN_URL . 'assets/js/custom-checkout.js', array( 'jquery' ), TPBS_VERSION, true );

		// Localize script with ajax URL and nonce for GET request.
		wp_localize_script(
			'tpbc_custom_scripts',
			'MYajax',
			array(
				'ajax_url' => admin_url( 'admin-ajax.php' ),
				'nonce'    => wp_create_nonce( 'custom_checkout_nonce' ), // Create a nonce for verification.
			)
		);
	}

	/**
	 * Load the plugin text domain for translations.
	 *
	 * This method loads the translation files for the plugin based on the defined text domain.
	 *
	 * @since 1.0.0
	 */
	public function load_plugin_textdomain() {
		load_plugin_textdomain( 'replace-variable-price-with-active-variation', false, basename( __DIR__ ) . '/languages/' );
	}

	/**
	 * Filter available payment gateways based on shipping region and visibility settings.
	 *
	 * This method modifies the list of available payment gateways during the checkout process
	 * based on the shipping region of the customer and the defined visibility settings
	 * for each payment method.
	 *
	 * @param array $available_gateways An array of available payment gateways.
	 *
	 * @return array Modified array of available payment gateways.
	 *
	 * @since 1.0.0
	 */
	public function shipping_checkout_payment( $available_gateways ) {
		if ( ! function_exists( 'WC' ) || ! WC()->cart ) {
			return $available_gateways;
		}

		$shipping_country = WC()->customer->get_shipping_country();
		$shipping_state   = WC()->customer->get_shipping_state();

		$payment_settings = get_option( 'tpbs_payment_settings', array() );
		$shipping_zone    = WC_Shipping_Zones::get_zone_matching_package(
			array(
				'destination' => array(
					'country' => $shipping_country,
					'state'   => $shipping_state,
				),
			)
		);

		if ( ! $shipping_zone ) {
			return $available_gateways;
		}

		$current_zone_id = $shipping_zone->get_id();

		foreach ( $payment_settings as $setting ) {
			if ( $setting['shipping_region'] === $current_zone_id ) {
				if ( $setting['method'] ) {
					unset( $available_gateways[ $setting['method'] ] );
				}
			}
		}

		return $available_gateways;
	}

	/**
	 * Retrieves shipping state data based on the selected state.
	 *
	 * This function checks the nonce for security, sanitizes the state input,
	 * retrieves all checkout settings, filters them based on the selected state,
	 * and returns the relevant data in JSON format.
	 *
	 * @since 1.0.0
	 */
	public function get_shipping_state_data() {
		// Check nonce for security.
		ob_start();

		// Check if nonce is set and sanitize it.
		if ( isset( $_GET['nonce'] ) ) {
			$nonce = isset( $_GET['nonce'] ) ? sanitize_text_field( wp_unslash( $_GET['nonce'] ) ) : '';
		} else {
			wp_send_json_error( array( 'message' => 'Nonce not set' ) );
			wp_die();
		}

		// Verify the nonce.
		if ( ! wp_verify_nonce( $nonce, 'custom_checkout_nonce' ) ) {
			wp_send_json_error( array( 'message' => 'Nonce verification failed' ) );
			wp_die();
		}

		// Check if 'state' is set and sanitize it.
		if ( isset( $_GET['state'] ) ) {
			$selected_state = isset( $_GET['state'] ) ? sanitize_text_field( wp_unslash( $_GET['state'] ) ) : '';
			$selected_state = sanitize_text_field( $selected_state ); // Sanitize the input.
		} else {
			wp_send_json_error( array( 'message' => 'State not set' ) );
			wp_die();
		}

		// Retrieve all checkout settings.
		$all_checkout_settings = get_option( 'tpbs_payment_settings', array() );

		// Filter the settings based on the selected state.
		$filtered = array_filter(
			$all_checkout_settings,
			function ( $setting ) use ( $selected_state ) {
				return trim( strtolower( $setting['shipping_region'] ) ) === trim( strtolower( $selected_state ) );
			}
		);

		// Get the first matched setting or an empty array.
		$matched_setting = ! empty( $filtered ) ? reset( $filtered ) : array();

		// Prepare the response data.
		$response_data = array(
			'success' => true,  // Set this to true for a successful response.
			'data'    => array(
				'state'   => $matched_setting,
				'message' => 'State data retrieved successfully',
			),
		);

		// Set the content type to application/json.
		header( 'Content-Type: application/json' );

		// Use wp_json_encode instead of json_encode.
		echo wp_json_encode( $response_data );

		// End script execution.
		wp_die(); // Ensure that the script stops executing after sending the response.
	}
}
