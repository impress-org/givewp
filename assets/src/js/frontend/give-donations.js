/* globals jQuery, Give */
jQuery( function( $ ) {

	var $forms = jQuery( 'form.give-form' ),
		doc = $( document );

	// Toggle validation classes
	$.fn.toggleError = function( errored ) {
		this.toggleClass( 'error', errored );
		this.toggleClass( 'valid', ! errored );

		return this;
	};

	// Initialize Give object.
	Give.init();

	/**
	 * Update state/province fields per country selection
	 */
	function update_billing_state_field() {
		var $this = $( this ),
			$form = $this.parents( 'form' );
		if ( 'card_state' !== $this.attr( 'id' ) ) {

			//Disable the State field until updated
			$form.find( '#card_state' ).empty().append( '<option value="1">' + Give.fn.getGlobalVar( 'general_loading' ) + '</option>' ).prop( 'disabled', true );

			// If the country field has changed, we need to update the state/province field
			var postData = {
				action: 'give_get_states',
				country: $this.val(),
				field_name: 'card_state'
			};

			$.ajax( {
				type: 'POST',
				data: postData,
				url: Give.fn.getGlobalVar( 'ajaxurl' ),
				xhrFields: {
					withCredentials: true
				},
				success: function ( response ) {
					var html = '';
					var states_label = response.states_label;
					if ( 'undefined' !== typeof response.states_found && true === response.states_found ) {
						html = response.data;
					} else {
						html = `<input type="text" id="card_state"  name="card_state" class="cart-state give-input required" placeholder="${states_label}" value="${response.default_state}" autocomplete="address-level4"/>`;
					}

					if ( false === $form.hasClass( 'float-labels-enabled' ) ) {
						if ( 'undefined' !== typeof ( response.states_require ) && true === response.states_require ) {
							$form.find( 'input[name="card_state"], select[name="card_state"]' ).closest( 'p' ).find( 'label .give-required-indicator' ).removeClass( 'give-hidden' );
						} else {
							$form.find( 'input[name="card_state"], select[name="card_state"]' ).closest( 'p' ).find( 'label .give-required-indicator' ).addClass( 'give-hidden' );
						}

						var $city = $form.find( 'input[name="card_city"]' );
						// check if city fields is require or not
						if ( 'undefined' !== typeof ( response.city_require ) && true === response.city_require ) {
							$city.closest( 'p' ).find( 'label .give-required-indicator' ).removeClass( 'give-hidden' ).removeClass( 'required' );
							$city.attr( 'required', true );
						} else {
							$city.closest( 'p' ).find( 'label .give-required-indicator' ).addClass( 'give-hidden' ).addClass( 'required' );
							$city.removeAttr( 'required' );
						}
					} else {
						$form.find( 'input[name="card_state"], select[name="card_state"]' ).closest( 'p' ).find( 'label' ).text( states_label );
					}

					$form.find( 'input[name="card_state"], select[name="card_state"]' ).closest( 'p' ).find( 'label .state-label-text' ).text( states_label );
					$form.find( 'input[name="card_state"], select[name="card_state"]' ).replaceWith( html );

					// Check if user want to show the feilds or not.
					if ( 'undefined' !== typeof ( response.show_field )  && true === response.show_field ) {
						$form.find( 'p#give-card-state-wrap' ).removeClass( 'give-hidden' );

						// Add support to zip fields.
						$form.find( 'p#give-card-zip-wrap' ).addClass( 'form-row-last' );
						$form.find( 'p#give-card-zip-wrap' ).removeClass( 'form-row-wide' );
					} else {
						$form.find( 'p#give-card-state-wrap' ).addClass( 'give-hidden' );

						// Add support to zip fields.
						$form.find( 'p#give-card-zip-wrap' ).addClass( 'form-row-wide' );
						$form.find( 'p#give-card-zip-wrap' ).removeClass( 'form-row-last' );
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

	// Sync state field with country.
	doc.on(
		'change',
		'#give_cc_address input.card_state, #give_cc_address select',
		update_billing_state_field
	);

	// Trigger formatting function when gateway changes.
	doc.on(
		'give_gateway_loaded',
		function() {
			Give.form.fn.field.formatCreditCard( $forms );
		}
	);

	// Make sure a gateway is selected.
	doc.on(
		'submit',
		'#give_payment_mode',
		function() {
			var gateway = Give.form.fn.getGateway( $( this ).closest( 'form' ) );
			if ( ! gateway.length ) {
				alert( Give.fn.getGlobalVar( 'no_gateway' ) );
				return false;
			}
		}
	);

	// Add a class to the currently selected gateway on click
	doc.on(
		'click',
		'#give-payment-mode-select input',
		function() {
			var $form = $( this ).parents( 'form' ),
				$gateways_li = $form.find( '#give-payment-mode-select li' ),
				old_payment_gateway = $form.find( 'li.give-gateway-option-selected input[name="payment-mode"]' ).val().trim(),
				new_payment_gateways;

			// Unselect all payment gateways.
			$gateways_li.removeClass( 'give-gateway-option-selected' );
			$gateways_li.prop( 'checked', false );

			// Select payment gateway.
			$( this ).prop( 'checked', true );
			$( this ).parent().addClass( 'give-gateway-option-selected' );

			// Get new payment gateway.
			new_payment_gateways = Give.form.fn.getGateway( $form );

			// Change form action.
			$form.attr( 'action', $form.attr( 'action' ).replace(
				'payment-mode=' + old_payment_gateway,
				'payment-mode=' + new_payment_gateways )
			);
		}
	);

	/**
	 * Custom Donation Amount Focus In
	 *
	 * @description: If user focuses on field & changes value then updates price
	 */
	doc.on( 'focus', '.give-donation-amount .give-text-input', function( e ) {

		var parent_form = $( this ).parents( 'form' );

		// Remove any invalid class
		$( this ).removeClass( 'invalid-amount' );

		// Set data amount
		var current_total = parent_form.find( '.give-final-total-amount' ).attr( 'data-total' );
		var decimal_separator = Give.form.fn.getInfo( 'decimal_separator', parent_form );
		$( this ).attr( 'data-amount', Give.fn.unFormatCurrency( current_total, decimal_separator ) );

		//This class is used for CSS purposes
		$( this ).parent( '.give-donation-amount' ).addClass( 'give-custom-amount-focus-in' );

		//Set Multi-Level to Custom Amount Field
		parent_form.find( '.give-default-level, .give-radio-input' ).removeClass( 'give-default-level' );
		parent_form.find( '.give-btn-level-custom' ).addClass( 'give-default-level' );
		parent_form.find( '.give-radio-input' ).prop( 'checked', false ); // Radio
		parent_form.find( '.give-radio-input.give-radio-level-custom' ).prop( 'checked', true ); // Radio
		parent_form.find( '.give-select-level' ).prop( 'selected', false ); // Select
		parent_form.find( '.give-select-level .give-donation-level-custom' ).prop( 'selected', true ); // Select

	} );

	/**
	 * Custom Donation Focus Out
	 *
	 * Fires on focus end aka "blur"
	 */
	doc.on( 'blur', '.give-donation-amount .give-text-input', function( e, $parent_form, donation_amount, price_id ) {

		let parent_form = ('undefined' !== typeof $parent_form) ? $parent_form : $( this ).closest( 'form' ),
			pre_focus_amount = $( this ).attr( 'data-amount' ),
			this_value = ('undefined' !== typeof donation_amount) ? donation_amount : $( this ).val(),
			decimal_separator = Give.form.fn.getInfo( 'decimal_separator', parent_form ),
			value_min = Give.form.fn.getMinimumAmount( parent_form ),
			value_max = Give.form.fn.getMaximumAmount( parent_form ),
			value_now = (this_value === 0) ? value_min : Give.fn.unFormatCurrency( this_value, decimal_separator ),
			formatted_total = Give.form.fn.formatAmount( value_now, parent_form, {} );

		price_id = 'undefined' === typeof price_id ? Give.form.fn.getPriceID( parent_form, true ) : price_id;

		// https://github.com/WordImpress/Give/issues/3299
		// If we change from custom amount to donation level then
		// this event fire twice. First on amount field blur and second time on level button/radio/select click which cause of minimum donation notice.
		// This condition will prevent minimum donation amount notice show by set default level.
		if( '' === value_now || 0 === value_now ) {
			let $default_level = $( '.give-donation-levels-wrap [data-default="1"]', $parent_form );

			if( $default_level.length ) {
				price_id = $default_level.data('price-id');
				this_value = value_now = Give.fn.unFormatCurrency( $default_level.val(), decimal_separator );
				formatted_total = Give.form.fn.formatAmount( value_now, parent_form, {} );
			}
		}

		// Cache donor selected price id for a amount.
		Give.fn.setCache( 'amount_' + value_now, price_id, parent_form );
		$( this ).val( formatted_total );

		// Does this number have an accepted min/max value?
		if ( ! Give.form.fn.isValidDonationAmount( parent_form ) ) {

			// It doesn't... add invalid class.
			$( this ).addClass( 'give-invalid-amount' );

			// Disable submit
			Give.form.fn.disable( parent_form, true );
			let invalid_minimum_notice = parent_form.find( '.give-invalid-minimum' ),
				invalid_maximum_notice = parent_form.find( '.give-invalid-maximum' );

			// If no error present, create it, insert, slide down (show).
			if ( 0 === invalid_minimum_notice.length && value_now < value_min ) {
				Give.notice.fn.renderNotice( 'bad_minimum', parent_form );
			} else if( value_now >= value_min ) {
				invalid_minimum_notice.slideUp( 300, function() { $( this ).remove(); } );
			}

			// For maximum custom amount error.
			if ( 0 === invalid_maximum_notice.length && value_now > value_max ) {
				Give.notice.fn.renderNotice( 'bad_maximum', parent_form );
			} else if (value_now <= value_max ){
				invalid_maximum_notice.slideUp( 300, function() { $( this ).remove(); } );
			}

		} else {

			// Remove error message class from price field.
			$( this ).removeClass( 'give-invalid-amount' );

			// Minimum amount met - slide up error & remove it from DOM.
			parent_form.find( '.give-invalid-minimum, .give-invalid-maximum' ).slideUp( 300, function() {
				$( this ).remove();
			} );

			// Re-enable submit.
			Give.form.fn.disable( parent_form, false );
		}

		// If values don't match up then proceed with updating donation total value
		if ( pre_focus_amount !== value_now ) {

			// Update donation total (include currency symbol)
			parent_form.find( '.give-final-total-amount' )
				.attr( 'data-total', value_now )
				.text( Give.fn.formatCurrency(
					value_now,
					{
						symbol: Give.form.fn.getInfo( 'currency_symbol', parent_form ),
						position: Give.form.fn.getInfo( 'currency_position', parent_form )
					},
					parent_form )
				);

		}

		// Set price id for current amount.
		if ( - 1 !== price_id ) {

			// Auto set give price id.
			$( 'input[name="give-price-id"]', parent_form ).val( price_id );

			// Update hidden amount field
			parent_form.find( '.give-amount-hidden' ).val( Give.form.fn.formatAmount( value_now, parent_form, {} ) );

			// Remove old selected class & add class for CSS purposes
			parent_form.find( '.give-default-level' ).removeClass( 'give-default-level' );

			// Auto select variable price items ( Radio/Button/Select ).
			Give.form.fn.autoSelectDonationLevel( parent_form, price_id );
		}

		// This class is used for CSS purposes
		$( this ).parent( '.give-donation-amount' )
			.removeClass( 'give-custom-amount-focus-in' );

		// trigger an event for hooks
		jQuery( document ).trigger( 'give_donation_value_updated', [ parent_form, value_now, price_id ] );

	} );

	// Multi-level Buttons: Update Amount Field based on Multi-level Donation Select
	doc.on( 'click touchend', '.give-donation-level-btn', function( e ) {
		e.preventDefault(); //don't let the form submit
		Give.form.fn.autoSetMultiLevel( $( this ) );
	} );

	// Multi-level Radios: Update Amount Field based on Multi-level Donation Select
	doc.on( 'click touchend', '.give-radio-input-level', function( e ) {
		Give.form.fn.autoSetMultiLevel( $( this ) );
	} );

	// Multi-level Checkboxes: Update Amount Field based on Multi-level Donation Select
	doc.on( 'change', '.give-select-level', function( e ) {
		Give.form.fn.autoSetMultiLevel( $( this ) );
	} );

	/**
	 * Show/Hide terms and conditions.
	 */
	doc.on( 'click', '.give_terms_links', function( e ) {
		e.preventDefault();
		var $fieldset_wrapper = $( this ).closest( 'fieldset' );
		$( '[class^=give_terms-]', $fieldset_wrapper ).slideToggle();
		$( 'a.give_terms_links', $fieldset_wrapper ).toggle();
		return false;
	} );

	/**
	 * Prevent level jump which happens due to same id.
	 * @see https://github.com/WordImpress/Give/issues/2292
	 */
	$( 'label[for^="give-radio-level"]' ).on( 'click', function( e ) {
		var $form = $( this ).closest( 'form' ),
			$inputField = $form.find( '#' + $( this ).attr( 'for' ) );

		if ( $inputField.length ) {
			$inputField.trigger( 'click' );
			e.preventDefault();
		}
	} );
} );

jQuery(window).on('load', function () {

	/**
	 * Validate cc fields on change
	 */
	jQuery( 'body' ).on( 'keyup change focusout', '.give-form .card-number, .give-form .card-cvc, .give-form .card-expiry', function( e ) {
		var el = jQuery( this ),
			give_form = el.parents( 'form.give-form' ),
			id = el.attr( 'id' ),
			card_number = give_form.find( '.card-number' ),
			card_cvc = give_form.find( '.card-cvc' ),
			card_expiry = give_form.find( '.card-expiry' ),
			type = jQuery.payment.cardType( card_number.val() ),
			error = false;

		switch ( e.type ) {
			case 'focusout':
				if ( id.indexOf( 'card_number' ) > - 1 ) {
					// Set card number error.
					error = ! jQuery.payment.validateCardNumber( card_number.val() );
					card_number.toggleError( error );

				} else if ( id.indexOf( 'card_cvc' ) > - 1 ) {
					// Set card cvc error.
					error = ! jQuery.payment.validateCardCVC( card_cvc.val(), type );
					card_cvc.toggleError( error );

				} else if ( id.indexOf( 'card_expiry' ) > - 1 ) {
					// Set card expiry error.
					error = ! jQuery.payment.validateCardExpiry( card_expiry.payment( 'cardExpiryVal' ) );
					card_expiry.toggleError( error );
				}

				// Disable submit button
				Give.form.fn.disable( el.parents( 'form' ), error );
				break;

			default:
				// Remove error class.
				if ( el.hasClass( 'error' ) ) {
					el.removeClass( 'error' );
				}

				if ( id.indexOf( 'card_number' ) > - 1 ) {
					// Add card related classes.
					var card_type = give_form.find( '.card-type' );

					if ( type === null ) {
						card_type.removeClass().addClass( 'off card-type' );
						el.removeClass( 'valid' ).addClass( 'error' );
					}
					else {
						card_type.removeClass().addClass( 'card-type ' + type );
					}

				} else if ( id.indexOf( 'card_expiry' ) > - 1 ) {
					// set expiry date params.
					var expiry = card_expiry.payment( 'cardExpiryVal' );

					give_form.find( '.card-expiry-month' ).val( expiry.month );
					give_form.find( '.card-expiry-year' ).val( expiry.year );
				}
		}
	} );
} );
