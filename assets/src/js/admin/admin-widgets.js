/* globals jQuery, Give, ajaxurl */
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
	 * Add events
	 */

	/* Form change handler. */
	jQuery( document ).on( 'change', 'select.give-select-chosen', function() {
		const $this = jQuery( this ),
			$container = jQuery( this ).closest( '.give_forms_widget_container' ),
			$loader = jQuery( '.js-loader', $container ),
			$oldSettings = jQuery( '.js-legacy-form-template-settings', $container ),
			$newSettings = jQuery( '.js-new-form-template-settings', $container );

		$oldSettings.addClass( 'give-hidden' );
		$newSettings.addClass( 'give-hidden' );

		$loader.removeClass( 'give-hidden' );

		jQuery.post(
			ajaxurl,
			{
				action: 'give_get_form_template_id',
				formId: $this.val(),
				savewidgets: $( '#_wpnonce_widgets' ).val(),
			},
			function( response ) {
				$loader.addClass( 'give-hidden' );

				// Exit if result is not successful.
				if ( true === response.success ) {
					if ( 'legacy' === response.data ) {
						$oldSettings.removeClass( 'give-hidden' );
					} else {
						$newSettings.removeClass( 'give-hidden' );
					}
				}
			}
		);
	} );

	/* Display style change handler. */
	jQuery( '.widget-liquid-right' ).on( 'change', '.give_forms_display_style_setting_row input', function() {
		const $parent = $( this ).parents( 'p' ),
			  $continue_button_title = $parent.next();

		if ( 'onpage' === $( 'input:checked', $parent ).val() ) {
			$continue_button_title.hide();
		} else {
			$continue_button_title.show();
		}
	} );

	/**
	 * On DOM Ready
	 */
	$( function() {
		// Trigger events.
		// $( '.give_forms_display_style_setting_row input', '.widget-liquid-right' ).trigger( 'change' );
		$( '.give-select-chosen' ).trigger( 'change' );
	} );

	/**
	 * When widget save successfully.
	 */
	$( document ).ajaxSuccess( function( e, xhr, settings ) {
		/**
		 * Setup chosen field.
		 */
		const action = Give.fn.getParameterByName( 'action', settings.data ),
			  widgetId = Give.fn.getParameterByName( 'widget-id', settings.data ),
			  sidebarId = Give.fn.getParameterByName( 'sidebar', settings.data ),
			  $widget = $( `#${ sidebarId } [id*="${ widgetId }"]` ),
			  $el = $( '.give-select-chosen', $widget );

		// Exit if not saving widget.
		if ( 'save-widget' !== action ) {
			return false;
		}

		// Trigger events.
		// $( '.give_forms_display_style_setting_row input', '.widget-liquid-right' ).trigger( 'change' );

		// Setup chosen.
		setupChosen( $el );
		$el.next().css( { width: '100%' } );
		$el.trigger( 'change' );
	} );
}( jQuery ) );
