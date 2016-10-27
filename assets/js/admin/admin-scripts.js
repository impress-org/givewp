/*!
 * Give Admin JS
 *
 * @description: The Give Admin scripts
 * @package:     Give
 * @subpackage:  Assets/JS
 * @copyright:   Copyright (c) 2016, WordImpress
 * @license:     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 */

jQuery.noConflict();
(function ($) {

	/**
	 * Setup Admin Datepicker
	 * @since: 1.0
	 */
	var enable_admin_datepicker = function () {
		// Date picker
		if ($('.give_datepicker').length > 0) {
			var dateFormat = 'mm/dd/yy';
			$('.give_datepicker').datepicker({
				dateFormat: dateFormat
			});
		}
	};

	/**
	 * Setup Pretty Chosen Select Fields
	 */
	var setup_chosen_give_selects = function () {
		// Setup Chosen Selects
		$('.give-select-chosen').chosen({
			inherit_select_classes   : true,
			placeholder_text_single  : give_vars.one_option,
			placeholder_text_multiple: give_vars.one_or_more_option
		});

		// This fixes the Chosen box being 0px wide when the thickbox is opened
		$('#post').on('click', '.give-thickbox', function () {
			$('.give-select-chosen', '#choose-give-form').css('width', '100%');
		});

	};

	/**
	 * Unformat Currency
	 *
	 * @use string give_vars.currency_decimals Number of decimals
	 *
	 * @param   {string}      price Price
	 * @param   {number|bool} dp    Number of decimals
	 *
	 * @returns {string}
	 */
	function give_unformat_currency(price, dp) {
		price                = accounting.unformat(price, give_vars.decimal_separator).toString();
		var decimal_position = price.indexOf('.');

		// Set default value for number of decimals.
		if (false != dp) {
			price = parseFloat(price).toFixed(dp);

			// If price do not have decimal value then set default number of decimals.
		} else if (
			( -1 === decimal_position )
			|| ( give_vars.currency_decimals > price.substr(decimal_position + 1).length )
		) {
			price = parseFloat(price).toFixed(give_vars.currency_decimals);
		}

		return price;
	}

	/**
	 * Edit donation screen JS
	 */
	var Give_Edit_Donation = {

		init: function () {
			this.edit_address();
			this.add_note();
			this.remove_note();
			this.new_donor();
			this.resend_receipt();
			this.variable_price_list();
		},

		edit_address: function () {

			// Update base state field based on selected base country
			$('select[name="give-payment-address[0][country]"]').change(function () {
				var $this = $(this);

				data = {
					action    : 'give_get_states',
					country   : $this.val(),
					field_name: 'give-payment-address[0][state]'
				};
				$.post(ajaxurl, data, function (response) {

					var state_wrap = $('#give-order-address-state-wrap');

					state_wrap.find('*').not('.order-data-address-line').remove();

					if ('nostates' == response) {
						state_wrap.append('<input type="text" name="give-payment-address[0][state]" value="" class="give-edit-toggles medium-text"/>');
					} else {
						state_wrap.append(response);
						state_wrap.find('select').chosen();
					}
				});

				return false;
			});

		},

		add_note: function () {

			$('#give-add-payment-note').on('click', function (e) {
				e.preventDefault();
				var postData = {
					action    : 'give_insert_payment_note',
					payment_id: $(this).data('payment-id'),
					note      : $('#give-payment-note').val()
				};

				if (postData.note) {

					$.ajax({
						type   : 'POST',
						data   : postData,
						url    : ajaxurl,
						success: function (response) {
							$('#give-payment-notes-inner').append(response);
							$('.give-no-payment-notes').hide();
							$('#give-payment-note').val('');
						}
					}).fail(function (data) {
						if (window.console && window.console.log) {
							console.log(data);
						}
					});

				} else {
					var border_color = $('#give-payment-note').css('border-color');
					$('#give-payment-note').css('border-color', 'red');
					setTimeout(function () {
						$('#give-payment-note').css('border-color', border_color);
					}, 500);
				}

			});

		},

		remove_note: function () {

			$('body').on('click', '.give-delete-payment-note', function (e) {

				e.preventDefault();

				if (confirm(give_vars.delete_donation_note)) {
					var postData = {
						action    : 'give_delete_payment_note',
						payment_id: $(this).data('payment-id'),
						note_id   : $(this).data('note-id')
					};

					$.ajax({
						type   : "POST",
						data   : postData,
						url    : ajaxurl,
						success: function (response) {
							$('#give-payment-note-' + postData.note_id).remove();
							if (!$('.give-payment-note').length) {
								$('.give-no-payment-notes').show();
							}
							return false;
						}
					}).fail(function (data) {
						if (window.console && window.console.log) {
							console.log(data);
						}
					});
					return true;
				}

			});

		},

		new_donor: function () {

			$('#give-customer-details').on('click', '.give-payment-new-customer, .give-payment-new-customer-cancel', function (e) {
				e.preventDefault();
				$('.customer-info').toggle();
				$('.new-customer').toggle();

				if ($('.new-customer').is(":visible")) {
					$('#give-new-customer').val(1);
				} else {
					$('#give-new-customer').val(0);
				}

			});

		},

		resend_receipt: function () {
			$('body').on('click', '#give-resend-receipt', function (e) {
				return confirm(give_vars.resend_receipt);
			});
		},

		variable_price_list: function () {
			$('select[name="forms"]').chosen().change(function () {
				var give_form_id,
					variable_prices_html_container = $('.give-donation-level');

				// Check for form ID.
				if (!( give_form_id = $(this).val() )) {
					return false;
				}

				// Ajax.
				$.ajax({
					type   : 'POST',
					url    : ajaxurl,
					data   : {
						form_id   : give_form_id,
						payment_id: $('input[name="give_payment_id"]').val(),
						action    : 'give_check_for_form_price_variations_html'
					},
					success: function (response) {
						response = response.trim();
						if (response) {

							// Update Variable price html.
							variable_prices_html_container.html(response);

							// Add chosen feature to select tag.
							$('select[name="give-variable-price"]').chosen();
						} else {
							// Update Variable price html.
							variable_prices_html_container.html('');
						}
					}
				});
			});
		}

	};

	/**
	 * Settings screen JS
	 */
	var Give_Settings = {

		init: function () {
			this.toggle_options();
		},

		toggle_options: function () {

			var email_access = $('#email_access');
			email_access.on('change', function () {
				if (email_access.prop('checked')) {
					$('.cmb2-id-recaptcha-key, .cmb2-id-recaptcha-secret').show();
				} else {
					$('.cmb2-id-recaptcha-key, .cmb2-id-recaptcha-secret').hide();
				}
			}).change();
		},

	};

	/**
	 * Reports / Exports / Tools screen JS
	 */
	var Give_Reports = {

		init: function () {
			this.date_options();
			this.donors_export();
			this.recount_stats();
		},

		date_options: function () {

			// Show hide extended date options
			$('#give-graphs-date-options').change(function () {
				var $this = $(this);
				if ('other' === $this.val()) {
					$('#give-date-range-options').show();
				} else {
					$('#give-date-range-options').hide();
				}
			});

		},

		donors_export: function () {

			// Show / hide Donation Form option when exporting donors
			$('#give_customer_export_form').change(function () {

				var $this                  = $(this),
					form_id                = $('option:selected', $this).val(),
					customer_export_option = $('#give_customer_export_option');

				if ('0' === $this.val()) {
					customer_export_option.show();
				} else {
					customer_export_option.hide();
				}

				var price_options_select = $('.give_price_options_select');

				// On Form Select, Check if Variable Prices Exist
				if (parseInt(form_id) != 0) {
					var data = {
						action    : 'give_check_for_form_price_variations',
						form_id   : form_id,
						all_prices: true
					};

					$.post(ajaxurl, data, function (response) {
						price_options_select.remove();
						$('#give_customer_export_form_chosen').after(response);
					});
				} else {
					price_options_select.remove();
				}

			});

		},

		recount_stats: function () {

			$('body').on('change', '#recount-stats-type', function () {

				var export_form   = $('#give-tools-recount-form');
				var selected_type = $('option:selected', this).data('type');
				var submit_button = $('#recount-stats-submit');
				var forms         = $('#tools-form-dropdown');

				// Reset the form
				export_form.find('.notice-wrap').remove();
				submit_button.removeClass('button-disabled').attr('disabled', false);
				forms.hide();
				$('.give-recount-stats-descriptions span').hide();

				if ('recount-form' === selected_type) {

					forms.show();
					forms.find('.give-select-chosen').css({
						'width'    : 'auto',
						'min-width': '250px'
					});

				} else if ('reset-stats' === selected_type) {

					export_form.append('<div class="notice-wrap"></div>');
					var notice_wrap = export_form.find('.notice-wrap');
					notice_wrap.html('<div class="notice notice-warning"><p><input type="checkbox" id="confirm-reset" name="confirm_reset_store" value="1" /> <label for="confirm-reset">' + give_vars.reset_stats_warn + '</label></p></div>');

					submit_button.addClass('button-disabled').attr('disabled', 'disabled');

				} else {

					forms.hide();
					forms.val(0);

				}

				$('#' + selected_type).show();
			});

			$('body').on('change', '#confirm-reset', function () {
				var checked = $(this).is(':checked');
				if (checked) {
					$('#recount-stats-submit').removeClass('button-disabled').removeAttr('disabled');
				} else {
					$('#recount-stats-submit').addClass('button-disabled').attr('disabled', 'disabled');
				}
			});

			$('#give-tools-recount-form').submit(function (e) {
				var selection     = $('#recount-stats-type').val();
				var export_form   = $(this);
				var selected_type = $('option:selected', this).data('type');

				if ('reset-stats' === selected_type) {
					var is_confirmed = $('#confirm-reset').is(':checked');
					if (is_confirmed) {
						return true;
					} else {
						has_errors = true;
					}
				}

				export_form.find('.notice-wrap').remove();

				export_form.append('<div class="notice-wrap"></div>');
				var notice_wrap = export_form.find('.notice-wrap');
				var has_errors  = false;

				if (null === selection || 0 === selection) {
					// Needs to pick a method give_vars.batch_export_no_class
					notice_wrap.html('<div class="updated error"><p>' + give_vars.batch_export_no_class + '</p></div>');
					has_errors = true;
				}

				if ('recount-form' === selected_type) {

					var selected_form = $('select[name="form_id"]').val();
					if (selected_form == 0) {
						// Needs to pick give_vars.batch_export_no_reqs
						notice_wrap.html('<div class="updated error"><p>' + give_vars.batch_export_no_reqs + '</p></div>');
						has_errors = true;
					}

				}

				if (has_errors) {
					export_form.find('.button-disabled').removeClass('button-disabled');
					return false;
				}
			});
		}

	};

	/**
	 * Export screen JS
	 */
	var Give_Export = {

		init: function () {
			this.submit();
			this.dismiss_message();
		},

		submit: function () {

			var self = this;

			$(document.body).on('submit', '.give-export-form', function (e) {
				e.preventDefault();

				var submitButton = $(this).find('input[type="submit"]');

				if (!submitButton.hasClass('button-disabled')) {

					var data = $(this).serialize();

					submitButton.addClass('button-disabled');
					$(this).find('.notice-wrap').remove();
					$(this).append('<div class="notice-wrap give-clearfix"><span class="spinner is-active"></span><div class="give-progress"><div></div></div></div>');

					// start the process
					self.process_step(1, data, self);

				}

			});
		},

		process_step: function (step, data, self) {

			$.ajax({
				type    : 'POST',
				url     : ajaxurl,
				data    : {
					form  : data,
					action: 'give_do_ajax_export',
					step  : step,
				},
				dataType: 'json',
				success : function (response) {

					if ('done' == response.step || response.error || response.success) {

						// We need to get the actual in progress form, not all forms on the page
						var export_form = $('.give-export-form').find('.give-progress').parent().parent();
						var notice_wrap = export_form.find('.notice-wrap');

						export_form.find('.button-disabled').removeClass('button-disabled');

						if (response.error) {

							var error_message = response.message;
							notice_wrap.html('<div class="updated error"><p>' + error_message + '</p></div>');

						} else if (response.success) {

							var success_message = response.message;
							notice_wrap.html('<div id="give-batch-success" class="updated notice is-dismissible"><p>' + success_message + '<span class="notice-dismiss"></span></p></div>');

						} else {

							notice_wrap.remove();
							window.location = response.url;

						}

					} else {
						$('.give-progress div').animate({
							width: response.percentage + '%',
						}, 50, function () {
							// Animation complete.
						});
						self.process_step(parseInt(response.step), data, self);
					}

				}
			}).fail(function (response) {
				if (window.console && window.console.log) {
					console.log(response);
				}

				$('.notice-wrap').append(response.responseText);

			});

		},

		dismiss_message: function () {
			$('body').on('click', '#give-batch-success .notice-dismiss', function () {
				$('#give-batch-success').parent().slideUp('fast');
			});
		}

	};

	/**
	 * Admin Status Select Field Change
	 *
	 * @description: Handle status switching
	 * @since: 1.0
	 */
	var handle_status_change = function () {

		//When sta
		$('select[name="give-payment-status"]').on('change', function () {

			var status = $(this).val();

			$('.give-donation-status').removeClass(function (index, css) {
				return (css.match(/\bstatus-\S+/g) || []).join(' ');
			}).addClass('status-' + status);

		});

	};

	/**
	 * Donor management screen JS
	 */
	var Give_Customer = {

		init          : function () {
			this.edit_customer();
			this.add_email();
			this.user_search();
			this.remove_user();
			this.cancel_edit();
			this.change_country();
			this.add_note();
			this.delete_checked();
		},
		edit_customer : function () {
			$('body').on('click', '#edit-customer', function (e) {
				e.preventDefault();
				$('#give-customer-card-wrapper .editable').hide();
				$('#give-customer-card-wrapper .edit-item').fadeIn().css('display', 'block');
			});
		},
		user_search   : function () {
			// Upon selecting a user from the dropdown, we need to update the User ID
			$('body').on('click.giveSelectUser', '.give_user_search_results a', function (e) {
				e.preventDefault();
				var user_id = $(this).data('userid');
				$('input[name="customerinfo[user_id]"]').val(user_id);
			});
		},
		remove_user   : function () {
			$('body').on('click', '#disconnect-customer', function (e) {
				e.preventDefault();
				var customer_id = $('input[name="customerinfo[id]"]').val();

				var postData = {
					give_action: 'disconnect-userid',
					customer_id: customer_id,
					_wpnonce   : $('#edit-customer-info #_wpnonce').val()
				};

				$.post(ajaxurl, postData, function (response) {

					window.location.href = window.location.href;

				}, 'json');

			});
		},
		cancel_edit   : function () {
			$('body').on('click', '#give-edit-customer-cancel', function (e) {
				e.preventDefault();
				$('#give-customer-card-wrapper .edit-item').hide();
				$('#give-customer-card-wrapper .editable').show();
				$('.give_user_search_results').html('');
			});
		},
		change_country: function () {
			$('select[name="customerinfo[country]"]').change(function () {
				var $this = $(this);
				var data  = {
					action    : 'give_get_states',
					country   : $this.val(),
					field_name: 'customerinfo[state]'
				};
				$.post(ajaxurl, data, function (response) {
					if ('nostates' == response) {
						$(':input[name="customerinfo[state]"]').replaceWith('<input type="text" name="' + data.field_name + '" value="" class="give-edit-toggles medium-text"/>');
					} else {
						$(':input[name="customerinfo[state]"]').replaceWith(response);
					}
				});

				return false;
			});
		},
		add_note      : function () {
			$('body').on('click', '#add-customer-note', function (e) {
				e.preventDefault();
				var postData = {
					give_action            : 'add-customer-note',
					customer_id            : $('#customer-id').val(),
					customer_note          : $('#customer-note').val(),
					add_customer_note_nonce: $('#add_customer_note_nonce').val()
				};

				if (postData.customer_note) {

					$.ajax({
						type   : "POST",
						data   : postData,
						url    : ajaxurl,
						success: function (response) {
							$('#give-customer-notes').prepend(response);
							$('.give-no-customer-notes').hide();
							$('#customer-note').val('');
						}
					}).fail(function (data) {
						if (window.console && window.console.log) {
							console.log(data);
						}
					});

				} else {
					var border_color = $('#customer-note').css('border-color');
					$('#customer-note').css('border-color', 'red');
					setTimeout(function () {
						$('#customer-note').css('border-color', border_color);
					}, 500);
				}
			});
		},
		delete_checked: function () {
			$('#give-customer-delete-confirm').change(function () {
				var records_input = $('#give-customer-delete-records');
				var submit_button = $('#give-delete-customer');

				if ($(this).prop('checked')) {
					records_input.attr('disabled', false);
					submit_button.attr('disabled', false);
				} else {
					records_input.attr('disabled', true);
					records_input.prop('checked', false);
					submit_button.attr('disabled', true);
				}
			});
		},
		add_email     : function () {
			if (!$('#add-customer-email').length) {
				return;
			}

			$(document.body).on('click', '#add-customer-email', function (e) {
				e.preventDefault();
				var button  = $(this);
				var wrapper = button.parent();

				wrapper.parent().find('.notice-wrap').remove();
				wrapper.find('.spinner').css('visibility', 'visible');
				button.attr('disabled', true);

				var customer_id = wrapper.find('input[name="customer-id"]').val();
				var email       = wrapper.find('input[name="additional-email"]').val();
				var primary     = wrapper.find('input[name="make-additional-primary"]').is(':checked');
				var nonce       = wrapper.find('input[name="add_email_nonce"]').val();

				var postData = {
					give_action: 'add_donor_email',
					customer_id: customer_id,
					email      : email,
					primary    : primary,
					_wpnonce   : nonce
				};

				$.post(ajaxurl, postData, function (response) {

					if (true === response.success) {
						window.location.href = response.redirect;
					} else {
						button.attr('disabled', false);
						wrapper.after('<div class="notice-wrap"><div class="notice notice-error inline"><p>' + response.message + '</p></div></div>');
						wrapper.find('.spinner').css('visibility', 'hidden');
					}

				}, 'json');

			});
		},
	};

	/**
	 * API screen JS
	 */
	var API_Screen = {

		init: function () {
			this.revoke_api_key();
			this.regenerate_api_key();
		},

		revoke_api_key    : function () {
			$('body').on('click', '.give-revoke-api-key', function (e) {
				return confirm(give_vars.revoke_api_key);
			});
		},
		regenerate_api_key: function () {
			$('body').on('click', '.give-regenerate-api-key', function (e) {
				return confirm(give_vars.regenerate_api_key);
			});
		}
	};

	/**
	 * Initialize qTips
	 */
	var initialize_qtips = function () {
		jQuery('[data-tooltip!=""]').qtip({ // Grab all elements with a non-blank data-tooltip attr.
			content: {
				attr: 'data-tooltip' // Tell qTip2 to look inside this attr for its content
			},
			style  : {classes: 'qtip-rounded qtip-tipsy'},
			events : {
				show: function (event, api) {
					var $el = $(api.elements.target[0]);
					$el.qtip('option', 'position.my', ($el.data('tooltip-my-position') == undefined) ? 'bottom center' : $el.data('tooltip-my-position'));
					$el.qtip('option', 'position.at', ($el.data('tooltip-target-position') == undefined) ? 'top center' : $el.data('tooltip-target-position'));
				}
			}
		})
	};

	//On DOM Ready
	$(function () {

		enable_admin_datepicker();
		handle_status_change();
		setup_chosen_give_selects();
		Give_Edit_Donation.init();
		Give_Settings.init();
		Give_Reports.init();
		Give_Customer.init();
		API_Screen.init();
		Give_Export.init();

		initialize_qtips();

		//Footer
		$('a.give-rating-link').click(function () {
			jQuery(this).parent().text(jQuery(this).data('rated'));
		});

		// Ajax user search
		$('.give-ajax-user-search').on('keyup', function () {
			var user_search = $(this).val();
			var exclude     = '';

			if ($(this).data('exclude')) {
				exclude = $(this).data('exclude');
			}

			$('.give-ajax').show();
			data = {
				action   : 'give_search_users',
				user_name: user_search,
				exclude  : exclude
			};

			document.body.style.cursor = 'wait';

			$.ajax({
				type    : "POST",
				data    : data,
				dataType: "json",
				url     : ajaxurl,
				success : function (search_response) {
					$('.give-ajax').hide();
					$('.give_user_search_results').removeClass('hidden');
					$('.give_user_search_results span').html('');
					$(search_response.results).appendTo('.give_user_search_results span');
					document.body.style.cursor = 'default';
				}
			});
		});

		$('body').on('click.giveSelectUser', '.give_user_search_results span a', function (e) {
			e.preventDefault();
			var login = $(this).data('login');
			$('.give-ajax-user-search').val(login);
			$('.give_user_search_results').addClass('hidden');
			$('.give_user_search_results span').html('');
		});

		$('body').on('click.giveCancelUserSearch', '.give_user_search_results a.give-ajax-user-cancel', function (e) {
			e.preventDefault();
			$('.give-ajax-user-search').val('');
			$('.give_user_search_results').addClass('hidden');
			$('.give_user_search_results span').html('');
		});

		/**
		 *  Amount format validation form price field setting
		 */

		// This function uses for adding qtip to money/price field.
		function give_add_qtip($fields) {
			// Add qtip to all existing money input fields.
			$fields.each(function () {
				$(this).qtip({
					style   : 'qtip-dark qtip-tipsy',
					content : {
						text: give_vars.price_format_guide.trim()
					},
					show    : '',
					position: {
						my: 'bottom center',
						at: 'top center'
					}
				});
			});
		}

		var $give_money_fields       = $('input.give-money-field, input.give-price-field');
		var thousand_separator       = give_vars.thousands_separator,
			decimal_separator        = give_vars.decimal_separator,
			thousand_separator_count = '',
			alphabet_count           = '',
			price_string             = '',

			// Thousand separation limit in price depends upon decimal separator symbol.
			// If thousand separator is equal to decimal separator then price does not have more then 1 thousand separator otherwise limit is zero.
			thousand_separator_limit = ( decimal_separator === thousand_separator ? 1 : 0 );

		// Add qtip to all existing money input fields.
		give_add_qtip($give_money_fields);

		// Add qtip to new created money/price input field.
		$('#_give_donation_levels_repeat').on('click', 'button.cmb-add-group-row', function () {
			window.setTimeout(
				function () {

					// Update input filed selector.
					$give_money_fields = $('input.give-money-field, input.give-price-field');

					// Add qtip to all existing money input fields.
					give_add_qtip($give_money_fields);
				},
				100
			)
		});

		// Check & show message on keyup event.
		$('#poststuff').on('keyup', 'input.give-money-field, input.give-price-field', function () {
			// Count thousand separator in price string.
			thousand_separator_count = ( $(this).val().match(new RegExp(thousand_separator, 'g')) || [] ).length;
			alphabet_count           = ( $(this).val().match(new RegExp('[a-z]', 'g')) || [] ).length;

			// Show qtip conditionally if thousand separator detected on price string.
			if (
				( -1 !== $(this).val().indexOf(thousand_separator) )
				&& ( thousand_separator_limit < thousand_separator_count )
			) {
				$(this).qtip('show');
			} else if (alphabet_count) {
				// Show qtip if user entered a number with alphabet letter.
				$(this).qtip('show');
			} else {
				$(this).qtip('hide');
			}

			// Reset thousand separator count.
			thousand_separator_count = alphabet_count = '';
		});

		// Format price sting of input field on focusout.
		$('#poststuff').on('focusout', 'input.give-money-field, input.give-price-field', function () {
			price_string = give_unformat_currency($(this).val(), false);

			// Back out.
			if (!price_string) {
				$(this).val('');
				return false;
			}

			// Replace dot decimal separator with user defined decimal separator.
			price_string = price_string.replace('.', decimal_separator);

			// Check if current number is negative or not.
			if (-1 !== price_string.indexOf('-')) {
				price_string = price_string.replace('-', '');
			}

			// Update format price string in input field.
			$(this).val(price_string);
		});

	});
})(jQuery);