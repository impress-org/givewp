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
	 * On DOM Ready
	 */
	jQuery( function() {
		// Setup widget fields on widget page.
		const $container = jQuery( '.widget-liquid-right' );
		if ( $container ) {
			setUpWidgetFields( $container );
		}
	} );

	/**
	 * When widget save successfully.
	 */
	jQuery( document ).ajaxSuccess( function( e, xhr, settings ) {
		/**
		 * Setup chosen field.
		 */
		const action = Give.fn.getParameterByName( 'action', settings.data ),
			  isDeletingWidget = Give.fn.getParameterByName( 'delete_widget', settings.data ),
			  widgetId = Give.fn.getParameterByName( 'widget-id', settings.data ),
			  sidebarId = Give.fn.getParameterByName( 'sidebar', settings.data ),
			  $widget = jQuery( `#${ sidebarId } [id*="${ widgetId }"]` ),
			  $el = jQuery( '.give-select', $widget ),
			  isDonationFormSelected = !! parseInt( $el.val() );

		// Exit if not saving widget.
		if ( isDeletingWidget || 'save-widget' !== action ) {
			return false;
		}

		console.log( 'pass 1', $el );

		// Setup chosen field.
		Promise.all( [
			initiateChosenField( $el ),
		] ).then( ()=>{
			// Hide loader only if performing widget saving when donation form is not selected.
			if ( ! isDonationFormSelected ) {
				jQuery( '.js-loader', $widget ).addClass( 'give-hidden' );
			}
		} );
	} );

	/**
	 * When widget added in customizer successfully.
	 */
	jQuery( document ).on( 'widget-added', function( e, $widgetContainer ) {
		const $el = jQuery( '.give-select', $widgetContainer ),
			  isDonationFormSelected = !! parseInt( $el.val() );

		console.log( 'pass 2', $widgetContainer );

		// widget-added event also fires on widget page but we only want to run this on customizer page.
		if ( ! jQuery( '.control-section-sidebar' ).length ) {
			return;
		}

		// Setup chosen field.
		Promise.all( [
			initiateChosenField( $el ),
		] ).then( ()=>{
			// Hide loader only if performing widget saving when donation form is not selected.
			if ( ! isDonationFormSelected ) {
				jQuery( '.js-loader', $widgetContainer ).addClass( 'give-hidden' );
			}

			addEventListenerToWidgetFields( $widgetContainer );
		} );
	} );

	/**
	 * Setup widget fields
	 *
	 * @since 2.7.0
	 * @param {object} $container
	 */
	function setUpWidgetFields( $container ) {
		addEventListenerToWidgetFields( $container );
		initiateChosenField( jQuery( '.give-select', $container ) );
	}

	/**
	 * Add evvent on widget fields.
	 *
	 * @since 2.7.0
	 * @param $container
	 */
	function addEventListenerToWidgetFields( $container ) {
		/**
		 * Add events
		 */
		/* Display style change handler. */
		$container.on( 'change', '.give_forms_display_style_setting_row input', function() {
			showConditionalFieldWhenEditDisplayStyleSetting( $( this ) );
		} );

		/* Donation form change handler. */
		$container.on( 'change', 'select.give-select', function() {
			showConditionalFieldWhenEditDonationFormSetting( jQuery( this ) );
		} );
	}

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
				const chosenContainer = jQuery( this ).next();

				chosenContainer.css( 'width', '100%' );
				jQuery( 'ul.chosen-results', chosenContainer ).css( 'width', '100%' );

				// Show field.
				jQuery( this ).parent().removeClass( 'give-hidden' );

				if ( parseInt( jQuery( this ).val() ) ) {
					showConditionalFieldWhenEditDonationFormSetting( jQuery( this ) );
				} else {
					// Hide loader.
					jQuery( '.js-loader', jQuery( this ).closest( '.widget-content' ) ).addClass( 'give-hidden' );
				}
			} );
		} );
	}

	/**
	 * Initiate colorpicker field.
	 *
	 * @since 2.7.0
	 * @param {Array} $els
	 */
	function initiateColorPicker( $els ) {
		let colorPickerTimer;

		$els.each( function() {
			const $this = $( this );

			$this.wpColorPicker( {
				change: () => {
					window.clearTimeout( colorPickerTimer );

					colorPickerTimer = window.setTimeout( function() {
						$this.trigger( 'change' );
					}, 100 );
				},
			} );
		} );
	}

	/**
	 * Display setting fields on basis of display_style setting.
	 *
	 * @since 2.7.0
	 * @param {Array} $els
	 */
	function showConditionalFieldWhenEditDisplayStyleSetting( $els ) {
		$els.each( function() {
			const $container = jQuery( this ).closest( '.give_forms_widget_container' ),
				  $fieldset = jQuery( '.js-form-template-settings.active', $container ),
				  $parent = jQuery( 'p.give_forms_display_style_setting_row', $fieldset ),
				  isFormHasNewTemplate = $fieldset.hasClass( 'js-new-form-template-settings' ),
				  isFormHasLegacyTemplate = $fieldset.hasClass( 'js-legacy-form-template-settings' );

			if ( isFormHasLegacyTemplate ) {
				const $continue_button_title = $parent.next();

				if ( 'onpage' === jQuery( 'input:checked', $parent ).val() ) {
					$continue_button_title.hide();
				} else {
					$continue_button_title.show();
				}
			} else if ( isFormHasNewTemplate ) {
				if ( 'button' === jQuery( 'input:checked', $parent ).val() ) {
					$fieldset.find( 'p' ).not( $parent ).removeClass( 'give-hidden' );
				} else {
					$fieldset.find( 'p' ).not( $parent ).addClass( 'give-hidden' );
				}
			}
		} );
	}

	/**
	 * Display setting fields on basis of donation form setting.
	 *
	 * @since 2.7.0
	 * @param {Array} $els
	 */
	function showConditionalFieldWhenEditDonationFormSetting( $els ) {
		$els.each( function() {
			const $this = $( this ),
				  $container = jQuery( this ).closest( '.give_forms_widget_container' ),
				  $loader = jQuery( '.js-loader', $container ),
				  $oldSettings = jQuery( '.js-legacy-form-template-settings', $container ),
				  $newSettings = jQuery( '.js-new-form-template-settings', $container );

			$oldSettings.addClass( 'give-hidden' ).removeClass( 'active' );
			$newSettings.addClass( 'give-hidden' ).removeClass( 'active' );

			$loader.removeClass( 'give-hidden' );

			jQuery.post(
				ajaxurl,
				{
					action: 'give_get_form_template_id',
					formId: $this.val(),
					security: jQuery( 'input[name="_wpnonce"]', $container ).val(),
				},
				function( response ) {
					$loader.addClass( 'give-hidden' );

					// Exit if result is not successful.
					if ( true === response.success ) {
						if ( 'legacy' === response.data ) {
							$oldSettings.removeClass( 'give-hidden' ).addClass( 'active' );
						} else {
							$newSettings.removeClass( 'give-hidden' ).addClass( 'active' );
						}
					}

					showConditionalFieldWhenEditDisplayStyleSetting( $this );
					initiateColorPicker( jQuery( '.give_forms_button_color_setting_row input', $container ) );
				}
			);
		} );
	}
}( jQuery ) );
