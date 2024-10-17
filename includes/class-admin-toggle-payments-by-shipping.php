<?php

defined( 'ABSPATH' ) || exit;

/**
 * Class Toggle_Payments_By_Shipping_Admin
 *
 * This class handles the admin functionalities for the Toggle Payments by Shipping plugin.
 * It includes methods for enqueuing admin scripts, managing the admin menu, displaying
 * the admin page, and updating plugin settings.
 *
 * @since 1.0.0
 */
class Toggle_Payments_By_Shipping_Admin {

	/**
	 * Toggle_Payments_By_Shipping_Admin constructor.
	 *
	 * Initializes the admin class and hooks necessary actions.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_scripts' ) );
		add_action( 'admin_menu', array( $this, 'admin_menu' ) );
		add_action( 'admin_post_tpbs_update_settings', array( $this, 'update_settings' ) );
	}

	/**
	 * Enqueues admin-specific styles and scripts.
	 *
	 * @since 1.0.0
	 */
	public function enqueue_admin_scripts() {
		wp_enqueue_style( 'tpbs_admin_style', TPBS_PLUGIN_URL . 'assets/css/admin.css', array(), TPBS_VERSION );
		wp_enqueue_script( 'tpbc_admin_scripts', TPBS_PLUGIN_URL . 'assets/js/admin.js', array( 'jquery' ), TPBS_VERSION, true );
	}

	/**
	 * Adds an item to the WordPress admin menu.
	 *
	 * @since 1.0.0
	 */
	public function admin_menu() {
		add_menu_page(
			'Toggle Payments by Shipping',
			'Toggle Payments by Shipping',
			'manage_options',
			'toggle-payments-by-shipping',
			array( $this, 'admin_page' ),
			'dashicons-money-alt',
			'58'
		);
	}

