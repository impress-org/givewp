/*!
 * Give AJAX JS
 *
 * @description: The Give AJAX scripts
 * @package:     Give
 * @subpackage:  Assets/JS
 * @copyright:   Copyright (c) 2016, WordImpress
 * @license:     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 */
var give_scripts, give_global_vars;
jQuery(document).ready(function ($) {

    //Run tooltips setup
    setup_give_tooltips();

    //Hide loading elements
    $('.give-loading-text').hide();

    // Show the login form in the checkout when the user clicks the "Login" link
    $(document).on('click', '.give-checkout-login', function (e) {
        var $this = $(this);
        var this_form = $(this).parents('form');
        var loading_animation = $(this_form).find('[id^="give-checkout-login-register"] .give-loading-text');
        var data = {
            action: $this.data('action'),
            form_id: $(this_form).find('[name="give-form-id"]').val()
        };

        // Show the ajax loader
        loading_animation.show();

        $.post(give_scripts.ajaxurl, data, function (checkout_response) {

            //Clear form HTML and add AJAX response containing fields
            $(this_form).find('[id^=give-checkout-login-register]').html(checkout_response);
            $(this_form).find('.give-submit-button-wrap').remove();

        }).done(function () {
            // Hide the ajax loader
            loading_animation.hide();
            // Trigger float-labels
            give_fl_trigger();
            //Setup tooltips again
            setup_give_tooltips();
        });
        return false;
    });

    // Register/Login Cancel
    $(document).on('click', '.give-checkout-register-cancel', function (e) {
        e.preventDefault();
        //User cancelled login
        var $this = $(this);
        var this_form = $(this).parents('form');
        var data = {
            action: $this.data('action'),
            form_id: $(this_form).find('[name="give-form-id"]').val()
        };
        //AJAX get the payment fields
        $.post(give_scripts.ajaxurl, data, function (checkout_response) {
            //Show fields
            $(this_form).find('[id^=give-checkout-login-register]').html($.parseJSON(checkout_response.fields));
            $(this_form).find('[id^=give_purchase_submit]').append($.parseJSON(checkout_response.submit));
        }).done(function () {
            // Trigger float-labels
            give_fl_trigger();
            //Setup tooltips again
            setup_give_tooltips();
        });
    });

    // Process the login form via ajax when the user clicks "login"
    $(document).on('click', '[id^=give-login-fields] input[type=submit]', function (e) {

        e.preventDefault();

        var complete_purchase_val = $(this).val();
        var this_form = $(this).parents('form');

        $(this).val(give_global_vars.purchase_loading);

        this_form.find('[id^=give-login-fields] .give-loading-animation').fadeIn();

        var data = {
            action: 'give_process_checkout_login',
            give_ajax: 1,
            give_user_login: this_form.find('[name=give_user_login]').val(),
            give_user_pass: this_form.find('[name=give_user_pass]').val()
        };

        $.post(give_global_vars.ajaxurl, data, function (data) {

            //user is logged in
            if ($.trim(data) == 'success') {
                //remove errors
                this_form.find('.give_errors').remove();
                //reload the selected gateway so it contains their logged in information
                give_load_gateway(this_form, this_form.find('.give-gateway-option-selected input').val());
            } else {
                //Login failed, show errors
                this_form.find('[id^=give-login-fields] input[type=submit]').val(complete_purchase_val);
                this_form.find('.give-loading-animation').fadeOut();
                this_form.find('.give_errors').remove();
                this_form.find('[id^=give-user-login-submit]').before(data);
            }
        });

    });

    //Switch the gateway on gateway selection field change
    $('select#give-gateway, input.give-gateway').on('change', function (e) {

        e.preventDefault();

        //Which payment gateway to load?
        var payment_mode = $(this).val();

        //Problema? Bounce
        if (payment_mode == '0') {
            console.log('There was a problem loading the selected gateway');
            return false;
        }

        give_load_gateway($(this).parents('form'), payment_mode);

        return false;

    });


    /**
     * Donation Form AJAX Submission
     *
     * @description: Process the donation submit
     */
    $('body').on('click touchend', '#give-purchase-button', function (e) {

        //this form object
        var this_form = $(this).parents('form.give-form');

        //loading animation
        var loading_animation = this_form.find('#give_purchase_submit .give-loading-animation');
        loading_animation.fadeIn();

        //this form selector
        var give_purchase_form = this_form.get(0);

        //HTML5 required check validity
        if (typeof give_purchase_form.checkValidity === "function" && give_purchase_form.checkValidity() === false) {

            //Don't leave any hanging loading animations
            loading_animation.fadeOut();

            //Check for Safari (doesn't support HTML5 required)
            if ((navigator.userAgent.indexOf('Safari') != -1 && navigator.userAgent.indexOf('Chrome') == -1) === false) {
                //Not safari: Support HTML5 "required" so skip the rest of this function
                return;
            }

        }

        //prevent form from submitting normally
        e.preventDefault();

        //Submit btn text
        var complete_purchase_val = $(this).val();

        //Update submit button text
        $(this).val(give_global_vars.purchase_loading);

        //Submit form via AJAX
        $.post(give_global_vars.ajaxurl, this_form.serialize() + '&action=give_process_checkout&give_ajax=true', function (data) {

            if ($.trim(data) == 'success') {
                //Remove any errors
                this_form.find('.give_errors').remove();
                //Submit form for normal processing
                $(give_purchase_form).submit();
            } else {
                //There was an error / remove old errors and prepend new ones
                this_form.find('#give-purchase-button').val(complete_purchase_val);
                loading_animation.fadeOut();
                this_form.find('.give_errors').remove();
                this_form.find('#give_purchase_submit').before(data);
            }

        });

    });

});

