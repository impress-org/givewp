/*!
 * Give Form Checkout JS
 *
 * @description: Handles JS functionality for the donation form checkout
 * @package:     Give
 * @subpackage:  Assets/JS
 * @copyright:   Copyright (c) 2014, WordImpress
 * @license:     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 */
jQuery( document ).ready( function ( $ ) {
	var $body = $( 'body' ),
		$form = $( "#give_purchase_form" ),
		$give_cart_amount = $( '.give_cart_amount' );

	// Update state/province field on checkout page
	$body.on( 'change', '#give_cc_address input.card_state, #give_cc_address select', function () {
		var $this = $( this );
		if ( 'card_state' != $this.attr( 'id' ) ) {

			// If the country field has changed, we need to update the state/province field
			var postData = {
				action    : 'give_get_shop_states',
				country   : $this.val(),
				field_name: 'card_state'
			};

			$.ajax( {
				type     : "POST",
				data     : postData,
				url      : give_global_vars.ajaxurl,
				xhrFields: {
					withCredentials: true
				},
				success  : function ( response ) {
					if ( 'nostates' == response ) {
						var text_field = '<input type="text" name="card_state" class="cart-state give-input required" value=""/>';
						$form.find( 'input[name="card_state"], select[name="card_state"]' ).replaceWith( text_field );
					} else {
						$form.find( 'input[name="card_state"], select[name="card_state"]' ).replaceWith( response );
					}
					$( 'body' ).trigger( 'give_cart_billing_address_updated', [response] );
				}
			} ).fail( function ( data ) {
				if ( window.console && window.console.log ) {
					console.log( data );
				}
			} ).done( function ( data ) {

			} );
		}

		return false;
	} );


	/* Credit card verification */
	$body.on( 'keyup change', '.give-do-validate .card-number', function () {
		give_validate_card( $( this ) );
	} );

	function give_validate_card( field ) {
		var card_field = field;
		card_field.validateCreditCard( function ( result ) {
			var $card_type = $( '.card-type' );

			if ( result.card_type == null ) {
				$card_type.removeClass().addClass( 'off card-type' );
				card_field.removeClass( 'valid' );
				card_field.addClass( 'error' );
			} else {
				$card_type.removeClass( 'off' );
				$card_type.addClass( result.card_type.name );
				if ( result.length_valid && result.luhn_valid ) {
					card_field.addClass( 'valid' );
					card_field.removeClass( 'error' );
				} else {
					card_field.removeClass( 'valid' );
					card_field.addClass( 'error' );
				}
			}
		} );
	}

	// Make sure a gateway is selected
	$body.on( 'submit', '#give_payment_mode', function () {
		var gateway = $( '#give-gateway option:selected' ).val();
		if ( gateway == 0 ) {
			alert( give_global_vars.no_gateway );
			return false;
		}
	} );

	// Add a class to the currently selected gateway on click
	$body.on( 'click', '#give-payment-mode-select input', function () {
		$( '#give-payment-mode-select label.give-gateway-option-selected' ).removeClass( 'give-gateway-option-selected' );
		$( '#give-payment-mode-select input:checked' ).parent().addClass( 'give-gateway-option-selected' );
	} );


	//Custom Donation Amount - If user focuses on field & changes value then update price
	$body.on( 'focus', '.give-donation-amount .give-text-input', function ( e ) {

		//Set data amount
		$( this ).data( 'amount', $( this ).val() );
		//This class is used for CSS purposes
		$( this ).parent( '.give-donation-amount' ).addClass( 'give-custom-amount-focus-in' );

	} );
	$body.on( 'blur', '.give-donation-amount .give-text-input', function ( e ) {
		var pre_focus_amount = $( this ).data( 'amount' );
		var value_now = $( this ).val();

		//If values don't match up then proceed with updating donation values
		if ( pre_focus_amount !== value_now ) {

			//update checkout total (include currency sign)
			$( this ).parents( 'form' ).find( '.give-final-total-amount' ).data( 'total', value_now ).text( give_global_vars.currency_sign + value_now );

			//fade in/out updating text
			$( this ).next( '.give-updating-price-loader' ).find( '.give-loading-animation' ).css( 'background-image', 'url(' + give_scripts.ajax_loader + ')' );
			$( this ).next( '.give-updating-price-loader' ).fadeIn().fadeOut();

		}
		//This class is used for CSS purposes
		$( this ).parent( '.give-donation-amount' ).removeClass( 'give-custom-amount-focus-in' );

	} );


	//Multi-level Buttons: Update Amount Field based on Multi-level Donation Select
	$body.on( 'click touchend', '.give-donation-level-btn', function ( e ) {
		e.preventDefault(); //don't let the form submit
		update_multiselect_vals( $( this ) );
	} );
	//Multi-level Radios: Update Amount Field based on Multi-level Donation Select
	$body.on( 'click touchend', '.give-radio-input-level', function ( e ) {
		update_multiselect_vals( $( this ) );
	} );
	//Multi-level Radios: Update Amount Field based on Multi-level Donation Select
	$body.on( 'change', '.give-select-level', function ( e ) {
		update_multiselect_vals( $( this ) );
	} );

	//Helper function: Sets the multiselect amount values
	function update_multiselect_vals( selected_field ) {

		//remove old selected class & add class for CSS purposes
		$( selected_field ).parents( '.give-donation-levels-wrap' ).find( '.give-default-level' ).removeClass( 'give-default-level' );
		$( selected_field ).addClass( 'give-default-level' );


		var this_amount = $( selected_field ).val();
		//update custom amount field
		$( selected_field ).parents( 'form' ).find( '#give-amount' ).val( this_amount );
		//update checkout data-total
		$( selected_field ).parents( 'form' ).find( '.give-final-total-amount' ).data( 'total', this_amount ).text( this_amount );

	}

} );
