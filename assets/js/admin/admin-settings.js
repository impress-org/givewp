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
});
