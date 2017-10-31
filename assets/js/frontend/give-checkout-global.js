/*!
 * Give Form Checkout JS
 *
 * @description: Handles JS functionality for the donation form checkout
 * @package:     Give
 * @subpackage:  Assets/JS
 * @copyright:   Copyright (c) 2016, WordImpress
 * @license:     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 */

/* global jQuery, accounting */
var give_scripts, give_global_vars;
var Give = 'undefined' !== typeof Give ? Give : {};

Give.form = {
	init: function () {
		this.fn.field.formatCreditCard(jQuery('form.give-form'));
		this.fn.__sendBackToForm();
	},

	fn: {
		/**
		 * Get formatted amount
		 *
		 * @param {string/number} amount
		 * @param {object} $form
		 * @param {object} args
		 */
		formatFormAmount: function (amount, $form, args) {
			// Do not format amount if form did not exist.
			if (!$form.length) {
				return amount;
			}

			return Give.form.fn.formatCurrency(amount, args, $form);
		},

		/**
		 * Format Currency
		 *
		 * @description format the currency with accounting.js
		 * @param {string} price
		 * @param {object}  args
		 * @param {object} $form
		 * @returns {*|string}
		 */
		formatCurrency: function (price, args, $form) {
			// Global currency setting.
			var format_args = {
				decimal: parseInt(give_global_vars.decimal_separator),
				thousand: give_global_vars.thousands_separator,
				precision: give_global_vars.number_decimals,
				currency: give_global_vars.currency
			};

			price = price.toString().trim();
			$form = 'undefined' === typeof $form ? {} : $form;

			// Form specific currency setting.
			if ($form.length) {
				//Set the custom amount input value format properly
				format_args = {
					decimal: Give.form.fn.getFormInfo('decimal_separator', $form),
					thousand: Give.form.fn.getFormInfo('thousands_separator', $form),
					precision: Give.form.fn.getFormInfo('number_decimals', $form),
					currency: Give.form.fn.getFormInfo('currency_code', $form),
				};
			}

			args = jQuery.extend(format_args, args);

			// Make sure precision is integer type
			args.precision = parseInt( args.precision );

			if ('INR' === args.currency) {
				var actual_price = accounting.unformat(price, args.decimal).toString();

				var decimal_amount = '',
					result,
					amount,
					decimal_index = actual_price.indexOf('.');

				if (( -1 !== decimal_index ) && args.precision) {
					decimal_amount = Number(actual_price.substr(parseInt(decimal_index)))
						.toFixed(args.precision)
						.toString()
						.substr(1);
					actual_price = actual_price.substr(0, parseInt(decimal_index));

					if (!decimal_amount.length) {
						decimal_amount = '.0000000000'.substr(0, ( parseInt(decimal_index) + 1 ));
					} else if (( args.precision + 1 ) > decimal_amount.length) {
						decimal_amount = ( decimal_amount + '000000000' ).substr(0, args.precision + 1);
					}
				}

				// Extract last 3 from amount
				result = actual_price.substr(-3);
				amount = actual_price.substr(0, parseInt(actual_price.length) - 3);

				// Apply digits 2 by 2
				while (amount.length > 0) {
					result = amount.substr(-2) + args.thousand + result;
					amount = amount.substr(0, parseInt(amount.length) - 2);
				}

				if (decimal_amount.length) {
					result = result + decimal_amount;
				}

				price = result;

				if (undefined !== args.symbol && args.symbol.length) {
					if ('after' === args.position) {
						price = price + args.symbol;
					} else {
						price = args.symbol + price;
					}
				}
			} else {
				//Properly position symbol after if selected
				if ('after' === args.position) {
					args.format = "%v%s";
				}

				price = accounting.formatMoney(price, args);
			}

			return price;

		},

		/**
		 * Unformat Currency
		 *
		 * @param price
		 * @param {string} decimal_separator
		 * @returns {number}
		 */
		unformatCurrency: function (price, decimal_separator) {
			return Math.abs(parseFloat(accounting.unformat(price, decimal_separator)));
		},

		/**
		 * Get Parameter by Name
		 *
		 * @see: http://stackoverflow.com/questions/901115/how-can-i-get-query-string-values-in-javascript
		 * @param name
		 * @param url
		 * @since 1.4.2
		 * @returns {*}
		 */
		getParameterByName: function (name, url) {
			if (!url) {
				url = window.location.href;
			}

			name = name.replace(/[\[\]]/g, "\\$&");

			var regex = new RegExp("[?&]" + name + "(=([^&#]*)|&|#|$)"),
				results = regex.exec(url);

			if (!results) {
				return null;
			}

			if (!results[2]) {
				return '';
			}

			return decodeURIComponent(results[2].replace(/\+/g, " "));
		},

		/**
		 * Get information from global var
		 *
		 * @since 1.8.17
		 * @param {string} str
		 *
		 * @return {string}
		 */
		getGlobalVar: function (str) {
			if ('undefined' === give_global_vars[str]) {
				return '';
			}

			return give_global_vars[str];
		},


		/**
		 * Get form information
		 *
		 * @since 1.8.17
		 * @param {string} str
		 * @param {object} $form
		 *
		 * @return {string}
		 */
		getFormInfo: function (str, $form) {
			var data = '';
			$form = 'undefined' !== typeof $form ? $form : {};

			// Bailout.
			if (!str.length || !$form.length) {
				return data;
			}

			switch (str) {
				case 'gateways':
					data = [];
					jQuery.each($form.find('input[name="payment-mode"]'), function (index, gateway) {
						gateway = !( gateway instanceof jQuery ) ? jQuery(gateway) : gateway;
						data.push(gateway.val().trim());
					});
					break;

				default:
					if ($form.get(0).hasAttribute('data-' + str)) {
						data = $form.attr('data-' + str);
					} else {
						data = $form.attr(str);
					}

					'undefined' !== typeof data ? data.trim() : data;
			}

			return data;
		},

		/**
		 * Set form information
		 *
		 * @since 1.8.17
		 * @param {string} str
		 * @param {string} val
		 * @param {object} $form
		 * @param {string} type
		 *
		 * @return {string|boolean}
		 */
		setFormInfo: function (str, val, $form, type) {
			// Bailout.
			if (!str.length || !$form.length) {
				return false;
			}

			type = 'undefined' === typeof type ? 'data' : type;

			switch (type) {
				case 'attr':
					$form.attr(str, val);
					break;

				default:
					$form.data(str, val);
					break;
			}

			return true;
		},

		/**
		 * Get formatted amount
		 *
		 * @since 1.8.17
		 * @param {object} $form
		 */
		getFormGateway: function ($form) {
			var gateway = '';

			if (!$form.length) {
				return gateway;
			}

			gateway = $form.find('input[name="payment-mode"]:checked').val().trim();

			return 'undefined' !== gateway ? gateway : '';
		},

		/**
		 * Get Price ID and levels for multi donation form
		 *
		 * @param   {Object} $form Form jQuery object
		 *
		 * @returns {Object}
		 */
		getFormVariablePrices: function ($form) {
			var variable_prices = [], formLevels;

			// check if correct form type is multi or not.
			if (
				!$form.length ||
				!$form.hasClass('give-form-type-multi') ||
				!( formLevels = $form.find('.give-donation-levels-wrap [data-price-id] ') )
			) {
				return variable_prices;
			}

			jQuery.each(formLevels, function (index, item) {
				// Get Jquery instance for item.
				item = !(item instanceof jQuery) ? jQuery(item) : item;

				var decimal_separator = Give.form.fn.getFormInfo('decimal_separator', $form);

				// Add price id and amount to collector.
				variable_prices.push({
					price_id: item.data('price-id'),
					amount: Give.form.fn.unformatCurrency(item.val(), decimal_separator)
				});
			});

			return variable_prices;
		},

		/**
		 * Get form price ID
		 *
		 * @since 1.8.17
		 * @param {object} $form
		 *
		 * @return {string}
		 */
		getFormPriceID: function ($form) {
			var variable_prices = this.getFormVariablePrices($form),
				current_amount = this.unformatCurrency(
					$form.find('input[name="give-amount"]').val(),
					this.getFormInfo('decimal_separator', $form)
				),

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
				price_id = -1;


			// Find price id with amount in variable prices.
			if (variable_prices.length) {

				// Find amount in donation levels.
				jQuery.each(variable_prices, function (index, variable_price) {
					if (variable_price.amount === current_amount) {
						price_id = variable_price.price_id;
						return false;
					}
				});

				// Set level to custom.
				if (-1 === price_id && this.getFormMinimumAmount($form) <= current_amount) {
					price_id = 'custom';
				}
			}

			return price_id;
		},

		/**
		 * Get form minimum amount
		 *
		 * @since 1.8.17
		 * @param {object} $form
		 *
		 * @return {string}
		 */
		getFormMinimumAmount: function ($form) {
			return Give.form.fn.unformatCurrency($form.find('input[name="give-form-minimum"]').val());
		},


		/**
		 * Get form amount
		 *
		 * @since 1.8.17
		 * @param $form
		 * @return {*}
		 */
		getFormAmount: function ($form) {
			// Bailout
			if (!$form.length) {
				return null;
			}

			var amount = $form.find('input[name="give-amount"]').val();

			if ('undefined' === amount || !amount) {
				amount = 0;
			}

			return this.unformatCurrency(amount, this.getFormInfo('decimal_separator', $form));
		},

		/**
		 * Get error notice
		 *
		 * @since 1.8.17
		 * @param {string} error_code
		 * @param {object} $form
		 *
		 * @return {*}
		 */
		getNotice: function (error_code, $form) {
			// Bailout.
			if (!error_code.length) {
				return null;
			}

			var notice = '';

			switch (error_code) {
				case 'bad_minimum':
					if ($form.length) {
						notice = Give.form.fn.getGlobalVar('bad_minimum') +
							' ' +
							Give.form.fn.formatCurrency(
								this.getFormMinimumAmount($form),
								{symbol: Give.form.fn.getFormInfo('currency_symbol', $form)},
								$form
							);
					}
					break;
			}

			return notice;
		},

		/**
		 * Donor sent back to the form
		 *
		 * @since 1.8.17
		 * @access private
		 */
		__sendBackToForm: function () {

			var form_id = this.getParameterByName('form-id'),
				payment_mode = this.getParameterByName('payment-mode');

			// Sanity check - only proceed if query strings in place.
			if (!form_id || !payment_mode) {
				return false;
			}

			var $form_wrapper = jQuery('body').find('#give-form-' + form_id + '-wrap'),
				$form = $form_wrapper.find('form.give-form'),
				display_modal = $form_wrapper.hasClass('give-display-modal'),
				display_reveal = $form_wrapper.hasClass('give-display-reveal');

			// Update payment mode radio so it's correctly checked.
			$form.find('#give-gateway-radio-list label')
				.removeClass('give-gateway-option-selected');
			$form.find('input[name=payment-mode][value=' + payment_mode + ']')
				.prop('checked', true)
				.parent()
				.addClass('give-gateway-option-selected');

			// Select the proper level for Multi-level forms.
			// It can either be a dropdown, buttons, or radio list. Default is buttons field type.
			var level_id = this.getParameterByName('level-id'),
				level_field = $form.find('*[data-price-id="' + level_id + '"]');

			if (level_field.length > 0) {
				update_multiselect_vals(level_field);
			}

			// This form is modal display so show the modal.
			if (display_modal) {
				give_open_form_modal($form_wrapper, $form);
			} else if (display_reveal) {
				// This is a reveal form, show it.
				$form.find('.give-btn-reveal').hide();
				$form.find('#give-payment-mode-select, #give_purchase_form_wrap').slideDown();
			}

		},

		/**
		 * Check if donation amount valid or not
		 * @since 1.8.17
		 *
		 * @param {object} $form
		 *
		 * @return {boolean}
		 */
		isValidDonationAmount: function ($form) {
			var min_amount = this.getFormMinimumAmount($form),
				amount = this.getFormAmount($form),
				price_id = this.getFormPriceID($form);

			return (
				( ( -1 < amount ) && ( amount > min_amount ) ) ||
				( -1 !== price_id )
			);
		},

		field: {
			/**
			 * Format CC Fields
			 * @description Set variables and format cc fields
			 * @since 1.2
			 *
			 * @param {object} $forms
			 */
			formatCreditCard: function ($forms) {
				//Loop through forms on page and set CC validation
				$forms.each(function (index, form) {
					form = jQuery(form);
					var card_number = form.find('.card-number'),
						card_cvc = form.find('.card-cvc'),
						card_expiry = form.find('.card-expiry');

					//Only validate if there is a card field
					if (card_number.length) {
						card_number.payment('formatCardNumber');
						card_cvc.payment('formatCardCVC');
						card_expiry.payment('formatCardExpiry');
					}
				});
			}
		}
	}
};


