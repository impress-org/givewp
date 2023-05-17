/* globals jQuery, Give */
jQuery( function( $ ) {
	const $forms = jQuery( 'form.give-form' ),
		doc = $( document );

	/**
	 * Donation Form fields state
	 * @type {{forms: {}}}
	 */
	const fieldsState = {
		forms: {},
	};

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
		const $this = $( this ),
			$form = $this.parents( 'form' );

		if ( 'card_state' !== $this.attr( 'id' ) ) {
			// Disable the State field until updated
			$form.find( '#card_state' ).empty().append( '<option value="1">' + Give.fn.getGlobalVar( 'general_loading' ) + '</option>' ).prop( 'disabled', true );

			// If the country field has changed, we need to update the state/province field
			const postData = {
				action: 'give_get_states',
				country: $this.val(),
				field_name: 'card_state',
			};

			$.ajax( {
				type: 'POST',
				data: postData,
				url: Give.fn.getGlobalVar( 'ajaxurl' ),
				xhrFields: {
					withCredentials: true,
				},
				success: function( response ) {
					let new_state_field = '',
						states_label = response.states_label,
						$current_state_field = $form.find( 'input[name="card_state"], select[name="card_state"]' ),
						$city = $form.find( 'input[name="card_city"]' );

					// Get response data from states query.
					if (
						'undefined' !== typeof response.states_found &&
						true === response.states_found
					) {
						new_state_field = $( response.data );
					} else {
						new_state_field = `<input type="text" id="card_state" name="card_state" class="card_state give-input required" placeholder="${ states_label }" value="${ response.default_state }" autocomplete="address-level4"/>`;
						new_state_field = $( new_state_field );
					}

					// No float labels.
					if ( false === $form.hasClass( 'float-labels-enabled' ) ) {
						if (
							'undefined' !== typeof ( response.states_require ) &&
							true === response.states_require
						) {
							new_state_field.attr( 'required', 'required' ).attr( 'aria-required', 'true' ).addClass( 'required' );
							$current_state_field.closest( 'p' ).find( 'label .give-required-indicator' ).removeClass( 'give-hidden' );
						} else {
							new_state_field.removeAttr( 'required' ).removeAttr( 'aria-required' ).removeClass( 'required' );
							$current_state_field.closest( 'p' ).find( 'label .give-required-indicator' ).addClass( 'give-hidden' );
						}

						// check if city fields is require or not
						if ( 'undefined' !== typeof ( response.city_require ) && true === response.city_require ) {
							$city.closest( 'p' ).find( 'label .give-required-indicator' ).removeClass( 'give-hidden' ).removeClass( 'required' );
							$city.attr( 'required', true );
						} else {
							$city.closest( 'p' ).find( 'label .give-required-indicator' ).addClass( 'give-hidden' ).addClass( 'required' );
							$city.removeAttr( 'required' );
						}
					} else {
						//Had floating labels
						if (
							'undefined' !== typeof ( response.states_require ) &&
							true === response.states_require
						) {
							new_state_field.attr( 'required', 'required' ).attr( 'aria-required', 'true' ).addClass( 'required' );
							$current_state_field.closest( 'p' ).find( '.give-fl-wrap' ).addClass( 'give-fl-is-required' );
						} else {
							new_state_field.removeAttr( 'required' ).removeAttr( 'aria-required' ).removeClass( 'required' );
							$current_state_field.closest( 'p' ).find( '.give-fl-wrap' ).removeClass( 'give-fl-is-required' );
						}

						// check if city fields is require or not
						if ( 'undefined' !== typeof ( response.city_require ) && true === response.city_require ) {
							$city.closest( 'p' ).find( '.give-fl-wrap' ).addClass( 'give-fl-is-required' );
							$city.attr( 'required', true );
						} else {
							$city.closest( 'p' ).find( '.give-fl-wrap' ).removeClass( 'give-fl-is-required' );
							$city.removeAttr( 'required' );
						}
					}

					$current_state_field.closest( 'p' ).find( 'label .state-label-text' ).text( states_label );

					// Set the new state field in the DOM.
					$current_state_field.replaceWith( new_state_field );

					// Check whether the fields should show or not.
					if (
                        'undefined' !== typeof (response.show_field) &&
                        true === response.show_field
                    ) {
                        $form.find('p#give-card-state-wrap').removeClass('give-hidden');

                        // Add support to zip fields.
                        $form.find('p#give-card-zip-wrap').addClass('form-row-last');
                        $form.find('p#give-card-zip-wrap').removeClass('form-row-wide');
                    } else {
                        $form.find('p#give-card-state-wrap').addClass('give-hidden');

                        // Add support to zip fields.
                        $form.find('p#give-card-zip-wrap').addClass('form-row-wide');
                        $form.find('p#give-card-zip-wrap').removeClass('form-row-last');
                    }

                    // Check whether the post code fields should be required
                    const zipRequired = !!response.zip_require;
                    $form.find('input#card_zip').toggleClass('required', zipRequired)
                         .attr('required', zipRequired)
                         .attr('aria-required', zipRequired);
                    $form.find('label[for="card_zip"] span.give-required-indicator').toggleClass('give-hidden', !zipRequired);

                    doc.trigger('give_checkout_billing_address_updated', [response, $form.attr('id')]);
                },
            }).fail(function (data) {
                if (window.console && window.console.log) {
                    console.log(data);
                }
            });
        }

        return false;
    }

    function update_company_option_field() {
        var $form = jQuery(this).closest('form.give-form'),
            value, $field;

        if (!$form) {
            $form = jQuery(this).parents('form');
        }

        $field = $form.find('#give-company-wrap');
        value = $form.find('input[name="give_company_option"]:radio:checked').val();

        if ('yes' === value) {
            $field.show();
        } else {
            $field
                .hide()
                .find('input').val('').trigger('keyup');
        }

        jQuery(this).trigger('give_option_change');
    }

    /**
     * Update fields state
     *
     * @param {Object} e Element
     */
    function setFieldState(e) {
        const element = e.target;
        const form = element.parentElement.closest('form.give-form');

        if (form) {
            const formId = form.getAttribute('id');
            fieldsState.forms = {
                ...fieldsState.forms,
                [formId]: {
                    ...fieldsState.forms[formId],
                    [element.name]: element.value,
                },
            };
        }
    }

    /**
     * Attach events to First Name, Last Name and the Email fields
     */
    doc.on(
        'keyup give_option_change',
        '#give-first, #give-last, #give-email, #give-company-name-radio-list .give_company_option, #give-company',
        setFieldState,
    );

    /**
     * Restore saved state values when gateway changes.
     */
    doc.on(
        'give_gateway_loaded',
        function () {
            for (const [formId, formData] of Object.entries(fieldsState.forms)) {
                for (const [name, value] of Object.entries(formData)) {
                    const element = document.querySelectorAll(`form#${formId} [name="${name}"]`);
                    if (element) {
                        element.forEach(function (el) {
                            if (el.type === 'radio') {
                                el.checked = el.value === value;
                            } else {
                                el.value = value;
                            }
                        });
                    }
                }
            }
        },
    );

    // Sync state field with country.
    doc.on(
        'change',
        '#give_cc_address input.card_state, #give_cc_address select',
        update_billing_state_field,
    );

    // Trigger formatting function when gateway changes.
    doc.on(
        'give_gateway_loaded',
        function () {
            Give.form.fn.field.formatCreditCard($forms);
            doc.find('#give-company-radio-list-wrap .give_company_option:checked').trigger('change');
        },
    );

    /**
     * Show/Hide Company field based on Company option selected.
     */
    doc.on(
        'change',
        '#give-company-radio-list-wrap .give_company_option',
        update_company_option_field,
    ).find('#give-company-radio-list-wrap .give_company_option:checked').trigger('change');

    // Make sure a gateway is selected.
    doc.on(
        'submit',
        '#give_payment_mode',
        function () {
            const gateway = Give.form.fn.getGateway($(this).closest('form'));
            if (!gateway.length) {
                alert(Give.fn.getGlobalVar('no_gateway'));
                return false;
            }
        }
    );

    // Add a class to the currently selected gateway on click
    doc.on(
        'click',
        '#give-payment-mode-select input[name="payment-mode"]',
        function () {
            let $form = $(this).parents('form'),
                $gateways_li = $form.find('#give-payment-mode-select li'),
                old_payment_gateway = $form.find('li.give-gateway-option-selected input[name="payment-mode"]').val().trim(),
                new_payment_gateways;

            // Unselect all payment gateways.
            $gateways_li.removeClass('give-gateway-option-selected');
            $gateways_li.prop('checked', false);

            // Select payment gateway.
            $(this).prop('checked', true);
            $(this).parent().addClass('give-gateway-option-selected');
            $(this).focus();

            // Get new payment gateway.
            new_payment_gateways = Give.form.fn.getGateway($form);

            // Change form action.
            $form.attr('action', $form.attr('action').replace(
                'payment-mode=' + old_payment_gateway,
                'payment-mode=' + new_payment_gateways),
            );
        }
    );

	/**
	 * Custom Donation Amount Focus In
	 *
	 * @description: If user focuses on field & changes value then updates price
	 */
	doc.on( 'focus', '.give-donation-amount .give-text-input', function( e ) {
		const parent_form = $( this ).parents( 'form' );

		// Remove any invalid class
		$( this ).removeClass( 'invalid-amount' );

		// Set data amount
		const current_total = parent_form.find( '.give-final-total-amount' ).attr( 'data-total' );
		const decimal_separator = Give.form.fn.getInfo( 'decimal_separator', parent_form );
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
		let parent_form = ( 'undefined' !== typeof $parent_form ) ? $parent_form : $( this ).closest( 'form' ),
			pre_focus_amount = $( this ).attr( 'data-amount' ),
			this_value = ( 'undefined' !== typeof donation_amount ) ? donation_amount : $( this ).val(),
			decimal_separator = Give.form.fn.getInfo( 'decimal_separator', parent_form ),
			value_min = Give.form.fn.getMinimumAmount( parent_form ),
			value_max = Give.form.fn.getMaximumAmount( parent_form ),
			value_now = ( this_value === 0 ) ? value_min : Give.fn.unFormatCurrency( this_value, decimal_separator ),
			formatted_total = Give.form.fn.formatAmount( value_now, parent_form, {} );

		price_id = 'undefined' === typeof price_id ? Give.form.fn.getPriceID( parent_form, true ) : price_id;

		// https://github.com/impress-org/give/issues/3299
		// If we change from custom amount to donation level then
		// this event fire twice. First on amount field blur and second time on level button/radio/select click which cause of minimum donation notice.
		// This condition will prevent minimum donation amount notice show by set default level.
		if ( '' === value_now || 0 === value_now ) {
			const $default_level = $( '.give-donation-levels-wrap [data-default="1"]', $parent_form );

			if ( $default_level.length ) {
				price_id = $default_level.data( 'price-id' );
				this_value = value_now = Give.fn.unFormatCurrency( $default_level.val(), decimal_separator );
				formatted_total = Give.form.fn.formatAmount( value_now, parent_form, {} );
			}
		}

		// Cache donor selected price id for an amount.
		Give.fn.setCache( 'amount_' + value_now, price_id, parent_form );
		$( this ).val( formatted_total );

		// Does this number have an accepted min/max value?
		if ( ! Give.form.fn.isValidDonationAmount( parent_form ) ) {
			// It doesn't... add invalid class.
			$( this ).addClass( 'give-invalid-amount' );

			// Disable submit
			Give.form.fn.disable( parent_form, true );
			const invalid_minimum_notice = parent_form.find( '.give-invalid-minimum' ),
				invalid_maximum_notice = parent_form.find( '.give-invalid-maximum' );

			// If no error present, create it, insert, slide down (show).
			if ( 0 === invalid_minimum_notice.length && value_now < value_min ) {
				Give.notice.fn.renderNotice( 'bad_minimum', parent_form );
			} else if ( value_now >= value_min ) {
				invalid_minimum_notice.slideUp( 300, function() {
					$( this ).remove();
				} );
			}

			// For maximum custom amount error.
			if ( 0 === invalid_maximum_notice.length && value_now > value_max ) {
				Give.notice.fn.renderNotice( 'bad_maximum', parent_form );
			} else if ( value_now <= value_max ) {
				invalid_maximum_notice.slideUp( 300, function() {
					$( this ).remove();
				} );
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
						position: Give.form.fn.getInfo( 'currency_position', parent_form ),
					},
					parent_form )
				);
		}

		// Set price id for current amount.
		if ( -1 !== price_id ) {
			// Auto set give price id.
			$( 'input[name="give-price-id"]', parent_form ).val( price_id );

            // Update hidden amount field
            const hiddenAmountField = parent_form.find('.give-amount-hidden');
            if (hiddenAmountField) {
                hiddenAmountField.val(Give.form.fn.formatAmount(value_now, parent_form, {}));
                // Trigger change event.
                // We use amount field classes to trigger change event on input field,
                // But when custom amount disabled then we use hidden field to store amount and span HTML tag to show amount.
                // This means, if logic depends on amount change event (field with "give-amount" name) then it will not work.
                // So, it is required to trigger change event on 'give-amount' hidden field.
                // For example: Form field manager (feature: conditional field visibility)
                hiddenAmountField.trigger('change');
            }

			// Remove old selected class & add class for CSS purposes
			parent_form.find( '.give-default-level' ).removeClass( 'give-default-level' );

			// Auto select variable price items ( Radio/Button/Select ).
			Give.form.fn.autoSelectDonationLevel( parent_form, price_id );
		}

		// This class is used for CSS purposes
		$( this ).parent( '.give-donation-amount' )
			.removeClass( 'give-custom-amount-focus-in' );

		// Trigger an event for hooks
		$( document ).trigger( 'give_donation_value_updated', [ parent_form, value_now, price_id ] );
	} );

	// Multi-level Buttons: Update Amount Field based on Multi-level Donation Select
	doc.on( 'click', '.give-donation-level-btn', function( e ) {
		e.preventDefault(); //don't let the form submit
		Give.form.fn.autoSetMultiLevel( $( this ) );
	} );

	// Multi-level Radios: Update Amount Field based on Multi-level Donation Select
	doc.on( 'click', '.give-radio-input-level', function( e ) {
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
		const $fieldset_wrapper = $( this ).closest( 'fieldset' );
		$( '[class^=give_terms-]', $fieldset_wrapper ).slideToggle();
		$( 'a.give_terms_links', $fieldset_wrapper ).toggle();
		return false;
	} );

	/**
	 * Prevent level jump which happens due to same id.
	 * @see https://github.com/impress-org/give/issues/2292
	 */
	$( 'label[for^="give-radio-level"]' ).on( 'click', function( e ) {
		const $form = $( this ).closest( 'form' ),
			$inputField = $form.find( '#' + $( this ).attr( 'for' ) );

		if ( $inputField.length ) {
			$inputField.trigger( 'click' );
			e.preventDefault();
		}
	} );
} );