	/**
	 * Displays the admin page for configuring payment settings.
	 *
	 * This method renders the HTML for the settings page, including forms
	 * for managing payment visibility based on shipping regions.
	 *
	 * @since 1.0.0
	 */
	public function admin_page() {
		?>
		<div class="wrap">
			<h2>Toggle Payments by Shipping</h2>
			<button type="button" class="add-new-button">Add New</button>
			<form class="product-catalog-mode-form" method="POST"
					action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
				<?php
				// Fetch shipping zones and payment gateways.
				$shipping_zones     = WC_Shipping_Zones::get_zones();
				$available_gateways = WC()->payment_gateways->get_available_payment_gateways();
				?>
				<div class="form-container">
					<table id="payment-table" class="form-table">
						<thead class="heading">
						<tr>
							<th style="text-align: center">Shipping Region</th>
							<th style="text-align: center">Payment Method</th>
							<th style="text-align: center">Payment Visibility</th>
							<th style="text-align: center">Action</th>
						</tr>
						</thead>

						<tbody class="row">
						<!-- Template Row (Hidden). -->
						<tr class="template-row" style="display:none;">
							<td>
								<select name="tpbs_shipping_region[]">
									<?php
									foreach ( $shipping_zones as $zone ) {
										$result = ( strpos( $zone['zone_locations'][2]->code, ':' ) !== false ) ? explode( ':', $zone['zone_locations'][2]->code )[1] : $zone['zone_locations'][2]->code;

										?>
										<option value="<?php echo esc_attr( $result ); ?>">
											<?php echo esc_html( $zone['zone_name'] ); ?>
										</option>
									<?php } ?>
								</select>
							</td>
							<td>
								<select name="tpbs_payment_method[]">
									<?php foreach ( $available_gateways as $gateway ) { ?>
										<option value="<?php echo esc_attr( $gateway->id ); ?>">
											<?php echo esc_html( $gateway->get_title() ); ?>
										</option>
									<?php } ?>
								</select>
							</td>
							<td>
								<label class="toggle-switch">
									<input type="checkbox" name="payment_visibility[]"
											value="hide" onchange="this.value = this.checked ? 'hide' : 'show';">
									<span class="slider"></span>
								</label>
								<span class="toggle-comment">Hide Payment</span>
							</td>
							<td>
								<button type="button" class="delete-button">Delete</button>
							</td>
						</tr>
						<?php
						// Load saved settings.
						$saved_payment_settings = get_option( 'tpbs_payment_settings', array() );

						if ( ! empty( $saved_payment_settings ) ) {
							foreach ( $saved_payment_settings as $key => $payment_setting ) {
								// Assuming shipping region and payment method have the same index.
								$shipping_zone = isset( $saved_payment_settings[ $key ]['shipping_region'] ) ? $saved_payment_settings[ $key ]['shipping_region'] : array();
								?>
								<tr>
									<td>
										<select name="tpbs_shipping_region[]">
											<?php
											foreach ( $shipping_zones as $zone ) {
												$result = ( strpos( $zone['zone_locations'][2]->code, ':' ) !== false ) ? explode( ':', $zone['zone_locations'][2]->code )[1] : $zone['zone_locations'][2]->code;
												?>
												<option value="<?php echo esc_attr( $result ); ?>"
													<?php selected( $result, $shipping_zone ); ?>>
													<?php echo esc_html( $zone['zone_name'] ); ?>
												</option>
											<?php } ?>
										</select>
									</td>
									<td>
										<select name="tpbs_payment_method[]">
											<?php foreach ( $available_gateways as $gateway ) { ?>
												<option value="<?php echo esc_attr( $gateway->id ); ?>"
													<?php selected( $gateway->id, $payment_setting['method'] ); ?>>
													<?php echo esc_html( $gateway->get_title() ); ?>
												</option>
											<?php } ?>
										</select>
									</td>
									<td>
										<label class="toggle-switch">
											<input type="hidden" name="payment_visibility_h[]" id="<?php echo esc_attr( 'payment_visibility_h_' . $key ); ?>"
													value="<?php echo esc_attr( ( 'hide' === $payment_setting['visibility'] ) ? 'hide' : 'show' ); ?>">
											<input type="checkbox" name="payment_visibility[]"
													value="<?php echo esc_attr( ( 'hide' === $payment_setting['visibility'] ) ? 'hide' : 'show' ); ?>"
												<?php checked( 'hide', $payment_setting['visibility'] ); ?>
													id="<?php echo esc_attr( 'payment_visibility_' . $key ); ?>"
													onchange="toogleSwitch(this.value, <?php echo esc_attr( $key ); ?>)">
											<span class="slider"></span>
										</label>
										<span class="toggle-comment">
								<?php echo esc_html( ( 'hide' === $payment_setting['visibility'] ) ? 'Hide Payment' : 'Show Payment' ); ?>
							</span>
									</td>
									<td>
										<button type="button" class="delete-button">Delete</button>
									</td>
								</tr>
								<?php
							}
						}
						?>
						</tbody>
					</table>

					<!-- Hidden Inputs for form submission -->
					<input type="hidden" name="action" value="tpbs_update_settings">
					<?php wp_nonce_field( 'tpbs_update_settings_nonce', 'tpbs_nonce_field' ); ?>
					<button type="submit" class="save-button">Save Changes</button>
				</div>
			</form>
		</div>
		<?php
	}

	/**
	 * Updates the plugin's settings when the form is submitted.
	 *
	 * This method processes the form submission, sanitizes and validates the input,
	 * and updates the plugin's settings in the database.
	 *
	 * @since 1.0.0
	 */
	public function update_settings() {
		// Check the nonce for security.
		if ( ! isset( $_POST['tpbs_nonce_field'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['tpbs_nonce_field'] ) ), 'tpbs_update_settings_nonce' ) ) {
			wp_die( 'Invalid nonce.' );
		}

		// Validate and sanitize the input data.
		$shipping           = isset( $_POST['tpbs_shipping_region'] ) ? array_map( 'sanitize_text_field', wp_unslash( $_POST['tpbs_shipping_region'] ) ) : array();
		$payment_methods    = isset( $_POST['tpbs_payment_method'] ) ? array_map( 'sanitize_text_field', wp_unslash( $_POST['tpbs_payment_method'] ) ) : array();
		$payment_visibility = isset( $_POST['payment_visibility'] ) ? array_map( 'sanitize_text_field', wp_unslash( $_POST['payment_visibility'] ) ) : array();

		// Update the plugin settings.
		$settings = array();
		for ( $i = 0, $n = count( $shipping ); $i < $n; $i++ ) {
			$settings[] = array(
				'shipping_region' => $shipping[ $i ],
				'method'          => $payment_methods[ $i ],
				'visibility'      => $payment_visibility[ $i ],
			);
		}

		update_option( 'tpbs_payment_settings', $settings );
		wp_safe_redirect( admin_url( 'admin.php?page=toggle-payments-by-shipping' ) );
		exit;
	}
}
