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
            var card_cvc = $(this).find('.card-cvc');
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
        var el = $(this),
            give_form = el.parents('form.give-form'),
            id = el.attr('id'),
            card_number = give_form.find('.card-number'),
            card_cvc = give_form.find('.card-cvc'),
            card_expiry = give_form.find('.card-expiry'),
            type = $.payment.cardType(card_number.val());

        if (id.indexOf('card_number') > -1) {

            var card_type = give_form.find('.card-type');

            if (type === null) {
                card_type.removeClass().addClass('off card-type');
                el.removeClass('valid').addClass('error');
            }
            else {
                card_type.removeClass('off').addClass(type);
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
        $('#give-payment-mode-select label.give-gateway-option-selected').removeClass('give-gateway-option-selected');
        $('#give-payment-mode-select input:checked').parent().addClass('give-gateway-option-selected');
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
    doc.on('blur', '.give-donation-amount .give-text-input', function (e) {

        var parent_form = $(this).closest('form');
        var pre_focus_amount = $(this).data('amount');
        var this_value = $(this).val();
        var minimum_amount = parent_form.find('input[name="give-form-minimum"]');
        var value_min = give_unformat_currency(minimum_amount.val());
        var value_now = (this_value == 0) ? value_min : give_unformat_currency(this_value);

        //Set the custom amount input value format properly
        var format_args = {
            symbol: '',
            decimal: give_global_vars.decimal_separator,
            thousand: give_global_vars.thousands_separator,
            precision: give_global_vars.number_decimals
        };
        var formatted_total = give_format_currency(value_now, format_args);
        $(this).val(formatted_total);

        //Flag Multi-levels for min. donation conditional
        var is_level = false;
        parent_form.find('*[data-price-id]').each(function () {
            if (this.value !== 'custom' && give_unformat_currency(this.value) === value_now) {
                is_level = true;
            }
        });

        //Does this number have an accepted minimum value?
        if (( value_now < value_min || value_now < 1 ) && !is_level && value_min !== 0) {

            //It doesn't... Invalid Minimum
            $(this).addClass('give-invalid-amount');
            format_args.symbol = give_global_vars.currency_sign;
            minimum_amount = give_global_vars.bad_minimum + ' ' + give_format_currency(value_min, format_args);
            //Disable submit
            parent_form.find('.give-submit').prop('disabled', true);
            var invalid_minimum = parent_form.find('.give-invalid-minimum');
            //If no error present, create it, insert, slide down (show)
            if (invalid_minimum.length === 0) {
                var error = $('<div class="give_error give-invalid-minimum">' + minimum_amount + '</div>').hide();
                error.insertBefore(parent_form.find('.give-total-wrap')).show();
            }

        } else {

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

        var parent_form = selected_field.parents('form');
        var this_amount = selected_field.val();
        var price_id = selected_field.data('price-id');
        var currency_symbol = parent_form.find('.give-currency-symbol').text();

        //remove old selected class & add class for CSS purposes
        selected_field.parents('.give-donation-levels-wrap').find('.give-default-level').removeClass('give-default-level');
        selected_field.find('option').removeClass('give-default-level');

        if (selected_field.is('select')) {
            selected_field.find(':selected').addClass('give-default-level');
        } else {
            selected_field.addClass('give-default-level');
        }

        parent_form.find('.give-amount-top').removeClass('invalid-amount');

        //Is this a custom amount selection?
        if (this_amount === 'custom') {
            //It is, so focus on the custom amount input
            parent_form.find('.give-amount-top').val('').focus();
            return false; //Bounce out
        }

        //check if price ID blank because of dropdown type
        if (!price_id) {
            price_id = selected_field.find('option:selected').data('price-id');
        }

        //update price id field for variable products
        parent_form.find('input[name=give-price-id]').val(price_id);

        //Update hidden price field
        parent_form.find('.give-amount-hidden').val(this_amount);

        //update custom amount field
        parent_form.find('.give-amount-top').val(this_amount);
        parent_form.find('span.give-amount-top').text(this_amount);

        //update checkout total
        var formatted_total = currency_symbol + this_amount;

        if (give_global_vars.currency_pos == 'after') {
            formatted_total = this_amount + currency_symbol;
        }

        $('.give-donation-amount .give-text-input').trigger('blur');

        // trigger an event for hooks
        $(document).trigger('give_donation_value_updated', [parent_form, this_amount, price_id]);

        //Update donation form bottom total data attr and text
        parent_form.find('.give-final-total-amount').data('total', this_amount).text(formatted_total);

    }

    /**
     * Donor sent back to the form
     */
    function sent_back_to_form() {

        var form_id = give_get_parameter_by_name('form-id');
        var payment_mode = give_get_parameter_by_name('payment-mode');

        //Sanity check - only proceed if query strings in place
        if (!form_id || !payment_mode) {
            return false;
        }

        var form_wrap = $('body').find('#give-form-' + form_id + '-wrap');
        var form = form_wrap.find('form.give-form');
        var display_modal = form_wrap.hasClass('give-display-modal');
        var display_reveal = form_wrap.hasClass('give-display-reveal');

        //Update payment mode radio so it's correctly checked
        form.find('#give-gateway-radio-list label').removeClass('give-gateway-option-selected');
        form.find('input[name=payment-mode][value=' + payment_mode + ']').prop('checked', true).parent().addClass('give-gateway-option-selected');

        //This form is modal display so show the modal
        if (display_modal) {

            //@TODO: Make this DRYer - repeated in give.js
            $.magnificPopup.open({
                mainClass: 'give-modal',
                items: {
                    src: form,
                    type: 'inline'
                },
                callbacks: {
                    open: function () {
                        // Will fire when this exact popup is opened
                        // this - is Magnific Popup object
                        if ($('.mfp-content').outerWidth() >= 500) {
                            $('.mfp-content').addClass('give-responsive-mfp-content');
                        }
                    },
                    close: function () {
                        //Remove popup class
                        form.removeClass('mfp-hide');

                    }
                }
            });
        }
        //This is a reveal form, show it
        else if (display_reveal) {

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
        if (!url) url = window.location.href;
        name = name.replace(/[\[\]]/g, "\\$&");
        var regex = new RegExp("[?&]" + name + "(=([^&#]*)|&|#|$)"),
            results = regex.exec(url);
        if (!results) return null;
        if (!results[2]) return '';
        return decodeURIComponent(results[2].replace(/\+/g, " "));
    }


});
