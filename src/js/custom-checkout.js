jQuery(document).ready(function($) {

	// Function to handle the AJAX request
	function handleShippingStateChange() {
		var selectedState = $('#shipping-state').val();
		console.log("Selected State:", selectedState); // Debugging line

		var data = {
			action: 'get_shipping_state_data',
			state: selectedState,
			nonce: MYajax.nonce
		};

		$.ajax({
			url: MYajax.ajax_url,
			type: 'GET',
			data: data,
			success: function(response) {
				if (response.success) {
					console.log("Successfully updated shipping state", response);
					// Check if the response method is in the radio buttons
					var methodToCheck = response.data.state.method;

					// Loop through each radio button to check conditions
					$('input[name="radio-control-wc-payment-method-options"]').each(function() {
						if ($(this).val() === methodToCheck && response.data.state.visibility === 'hide') {
							$(this).prop('checked', false); // Uncheck the current radio button
							$(this).parent().parent().hide(); // Hide the label of the current radio button
						} else {
							$(this).parent().parent().show(); // Show the label if it doesn't match
						}
					});
				} else {
					console.log('Error:', response.data.message); // Handle error from server
				}
			},
			error: function(jqXHR, textStatus, errorThrown) {
				alert("Something went wrong. Please try again later.");
			}
		});
	}

	// Call the function on page load
	handleShippingStateChange();

	// Call the function when #shipping-state changes
	$('#shipping-state').on('change', function() {
		handleShippingStateChange();
	});

	// Additional code for #shipping-country change
	$('#shipping-country').on('change', function() {
		$('input[name="radio-control-wc-payment-method-options"]').each(function() {
			$(this).prop('checked', true); // Check the current radio button
			$(this).parent().parent().show(); // Show the label of the current radio button
		});
	});

});
