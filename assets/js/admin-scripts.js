/**
 * Give Admin JS
 *
 */

jQuery.noConflict();
(function ( $ ) {

//--------------------------------------
// Single Forms
//--------------------------------------

	//Default Radio Button: Allow only one to be checked
	var handle_default_radio = function () {
		"use strict";
		var default_radio = $( '#_give_donation_levels_repeat input[type=radio]' );
		default_radio.on( 'change', function () {
			default_radio.not( this ).prop( 'checked', false );
		} );

	};


	/**
	 * Toggle Conditional Form Fields
	 */
	var toggle_conditional_form_fields = function () {

		//Price Option
		var price_option = $( '.cmb2-id--give-price-option input:radio' );

		price_option.on( 'change', function () {

			var price_option_val = $( '.cmb2-id--give-price-option input:radio:checked' ).val();
			if ( price_option_val === 'set' ) {
				$( '.cmb2-id--give-set-price' ).show();
				$( '.cmb2-id--give-levels-header, .cmb2-id--give-levels-header + .cmb-repeat-group-wrap' ).hide();
			} else {
				$( '.cmb2-id--give-set-price' ).hide();
				$( '.cmb2-id--give-levels-header, .cmb2-id--give-levels-header + .cmb-repeat-group-wrap' ).show();
			}
		} ).change();


	};


	//On DOM Ready
	$( function () {

		handle_default_radio();
		toggle_conditional_form_fields();

	} );


})( jQuery );