/*!
 * Give Form Checkout JS
 *
 * @description: Handles JS functionality for the donation form checkout
 * @package:     Give
 * @subpackage:  Assets/JS
 * @copyright:   Copyright (c) 2016, WordImpress
 * @license:     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 */
var give_scripts, give_global_vars;

jQuery(function ($) {

	var doc = $(document);

	/**
	 * Update state/province fields per country selection
	 */
	function update_billing_state_field() {
		var $this = $(this),
			$form = $this.parents('form');
		if ('card_state' != $this.attr('id')) {

			//Disable the State field until updated
			$form.find('#card_state').empty().append('<option value="1">' + give_global_vars.general_loading + '</option>').prop('disabled', true);

			// If the country field has changed, we need to update the state/province field
			var postData = {
				action    : 'give_get_states',
				country   : $this.val(),
				field_name: 'card_state'
			};

			$.ajax({
				type     : 'POST',
				data     : postData,
				url      : give_global_vars.ajaxurl,
				xhrFields: {
					withCredentials: true
				},
				success  : function (response) {
					if ('nostates' == response) {
						var text_field = '<input type="text" id="card_state" name="card_state" class="cart-state give-input required" value=""/>';
						$form.find('input[name="card_state"], select[name="card_state"]').replaceWith(text_field);
					} else {
						$form.find('input[name="card_state"], select[name="card_state"]').replaceWith(response);
					}
					doc.trigger('give_checkout_billing_address_updated', [response, $form.attr('id')]);
				}
			}).fail(function (data) {
				if (window.console && window.console.log) {
					console.log(data);
				}
			});
		}

		return false;
	}

	doc.on('change', '#give_cc_address input.card_state, #give_cc_address select', update_billing_state_field
	);

	/**
	 * Format CC Fields
	 * @description Set variables and format cc fields
	 * @since 1.2
	 */
	function format_cc_fields() {
		give_form = $('form.give-form');

		//Loop through forms on page and set CC validation
		give_form.each(function () {
			var card_number = $(this).find('.card-number');
			var card_cvc    = $(this).find('.card-cvc');
			var card_expiry = $(this).find('.card-expiry');

			//Only validate if there is a card field
			if (card_number.length === 0) {
				return false;
			}

			card_number.payment('formatCardNumber');
			card_cvc.payment('formatCardCVC');
			card_expiry.payment('formatCardExpiry');
		});

	}

	format_cc_fields();

	// Trigger formatting function when gateway changes
	doc.on('give_gateway_loaded', function () {
		format_cc_fields();
	});

	// Toggle validation classes
	$.fn.toggleError = function (errored) {
		this.toggleClass('error', errored);
		this.toggleClass('valid', !errored);

		return this;
	};

	/**
	 * Validate cc fields on change
	 */
	doc.on('keyup change', '.give-form .card-number, .give-form .card-cvc, .give-form .card-expiry', function () {
		var el          = $(this),
			give_form   = el.parents('form.give-form'),
			id          = el.attr('id'),
			card_number = give_form.find('.card-number'),
			card_cvc    = give_form.find('.card-cvc'),
			card_expiry = give_form.find('.card-expiry'),
			type        = $.payment.cardType(card_number.val());

		if (id.indexOf('card_number') > -1) {

			var card_type = give_form.find('.card-type');

			if (type === null) {
				card_type.removeClass().addClass('off card-type');
				el.removeClass('valid').addClass('error');
			}
			else {
				card_type.removeClass().addClass('card-type ' + type);
			}

			card_number.toggleError(!$.payment.validateCardNumber(card_number.val()));
		}
		if (id.indexOf('card_cvc') > -1) {

			card_cvc.toggleError(!$.payment.validateCardCVC(card_cvc.val(), type));
		}
		if (id.indexOf('card_expiry') > -1) {

			card_expiry.toggleError(!$.payment.validateCardExpiry(card_expiry.payment('cardExpiryVal')));

			var expiry = card_expiry.payment('cardExpiryVal');

			give_form.find('.card-expiry-month').val(expiry.month);
			give_form.find('.card-expiry-year').val(expiry.year);
		}
	});

	/**
	 * Format Currency
	 *
	 * @description format the currency with accounting.js
	 * @param price
	 * @param args object
	 * @returns {*|string}
	 */
	function give_format_currency(price, args) {

		//Properly position symbol after if selected
		if (give_global_vars.currency_pos == 'after') {
			args.format = "%v%s";
		}

		return accounting.formatMoney(price, args).trim();

	}

	/**
	 * Unformat Currency
	 *
	 * @param price
	 * @returns {number}
	 */
	function give_unformat_currency(price) {
		return Math.abs(parseFloat(accounting.unformat(price, give_global_vars.decimal_separator)));
	}

	/**
	 * Get formatted amount
	 *
	 * @param {string/number} amount
	 */
	function give_format_amount(amount) {

		//Set the custom amount input value format properly
		var format_args = {
			symbol   : '',
			decimal  : give_global_vars.decimal_separator,
			thousand : give_global_vars.thousands_separator,
			precision: give_global_vars.number_decimals
		};

		return accounting.formatMoney(amount, format_args);
	}

	/**
	 * Get Price ID and levels for multi donation form
	 *
	 * @param   {Object} $form Form jQuery object
	 *
	 * @returns {Object}
	 */
	function give_get_variable_prices($form) {
		var variable_prices = [];

		// check if currect form type is muti or not.
		if (!$form.hasClass('give-form-type-multi')) {
			return variable_prices;
		}

		$.each($form.find('.give-donation-levels-wrap [data-price-id] '), function (index, item) {
			// Get Jquery instance for item.
			item = (
				!(
					item instanceof jQuery
				) ? jQuery(item) : item
			);

			// Add price id and amount to collector.
			variable_prices.push({
				price_id: item.data('price-id'),
				amount  : give_unformat_currency(item.val())
			});
		});

		return variable_prices;
	}

	// Make sure a gateway is selected
	doc.on('submit', '#give_payment_mode', function () {
		var gateway = $('#give-gateway option:selected').val();
		if (gateway == 0) {
			alert(give_global_vars.no_gateway);
			return false;
		}
	});

	// Add a class to the currently selected gateway on click
	doc.on('click', '#give-payment-mode-select input', function () {
		var $form                = $(this).parents('form'),
			$gateways_li         = $('#give-payment-mode-select li'),
			old_payment_gateway  = $('#give-payment-mode-select li.give-gateway-option-selected input[name="payment-mode"]').val(),
			new_payment_gateways = '';

		// Unselect all payment gateways.
		$gateways_li.removeClass('give-gateway-option-selected');
		$gateways_li.prop('checked', false);

		// Select payment gateway.
		$(this).prop('checked', true);
		$(this).parent().addClass('give-gateway-option-selected');

		// Get new payment gateway.
		new_payment_gateways = $('#give-payment-mode-select li.give-gateway-option-selected input[name="payment-mode"]').val();

		// Change form action.
		$form.attr('action', $form.attr('action').replace(
			'payment-mode=' + old_payment_gateway,
			'payment-mode=' + new_payment_gateways)
		);
	});

	/**
	 * Custom Donation Amount Focus In
	 *
	 * @description: If user focuses on field & changes value then updates price
	 */
	doc.on('focus', '.give-donation-amount .give-text-input', function (e) {

		var parent_form = $(this).parents('form');

		//Remove any invalid class
		$(this).removeClass('invalid-amount');

		//Set data amount
		var current_total = parent_form.find('.give-final-total-amount').data('total');
		$(this).data('amount', give_unformat_currency(current_total));

		//This class is used for CSS purposes
		$(this).parent('.give-donation-amount').addClass('give-custom-amount-focus-in');

		//Set Multi-Level to Custom Amount Field
		parent_form.find('.give-default-level, .give-radio-input').removeClass('give-default-level');
		parent_form.find('.give-btn-level-custom').addClass('give-default-level');
		parent_form.find('.give-radio-input').prop('checked', false); //Radio
		parent_form.find('.give-radio-input.give-radio-level-custom').prop('checked', true); //Radio
		parent_form.find('.give-select-level').prop('selected', false); //Select
		parent_form.find('.give-select-level .give-donation-level-custom').prop('selected', true); //Select

	});

	/**
	 * Custom Donation Focus Out
	 *
	 * @description: Fires on focus end aka "blur"
	 *
	 */
	doc.on('blur', '.give-donation-amount .give-text-input', function (e, $parent_form, donation_amount, price_id) {
		var parent_form      = ($parent_form != undefined) ? $parent_form : $(this).closest('form'),
			pre_focus_amount = $(this).data('amount'),
			this_value       = (donation_amount != undefined) ? donation_amount : $(this).val(),
			$minimum_amount  = parent_form.find('input[name="give-form-minimum"]'),
			value_min        = give_unformat_currency($minimum_amount.val()),
			value_now        = (this_value == 0) ? value_min : give_unformat_currency(this_value),
			variable_prices  = give_get_variable_prices($(this).parents('form')),
			error_msg        = '';

		/**
		 * Flag Multi-levels for min. donation conditional.
		 *
		 * Note: Value of this variable will be:
		 *  a. -1      if no any level found.
		 *  b. [0-*]   Any number from zero if donation level found.
		 *  c  custom  if donation level not found and donation amount is greater than the custom minimum amount.
		 *
		 * @type {number/string} Donation level ID.
		 */
		price_id = (
			undefined != price_id
		) ? price_id : -1;

		//Set the custom amount input value format properly
		var format_args = {
			symbol   : '',
			decimal  : give_global_vars.decimal_separator,
			thousand : give_global_vars.thousands_separator,
			precision: give_global_vars.number_decimals
		};

		var formatted_total = give_format_currency(value_now, format_args);
		$(this).val(formatted_total);

		// Find price id with amount in variable prices.
		if (
			variable_prices.length
			&& !(-1 < price_id )
		) {

			// Find amount in donation levels.
			$.each(variable_prices, function (index, variable_price) {
				if (variable_price.amount === value_now) {
					price_id = variable_price.price_id;
					return false;
				}
			});

			// Set level to custom.
			if (
				!(-1 < price_id)
				&& (value_min <= value_now)
			) {
				price_id = 'custom';
			}
		}

		//Does this number have an accepted minimum value?
		if (
			(value_now < value_min || value_now < 1)
			&& (-1 === price_id)
		) {

			//It doesn't... Invalid Minimum
			$(this).addClass('give-invalid-amount');
			format_args.symbol = give_global_vars.currency_sign;
			error_msg          = give_global_vars.bad_minimum + ' ' + give_format_currency(value_min, format_args);

			//Disable submit
			parent_form.find('.give-submit').prop('disabled', true);
			var invalid_minimum = parent_form.find('.give-invalid-minimum');

			//If no error present, create it, insert, slide down (show)
			if (invalid_minimum.length === 0) {
				var error = $('<div class="give_error give-invalid-minimum">' + error_msg + '</div>').hide();
				error.insertBefore(parent_form.find('.give-total-wrap')).show();
			}

		} else {

			// Remove error massage class from price field.
			$(this).removeClass('give-invalid-amount');

			//Minimum amount met - slide up error & remove it from DOM
			parent_form.find('.give-invalid-minimum').slideUp(300, function () {
				$(this).remove();
			});

			//Re-enable submit
			parent_form.find('.give-submit').prop('disabled', false);

		}

		//If values don't match up then proceed with updating donation total value
		if (pre_focus_amount !== value_now) {

			//update donation total (include currency symbol)
			format_args.symbol = give_global_vars.currency_sign;
			parent_form.find('.give-final-total-amount').data('total', value_now).text(give_format_currency(value_now, format_args));

		}

		// Set price id for current amount.
		if (-1 !== price_id) {

			// Auto set give price id.
			$('input[name="give-price-id"]', parent_form).val(price_id);

			// Update hidden amount field
			parent_form.find('.give-amount-hidden').val(give_format_amount(value_now));

			// Remove old selected class & add class for CSS purposes
			parent_form.find('.give-default-level').removeClass('give-default-level');

			// Auto select variable price items ( Radio/Button/Select ).
			switch (true) {

				// Auto select radio button.
				case (
					!!parent_form.find('.give-radio-input').length
				) :
					parent_form.find('.give-radio-input').prop('checked', false);
					parent_form.find('.give-radio-input[data-price-id="' + price_id + '"]')
						.prop('checked', true)
						.addClass('give-default-level');
					break;

				// Set focus to price id button.
				case (
					!!parent_form.find('button.give-donation-level-btn').length
				) :
					parent_form.find('button.give-donation-level-btn').blur();
					parent_form.find('button.give-donation-level-btn[data-price-id="' + price_id + '"]')
						.focus()
						.addClass('give-default-level');
					break;

				// Auto select option.
				case (
					!!parent_form.find('select.give-select-level').length
				) :
					parent_form.find('select.give-select-level option').prop('selected', false);
					parent_form.find('select.give-select-level option[data-price-id="' + price_id + '"]')
						.prop('selected', true)
						.addClass('give-default-level');
					break;

			}
		}

		//This class is used for CSS purposes
		$(this).parent('.give-donation-amount').removeClass('give-custom-amount-focus-in');

	});

	//Multi-level Buttons: Update Amount Field based on Multi-level Donation Select
	doc.on('click touchend', '.give-donation-level-btn', function (e) {
		e.preventDefault(); //don't let the form submit
		update_multiselect_vals($(this));
	});

	//Multi-level Radios: Update Amount Field based on Multi-level Donation Select
	doc.on('click touchend', '.give-radio-input-level', function (e) {
		update_multiselect_vals($(this));
	});

	//Multi-level Radios: Update Amount Field based on Multi-level Donation Select
	doc.on('change', '.give-select-level', function (e) {
		update_multiselect_vals($(this));
	});

	/**
	 * Update Multiselect Values
	 *
	 * @description Helper function: Sets the multiselect amount values
	 *
	 * @param selected_field
	 * @returns {boolean}
	 */
	function update_multiselect_vals(selected_field) {

		var $parent_form = selected_field.parents('form'),
			this_amount  = selected_field.val(),
			price_id     = selected_field.data('price-id');

		// Check if price ID blank because of dropdown type
		if (!price_id) {
			price_id = selected_field.find('option:selected').data('price-id');
		}

		// Is this a custom amount selection?
		if (this_amount === 'custom') {
			//It is, so focus on the custom amount input
			$parent_form.find('.give-amount-top').val('').focus();
			return false; //Bounce out
		}

		//update custom amount field
		$parent_form.find('.give-amount-top').val(this_amount);
		$parent_form.find('span.give-amount-top').text(this_amount);

		// Cache previous amount and set data amount.
		$('.give-donation-amount .give-text-input', $parent_form)
			.data(
				'amount',
				give_unformat_currency(
					$parent_form.find('.give-final-total-amount').data('total')
				)
			);

		// Manually trigger blur event with two params:
		// (a) form jquery object
		// (b) price id
		// (c) donation amount
		$parent_form.find('.give-donation-amount .give-text-input').trigger('blur', [
			$parent_form,
			this_amount,
			price_id
		]);

		// trigger an event for hooks
		$(document).trigger('give_donation_value_updated', [$parent_form, this_amount, price_id]);
	}

	/**
	 * Donor sent back to the form
	 */
	function sent_back_to_form() {

		var form_id      = give_get_parameter_by_name('form-id');
		var payment_mode = give_get_parameter_by_name('payment-mode');

		// Sanity check - only proceed if query strings in place.
		if (!form_id || !payment_mode) {
			return false;
		}

		var form_wrap      = $('body').find('#give-form-' + form_id + '-wrap');
		var form           = form_wrap.find('form.give-form');
		var display_modal  = form_wrap.hasClass('give-display-modal');
		var display_reveal = form_wrap.hasClass('give-display-reveal');

		// Update payment mode radio so it's correctly checked.
		form.find('#give-gateway-radio-list label').removeClass('give-gateway-option-selected');
		form.find('input[name=payment-mode][value=' + payment_mode + ']').prop('checked', true).parent().addClass('give-gateway-option-selected');

		// Select the proper level for Multi-level forms.
		// It can either be a dropdown, buttons, or radio list. Default is buttons field type.
		var level_id    = give_get_parameter_by_name('level-id');
		var level_field = form.find('*[data-price-id="' + level_id + '"]');
		if (level_field.length > 0) {
			update_multiselect_vals(level_field);
		}

		// This form is modal display so show the modal.
		if (display_modal) {
			give_open_form_modal(form_wrap, form);
		} else if (display_reveal) {
			// This is a reveal form, show it.
			form.find('.give-btn-reveal').hide();
			form.find('#give-payment-mode-select, #give_purchase_form_wrap').slideDown();
		}

	}

	sent_back_to_form();

	/**
	 * Get Parameter by Name
	 *
	 * @see: http://stackoverflow.com/questions/901115/how-can-i-get-query-string-values-in-javascript
	 * @param name
	 * @param url
	 * @since 1.4.2
	 * @returns {*}
	 */
	function give_get_parameter_by_name(name, url) {
		if (!url) {
			url = window.location.href;
		}
		name        = name.replace(/[\[\]]/g, "\\$&");
		var regex   = new RegExp("[?&]" + name + "(=([^&#]*)|&|#|$)"),
			results = regex.exec(url);
		if (!results) {
			return null;
		}
		if (!results[2]) {
			return '';
		}
		return decodeURIComponent(results[2].replace(/\+/g, " "));
	}

	/**
	 * Show/Hide term and condition
	 */
	doc.on('click', '.give_terms_links', function (e) {
		e.preventDefault();
		var $fieldset_wrapper = $(this).closest('fieldset');
		$('[class^=give_terms-]', $fieldset_wrapper).slideToggle();
		$('a.give_terms_links', $fieldset_wrapper).toggle();
		return false;
	});

});