/**
 * Load the Payment Gateways
 *
 * @description: AJAX load appropriate gateway fields
 * @param form_object Obj The specific form to load a gateway for
 * @param payment_mode
 */
function give_load_gateway(form_object, payment_mode) {

    var loading_element = jQuery(form_object).find('#give-payment-mode-select .give-loading-text');
    var give_total = jQuery(form_object).find('#give-amount').val();
    var give_form_id = jQuery(form_object).find('input[name="give-form-id"]').val();

    // Show the ajax loader
    loading_element.fadeIn();

    var form_data = jQuery(form_object).data();

    if (form_data["blockUI.isBlocked"] != 1) {
        jQuery(form_object).find('#give_purchase_form_wrap').block({
            message: null,
            overlayCSS: {
                background: '#fff',
                opacity: 0.6
            }
        });
    }

    //Post via AJAX to Give
    jQuery.post(give_scripts.ajaxurl + '?payment-mode=' + payment_mode, {
            action: 'give_load_gateway',
            give_total: give_total,
            give_form_id: give_form_id,
            give_payment_mode: payment_mode
        },
        function (response) {
            //Success: let's output the gateway fields in the appropriate form space
            jQuery(form_object).unblock();
            jQuery(form_object).find('#give_purchase_form_wrap').html(response);
            jQuery('.give-no-js').hide();
            jQuery(form_object).find('#give-payment-mode-wrap .give-loading-text').fadeOut();
            setup_give_tooltips();

            // trigger an event on success for hooks
            jQuery(document).trigger('give_gateway_loaded', [response, jQuery(form_object).attr('id')]);
        }
    );

}

/**
 * Load Tooltips
 *
 * @description Give tooltips use qTip2
 * @since 1.0
 */
function setup_give_tooltips() {
    jQuery('[data-tooltip!=""]').qtip({ // Grab all elements with a non-blank data-tooltip attr.
        content: {
            attr: 'data-tooltip' // Tell qTip2 to look inside this attr for its content
        },
        style: {classes: 'qtip-rounded qtip-tipsy'},
        position: {
            my: 'bottom center',  // Position my top left...
            at: 'top center' // at the bottom right of...
        }
    });
    jQuery.fn.qtip.zindex = 2147483641; // Higher z-index than Give's magnific modal

}
