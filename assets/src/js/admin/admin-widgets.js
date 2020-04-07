/* globals jQuery, Give*/
/*!
 * Give Admin Widgets JS
 *
 * @description: The Give Admin Widget scripts. Only enqueued on the admin widgets screen; used to validate fields, show/hide, and other functions
 * @package:     Give
 * @subpackage:  Assets/JS
 * @copyright:   Copyright (c) 2016, GiveWP
 * @license:     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 */

import setupChosen from './utils/setupChosen';

( function( $ ) {
	/**
	 * Show/Hide continue button tile setting on basis of display setting for Give Form widget.
	 */
	const continue_button_setting_js = function() {
		$( '.widget-liquid-right' ).on( 'change', '.give_forms_display_style_setting_row input', function() {
			const $parent = $( this ).parents( 'p' ),
				$continue_button_title = $parent.next();

			if ( 'onpage' === $( 'input:checked', $parent ).val() ) {
				$continue_button_title.hide();
			} else {
				$continue_button_title.show();
			}
		} );
	};

	//On DOM Ready
	$( function() {
		continue_button_setting_js();
		$( '.give_forms_display_style_setting_row input', '.widget-liquid-right' ).trigger( 'change' );
	} );

	//Function to Refresh jQuery toggles for Yelp Widget Pro upon saving specific widget
	$( document ).ajaxSuccess( function( e, xhr, settings ) {
		continue_button_setting_js();
		$( '.give_forms_display_style_setting_row input', '.widget-liquid-right' ).trigger( 'change' );
	} );

	$( document ).ajaxComplete( function( event, request, settings ) {
		const action = Give.fn.getParameterByName( 'action', settings.data ),
			  widgetId = Give.fn.getParameterByName( 'widget-id', settings.data ),
			  sidebarId = Give.fn.getParameterByName( 'sidebar', settings.data );

		// Exit if not saving widget.
		if ( 'save-widget' !== action ) {
			return false;
		}

		const $widget = $( `#${ sidebarId } [id*="${ widgetId }"]` ),
			  $el = $( '.give-select-chosen', $widget );

		setupChosen( $el );
		$el.next().css( { width: '100%' } );
	} );
}( jQuery ) );
