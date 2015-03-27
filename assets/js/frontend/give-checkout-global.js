/*!
 * Give Form Checkout JS
 *
 * @description: Handles JS functionality for the donation form checkout
 * @package:     Give
 * @subpackage:  Assets/JS
 * @copyright:   Copyright (c) 2015, WordImpress
 * @license:     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 */
var give_scripts, give_global_vars;

jQuery( document ).ready( function ( $ ) {

	var $body = $( 'body' );

	// Update state/province field on checkout page
	$body.on( 'change', '#give_cc_address input.card_state, #give_cc_address select', function () {
		var $this = $( this ),
			$form = $this.parents( "form" );
		if ( 'card_state' != $this.attr( 'id' ) ) {

			//Disable the State field until updated
			$form.find( '#card_state' ).empty().append( '<option value="1">' + give_global_vars.general_loading + '</option>' ).prop( 'disabled', true );

			// If the country field has changed, we need to update the state/province field
			var postData = {
				action    : 'give_get_states',
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
					$( 'body' ).trigger( 'give_checkout_billing_address_updated', [response] );
				}
			} ).fail( function ( data ) {
				if ( window.console && window.console.log ) {
					console.log( data );
				}
			} );
		}

		return false;
	} );


	/* Credit card verification */
	$body.on( 'keyup change', '.give-do-validate .card-number', function () {
		give_validate_card( $( this ) );
	} );

	function give_validate_card( field ) {

		var form = field.parents( 'form' ),
			card_field = field;

		card_field.validateCreditCard( function ( result ) {
			var $card_type = form.find( '.card-type' );

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


	//Custom Donation Amount - If user focuses on field & changes value then updates price
	$body.on( 'focus', '.give-donation-amount .give-text-input', function ( e ) {

		//Remove any invalid class
		$( this ).removeClass( 'invalid-amount' );

		//Fire up Mask Money
		$( this ).maskMoney( {
			decimal  : give_global_vars.decimal_separator,
			thousands: give_global_vars.thousands_separator
		} );

		var parent_form = $( this ).parents( 'form' );

		//Set data amount
		$( this ).data( 'amount', $( this ).val() );
		//This class is used for CSS purposes
		$( this ).parent( '.give-donation-amount' ).addClass( 'give-custom-amount-focus-in' );
		//Set Multi-Level to Custom Amount Field
		parent_form.find( '.give-default-level, .give-radio-input' ).removeClass( 'give-default-level' );
		parent_form.find( '.give-btn-level-custom' ).addClass( 'give-default-level' );
		parent_form.find( '.give-radio-input' ).prop( 'checked', false ); //Radio
		parent_form.find( '.give-radio-input.give-radio-level-custom' ).prop( 'checked', true ); //Radio
		parent_form.find( '.give-select-level' ).prop( 'selected', false ); //Select
		parent_form.find( '.give-select-level .give-donation-level-custom' ).prop( 'selected', true ); //Select

	} );
	$body.on( 'blur', '.give-donation-amount .give-text-input', function ( e ) {

		var pre_focus_amount = $( this ).data( 'amount' );
		var value_now = $( this ).val();


		//Does this number have a value?
		if ( !value_now || value_now <= 0 ) {
			$( this ).addClass( 'invalid-amount' );
		}

		//If values don't match up then proceed with updating donation total value
		if ( pre_focus_amount !== value_now ) {

			//update checkout total (include currency sign)
			$( this ).parents( 'form' ).find( '.give-final-total-amount' ).data( 'total', value_now ).text( give_global_vars.currency_sign + value_now );

			//fade in/out updating text
			$( this ).next( '.give-updating-price-loader' ).find( '.give-loading-animation' ).css( 'background-image', 'url(' + give_scripts.ajax_loader + ')' );
			$( this ).next( '.give-updating-price-loader' ).stop().fadeIn().fadeOut();

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

		var parent_form = selected_field.parents( 'form' );
		var this_amount = selected_field.val();
		var price_id = selected_field.data( 'price-id' );
		var currency_symbol = parent_form.find( '.give-currency-symbol' ).text();

		//remove old selected class & add class for CSS purposes
		$( selected_field ).parents( '.give-donation-levels-wrap' ).find( '.give-default-level' ).removeClass( 'give-default-level' );
		$( selected_field ).addClass( 'give-default-level' );
		parent_form.find( '#give-amount' ).removeClass( 'invalid-amount' );

		//Is this a custom amount selection?
		if ( this_amount === 'custom' ) {
			//It is, so focus on the custom amount input
			parent_form.find( '#give-amount' ).val( '' ).focus();
			return false; //Bounce out
		}

		//check if price ID blank because of dropdown type
		if ( !price_id ) {
			price_id = selected_field.find( 'option:selected' ).data( 'price-id' );
		}

		//Fade in/out price loading updating image
		parent_form.find( '.give-updating-price-loader' ).stop().fadeIn().fadeOut();

		//update price id field for variable products
		parent_form.find( 'input[name=give-price-id]' ).val( price_id );

		//update custom amount field
		parent_form.find( 'input#give-amount' ).val( this_amount );
		parent_form.find( 'span#give-amount' ).text( this_amount );

		//update checkout total
		var formatted_total = currency_symbol + this_amount;

		if ( give_global_vars.currency_pos == 'after' ) {
			formatted_total = this_amount + currency_symbol;
		}
		parent_form.find( '.give-final-total-amount' ).data( 'total', this_amount ).text( formatted_total );

	}

} );
