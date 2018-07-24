/*!
 * Give Admin Forms JS
 *
 * @description: The Give Admin Settings scripts. Only enqueued on the give-settings page; used for tabs and other show/hide functionality
 * @package:     Give
 * @since:       1.5
 * @subpackage:  Assets/JS
 * @copyright:   Copyright (c) 2016, WordImpress
 * @license:     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 */
/* globals Give*/
import {GiveWarningAlert, GiveErrorAlert, GiveConfirmModal, GiveSuccessAlert} from '../plugins/modal';

jQuery(document).ready(function ($) {

	/**
	 *  Sortable payment gateways.
	 */
	var $payment_gateways = jQuery( 'ul.give-payment-gatways-list' );
	if( $payment_gateways.length ){
		$payment_gateways.sortable();
	}

	/**
	 * Change currency position symbol on changing the currency
	 */
	var give_settings_currency = '#give-mainform #currency';
	var give_settings_position = '#give-mainform #currency_position';
	$( 'body' ).on( 'change', give_settings_currency, function () {
		var currency_text = $( give_settings_currency + ' option:selected' ).text(),
			currency_sign = currency_text.split( '(' ).pop().split( ')' ).shift();

		if ( '' === currency_sign ) {
			currency_sign = give_vars.currency_sign;
		}

		var before_text = $( give_settings_position ).data( 'before-template' );
		before_text = before_text.replace( '{currency_pos}', currency_sign );
		$( give_settings_position + ' option[value="before"]' ).text( before_text );


		var after_text = $( give_settings_position ).data( 'after-template' );
		after_text = after_text.replace( '{currency_pos}', currency_sign );
		$( give_settings_position + ' option[value="after"]' ).text( after_text );

	} );

	/**
	 * Show/Hide Title Prefixes
	 */
	if ( 'disabled' !== $('input[name="name_title_prefix"]:checked').val() ) {
		$( '.give-title-prefixes-settings-wrap' ).show();
	}

	$( 'input[name="name_title_prefix"]' ).on( 'change', function() {
		if ( 'disabled' !== $(this).val() ) {
			$( '.give-title-prefixes-settings-wrap' ).show();
		} else {
			$( '.give-title-prefixes-settings-wrap' ).hide();
		}
	});

	/**
	 * Repeater setting field event.
	 */
	$( 'a.give-repeat-setting-field' ).on( 'click', function(e){
		e.preventDefault();
		var $parent = $(this).parents('td'),
			$first_setting_field_group = $( 'p:first-child', $parent ),
			$new_setting_field_group = $first_setting_field_group.clone(),
			setting_field_count = $( 'p', $parent ).not('.give-field-description').length,
			fieldID = $(this).data('id') + '_' + (++setting_field_count),
			$prev_field = $(this).prev();

		// Create new field only if previous is non empty.
		if( $( 'input', $prev_field ).val() ) {
			// Add setting field html to dom.
			$(this).before( $new_setting_field_group );
			$prev_field = $(this).prev();

			// Set id and value for setting field.
			$( 'input', $prev_field ).attr( 'id', fieldID );
			$( 'input', $prev_field ).val( '' );
		}

		return false;
	});

	$( '.give-settings-page' ).on( 'click', 'span.give-remove-setting-field', function(e){
		$(this).parents('p').remove();
	});

	/**
	 * Enabled & disable email notification event.
	 */
	$( '.give-email-notification-status', 'table.giveemailnotifications' ).on( 'click', function(){
		var $this = $(this),
			$icon_container = $('i', $this),
			$loader = $(this).next(),
			set_notification_status = $(this).hasClass( 'give-email-notification-enabled' ) ? 'disabled' : 'enabled',
			notification_id = $(this).data('id');

		// Bailout if admin can not edit notification status setting.
		if( ! parseInt( $this.data('edit') ) ) {
			// Remove all notice.
			$('div.give-email-notification-status-notice').remove();

			// Add notice.
			$('hr.wp-header-end').after('<div class="updated error give-email-notification-status-notice"><p>' + $(this).closest('.give-email-notification-status').data('notice') + '</p></div>');

			// Scroll to notice.
			$('html, body').animate({scrollTop:$('div.give-email-notification-status-notice').position().top}, 'slow');

			return false;
		}

		$.ajax({
			url: ajaxurl,
			method: 'POST',
			data: {
				action: 'give_set_notification_status',
				status: set_notification_status,
				notification_id: notification_id
			},
			beforeSend: function(){
				$this.hide();
				$loader.addClass('is-active');
			},
			success: function(res) {
				if( res.success ) {
					$this.removeClass( 'give-email-notification-' + $this.data('status') );
					$this.addClass( 'give-email-notification-' + set_notification_status );
					$this.data( 'status', set_notification_status );

					if( 'enabled' === set_notification_status ) {
						$icon_container.removeClass('dashicons-no-alt');
						$icon_container.addClass('dashicons-yes');
					} else{
						$icon_container.removeClass('dashicons-yes');
						$icon_container.addClass('dashicons-no-alt');
					}

					$loader.removeClass('is-active');
					$this.show();
				}
			}
		});
	});

	/**
	 * Ajax call to clear Give's cache.
	 */
	$( '#give-clear-cache' ).on( 'click', function() {
		$.ajax({
			url: ajaxurl,
			type: 'GET',
			data: {
				action: 'give_cache_flush'
			}
		})
		.done( function( response ) {
			if ( response.success ) {
				new GiveSuccessAlert({
					modalContent:{
						title: give_vars.flush_success,
						desc: response.data.message,
						cancelBtnTitle: give_vars.ok,
					}
				}).render();
			} else {
				new GiveErrorAlert({
					modalContent:{
						title: give_vars.flush_error,
						desc: response.data.message,
						cancelBtnTitle: give_vars.ok,
					}
				}).render();
			}
		})
	});


	let dTemp         = give_global_vars.decimal_separator, // Temporary variable to store decimal separator.
	    tTemp         = give_global_vars.thousands_separator, // Temporary variable to store thousand separator.
	    symbolRegex   = /\(([^)]+)\)/, // Regex to extract currency symbol.
	    formatterArgs = {
			position: give_global_vars.currency_pos,
			symbol: give_global_vars.currency_sign,
			precision: give_global_vars.number_decimals,
			decimal: give_global_vars.decimal_separator,
			thousand: give_global_vars.thousands_separator,
		}; // Object argument required to format the amount.

	/**
	 * Logic to show Currency Preview.
	 *
	 * The variables above are part of the below code which should lie outside the code below.
	 */
	$( '#number_decimals, #decimal_separator, #thousands_separator, #currency_position, #currency' ).on( 'input blur change', function( e ) {
		let preview     = $( '#currency_preview' ),
		    dSeparator  = $( '#decimal_separator' ),
		    tSeparator  = $( '#thousands_separator' ),
		    targetName  = e.target.name,
		    targetValue = e.target.value;

		/**
		 * Sets the precision (number of decimals) for the formatted amount.
		 *
		 */
		if ( 'number_decimals' === targetName && ( 'input' === e.type || 'blur' === e.type ) ) {

			if ( ( ! targetValue || isNaN( targetValue ) ) && 'blur' === e.type ) {
				e.target.value = give_global_vars.number_decimals;
			}

			/**
			 * Checks if the input is a number, will set to '0' if otherwise or
			 * if the input is left empty.
			 */
			formatterArgs.precision = isNaN( parseInt( targetValue ) ) ? give_global_vars.number_decimals : parseInt( targetValue );
		}

		/**
		 * The next 2 sections are for the decimal separator and thousand separator.
		 * if the decimal separator === thousand separator, then the values are swapped.
		 */
		if ( 'decimal_separator' == targetName && ( 'input' === e.type || 'blur' === e.type ) ) {

			if ( ! targetValue && 'blur' === e.type ) {
				e.target.value = dTemp;
			}

			formatterArgs.decimal = targetValue;

			/**
			 * Logic for swapping decimal separator with thousand separator if both
			 * are the same value.
			 */
			if ( formatterArgs.hasOwnProperty( 'thousand' ) && 'input' === e.type ) {
				if ( formatterArgs.decimal === formatterArgs.thousand ) {
					formatterArgs.thousand = dTemp;
					tSeparator.val( dTemp );
					dTemp = targetValue;
					tTemp = tSeparator.val();
				}

			} else if ( formatterArgs.decimal === give_global_vars.thousands_separator && 'input' === e.type ) {
				formatterArgs.thousand = dTemp;
				tSeparator.val( dTemp );
				dTemp = targetValue;
				tTemp = tSeparator.val();
			}
		}

		if ( 'thousands_separator' == targetName && ( 'input' === e.type || 'blur' === e.type ) ) {

			if ( ! targetValue && 'blur' === e.type ) {
				e.target.value = tTemp;
			}

			formatterArgs.thousand = targetValue;

			/**
			 * Logic for swapping decimal separator with thousand separator if both
			 * are the same value.
			 */
			if ( formatterArgs.hasOwnProperty( 'decimal' ) && 'input' === e.type ) {
				if ( formatterArgs.decimal === formatterArgs.thousand ) {
					formatterArgs.decimal = tTemp;
					dSeparator.val( tTemp );
					tTemp = targetValue;
					dTemp = dSeparator.val();
				}

			} else if ( formatterArgs.thousand === give_global_vars.decimal_separator && 'input' === e.type ) {
				formatterArgs.decimal = tTemp;
				dSeparator.val( tTemp );
				tTemp = targetValue;
				dTemp = dSeparator.val();
			}
		}

		/**
		 * Sets the currency position: Before | After
		 */
		if ( 'currency_position' == targetName && 'change' === e.type ) {
			formatterArgs.position = targetValue;
		}

		/**
		 * Sets the currency and the symbol.
		 */
		if ( 'currency' === targetName && 'change' === e.type ) {
			formatterArgs.currency = targetValue;
			let matched = symbolRegex.exec( e.target[ e.target.selectedIndex ].text );
			formatterArgs.symbol = matched[1];
		}

		preview.val( Give.fn.formatCurrency( 4562.57, formatterArgs ) );
	});
});
