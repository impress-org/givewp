/*!
 * Give Admin Widgets JS
 *
 * @description: The Give Admin Widget scripts. Only enqueued on the admin widgets screen; used to validate fields, show/hide, and other functions
 * @package:     Give
 * @subpackage:  Assets/JS
 * @copyright:   Copyright (c) 2016, WordImpress
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

	/**
	 * Show/Hide continue button tile setting on basis of display setting for Give Form widget.
	 */
	var continue_button_setting_js = function() {
		$( '.widget-liquid-right' ).on( 'change', '.give_forms_display_style_setting_row input', function(){
			var $parent = $(this).parents('p'),
				$continue_button_title = $parent.next();

			if( 'onpage' === $('input:checked', $parent ).val() ) {
				$continue_button_title.hide();
			} else {
				$continue_button_title.show();
			}
		});
	};


	//On DOM Ready
	$( function () {
		initialize_qtips();

		continue_button_setting_js();
		$( '.give_forms_display_style_setting_row input', '.widget-liquid-right' ).trigger('change');
	} );

	//Function to Refresh jQuery toggles for Yelp Widget Pro upon saving specific widget
	$( document ).ajaxSuccess( function ( e, xhr, settings ) {
		initialize_qtips();

		continue_button_setting_js();
		$( '.give_forms_display_style_setting_row input', '.widget-liquid-right' ).trigger('change');
	} );


})( jQuery );