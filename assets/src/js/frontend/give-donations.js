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

/**
 *  This API is under development.
 *
 *  Currently uses only for internal purpose.
 */
Give = {
	init: function () {
		var subHelperObjs = ['form'],
			counter       = 0;

		jQuery(document).trigger('give:preInit');

		this.fn.__initialize_cache();

		// Initialize all init methods od sub helper objects.
		while (counter < subHelperObjs.length) {
			if (!!Give[subHelperObjs[counter]].init) {
				Give[subHelperObjs[counter]].init();
			}
			counter++;
		}

		jQuery(document).trigger('give:postInit');
	},

	fn: {
		/**
		 * Format Currency
		 *
		 * Formats the currency with accounting.js
		 *
		 * @param {string} price
		 * @param {object}  args
		 * @param {object} $form
		 * @returns {*|string}
		 */
		formatCurrency: function (price, args, $form) {
			// Global currency setting.
			var format_args = {
				symbol: '',
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
					symbol: '',
					decimal: Give.form.fn.getInfo('decimal_separator', $form),
					thousand: Give.form.fn.getInfo('thousands_separator', $form),
					precision: Give.form.fn.getInfo('number_decimals', $form),
					currency: Give.form.fn.getInfo('currency_code', $form),
				};
			}

			args = jQuery.extend(format_args, args);

			// Make sure precision is integer type
			args.precision = parseInt(args.precision);

			if ('INR' === args.currency) {
				var actual_price = accounting.unformat(price, '.').toString();

				var decimal_amount = '',
					result,
					amount,
					decimal_index  = actual_price.indexOf('.');

				if ((-1 !== decimal_index) && args.precision) {
					decimal_amount = Number(actual_price.substr(parseInt(decimal_index)))
						.toFixed(args.precision)
						.toString()
						.substr(1);
					actual_price   = actual_price.substr(0, parseInt(decimal_index));

					if (!decimal_amount.length) {
						decimal_amount = '.0000000000'.substr(0, (parseInt(decimal_index) + 1));
					} else if ((args.precision + 1) > decimal_amount.length) {
						decimal_amount = (decimal_amount + '000000000').substr(0, args.precision + 1);
					}
				} else {
					decimal_amount = '.000000000'.substr(0, args.precision + 1);
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
		unFormatCurrency: function (price, decimal_separator) {
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

			var regex   = new RegExp("[?&]" + name + "(=([^&#]*)|&|#|$)"),
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
			if ('undefined' === typeof give_global_vars[str]) {
				return '';
			}

			return give_global_vars[str];
		},

		/**
		 * set cache
		 *
		 * @since 1.8.17
		 *
		 * @param {string} key
		 * @param {string} value
		 * @param {object} $form
		 */
		setCache: function (key, value, $form) {
			if ($form.length) {
				Give.cache['form_' + Give.form.fn.getInfo('form-id', $form)][key] = value;
			} else {
				Give.cache[key] = value;
			}
		},

		/**
		 * Get cache
		 *
		 * @since 1.8.17
		 * @param key
		 * @param $form
		 * @return {string|*}
		 */
		getCache: function (key, $form) {
			var cache;

			if ($form.length) {
				cache = Give.cache['form_' + Give.form.fn.getInfo('form-id', $form)][key];
			} else {
				cache = Give.cache[key];
			}

			cache = 'undefined' === typeof cache ? '' : cache;

			return cache;
		},

		/**
		 * Initialize cache.
		 *
		 * @since 1.8.17
		 * @private
		 */
		__initialize_cache: function () {
			jQuery.each(jQuery('.give-form'), function (index, $item) {
				$item = $item instanceof jQuery ? $item : jQuery($item);

				Give.cache['form_' + Give.form.fn.getInfo('form-id', $item)] = [];
			});
		}
	}
	,

	/**
	 * This object key will be use to cache predicted data or donor activity.
	 *
	 * @since 1.8.17
	 */
	cache: {}
}
;

Give.form = {
	init: function () {
		this.fn.field.formatCreditCard(jQuery('form.give-form'));
		
		window.onload = function () {
			Give.form.fn.__sendBackToForm();
		};
	},

	fn: {

		/**
		 * Disable donation form.
		 *
		 * @param {object} $form
		 * @param {boolean} is_disable
		 *
		 * @return {*}
		 */
		disable: function ($form, is_disable) {
			if (!$form.length) {
				return false;
			}

			$form.find('.give-submit').prop('disabled', is_disable);
		},

		/**
		 * Get formatted amount
		 *
		 * @param {string/number} amount
		 * @param {object} $form
		 * @param {object} args
		 */
		formatAmount: function (amount, $form, args) {
			// Do not format amount if form did not exist.
			if (!$form.length) {
				return amount;
			}

			return Give.fn.formatCurrency(amount, args, $form);
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
		getInfo: function (str, $form) {
			var data = '';
			$form    = 'undefined' !== typeof $form ? $form : {};

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

				case 'form-type':
					if ($form.hasClass('give-form-type-set')) {
						data = 'set';
					} else if ($form.hasClass('give-form-type-multi')) {
						data = 'multi';
					}
					break;

				case 'form-id':
					data = $form.find('input[name="give-form-id"]').val();
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
		setInfo: function (type, val, $form, str ) {
			// Bailout.
			if ( !$form.length) {
				return false;
			}

			type = 'undefined' === typeof type ? 'data' : type;

			switch (type){
				case 'nonce':
					$form.find('input[name="_wpnonce"]').val( val );
					break;
			}

			// Bailout.
			if( 'undefined' !== typeof str && ! str.length ) {
				return false;
			}

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
		getGateway: function ($form) {
			var gateway = '';

			if (!$form.length) {
				return gateway;
			}

			gateway = $form.find('input[name="payment-mode"]:checked').val().trim();

			return 'undefined' !== typeof gateway ? gateway : '';
		},

		/**
		 * Get Price ID and levels for multi donation form
		 *
		 * @param   {Object} $form Form jQuery object
		 *
		 * @returns {Object}
		 */
		getVariablePrices: function ($form) {
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

				var decimal_separator = Give.form.fn.getInfo('decimal_separator', $form);

				// Add price id and amount to collector.
				variable_prices.push({
					price_id: item.data('price-id'),
					amount: Give.fn.unFormatCurrency(item.val(), decimal_separator)
				});
			});

			return variable_prices;
		},

		/**
		 * Get form price ID
		 *
		 * @since 1.8.17
		 * @param {object} $form
		 * @param {boolean} is_amount
		 *
		 * @return {string}
		 */
		getPriceID: function ($form, is_amount) {

			var variable_prices = this.getVariablePrices($form),
				current_amount  = Give.fn.unFormatCurrency(
					$form.find('input[name="give-amount"]').val(),
					this.getInfo('decimal_separator', $form)
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
				price_id        = !!Give.fn.getCache('amount_' + current_amount, $form) ? Give.fn.getCache('amount_' + current_amount, $form) : -1;

			// Flag to decide on which param we want to find price_id
			is_amount = 'undefined' === typeof is_amount ? true : is_amount;

			// Find price id with amount in variable prices.
			if (variable_prices.length) {

				// Get recent selected price id for same amount.
				if (-1 === price_id) {
					if (is_amount) {
						// Find amount in donation levels.
						jQuery.each(variable_prices, function (index, variable_price) {
							if (variable_price.amount === current_amount) {
								price_id = variable_price.price_id;

								return false;
							}
						});

						// Set level to custom.
						if (-1 === price_id && this.getMinimumAmount($form) <= current_amount) {
							price_id = 'custom';
						}
					} else {
						price_id = jQuery('input[name="give-price-id"]', $form).val();
					}
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
		getMinimumAmount: function ($form) {
			return Give.fn.unFormatCurrency(
				$form.find('input[name="give-form-minimum"]').val(),
				Give.form.fn.getInfo('decimal_separator', $form )
			);
		},

		/**
		 * Get form amount
		 *
		 * @since 1.8.17
		 * @param $form
		 * @return {*}
		 */
		getAmount: function ($form) {
			// Bailout
			if (!$form.length) {
				return null;
			}

			var amount = $form.find('input[name="give-amount"]').val();

			if ('undefined' === typeof amount || !amount) {
				amount = 0;
			}

			return Give.fn.unFormatCurrency(amount, this.getInfo('decimal_separator', $form));
		},

		/**
		 * Get form security nonce
		 *
		 * @since 1.8.17
		 * @param {object} $form
		 * @return {string}
		 */
		getNonce: function ($form) {
			// Bailout
			if (!$form.length) {
				return '';
			}

			var nonce = $form.find('input[name="_wpnonce"]').val();

			if ('undefined' === typeof nonce || !nonce) {
				nonce = '';
			}

			return nonce;
		},

		/**
		 * Reset form noce.
		 *
		 * @since 2.0
		 *
		 * @param {object} $form Donation form object.
		 * @returns {boolean}
		 */
		resetNonce: function ($form) {
			// Return false, if form is missing.
			if ( ! $form.length ) {
				return false;
			}

			//Post via AJAX to Give
			jQuery.post(give_scripts.ajaxurl, {
					action: 'give_donation_form_nonce',
					give_form_id: Give.form.fn.getInfo('form-id', $form )
				},
				function (response) {
					// Update nonce field.
					Give.form.fn.setInfo( 'nonce', response.data, $form, '' );
				}
			);
		},

		/**
		 * Auto select donation level
		 *
		 * @since 1.8.17
		 * @param {object} $form
		 * @param {string} price_id
		 *
		 * @return {boolean}
		 */
		autoSelectDonationLevel: function ($form, price_id) {

			if (!$form.length || 'multi' !== this.getInfo('form-type', $form)) {
				return false;
			}

			price_id = ( 'undefined' === typeof price_id ) ? this.getPriceID($form, false) : price_id;

			switch (true) {

				// Auto select radio button.
				case (!!$form.find('.give-radio-input').length) :
					$form.find('.give-radio-input')
						.prop('checked', false);
					$form.find('.give-radio-input[data-price-id="' + price_id + '"]')
						.prop('checked', true)
						.addClass('give-default-level');
					break;

				// Set focus to price id button.
				case (!!$form.find('button.give-donation-level-btn').length) :
					$form.find('button.give-donation-level-btn')
						.blur();
					$form.find('button.give-donation-level-btn[data-price-id="' + price_id + '"]')
						.focus()
						.addClass('give-default-level');
					break;

				// Auto select option.
				case (!!$form.find('select.give-select-level').length) :
					$form.find('select.give-select-level option')
						.prop('selected', false);
					$form.find('select.give-select-level option[data-price-id="' + price_id + '"]')
						.prop('selected', true)
						.addClass('give-default-level');
					break;

			}
		},

		/**
		 * Update level values
		 *
		 * Helper function: Sets the multi-select amount values
		 *
		 * @since 1.8.17
		 * @param {object} $level
		 * @returns {boolean}
		 */
		autoSetMultiLevel: function ($level) {

			var $form          = $level.parents('form'),
				level_amount   = $level.val(),
				level_price_id = $level.data('price-id');

			// Check if price ID blank because of dropdown type
			if ('undefined' === typeof  level_price_id) {
				level_price_id = $level.find('option:selected').data('price-id');
			}

			// Is this a custom amount selection?
			if ('custom' === level_amount) {
				// It is, so focus on the custom amount input.
				$form.find('.give-amount-top').val('').focus();
				return false; // Bounce out
			}

			// Update custom amount field
			$form.find('.give-amount-top').val(level_amount);
			$form.find('span.give-amount-top').text(level_amount);

			var decimal_separator = Give.form.fn.getInfo('decimal_separator', $form);

			// Cache previous amount and set data amount.
			jQuery('.give-donation-amount .give-text-input', $form)
				.data(
					'amount',
					Give.fn.unFormatCurrency(
						$form.find('.give-final-total-amount').data('total'),
						decimal_separator
					)
				);

			// Manually trigger blur event with two params:
			// (a) form jquery object
			// (b) price id
			// (c) donation amount
			$form.find('.give-donation-amount .give-text-input')
				.trigger('blur', [$form, level_amount, level_price_id]);

			// trigger an event for hooks
			jQuery(document).trigger('give_donation_value_updated', [$form, level_amount, level_price_id]);
		},

		/**
		 * Donor sent back to the form
		 *
		 * @since 1.8.17
		 * @access private
		 */
		__sendBackToForm: function () {

			var form_id      = Give.fn.getParameterByName('form-id'),
				payment_mode = Give.fn.getParameterByName('payment-mode');

			// Sanity check - only proceed if query strings in place.
			if (!form_id || !payment_mode) {
				return false;
			}

			var $form_wrapper  = jQuery('body').find('#give-form-' + form_id + '-wrap'),
				$form          = $form_wrapper.find('form.give-form'),
				display_modal  = $form_wrapper.hasClass('give-display-modal'),
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
			var level_id    = Give.fn.getParameterByName('level-id'),
				level_field = $form.find('*[data-price-id="' + level_id + '"]');

			if (level_field.length > 0) {
				this.autoSetMultiLevel(level_field);
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
			var min_amount = this.getMinimumAmount($form),
				amount     = this.getAmount($form),
				price_id   = this.getPriceID($form, true);

			return (
				( ( -1 < amount ) && ( amount >= min_amount ) ) ||
				( -1 !== price_id )
			);
		},

		field: {

			/**
			 * Format CC Fields
			 *
			 * Set variables and format cc fields.
			 *
			 * @since 1.2
			 *
			 * @param {object} $forms
			 */
			formatCreditCard: function ($forms) {
				//Loop through forms on page and set CC validation
				$forms.each(function (index, form) {
					form            = jQuery(form);
					var card_number = form.find('.card-number'),
						card_cvc    = form.find('.card-cvc'),
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

Give.notice = {
	fn: {
		/**
		 * Render notice
		 * @since 1.8.17
		 *
		 * @param {string} notice_code
		 * @param {object} $container
		 *
		 * @return {string}
		 */
		renderNotice: function (notice_code, $container) {
			var notice_html = '',
				$notice;
			$container      = 'undefined' !== typeof $container ? $container : {};

			switch (notice_code) {
				case 'bad_minimum':
					$notice = jQuery(
						'<div class="give_error give-invalid-minimum give-hidden">' +
						this.getNotice(notice_code, $container) +
						'</div>'
					);
					break;
			}

			// Return html if container did not find.
			if (!$container.length) {
				return notice_html;
			}

			$notice.insertBefore($container.find('.give-total-wrap')).show();
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
						notice = Give.fn.getGlobalVar('bad_minimum') +
							' ' +
							Give.fn.formatCurrency(
								Give.form.fn.getMinimumAmount($form),
								{symbol: Give.form.fn.getInfo('currency_symbol', $form)},
								$form
							);
					}
					break;
			}

			return notice;
		}
	}
};

jQuery(function ($) {

	var $forms = jQuery('form.give-form'),
		doc    = $(document);

	// Toggle validation classes
	$.fn.toggleError = function (errored) {
		this.toggleClass('error', errored);
		this.toggleClass('valid', !errored);

		return this;
	};

	// Initialize Give object.
	Give.init();

	/**
	 * Update state/province fields per country selection
	 */
	function update_billing_state_field() {
		var $this = $(this),
			$form = $this.parents('form');
		if ('card_state' !== $this.attr('id')) {

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
					var html         = "";
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

						// Add support to zip fields.
						$form.find('p#give-card-zip-wrap').addClass('form-row-last');
						$form.find('p#give-card-zip-wrap').removeClass('form-row-wide');
					} else {
						$form.find('p#give-card-state-wrap').addClass('give-hidden');

						// Add support to zip fields.
						$form.find('p#give-card-zip-wrap').addClass('form-row-wide');
						$form.find('p#give-card-zip-wrap').removeClass('form-row-last');
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
			var gateway = Give.form.fn.getGateway($(this).closest('form'));
			if (!gateway.length) {
				alert(Give.fn.getGlobalVar('no_gateway'));
				return false;
			}
		}
	);

	// Add a class to the currently selected gateway on click
	doc.on(
		'click',
		'#give-payment-mode-select input',
		function () {
			var $form               = $(this).parents('form'),
				$gateways_li        = $form.find('#give-payment-mode-select li'),
				old_payment_gateway = $form.find('li.give-gateway-option-selected input[name="payment-mode"]').val().trim(),
				new_payment_gateways;

			// Unselect all payment gateways.
			$gateways_li.removeClass('give-gateway-option-selected');
			$gateways_li.prop('checked', false);

			// Select payment gateway.
			$(this).prop('checked', true);
			$(this).parent().addClass('give-gateway-option-selected');

			// Get new payment gateway.
			new_payment_gateways = Give.form.fn.getGateway($form);

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
		var current_total     = parent_form.find('.give-final-total-amount').data('total');
		var decimal_separator = Give.form.fn.getInfo('decimal_separator', parent_form);
		$(this).data('amount', Give.fn.unFormatCurrency(current_total, decimal_separator));

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
	 * Fires on focus end aka "blur"
	 */
	doc.on('blur', '.give-donation-amount .give-text-input', function (e, $parent_form, donation_amount, price_id) {
		var parent_form       = ( 'undefined' !== typeof $parent_form ) ? $parent_form : $(this).closest('form'),
			pre_focus_amount  = $(this).data('amount'),
			this_value        = ( 'undefined' !== typeof donation_amount ) ? donation_amount : $(this).val(),
			decimal_separator = Give.form.fn.getInfo('decimal_separator', parent_form),
			value_min         = Give.form.fn.getMinimumAmount(parent_form),
			value_now         = (this_value === 0) ? value_min : Give.fn.unFormatCurrency(this_value, decimal_separator),
			formatted_total   = Give.form.fn.formatAmount(value_now, parent_form, {});

		price_id = 'undefined' === typeof price_id ? Give.form.fn.getPriceID(parent_form, true) : price_id;

		// Cache donor selected price id for a amount.
		Give.fn.setCache('amount_' + value_now, price_id, parent_form);

		$(this).val(formatted_total);

		//Does this number have an accepted minimum value?
		if (!Give.form.fn.isValidDonationAmount(parent_form)) {

			//It doesn't... Invalid Minimum
			$(this).addClass('give-invalid-amount');

			//Disable submit
			Give.form.fn.disable(parent_form, true);
			var invalid_minimum = parent_form.find('.give-invalid-minimum');

			//If no error present, create it, insert, slide down (show)
			if (invalid_minimum.length === 0) {
				Give.notice.fn.renderNotice('bad_minimum', parent_form);
			}

		} else {

			// Remove error massage class from price field.
			$(this).removeClass('give-invalid-amount');

			// Minimum amount met - slide up error & remove it from DOM.
			parent_form.find('.give-invalid-minimum').slideUp(300, function () {
				$(this).remove();
			});

			// Re-enable submit.
			Give.form.fn.disable(parent_form, false);
		}

		//If values don't match up then proceed with updating donation total value
		if (pre_focus_amount !== value_now) {

			// Update donation total (include currency symbol)
			parent_form.find('.give-final-total-amount')
				.data('total', value_now)
				.text(Give.fn.formatCurrency(
					value_now,
					{
						symbol: Give.form.fn.getInfo('currency_symbol', parent_form),
						position: Give.form.fn.getInfo('currency_position', parent_form)
					},
					parent_form)
				);

		}

		// Set price id for current amount.
		if (-1 !== price_id) {

			// Auto set give price id.
			$('input[name="give-price-id"]', parent_form).val(price_id);

			// Update hidden amount field
			parent_form.find('.give-amount-hidden').val(Give.form.fn.formatAmount(value_now, parent_form, {}));

			// Remove old selected class & add class for CSS purposes
			parent_form.find('.give-default-level').removeClass('give-default-level');

			// Auto select variable price items ( Radio/Button/Select ).
			Give.form.fn.autoSelectDonationLevel(parent_form, price_id);
		}

		//This class is used for CSS purposes
		$(this).parent('.give-donation-amount')
			.removeClass('give-custom-amount-focus-in');

	});

	// Multi-level Buttons: Update Amount Field based on Multi-level Donation Select
	doc.on('click touchend', '.give-donation-level-btn', function (e) {
		e.preventDefault(); //don't let the form submit
		Give.form.fn.autoSetMultiLevel($(this));
	});

	// Multi-level Radios: Update Amount Field based on Multi-level Donation Select
	doc.on('click touchend', '.give-radio-input-level', function (e) {
		Give.form.fn.autoSetMultiLevel($(this));
	});

	// Multi-level Checkboxes: Update Amount Field based on Multi-level Donation Select
	doc.on('change', '.give-select-level', function (e) {
		Give.form.fn.autoSetMultiLevel($(this));
	});

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

	/**
	 * Prevent level jump which happen due to same id.
	 * @see https://github.com/WordImpress/Give/issues/2292
	 */
	$('label[for^="give-radio-level"]').on('click', function (e) {
		var $form       = $(this).closest('form'),
			$inputField = $form.find('#' + $(this).attr('for'));

		if ($inputField.length) {
			$inputField.trigger('click');
			e.preventDefault();
		}
	});
});

jQuery(window).load(function () {

	/**
	 * Validate cc fields on change
	 */
	jQuery('body').on('keyup change focusout', '.give-form .card-number, .give-form .card-cvc, .give-form .card-expiry', function (e) {
		var el          = jQuery(this),
			give_form   = el.parents('form.give-form'),
			id          = el.attr('id'),
			card_number = give_form.find('.card-number'),
			card_cvc    = give_form.find('.card-cvc'),
			card_expiry = give_form.find('.card-expiry'),
			type        = jQuery.payment.cardType(card_number.val()),
			error       = false;

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
