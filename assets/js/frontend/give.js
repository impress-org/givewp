/*!
 * Give JS
 *
 * @description: Scripts that power the Give experience
 * @package:     Give
 * @subpackage:  Assets/JS
 * @copyright:   Copyright (c) 2015, WordImpress
 * @license:     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 */
var give_scripts;
jQuery( document ).ready( function ( $ ) {

	var $body = $( 'body' );

	// Reveal Btn which displays the checkout content
	$body.on( 'click', '.give-btn-reveal', function ( e ) {
		e.preventDefault();
		var this_button = $( this );
		var this_form = $( this ).parents( 'form' );
		this_button.hide();
		this_form.find( '#give-payment-mode-select, #give_purchase_form_wrap' ).slideDown();
		return false;
	} );

	// Modal with Magnific
	$body.on( 'click', '.give-btn-modal', function ( e ) {
		e.preventDefault();
		var this_form = $( this ).parents( 'div.give-form-wrap' );
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
			items  : {
				src : this_form,
				type: 'inline'
			},
			open: function () {
				// Will fire when this exact popup is opened
				// this - is Magnific Popup object


			},
			close  : function () {
				//Remove popup class
				this_form.removeClass( 'mfp-hide' );
			}
		} );

	} );


} );

