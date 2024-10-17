// admin.js

jQuery(document).ready(function($) {

	// function updatePaymentGateways() {
	//     // Get the selected country and state
	//     const selectedCountry = $('#billing_country').val(); // Change this selector based on your HTML structure
	//     const selectedState = $('#billing_state').val(); // Change this selector based on your HTML structure
	//
	//     // Make an AJAX request to fetch the available payment gateways
	//     $.ajax({
	//         url: ajaxurl,
	//         method: 'POST',
	//         data: {
	//             action: 'update_payment_gateways',
	//             shipping_country: selectedCountry,
	//             shipping_state: selectedState
	//         },
	//         success: function(response) {
	//             if (response.success) {
	//                 // Update the payment methods displayed on the cart page
	//                 $('#available_payment_methods').html(response.data); // Update this selector based on your HTML structure
	//             }
	//         },
	//         error: function() {
	//             console.error('Error updating payment gateways');
	//         }
	//     });
	// }

	// Listen for changes in the shipping country and state dropdowns
	// $('#billing_country, #billing_state').on('change', function() {
	//     updatePaymentGateways();
	// });
	// Add new row to the table when clicking the "Add New" button
	$('.add-new-button').on('click', function() {
		var table = $('#payment-table');
		var templateRow = table.find('.template-row').clone(); // Clone the template row
		templateRow.removeClass('template-row').show(); // Remove the template class and show the row

		// Reset all select inputs and checkboxes in the cloned row
		templateRow.find('select').each(function() {
			$(this).val(''); // Reset all select inputs to empty
		});

		templateRow.find('input[type="checkbox"]').prop('checked', false).val('show'); // Reset checkbox and set value to 'show'

		// Append the new row to the table body
		table.find('tbody').append(templateRow);
	});

	// Handle the delete button click using event delegation
	$('#payment-table').on('click', '.delete-button', function() {
		var row = $(this).closest('tr'); // Find the closest row
		// Check if the row is not the only one remaining
		if ($('#payment-table tbody tr').length > 1) {
			if (confirm('Are you sure you want to delete this row?')) {
				row.remove(); // Remove the row if confirmed
			}
		} else {
			alert('Cannot delete the last row.'); // Alert if trying to delete the last row
		}
	});

	// Before form submission, remove the template row to avoid it being submitted
	$('form.product-catalog-mode-form').on('submit', function() {
		$('#payment-table').find('.template-row').remove(); // Remove the template row before submitting the form
	});



});

(function($) {
	function toogleSwitch(value, id) {
		if (value == 'hide') {

			$('#payment_visibility_' + id).val('show');
			$('#payment_visibility_h_' + id).val('show');
		} else {
			$('#payment_visibility_' + id).val('hide');
			$('#payment_visibility_h_' + id).val('hide');
		}
	}

	// Attach the function to the global scope if necessary
	window.toogleSwitch = toogleSwitch;

})(jQuery);




