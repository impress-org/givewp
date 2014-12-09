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
		default_radio.prop( 'checked', false );

		default_radio.on( 'change', function () {
			default_radio.not( this ).prop( 'checked', false );
		} );

	};


	//On DOM Ready
	$( function () {

		// More code using $ as alias to jQuery
		handle_default_radio();

	} );


})( jQuery );