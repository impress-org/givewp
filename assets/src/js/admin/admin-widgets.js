/* globals jQuery, Give, ajaxurl, wpWidgets */
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
	 * On DOM Ready
	 */
	$( function() {
		const $sidebarContainer = jQuery( '.widget-liquid-right' );
		/**
		 * Add events
		 */

		/* Form change handler. */
		jQuery( document ).on( 'change', 'select.give-select', function() {
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
		$sidebarContainer.on( 'change', '.give_forms_display_style_setting_row input', function() {
			const $fieldset = $( this ).closest( 'fieldset' ),
				  $parent = $( this ).parents( 'p' ),
				isFormHasNewTemplate = $fieldset.hasClass( 'js-new-form-template-settings' ),
				isFormHasLegacyTemplate = $fieldset.hasClass( 'js-legacy-form-template-settings' );

			if ( isFormHasLegacyTemplate ) {
				const $continue_button_title = $parent.next();

				if ( 'onpage' === $( 'input:checked', $parent ).val() ) {
					$continue_button_title.hide();
				} else {
					$continue_button_title.show();
				}
			} else if ( isFormHasNewTemplate ) {
				if ( 'button' === $( 'input:checked', $parent ).val() ) {
					$fieldset.find( 'p' ).not( $parent ).removeClass( 'give-hidden' );
				} else {
					$fieldset.find( 'p' ).not( $parent ).addClass( 'give-hidden' );
				}
			}
		} );

		/* Setup button color field. */
		$sidebarContainer.on( 'change', '.give_forms_button_color_setting_row input', function() {
			const $this = jQuery( this );

			$this.wpColorPicker( {
				change: () => {
					const $widget = $this.closest( '.widget' );
					jQuery( 'input[name="savewidget"]', $widget ).prop( 'disabled', false ).val( wpWidgets.l10n.save );
				},
			} );
		} );

		// Trigger events.
		$( '.give_forms_display_style_setting_row input', '.widget-liquid-right' ).trigger( 'change' );
		$( '.give_forms_button_color_setting_row input', '.widget-liquid-right' ).trigger( 'change' );

		// Setup chosen field.
		const $els = jQuery( '.give-select', '.widget-liquid-right' );
		initiateChosenField( $els );
		$els.trigger( 'change' );
	} );

	/**
	 * When widget save successfully.
	 */
	$( document ).ajaxSuccess( function( e, xhr, settings ) {
		/**
		 * Setup chosen field.
		 */
		const action = Give.fn.getParameterByName( 'action', settings.data ),
			  isDeletingWidget = Give.fn.getParameterByName( 'delete_widget', settings.data ),
			  widgetId = Give.fn.getParameterByName( 'widget-id', settings.data ),
			  sidebarId = Give.fn.getParameterByName( 'sidebar', settings.data ),
			  $widget = $( `#${ sidebarId } [id*="${ widgetId }"]` ),
			  $el = $( '.give-select', $widget );

		// Exit if not saving widget.
		if ( isDeletingWidget || 'save-widget' !== action ) {
			return false;
		}

		// Setup chosen field.
		initiateChosenField( $el );

		// Trigger events.
		$( '.give_forms_display_style_setting_row input', $widget ).trigger( 'change' );
		$( '.give_forms_button_color_setting_row input', $widget ).trigger( 'change' );
	} );

	/**
	 * Initiate chosen field
	 *
	 * @param {object} $els
	 * @since 2.7.0
	 */
	function initiateChosenField( $els ) {
		Promise.all( [
			setupChosen( $els ),

		] ).then( () => {
			$els.each( function() {
				const chosenContainer = $( this ).next();

				chosenContainer.css( 'width', '100%' );
				jQuery( 'ul.chosen-results', chosenContainer ).css( 'width', '100%' );

				// Trigger change event on select field only if valid donation for selected.
				if ( parseInt( $( this ).val() ) ) {
					$( this ).trigger( 'change' );
				}
			} );
		} );
	}
}( jQuery ) );