jQuery( window ).on( 'load', function() {

    /**
     * Validate cc fields on change
     */
    jQuery('body').on('keyup change focusout', '.give-form .card-number, .give-form .card-cvc, .give-form .card-expiry', function (e) {
        let el = jQuery(this),
            give_form = el.parents('form.give-form'),
            id = el.attr('id'),
            card_number = give_form.find('.card-number'),
            card_cvc = give_form.find('.card-cvc'),
            card_expiry = give_form.find('.card-expiry'),
            type = jQuery.payment.cardType(card_number.val()),
            error = false;

        switch (e.type) {
            case 'focusout':
                if (id.indexOf('card_number') > -1) {
                    // Set card number error.
                    error = !jQuery.payment.validateCardNumber(card_number.val());
                    card_number.toggleError(error);
                } else if (id.indexOf('card_cvc') > -1) {
                    // Set card cvc error.
                    error = !jQuery.payment.validateCardCVC(card_cvc.val(), type);
                    card_cvc.toggleError(error);
                } else if (id.indexOf('card_expiry') > -1) {
                    // Set card expiry error.
                    error = !jQuery.payment.validateCardExpiry(card_expiry.payment('cardExpiryVal'));
                    card_expiry.toggleError(error);
                }

                // Disable submit button
                Give.form.fn.disable(el.parents('form'), error);
                break;

            default:
                // Remove error class.
                if (el.hasClass('error')) {
                    el.removeClass('error');
                }

                if (id.indexOf('card_number') > -1) {
                    // Add card related classes.
                    const card_type = give_form.find('.card-type');

                    if (type === null) {
                        card_type.removeClass().addClass('off card-type');
                        el.removeClass('valid').addClass('error');
                    } else {
                        card_type.removeClass().addClass('card-type ' + type);
                    }
                } else if (id.indexOf('card_expiry') > -1) {
                    // set expiry date params.
                    const expiry = card_expiry.payment('cardExpiryVal');

                    give_form.find('.card-expiry-month').val(expiry.month);
                    give_form.find('.card-expiry-year').val(expiry.year);
                }
		}
	} );
} );
