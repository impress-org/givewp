/*!
 * Give JS
 *
 * @description: Scripts that power the Give experience
 * @package:     Give
 * @subpackage:  Assets/JS
 * @copyright:   Copyright (c) 2016, WordImpress
 * @license:     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 */

var give_scripts;

jQuery(function ($) {

	var doc = $(document);

	// Trigger float-labels
	give_fl_trigger();

	// Set custom validation message.
	give_change_html5_form_field_validation_message();

	doc.on('give_gateway_loaded', function (ev, response, form_id) {
		// Trigger float-labels
		give_fl_trigger();
	});

	doc.on('give_checkout_billing_address_updated', function (ev, response, form_id) {

		var form = $('form#' + form_id);

		if (form.hasClass('float-labels-enabled')) {

			var wrap = form.find('#give-card-state-wrap');
			var el = wrap.find('#card_state');
			var label = wrap.find('label[for="card_state"]');

			label = label.length ? label.text().replace(/[*:]/g, '').trim() : '';

			if ('nostates' === response) {
				// fix input
				el.attr('placeholder', label).parent().removeClass('styled select');
			} else {
				// fix select
				el.children().first().text(label);
				el.parent().addClass('styled select');
			}

			el.parent().removeClass('is-active');

			// Trigger float-labels
			give_fl_trigger();
		}
	});

	// Reveal Btn which displays the checkout content
	doc.on('click', '.give-btn-reveal', function (e) {
		e.preventDefault();
		var this_button = $(this);
		var this_form = $(this).parents('form');
		var payment_field_id = '#give-payment-mode-select',
			$payment_field = $(payment_field_id),
			show_field_ids = '';
		this_button.hide();

		// Show payment field if active payment gateway count greater then one.
		if( $( 'li', $payment_field ).length > 1 ) {
			show_field_ids = payment_field_id + ', ';
		}

		this_form.find( show_field_ids + '#give_purchase_form_wrap' ).slideDown();
		return false;
	});

	// Modal with Magnific
	doc.on('click', '.give-btn-modal', function (e) {
		e.preventDefault();
		var this_form_wrap = $(this).parents('div.give-form-wrap');
		var this_form = this_form_wrap.find('form.give-form');
		var this_amount_field = this_form.find('#give-amount');
		var this_amount = this_amount_field.val();

		//Check to ensure our amount is greater than 0
		//Does this number have a value
		if (!this_amount || this_amount <= 0) {
			this_amount_field.focus();
			return false;
		}

		give_open_form_modal(this_form_wrap, this_form);

	});

});

/**
 * Open form modal
 * @param $form_wrap
 * @param $form
 */
function give_open_form_modal($form_wrap, $form) {
	// Hide form children.
	var children = '#give_purchase_form_wrap, #give-payment-mode-select, .mfp-close, .give-hidden';

	//Alls well, open popup!
	jQuery.magnificPopup.open({
		mainClass   : give_global_vars.magnific_options.main_class,
		closeOnBgClick : give_global_vars.magnific_options.close_on_bg_click,
		items: {
			src: $form,
			type: 'inline'
		},
		callbacks: {
			beforeOpen: function () {
				// add title, content, goal and error to form if admin want to show button only
				if ($form_wrap.hasClass('give-display-button-only') && !$form.data('content')) {

					var $form_content = jQuery('.give-form-content-wrap', $form_wrap),
						$form_title = jQuery('.give-form-title', $form_wrap),
						$form_goal = jQuery('.give-goal-progress', $form_wrap),
						$form_error = jQuery('>.give_error', $form_wrap),
						$form_errors = jQuery('.give_errors', $form_wrap);

					// Add content container to form.
					if ($form_content.length && !jQuery('.give-form-content-wrap', $form).length) {
						if ($form_content.hasClass('give_post_form-content')) {
							$form.append($form_content);
						} else {
							$form.prepend($form_content);
						}
					}

					// Add errors container to form.
					if ($form_errors.length && !jQuery('.give_errors', $form).length) {
						$form_errors.each(function (index, $error) {
							$form.prepend(jQuery($error));
						});
					}

					// Add error container to form.
					if ($form_error.length && !jQuery('>.give_error', $form).length) {
						$form_error.each(function (index, $error) {
							$form.prepend(jQuery($error));
						});
					}

					// Add goal container to form.
					if ($form_goal.length && !jQuery('.give-goal-progress', $form).length) {
						$form.prepend($form_goal);
					}

					// Add title container to form.
					if ($form_title.length && !jQuery('.give-form-title', $form).length) {
						$form.prepend($form_title);
					}

					$form.data('content', 'loaded');
				}
			},
			open: function () {
				// Will fire when this exact popup is opened
				// this - is Magnific Popup object
				var $mfp_content = jQuery('.mfp-content');
				if ($mfp_content.outerWidth() >= 500) {
					$mfp_content.addClass('give-responsive-mfp-content');
				}



				// Hide .give-hidden and .give-btn-modal  if admin only want to show only button.
				if ($form_wrap.hasClass('give-display-button-only')) {
					children = $form.children().not('.give-hidden, .give-btn-modal');
				}

				//Hide all form elements besides the ones required for payment
				$form.children().not(children).hide();
			},
			close: function () {
				//Remove popup class
				$form.removeClass('mfp-hide');

				//Show all fields again
				$form.children().not(children).show();
			}
		}
	});
}

/**
 * Floating Labels Custom Events
 */
function give_fl_trigger() {
	var options = {
		exclude: ['#give-amount, .give-select-level, .multiselect, .give-repeater-table input, input[type="url"]'],
		customEvent: give_fl_custom_events
	};
	jQuery('.float-labels-enabled').floatlabels(options);
}

/**
 * Floating Labels Custom Events
 * @param el
 */
function give_fl_custom_events(el) {
	if (el.hasClass('card-number')) {
		el.after('<span class="off card-type"/>');
	}
}

/**
 * Change localize html5 form validation message
 */
function give_change_html5_form_field_validation_message() {
	var $forms = jQuery('.give-form'),
		$input_fields;

	// Bailout if no any donation from exist.
	if (!$forms.length) {
		return;
	}

	jQuery.each($forms, function (index, $form) {
		// Get form input fields.
		$input_fields = jQuery('input', $form);

		// Bailout.
		if (!$input_fields.length) {
			return;
		}

		jQuery.each($input_fields, function (index, item) {
			item = jQuery(item).get(0);

			// Set custom message only if translation exit in give global object.
			if (give_global_vars.form_translation.hasOwnProperty(item.name)) {
				item.oninvalid = function (e) {
					e.target.setCustomValidity('');
					if (!e.target.validity.valid) {
						e.target.setCustomValidity(give_global_vars.form_translation[item.name]);
					}
				};
			}
		});
	});
}
