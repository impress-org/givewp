/**
 * Give Form Checkout JS
 */
jQuery(document).ready(function($) {
    var $body = $('body'),
		$form = $("#give_purchase_form"),
        $give_cart_amount = $('.give_cart_amount');

    // Update state/province field on checkout page
    $body.on('change', '#give_cc_address input.card_state, #give_cc_address select', function() {
        var $this = $(this);
        if( 'card_state' != $this.attr('id') ) {

            // If the country field has changed, we need to update the state/province field
            var postData = {
                action: 'give_get_shop_states',
                country: $this.val(),
                field_name: 'card_state'
            };

            $.ajax({
                type: "POST",
                data: postData,
                url: give_global_vars.ajaxurl,
                xhrFields: {
                    withCredentials: true
                },
                success: function (response) {
					if( 'nostates' == response ) {
						var text_field = '<input type="text" name="card_state" class="cart-state give-input required" value=""/>';
						$form.find('input[name="card_state"], select[name="card_state"]').replaceWith( text_field );
					} else {
						$form.find('input[name="card_state"], select[name="card_state"]').replaceWith( response );
					}
                    $('body').trigger('give_cart_billing_address_updated', [ response ]);
                }
            }).fail(function (data) {
                if ( window.console && window.console.log ) {
                    console.log( data );
                }
            }).done(function (data) {

            });
        }

        return false;
    });


    /* Credit card verification */
    $body.on('keyup change', '.give-do-validate .card-number', function() {
        give_validate_card( $(this) );
    });

    function give_validate_card( field ) {
        var card_field = field;
        card_field.validateCreditCard(function(result) {
            var $card_type = $('.card-type');

            if(result.card_type == null) {
                $card_type.removeClass().addClass('off card-type');
                card_field.removeClass('valid');
                card_field.addClass('error');
            } else {
                $card_type.removeClass('off');
                $card_type.addClass( result.card_type.name );
                if (result.length_valid && result.luhn_valid) {
                    card_field.addClass('valid');
                    card_field.removeClass('error');
                } else {
                    card_field.removeClass('valid');
                    card_field.addClass('error');
                }
            }
        });
    }

    // Make sure a gateway is selected
    $body.on('submit', '#give_payment_mode', function() {
        var gateway = $('#give-gateway option:selected').val();
        if( gateway == 0 ) {
            alert( give_global_vars.no_gateway );
            return false;
        }
    });

    // Add a class to the currently selected gateway on click
    $body.on('click', '#give-payment-mode-select input', function() {
        $('#give-payment-mode-select label.give-gateway-option-selected').removeClass( 'give-gateway-option-selected' );
        $('#give-payment-mode-select input:checked').parent().addClass( 'give-gateway-option-selected' );
    });

    /* Discounts */
    var before_discount = $give_cart_amount.text(),
        $checkout_form_wrap = $('#give_checkout_form_wrap');

    // Validate and apply a discount
    $checkout_form_wrap.on('click', '.give-apply-discount', function (event) {

    	event.preventDefault();

        var $this = $(this),
            discount_code = $('#give-discount').val(),
            give_discount_loader = $('#give-discount-loader');

        if (discount_code == '' || discount_code == give_global_vars.enter_discount ) {
            return false;
        }

        var postData = {
            action: 'give_apply_discount',
            code: discount_code
        };

        $('#give-discount-error-wrap').html('').hide();
        give_discount_loader.show();

        $.ajax({
            type: "POST",
            data: postData,
            dataType: "json",
            url: give_global_vars.ajaxurl,
            xhrFields: {
                withCredentials: true
            },
            success: function (discount_response) {
                if( discount_response ) {
                    if (discount_response.msg == 'valid') {
                        $('.give_cart_discount').html(discount_response.html);
                        $('.give_cart_discount_row').show();
                        $('.give_cart_amount').each(function() {
                            $(this).text(discount_response.total);
                        });
                        $('#give-discount', $checkout_form_wrap ).val('');

                        recalculate_taxes();

                    	if( '0.00' == discount_response.total_plain ) {

                    		$('#give_cc_fields,#give_cc_address').slideUp();
                    		$('input[name="give-gateway"]').val( 'manual' );

                    	} else {

                    		$('#give_cc_fields,#give_cc_address').slideDown();

                    	}

						$('body').trigger('give_discount_applied', [ discount_response ]);

                    } else {
                        $('#give-discount-error-wrap').html( '<span class="give_error">' + discount_response.msg + '</span>' );
                        $('#give-discount-error-wrap').show();
                        $('body').trigger('give_discount_invalid', [ discount_response ]);
                    }
                } else {
                    if ( window.console && window.console.log ) {
                        console.log( discount_response );
                    }
                    $('body').trigger('give_discount_failed', [ discount_response ]);
                }
                give_discount_loader.hide();
            }
        }).fail(function (data) {
            if ( window.console && window.console.log ) {
                console.log( data );
            }
        });

        return false;
    });

    // Prevent the checkout form from submitting when hitting Enter in the discount field
    $checkout_form_wrap.on('keypress', '#give-discount', function (event) {
        if (event.keyCode == '13') {
            return false;
        }
    });

    // Apply the discount when hitting Enter in the discount field instead
    $checkout_form_wrap.on('keyup', '#give-discount', function (event) {
        if (event.keyCode == '13') {
            $checkout_form_wrap.find('.give-apply-discount').trigger('click');
        }
    });

    // Remove a discount
    $body.on('click', '.give_discount_remove', function (event) {

        var $this = $(this), postData = {
            action: 'give_remove_discount',
            code: $this.data('code')
        };

        $.ajax({
            type: "POST",
            data: postData,
            dataType: "json",
            url: give_global_vars.ajaxurl,
            xhrFields: {
                withCredentials: true
            },
            success: function (discount_response) {

                $('.give_cart_amount').each(function() {
                	if( give_global_vars.currency_sign + '0.00' == $(this).text() || '0.00' + give_global_vars.currency_sign == $(this).text() ) {
                		// We're removing a 100% discount code so we need to force the payment gateway to reload
                		window.location.reload();
                	}
                    $(this).text(discount_response.total);
                });

                $('.give_cart_discount').html(discount_response.html);

                if( ! discount_response.discounts ) {
                   $('.give_cart_discount_row').hide();
                }


                recalculate_taxes();

                $('#give_cc_fields,#give_cc_address').slideDown();

				$('body').trigger('give_discount_removed', [ discount_response ]);
            }
        }).fail(function (data) {
            if ( window.console && window.console.log ) {
                console.log( data );
            }
        });

        return false;
    });

    // When discount link is clicked, hide the link, then show the discount input and set focus.
    $body.on('click', '.give_discount_link', function(e) {
        e.preventDefault();
        $('.give_discount_link').parent().hide();
        $('#give-discount-code-wrap').show().find('#give-discount').focus();
    });

    // Hide / show discount fields for browsers without javascript enabled
    $body.find('#give-discount-code-wrap').hide();
    $body.find('#give_show_discount').show();

    // Update the checkout when item quantities are updated
    $(document).on('change', '.give-item-quantity', function (event) {

        var $this = $(this),
            quantity = $this.val(),
            key = $this.data('key'),
            download_id = $this.closest('tr.give_cart_item').data('download-id'),
            options = $this.parent().find('input[name="give-cart-download-' + key + '-options"]').val();

        var postData = {
            action: 'give_update_quantity',
            quantity: quantity,
            download_id: download_id,
            options: options
        };

        //give_discount_loader.show();

        $.ajax({
            type: "POST",
            data: postData,
            dataType: "json",
            url: give_global_vars.ajaxurl,
            xhrFields: {
                withCredentials: true
            },
            success: function (response) {

                console.log( response );
                $('.give_cart_subtotal_amount').each(function() {
                    $(this).text(response.subtotal);
                });

                $('.give_cart_tax_amount').each(function() {
                    $(this).text(response.taxes);
                });

                $('.give_cart_amount').each(function() {
                    $(this).text(response.total);
                    $('body').trigger('give_quantity_updated', [ response ]);
                });
            }
        }).fail(function (data) {
            if ( window.console && window.console.log ) {
                console.log( data );
            }
        });

        return false;
    });

});
