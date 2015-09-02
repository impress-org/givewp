/*!
 * Give Admin Widgets JS
 *
 * @description: The Give Admin Widget scripts. Only enqueued on the admin widgets screen; used to validate fields, show/hide, and other functions
 * @package:     Give
 * @subpackage:  Assets/JS
 * @copyright:   Copyright (c) 2015, WordImpress
 * @license:     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 */

jQuery.noConflict();
(function ( $ ) {

	var initialize_qtips = function () {
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
	};


	//On DOM Ready
	$( function () {

		initialize_qtips();

	} );

	//Function to Refresh jQuery toggles for Yelp Widget Pro upon saving specific widget
	$( document ).ajaxSuccess( function ( e, xhr, settings ) {
		initialize_qtips();
	} );


})( jQuery );