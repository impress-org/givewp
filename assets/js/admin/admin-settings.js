/*!
 * Give Admin Forms JS
 *
 * @description: The Give Admin Settings scripts. Only enqueued on the give-settings page; used for tabs and other show/hide functionality
 * @package:     Give
 * @since:       1.5
 * @subpackage:  Assets/JS
 * @copyright:   Copyright (c) 2016, WordImpress
 * @license:     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 */

jQuery.noConflict();
jQuery(document).ready(function ($) {

	/**
	 *  Sortable payment gateways.
	 */
	var $payment_gateways = jQuery( 'ul.give-payment-gatways-list' );
	if( $payment_gateways.length ){
		$payment_gateways.sortable();
	}

	/**
	 * Set payment gateway list under default payment gaetway option
	 */
	var $payment_gateway_list_item = $('input', $payment_gateways),
		$default_gateway           = $('#default_gateway');
	$payment_gateway_list_item.on('click', function () {

		// Bailout.
		if( $(this)[0].hasAttribute( 'readonly' ) ) {
			return false;
		}

		// Selectors.
		var saved_default_gateway      = $default_gateway.val(),
			active_payment_option_html = '',
			$active_payment_gateways   = $('input:checked', $payment_gateways);

		// Set last active payment gateways to readonly.
		if( 1 === $active_payment_gateways.length ){
			$active_payment_gateways.prop( 'readonly', true );
		}else{
			$('input[readonly]:checked', $payment_gateways ).removeAttr('readonly');
		}

		// Create option html from active payment gateway list.
		$active_payment_gateways.each(function (index, item) {
			item           = $(item);
			var item_value = item.attr('name').match(/\[(.*?)\]/)[1];

			active_payment_option_html += '<option value="' + item_value + '"';

			if (saved_default_gateway === item_value) {
				active_payment_option_html += ' selected="selected"';
			}

			active_payment_option_html += '>' + item.next('label').text() + '</option>';
		});

		// Update select html.
		$default_gateway.html(active_payment_option_html);
	});

	/**
	 * Updates states list on change of country
	 */
	var $country_list = $('#base_country'),
		$state_list   = $('#base_state'),
		$name         = null;
	$country_list.on('change', function() {
		$.ajax({
			url: ajaxurl,
			type: 'POST',
			data: {
				action       : 'give_update_states',
				country_code : $(this).val(),
			},
		})
		.done(function(response) {
			$state_list[0].options.length = 0;
			var $parsed = JSON.parse(response);
			var $options = '';
			if(null === $parsed) {
				$state_list.prop('disabled', true);
				$options += '<option selected>No states</option>';
			} else {
				$state_list.prop('disabled', false);
			}
			for($name in $parsed) {
				if($parsed.hasOwnProperty($name)) {
					if('' !== $parsed[$name]) {
						$options += '<option value="' + $name + '">' + $parsed[$name] + '</option>';
					}
				}
			}
			$state_list.html($options);
		});
	});
});