jQuery(function ($) {

	var $forms = jQuery('form.give-form'),
		doc = $(document);

	// Toggle validation classes
	$.fn.toggleError = function (errored) {
		this.toggleClass('error', errored);
		this.toggleClass('valid', !errored);

		return this;
	};

	// Initialize Give object.
	Give.form.init();

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
				action: 'give_get_states',
				country: $this.val(),
				field_name: 'card_state'
			};

			$.ajax({
				type: 'POST',
				data: postData,
				url: give_global_vars.ajaxurl,
				xhrFields: {
					withCredentials: true
				},
				success: function (response) {
					var html = "";
					var states_label = response.states_label;
					if (typeof ( response.states_found ) != undefined && true == response.states_found) {
						html = response.data;
					} else {
						html = '<input type="text" id="card_state"  name="card_state" class="cart-state give-input required" placeholder="' + states_label + '" value="' + response.default_state + '"/>';
					}

					if (false === $form.hasClass('float-labels-enabled')) {
						if (typeof ( response.states_require ) != 'undefined' && true == response.states_require) {
							$form.find('input[name="card_state"], select[name="card_state"]').closest('p').find('label .give-required-indicator').removeClass('give-hidden');
						} else {
							$form.find('input[name="card_state"], select[name="card_state"]').closest('p').find('label .give-required-indicator').addClass('give-hidden');
						}
					} else {
						$form.find('input[name="card_state"], select[name="card_state"]').closest('p').find('label').text(states_label);
					}

					$form.find('input[name="card_state"], select[name="card_state"]').closest('p').find('label .state-label-text').text(states_label);
					$form.find('input[name="card_state"], select[name="card_state"]').replaceWith(html);

					// Check if user want to show the feilds or not.
					if (typeof ( response.show_field ) != undefined && true == response.show_field) {
						$form.find('p#give-card-state-wrap').removeClass('give-hidden');
					} else {
						$form.find('p#give-card-state-wrap').addClass('give-hidden');
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

	// Sync state field with country.
	doc.on(
		'change',
		'#give_cc_address input.card_state, #give_cc_address select',
		update_billing_state_field
	);

	// Trigger formatting function when gateway changes.
	doc.on(
		'give_gateway_loaded',
		function () {
			Give.form.fn.field.formatCreditCard($forms);
		}
	);

	// Make sure a gateway is selected.
	doc.on(
		'submit',
		'#give_payment_mode',
		function () {
			var gateway = Give.form.fn.getFormGateway($(this).closest('form'));
			if (!gateway.length) {
				alert(Give.form.fn.getGlobalVar('no_gateway'));
				return false;
			}
		}
	);

	// Add a class to the currently selected gateway on click
	doc.on(
		'click',
		'#give-payment-mode-select input',
		function () {
			var $form = $(this).parents('form'),
				$gateways_li = $form.find('#give-payment-mode-select li'),
				old_payment_gateway = $form.find('li.give-gateway-option-selected input[name="payment-mode"]').val().trim(),
				new_payment_gateways;

			// Unselect all payment gateways.
			$gateways_li.removeClass('give-gateway-option-selected');
			$gateways_li.prop('checked', false);

			// Select payment gateway.
			$(this).prop('checked', true);
			$(this).parent().addClass('give-gateway-option-selected');

			// Get new payment gateway.
			new_payment_gateways = Give.form.fn.getFormGateway($form);

			// Change form action.
			$form.attr('action', $form.attr('action').replace(
				'payment-mode=' + old_payment_gateway,
				'payment-mode=' + new_payment_gateways)
			);
		}
	);

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
		var decimal_separator = Give.form.fn.getFormInfo('decimal_separator', parent_form);
		$(this).data('amount', Give.form.fn.unformatCurrency(current_total, decimal_separator));

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
		var parent_form = ( 'undefined' !== typeof $parent_form ) ? $parent_form : $(this).closest('form'),
			pre_focus_amount = $(this).data('amount'),
			this_value = ( 'undefined' !== typeof donation_amount ) ? donation_amount : $(this).val(),
			decimal_separator = Give.form.fn.getFormInfo('decimal_separator', parent_form),
			value_min = Give.form.fn.getFormMinimumAmount(parent_form),
			value_now = (this_value === 0) ? value_min : Give.form.fn.unformatCurrency(this_value, decimal_separator),
			error_msg = '',
			formatted_total = Give.form.fn.formatFormAmount(value_now, parent_form, {});

		price_id = Give.form.fn.getFormPriceID(parent_form);

		$(this).val(formatted_total);

		//Does this number have an accepted minimum value?
		if (!Give.form.fn.isValidDonationAmount(parent_form)) {

			//It doesn't... Invalid Minimum
			$(this).addClass('give-invalid-amount');
			error_msg = Give.form.fn.getNotice('bad_minimum', parent_form);

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

			// Update donation total (include currency symbol)
			parent_form.find('.give-final-total-amount')
				.data('total', value_now)
				.text(Give.form.fn.formatCurrency(
					value_now,
					{
						symbol: Give.form.fn.getFormInfo('currency_symbol', parent_form ),
						position: Give.form.fn.getFormInfo('currency_position', parent_form )
					},
					parent_form)
				);

		}

		// Set price id for current amount.
		if (-1 !== price_id) {

			// Auto set give price id.
			$('input[name="give-price-id"]', parent_form).val(price_id);

			// Update hidden amount field
			parent_form.find('.give-amount-hidden').val(Give.form.fn.formatFormAmount(value_now, parent_form, {}));

			// Remove old selected class & add class for CSS purposes
			parent_form.find('.give-default-level').removeClass('give-default-level');

			// Auto select variable price items ( Radio/Button/Select ).
			switch (true) {

				// Auto select radio button.
				case (!!parent_form.find('.give-radio-input').length) :
					parent_form.find('.give-radio-input')
						.prop('checked', false);
					parent_form.find('.give-radio-input[data-price-id="' + price_id + '"]')
						.prop('checked', true)
						.addClass('give-default-level');
					break;

				// Set focus to price id button.
				case (!!parent_form.find('button.give-donation-level-btn').length) :
					parent_form.find('button.give-donation-level-btn')
						.blur();
					parent_form.find('button.give-donation-level-btn[data-price-id="' + price_id + '"]')
						.focus()
						.addClass('give-default-level');
					break;

				// Auto select option.
				case (!!parent_form.find('select.give-select-level').length) :
					parent_form.find('select.give-select-level option')
						.prop('selected', false);
					parent_form.find('select.give-select-level option[data-price-id="' + price_id + '"]')
						.prop('selected', true)
						.addClass('give-default-level');
					break;

			}
		}

		//This class is used for CSS purposes
		$(this).parent('.give-donation-amount')
			.removeClass('give-custom-amount-focus-in');

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
			this_amount = selected_field.val(),
			price_id = selected_field.data('price-id');

		// Check if price ID blank because of dropdown type
		if ('undefined' === price_id) {
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

		var decimal_separator = Give.form.fn.getFormInfo('decimal_separator', $parent_form);

		// Cache previous amount and set data amount.
		$('.give-donation-amount .give-text-input', $parent_form)
			.data(
				'amount',
				Give.form.fn.unformatCurrency(
					$parent_form.find('.give-final-total-amount').data('total'),
					decimal_separator
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

jQuery(window).load(function () {

	/**
	 * Validate cc fields on change
	 */
	jQuery('body').on('keyup change focusout', '.give-form .card-number, .give-form .card-cvc, .give-form .card-expiry', function (e) {
		var el = jQuery(this),
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
				el.parents('form').find('.give-submit').prop('disabled', error);
				break;

			default:
				// Remove error class.
				if (el.hasClass('error')) {
					el.removeClass('error');
				}

				if (id.indexOf('card_number') > -1) {
					// Add card related classes.
					var card_type = give_form.find('.card-type');

					if (type === null) {
						card_type.removeClass().addClass('off card-type');
						el.removeClass('valid').addClass('error');
					}
					else {
						card_type.removeClass().addClass('card-type ' + type);
					}

				} else if (id.indexOf('card_expiry') > -1) {
					// set expiry date params.
					var expiry = card_expiry.payment('cardExpiryVal');

					give_form.find('.card-expiry-month').val(expiry.month);
					give_form.find('.card-expiry-year').val(expiry.year);
				}
		}
	});
});
