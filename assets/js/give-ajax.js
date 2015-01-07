/*!
 * Give AJAX JS
 *
 * @description: The Give AJAX scripts
 * @package:     Give
 * @subpackage:  Assets/JS
 * @copyright:   Copyright (c) 2014, WordImpress
 * @license:     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 */
var give_scripts;
jQuery( document ).ready( function ( $ ) {

	// Auto load default payment gateway
	if ( $( 'input.give-gateway' ).length ) {
		setTimeout( function () {
			give_load_gateway( give_scripts.default_gateway );
		}, 200 );
	}

	//Run tooltips setup
	setup_give_tooltips();

	// Show the login form on the checkout page
	$( 'body' ).on( 'click', '.give_checkout_register_login', function ( e ) {
		var $this = $( this ),
			data = {
				action: $this.data( 'action' )
			};
		// Show the ajax loader
		$( '.give-cart-ajax' ).show();

		$.post( give_scripts.ajaxurl, data, function ( checkout_response ) {
			$( '#give_checkout_login_register' ).html( give_scripts.loading );
			$( '#give_checkout_login_register' ).html( checkout_response );
			// Hide the ajax loader
			$( '.give-cart-ajax' ).hide();
		} );
		return false;
	} );

	// Process the login form via ajax
	$( document ).on( 'click', '#give-user-login-submit input[type=submit]', function ( e ) {

		e.preventDefault();

		var complete_purchase_val = $( this ).val();

		$( this ).val( give_global_vars.purchase_loading );

		$( this ).after( '<span class="give-cart-ajax"><i class="give-icon-spinner give-icon-spin"></i></span>' );

		var data = {
			action         : 'give_process_checkout_login',
			give_ajax      : 1,
			give_user_login: $( '#give_user_login' ).val(),
			give_user_pass : $( '#give_user_pass' ).val()
		};

		$.post( give_global_vars.ajaxurl, data, function ( data ) {

			if ( $.trim( data ) == 'success' ) {
				$( '.give_errors' ).remove();
				window.location.reload();
			} else {
				$( '#give_login_fields input[type=submit]' ).val( complete_purchase_val );
				$( '.give-cart-ajax' ).remove();
				$( '.give_errors' ).remove();
				$( '#give-user-login-submit' ).before( data );
			}
		} );

	} );

	// Load the fields for the selected payment method
	$( 'select#give-gateway, input.give-gateway' ).change( function ( e ) {

		var payment_mode = $( '#give-gateway option:selected, input.give-gateway:checked' ).val();

		if ( payment_mode == '0' ) {
			return false;
		}

		give_load_gateway( payment_mode );

		return false;

	} );


	$( document ).on( 'click', '.give_form #give_purchase_submit input[type=submit]', function ( e ) {

		var givePurchaseform = $( '.give-form' ).get( 0 );

		if ( typeof givePurchaseform.checkValidity === "function" && false === givePurchaseform.checkValidity() ) {
			return;
		}

		//prevent form from submitting normally
		e.preventDefault();

		//this form
		var this_form = $( this ).parents( 'form:first' );

		//Submit btn text
		var complete_purchase_val = $( this ).val();

		//Update submit button text
		$( this ).val( give_global_vars.purchase_loading );

		//Submit form via AJAX
		$.post( give_global_vars.ajaxurl, this_form.serialize() + '&action=give_process_checkout&give_ajax=true', function ( data ) {

			if ( $.trim( data ) == 'success' ) {
				//Remove any errors
				$( '.give_errors' ).remove();
				//Submit form for normal processing
				$( givePurchaseform ).submit();
			} else {
				$( '#give-purchase-button' ).val( complete_purchase_val );
				$( '.give-cart-ajax' ).remove();
				$( '.give_errors' ).remove();
				$( '#give_purchase_submit' ).before( data );
			}
		} );

	} );

} );

function give_load_gateway( payment_mode ) {

	var give_form = jQuery( '#give_purchase_form_wrap' );

	// Show the ajax loader
	jQuery( '#give-payment-mode-wrap' ).find( '.give-loading-animation' ).css( 'background-image', 'url(' + give_scripts.ajax_loader + ')' );
	jQuery( '#give-payment-mode-wrap .give-loading-text' ).fadeIn();

	//Update form action
	give_form.attr( 'action', '?payment-mode=' + payment_mode );

	//Post via AJAX to Give
	jQuery.post( give_scripts.ajaxurl + '?payment-mode=' + payment_mode, {
			action           : 'give_load_gateway',
			give_total       : jQuery( '#give-amount' ).val(),
			give_form_id     : jQuery( 'input[name="give-form-id"]' ).val(),
			give_payment_mode: payment_mode
		},
		function ( response ) {
			jQuery( '#give_purchase_form_wrap' ).html( response );
			jQuery( '.give-no-js' ).hide();
			jQuery( '#give-payment-mode-wrap .give-loading-text' ).fadeOut();
			setup_give_tooltips();

		}
	);

}

function setup_give_tooltips() {
	jQuery( '[data-tooltip!=""]' ).qtip( { // Grab all elements with a non-blank data-tooltip attr.
		content : {
			attr: 'data-tooltip' // Tell qTip2 to look inside this attr for its content
		},
		style   : {classes: 'qtip-rounded qtip-tipsy'},
		position: {
			my: 'bottom center',  // Position my top left...
			at: 'top center' // at the bottom right of...
		}
	} )
}