/*!
 * Give AJAX JS
 *
 * @description: The Give AJAX scripts
 * @package:     Give
 * @subpackage:  Assets/JS
 * @copyright:   Copyright (c) 2015, WordImpress
 * @license:     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 */
var give_scripts, give_global_vars;
jQuery( document ).ready( function ( $ ) {

	//Run tooltips setup
	setup_give_tooltips();

	//Hide loading elements
	$( '.give-loading-text' ).hide();

	// Show the login form in the checkout when the user clicks the "Login" link
	$( 'body' ).on( 'click', '.give_checkout_register_login', function ( e ) {
		var $this = $( this );
		var data = {
			action: $this.data( 'action' )
		};
		var this_form = $( this ).parents( 'form' );
		var register_loading_img = $( this_form ).find( '#give-login-account-wrap .give-loading-text' );

		// Show the ajax loader
		register_loading_img.show();

		$.post( give_scripts.ajaxurl, data, function ( checkout_response ) {
			$( this_form ).find( '#give_checkout_login_register' ).html( '' ).html( checkout_response );

		} ).done( function () {
			setup_form_loading_images();
			// Hide the ajax loader
			register_loading_img.hide();
		} );
		return false;
	} );


	// Process the login form via ajax when the user clicks "login"
	$( document ).on( 'click', '#give_login_fields input[type=submit]', function ( e ) {

		e.preventDefault();

		var complete_purchase_val = $( this ).val();
		var this_form = $( this ).parents( 'form' );

		$( this ).val( give_global_vars.purchase_loading );

		this_form.find( '#give_login_fields .give-loading-animation' ).fadeIn();

		var data = {
			action         : 'give_process_checkout_login',
			give_ajax      : 1,
			give_user_login: this_form.find( '#give_user_login' ).val(),
			give_user_pass : this_form.find( '#give_user_pass' ).val()
		};

		$.post( give_global_vars.ajaxurl, data, function ( data ) {

			//user is logged in
			if ( $.trim( data ) == 'success' ) {
				//remove errors
				this_form.find( '.give_errors' ).remove();
				//reload the selected gateway so it contains their logged in information
				give_load_gateway( this_form, this_form.find( '.give-gateway-option-selected input' ).val() );
			} else {
				//Login failed, show errors
				this_form.find( '#give_login_fields input[type=submit]' ).val( complete_purchase_val );
				this_form.find( '.give-loading-animation' ).fadeOut();
				this_form.find( '.give_errors' ).remove();
				this_form.find( '#give-user-login-submit' ).before( data );
			}
		} );

	} );

	//Switch the gateway on gateway selection field change
	$( 'select#give-gateway, input.give-gateway' ).on( 'change', function ( e ) {

		e.preventDefault();

		//Which payment gateway to load?
		var payment_mode = $( this ).val();

		//Problema? Bounce
		if ( payment_mode == '0' ) {
			console.log( 'There was a problem loading the selected gateway' );
			return false;
		}

		give_load_gateway( $( this ).parents( 'form' ), payment_mode );

		return false;

	} );


	//Process the donation submit
	$( 'body' ).on( 'click touchend', '#give-purchase-button', function ( e ) {

		//this form object
		var this_form = $( this ).parents( 'form.give-form' );

		//loading animation
		var loading_animation = this_form.find( '#give_purchase_submit .give-loading-animation' );
		loading_animation.fadeIn();

		//this form selector
		var give_purchase_form = this_form.get( 0 );

		//check validity
		if ( typeof give_purchase_form.checkValidity === "function" && give_purchase_form.checkValidity() === false ) {
			loading_animation.fadeOut(); //Don't leave any handing loading animations
			return;
		}

		//prevent form from submitting normally
		e.preventDefault();

		//Submit btn text
		var complete_purchase_val = $( this ).val();

		//Update submit button text
		$( this ).val( give_global_vars.purchase_loading );

		//Submit form via AJAX
		$.post( give_global_vars.ajaxurl, this_form.serialize() + '&action=give_process_checkout&give_ajax=true', function ( data ) {

			if ( $.trim( data ) == 'success' ) {
				//Remove any errors
				this_form.find( '.give_errors' ).remove();
				//Submit form for normal processing
				$( give_purchase_form ).submit();
			} else {
				this_form.find( '#give-purchase-button' ).val( complete_purchase_val );
				loading_animation.fadeOut();
				this_form.find( '.give_errors' ).remove();
				this_form.find( '#give_purchase_submit' ).before( data );
			}
		} );

	} );

} );

/**
 * Load the Payment Gateways
 *
 * @description: AJAX load appropriate gateway fields
 * @param form_object Obj The specific form to load a gateway for
 * @param payment_mode
 */
function give_load_gateway( form_object, payment_mode ) {

	var loading_element = jQuery( form_object ).find( '#give-payment-mode-select .give-loading-text' );
	var give_total = jQuery( form_object ).find( '#give-amount' ).val();
	var give_form_id = jQuery( form_object ).find( 'input[name="give-form-id"]' ).val();

	// Show the ajax loader
	loading_element.fadeIn();


	var form_data = jQuery( form_object ).data();

	if ( form_data["blockUI.isBlocked"] != 1 ) {
		jQuery( form_object ).find( '#give_purchase_form_wrap' ).block( {
			message   : null,
			overlayCSS: {
				background: '#fff',
				opacity   : 0.6
			}
		} );
	}

	//Post via AJAX to Give
	jQuery.post( give_scripts.ajaxurl + '?payment-mode=' + payment_mode, {
			action           : 'give_load_gateway',
			give_total       : give_total,
			give_form_id     : give_form_id,
			give_payment_mode: payment_mode
		},
		function ( response ) {
			//Success: let's output the gateway fields in the appropriate form space
			jQuery( form_object ).unblock();
			jQuery( form_object ).find( '#give_purchase_form_wrap' ).html( response );
			jQuery( '.give-no-js' ).hide();
			jQuery( form_object ).find( '#give-payment-mode-wrap .give-loading-text' ).fadeOut();
			setup_give_tooltips();

			// trigger an event on success for hooks
			jQuery( document ).trigger( 'give_gateway_loaded', [ response, jQuery( form_object ).attr( 'id' ) ] );
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
