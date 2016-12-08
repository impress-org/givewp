/*!
 * Give JS
 *
 * @description: Scripts that power the Give experience
 * @package:     Give
 * @subpackage:  Assets/JS
 * @copyright:   Copyright (c) 2016, WordImpress
 * @license:     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 */


var give_global_vars;

jQuery( function ( $ ) {

	var doc = $( document );

	// Trigger float-labels
	give_fl_trigger();

    // Set custom validation message.
    give_change_html5_form_field_validation_message();

	doc.on( 'give_gateway_loaded', function ( ev, response, form_id ) {
		// Trigger float-labels
		give_fl_trigger();
	} );

	doc.on( 'give_checkout_billing_address_updated', function ( ev, response, form_id ) {

		var form  = $( 'form#' + form_id );

		if ( form.hasClass( 'float-labels-enabled' ) ) {

			var wrap  = form.find( '#give-card-state-wrap' );
			var el    = wrap.find( '#card_state' );
			var label = wrap.find( 'label[for="card_state"]' );

			label = label.length ? label.text().replace( /[*:]/g, '' ).trim() : '';

			if ( 'nostates' === response ) {
				// fix input
				el.attr( 'placeholder', label ).parent().removeClass( 'styled select' );
			} else {
				// fix select
				el.children().first().text( label );
				el.parent().addClass( 'styled select' );
			}

			el.parent().removeClass( 'is-active' );

			// Trigger float-labels
			give_fl_trigger();
		}
	} );

	// Reveal Btn which displays the checkout content
	doc.on( 'click', '.give-btn-reveal', function ( e ) {
		e.preventDefault();
		var this_button = $( this );
		var this_form = $( this ).parents( 'form' );
		this_button.hide();
		this_form.find( '#give-payment-mode-select, #give_purchase_form_wrap' ).slideDown();
		return false;
	} );

	// Modal with Magnific
	doc.on( 'click', '.give-btn-modal', function ( e ) {
		e.preventDefault();
		var this_form_wrap = $( this ).parents( 'div.give-form-wrap' );
		var this_form = this_form_wrap.find( 'form.give-form' );
		var this_amount_field = this_form.find( '#give-amount' );
		var this_amount = this_amount_field.val();
		//Check to ensure our amount is greater than 0

		//Does this number have a value
		if ( !this_amount || this_amount <= 0 ) {
			this_amount_field.focus();
			return false;
		}

		//Alls well, open popup!
		$.magnificPopup.open( {
			mainClass   : give_global_vars.magnific_options.main_class,
			closeOnBgClick : give_global_vars.magnific_options.close_on_bg_click,
			items       : {
				src : this_form,
				type: 'inline'
			},
			callbacks   : {
				open : function () {
					// Will fire when this exact popup is opened
					// this - is Magnific Popup object
					if ( $( '.mfp-content' ).outerWidth() >= 500 ) {
						$( '.mfp-content' ).addClass( 'give-responsive-mfp-content' );
					}
					//Hide all form elements besides the ones required for payment
					this_form.children().not( '#give_purchase_form_wrap, #give-payment-mode-select, .mfp-close' ).hide();

				},
				close: function () {
					//Remove popup class
					this_form.removeClass( 'mfp-hide' );
					//Show all fields again
					this_form.children().not( '#give_purchase_form_wrap, #give-payment-mode-select, .mfp-close, .give-hidden' ).show();
				}
			}
		} );
	} );

} );

/**
 * Floating Labels Custom Events
 */
function give_fl_trigger() {
	var options = {
		exclude    : ['#give-amount, .give-select-level, .multiselect, .give-repeater-table input, input[type="url"]'],
		customEvent: give_fl_custom_events
	};
	jQuery( '.float-labels-enabled' ).floatlabels( options );
}

/**
 * Floating Labels Custom Events
 * @param el
 */
function give_fl_custom_events( el ) {
	if ( el.hasClass( 'card-number' ) ) {
		el.after( '<span class="off card-type"/>' );
	}
}


/**
 * Change localize html5 form validation message
 */
function give_change_html5_form_field_validation_message() {
	var $forms = jQuery( '.give-form' ),
        $input_fields;

    // Bailout if no any donation from exist.
    if( ! $forms.length ) {
        return;
    }

    jQuery.each( $forms, function( index, $form ){
        // Get form input fields.
        $input_fields = jQuery( 'input', $form );

        // Bailout.
        if( ! $input_fields.length ) {
            return;
        }

        jQuery.each( $input_fields, function( index, item ){
            item = jQuery( item ).get(0);

            // Set custom message only if translation exit in give global object.
            if( give_global_vars.form_translation.hasOwnProperty( item.name ) ) {
                item.oninvalid = function (e) {
                    e.target.setCustomValidity('');
                    if ( ! e.target.validity.valid ){
                        e.target.setCustomValidity( give_global_vars.form_translation[ item.name ] );
                    }
                };
            }
        });
    });
}
