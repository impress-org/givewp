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

jQuery.noConflict();
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
				alert( response.data.message );
			} else {
				alert( response.data.message );
			}
		})
	});

	/**
	 * Ajax call to get donation fields.
	 */
	$( '.give-export_donations #give-export_donations-form #give_form_for_csv_export' ).chosen().change( function () {

		$( '.give-export-donations-hide' ).addClass( 'give-hidden' );

		jQuery(document).trigger('give_export_donations_form_change' );

		var give_form_id;

		// Check for form ID.
		if ( ! (
				give_form_id = $( this ).val()
			) ) {
			return false;

		}

		// Ajax.
		$.ajax( {
			type: 'POST',
			url: ajaxurl,
			data: {
				form_id: give_form_id,
				action: 'give_export_donations_get_custom_fields'
			},
			success: function ( response ) {

				if ( response ) {
					output_give_donations_fields( response );

				} else {
					alert( 'An AJAX error occurred.' );
				}

				jQuery(document).trigger('give_export_donations_form_response', response );
			}
		} );
	} );


	/**
	 * Outputs the custom field checkboxes.
	 *
	 * @param response
	 */
	function output_give_donations_fields( response ) {

		/**
		 * FFM Fields
		 */
		var ffm_fields = (
			typeof response.ffm_fields !== 'undefined'
		) ? response.ffm_fields : '';

		if ( ffm_fields ) {

			var ffm_field_list = $( '.give-export-donations-ffm ul' );

			ffm_field_list.closest( 'tr' ).removeClass( 'give-hidden' );

			// Loop through FFM fields & output
			$( ffm_fields ).each( function ( index, value ) {

				// Repeater sections.
				var repeater_sections = (
					typeof value.repeaters !== 'undefined'
				) ? value.repeaters : '';

				if ( repeater_sections ) {

					var parent_title = '';
					// Repeater section field.
					$( repeater_sections ).each( function ( index, value ) {
						if ( parent_title !== value.parent_title ) {
							ffm_field_list.append( '<li class="repeater-section-title" data-parent-meta="' + value.parent_meta + '"><label for="give-give-donations-ffm-field-' + value.parent_meta + '"><input type="checkbox" name="give_give_donations_export_parent[' + value.parent_meta + ']" id="give-give-donations-ffm-field-' + value.parent_meta + '">' + value.parent_title + '</label></li>' );
						}
						parent_title = value.parent_title;
						ffm_field_list.append( '<li class="repeater-section repeater-section-' + value.parent_meta + '"><label for="give-give-donations-ffm-field-' + value.subkey + '"><input type="checkbox" name="give_give_donations_export_option[' + value.subkey + ']" id="give-give-donations-ffm-field-' + value.subkey + '">' + value.label + '</label></li>' );
					} );
				}
				// Repeater sections.
				var single_repeaters = (
					typeof value.single !== 'undefined'
				) ? value.single : '';

				if ( single_repeaters ) {
					// Repeater section field.
					$( single_repeaters ).each( function ( index, value ) {
						ffm_field_list.append( '<li><label for="give-give-donations-ffm-field-' + value.subkey + '"><input type="checkbox" name="give_give_donations_export_option[' + value.metakey + ']" id="give-give-donations-ffm-field-' + value.subkey + '">' + value.label + '</label> </li>' );
					} );
				}
			} );

		}

		/**
		 * Standard Fields
		 */
		var standard_fields = (
			typeof response.standard_fields !== 'undefined'
		) ? response.standard_fields : '';
		var standard_field_list = $( '.give-export-donations-standard-fields ul' );
		if ( standard_fields.length > 0 ) {
			standard_field_list.closest( 'tr' ).removeClass( 'give-hidden' );
			// Loop through STANDARD fields & output
			$( standard_fields ).each( function ( index, value ) {
				standard_field_list.append( '<li><label for="give-give-donations-standard-field-' + value + '"><input type="checkbox" name="give_give_donations_export_option[' + value + ']" id="give-give-donations-standard-field-' + value + '">' + value + '</label> </li>' );
			} );
		}

		/**
		 * Hidden Fields
		 */
		var hidden_fields = response.hidden_fields ? response.hidden_fields : '';
		var hidden_field_list = $( '.give-export-donations-hidden-fields ul' );

		if ( hidden_fields ) {
			hidden_field_list.closest( 'tr' ).removeClass( 'give-hidden' );

			// Loop through HIDDEN fields & output.
			$( hidden_fields ).each( function ( index, value ) {
				hidden_field_list.append( '<li><label for="give-give-donations-hidden-field-' + value + '"><input type="checkbox" name="give_give_donations_export_option[' + value + ']" id="give-give-donations-hidden-field-' + value + '">' + value + '</label> </li>' );
			} );
		}
	}
});
