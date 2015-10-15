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


//Get Query function used to grab URL params
(function ( $ ) {
	$.getQuery = function ( query ) {
		query = query.replace( /[\[]/, "\\\[" ).replace( /[\]]/, "\\\]" );
		var expr = "[\\?&]" + query + "=([^&#]*)";
		var regex = new RegExp( expr );
		var results = regex.exec( window.location.href );
		if ( results !== null ) {
			return results[1];
			return decodeURIComponent( results[1].replace( /\+/g, " " ) );
		} else {
			return false;
		}
	};
})( jQuery );


jQuery( function ( $ ) {

	var doc = $( document );

	/**
	 * Update state/province fields per country selection
	 */
	function update_billing_state_field() {
		var $this = $( this ),
			$form = $this.parents( 'form' );
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
				type     : 'POST',
				data     : postData,
				url      : give_global_vars.ajaxurl,
				xhrFields: {
					withCredentials: true
				},
				success  : function ( response ) {
					if ( 'nostates' == response ) {
						var text_field = '<input type="text" id="card_state" name="card_state" class="cart-state give-input required" value=""/>';
						$form.find( 'input[name="card_state"], select[name="card_state"]' ).replaceWith( text_field );
					} else {
						$form.find( 'input[name="card_state"], select[name="card_state"]' ).replaceWith( response );
					}
					doc.trigger( 'give_checkout_billing_address_updated', [response, $form.attr( 'id' )] );
				}
			} ).fail( function ( data ) {
				if ( window.console && window.console.log ) {
					console.log( data );
				}
			} );
		}

		return false;
	}

	doc.on( 'change', '#give_cc_address input.card_state, #give_cc_address select', update_billing_state_field
	);

	sent_back_to_form();

	/**
	 * Format CC Fields
	 * @description Set variables and format cc fields
	 * @since 1.2
	 */
	function format_cc_fields() {
		give_form = $( 'form.give-form' );

		//Loop through forms on page and set CC validation
		give_form.each( function () {
			var card_number = $( this ).find( '.card-number' );
			var card_cvc = $( this ).find( '.card-cvc' );
			var card_expiry = $( this ).find( '.card-expiry' );

			//Only validate if there is a card field
			if ( card_number.length === 0 ) {
				return false;
			}

			card_number.payment( 'formatCardNumber' );
			card_cvc.payment( 'formatCardCVC' );
			card_expiry.payment( 'formatCardExpiry' );
		} );

	}

	format_cc_fields();

	// Trigger formatting function when gateway changes
	doc.on( 'give_gateway_loaded', function () {
		format_cc_fields();
	} );

	// Toggle validation classes
	$.fn.toggleError = function ( errored ) {
		this.toggleClass( 'error', errored );
		this.toggleClass( 'valid', !errored );

		return this;
	};

	/**
	 * Validate cc fields on change
	 */
	doc.on( 'keyup change', '.card-number, .card-cvc, .card-expiry', function () {
		var el = $( this ),
			give_form = el.parents( 'form.give-form' ),
			id = el.attr( 'id' ),
			card_number = give_form.find( '.card-number' ),
			card_cvc = give_form.find( '.card-cvc' ),
			card_expiry = give_form.find( '.card-expiry' ),
			type = $.payment.cardType( card_number.val() );

		if ( id.indexOf( 'card_number' ) > -1 ) {

			var card_type = give_form.find( '.card-type' );

			if ( type === null ) {
				card_type.removeClass().addClass( 'off card-type' );
				el.removeClass( 'valid' ).addClass( 'error' );
			}
			else {
				card_type.removeClass( 'off' ).addClass( type );
			}

			card_number.toggleError( !$.payment.validateCardNumber( card_number.val() ) );
		}
		if ( id.indexOf( 'card_cvc' ) > -1 ) {

			card_cvc.toggleError( !$.payment.validateCardCVC( card_cvc.val(), type ) );
		}
		if ( id.indexOf( 'card_expiry' ) > -1 ) {

			card_expiry.toggleError( !$.payment.validateCardExpiry( card_expiry.payment( 'cardExpiryVal' ) ) );

			var expiry = card_expiry.payment( 'cardExpiryVal' );

			give_form.find( '.card-expiry-month' ).val( expiry.month );
			give_form.find( '.card-expiry-year' ).val( expiry.year );
		}
	} );

	/**
	 * Format Currency
	 *
	 * @description format the currency with accounting.js
	 * @param price
	 * @returns {*|string}
	 */
	function give_format_currency( price ) {
		return accounting.formatMoney( price, {
			symbol   : '',
			decimal  : give_global_vars.decimal_separator,
			thousand : give_global_vars.thousands_separator,
			precision: give_global_vars.number_decimals
		} ).trim();
	}

	/**
	 * Unformat Currency
	 * @param price
	 * @returns {number}
	 */
	function give_unformat_currency( price ) {
		return Math.abs( parseFloat( accounting.unformat( price, give_global_vars.decimal_separator ) ) );
	}

	// Make sure a gateway is selected
	doc.on( 'submit', '#give_payment_mode', function () {
		var gateway = $( '#give-gateway option:selected' ).val();
		if ( gateway == 0 ) {
			alert( give_global_vars.no_gateway );
			return false;
		}
	} );

	// Add a class to the currently selected gateway on click
	doc.on( 'click', '#give-payment-mode-select input', function () {
		$( '#give-payment-mode-select label.give-gateway-option-selected' ).removeClass( 'give-gateway-option-selected' );
		$( '#give-payment-mode-select input:checked' ).parent().addClass( 'give-gateway-option-selected' );
	} );

	/**
	 * Custom Donation Amount - If user focuses on field & changes value then updates price
	 */
	doc.on( 'focus', '.give-donation-amount .give-text-input', function ( e ) {

		var parent_form = $( this ).parents( 'form' );

		//Remove any invalid class
		$( this ).removeClass( 'invalid-amount' );

		//Set data amount
		$( this ).data( 'amount', give_unformat_currency( $( this ).val() ) );
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

	/**
	 * Custom Donation (fires on focus end aka "blur")
	 */
	doc.on( 'blur', '.give-donation-amount .give-text-input', function ( e ) {

		var pre_focus_amount = $( this ).data( 'amount' );

		var value_now = give_unformat_currency( $( this ).val() );
		var formatted_total = give_format_currency( value_now );

		$( this ).val( formatted_total );

		//Does this number have a value?
		if ( !$.isNumeric( value_now ) || value_now <= 0 ) {
			$( this ).addClass( 'invalid-amount' );
		}

		//If values don't match up then proceed with updating donation total value
		if ( pre_focus_amount !== value_now ) {

			//update checkout total (include currency sign)
			$( this ).parents( 'form' ).find( '.give-final-total-amount' ).data( 'total', value_now ).text( formatted_total );

			//fade in/out updating text
			$( this ).next( '.give-updating-price-loader' ).stop().fadeIn().fadeOut();

		}
		//This class is used for CSS purposes
		$( this ).parent( '.give-donation-amount' ).removeClass( 'give-custom-amount-focus-in' );
	} );

	//Multi-level Buttons: Update Amount Field based on Multi-level Donation Select
	doc.on( 'click touchend', '.give-donation-level-btn', function ( e ) {
		e.preventDefault(); //don't let the form submit
		update_multiselect_vals( $( this ) );
	} );

	//Multi-level Radios: Update Amount Field based on Multi-level Donation Select
	doc.on( 'click touchend', '.give-radio-input-level', function ( e ) {
		update_multiselect_vals( $( this ) );
	} );

	//Multi-level Radios: Update Amount Field based on Multi-level Donation Select
	doc.on( 'change', '.give-select-level', function ( e ) {
		update_multiselect_vals( $( this ) );
	} );

	/**
	 * Update Multiselect Vals
	 * @description Helper function: Sets the multiselect amount values
	 *
	 * @param selected_field
	 * @returns {boolean}
	 */
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

	/**
	 * Donor sent back to the form
	 */
	function sent_back_to_form() {

		var form_id = $.getQuery( 'form-id' );
		var payment_mode = $.getQuery( 'payment-mode' );

		//Sanitch check - only proceed if query strings in place
		if ( !form_id || !payment_mode ) {
			return false;
		}

		var form_wrap = $( 'body' ).find( '#give-form-' + form_id + '-wrap' );
		var form = form_wrap.find( 'form.give-form' );
		var display_modal = form_wrap.hasClass( 'give-display-modal' );
		var display_reveal = form_wrap.hasClass( 'give-display-reveal' );

		//Update payment mode radio so it's correctly checked
		form.find( '#give-gateway-radio-list label' ).removeClass( 'give-gateway-option-selected' );
		form.find( 'input[name=payment-mode][value=' + payment_mode + ']' ).prop( 'checked', true ).parent().addClass( 'give-gateway-option-selected' );

		//This form is modal display so show the modal
		if ( display_modal ) {

			//@TODO: Make this DRYer - repeated in give.js
			$.magnificPopup.open( {
				mainClass: 'give-modal',
				items    : {
					src : form,
					type: 'inline'
				},
				callbacks: {
					open : function () {
						// Will fire when this exact popup is opened
						// this - is Magnific Popup object
						if ( $( '.mfp-content' ).outerWidth() >= 500 ) {
							$( '.mfp-content' ).addClass( 'give-responsive-mfp-content' );
						}
						//Hide all form elements besides the ones required for payment
						//form.children().not( '#give_purchase_form_wrap, #give-payment-mode-select, .mfp-close, .give_error' ).hide();

					},
					close: function () {
						//Remove popup class
						form.removeClass( 'mfp-hide' );
						//Show all fields again
						//form.children().not( '#give_purchase_form_wrap, #give-payment-mode-select, .mfp-close, .give_error' ).show();
					}
				}
			} );
		}
		//This is a reveal form, show it
		else if ( display_reveal ) {


			form.find( '.give-btn-reveal' ).hide();
			form.find( '#give-payment-mode-select, #give_purchase_form_wrap' ).slideDown();

		}


	}


} );
