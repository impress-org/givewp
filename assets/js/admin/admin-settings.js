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
	 * Set payment gateway list under default payment gaetway option
	 */
	var $payment_gateway_list_item = $('input', $payment_gateways),
		$default_gateway           = $('#default_gateway');
	$payment_gateway_list_item.on('click', function () {

		// Bailout.
		if( $(this)[0].hasAttribute( 'readonly' ) ) {
			return false;
		}

		// Selectors.
		var saved_default_gateway      = $default_gateway.val(),
			active_payment_option_html = '',
			$active_payment_gateways   = $('input:checked', $payment_gateways);

		// Set last active payment gateways to readonly.
		if( 1 === $active_payment_gateways.length ){
			$active_payment_gateways.prop( 'readonly', true );
		}else{
			$('input[readonly]:checked', $payment_gateways ).removeAttr('readonly');
		}

		// Create option html from active payment gateway list.
		$active_payment_gateways.each(function (index, item) {
			item           = $(item);
			var item_value = item.attr('name').match(/\[(.*?)\]/)[1];

			active_payment_option_html += '<option value="' + item_value + '"';

			if (saved_default_gateway === item_value) {
				active_payment_option_html += ' selected="selected"';
			}

			active_payment_option_html += '>' + item.next('label').text() + '</option>';
		});

		// Update select html.
		$default_gateway.html(active_payment_option_html);
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
});
