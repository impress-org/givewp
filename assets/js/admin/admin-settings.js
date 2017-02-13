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
	 * Upload file button.
	 */

	// Set all variables to be used in scope
	var $upload_image_frame,
		$upload_file_btn = $('.give-upload-button'),
		$active_upload_file_btn;

	// ADD IMAGE LINK
	$upload_file_btn.on( 'click', function( event ){

		event.preventDefault();

		// Cache active upload button selector.
		$active_upload_file_btn = $(this);

		// If the media $upload_image_frame already exists, reopen it.
		if ( $upload_image_frame ) {
			$upload_image_frame.open();
			return;
		}

		// Create a new media $upload_image_frame
		$upload_image_frame = wp.media({
			title: give_vars.logo,
			button: {
				text: give_vars.use_this_image
			},
			frame: 'post',
			multiple: false, // Set to true to allow multiple files to be selected
			library : {
				type:'image'
			}
		});


		// When an image is selected in the media $upload_image_frame...
		$upload_image_frame.on( 'insert', function() {

			// Get media attachment details from the $upload_image_frame state
			var attachment = $upload_image_frame.state().get('selection').first().toJSON(),
				$parent = $active_upload_file_btn.parents('.give-field-wrap'),
				$image_container = $('.give-image-thumb', $parent ),
				$selected_image_size = $('.attachment-display-settings .size').val();

			// Send the attachment URL to our custom image input field.
			$image_container.find('img').attr( 'src', attachment.sizes[ $selected_image_size ].url );

			// Send the attachment id to our hidden input
			$('input[type="text"]', $parent ).val( attachment.sizes[ $selected_image_size ].url );

			// Hide the add image link
			$image_container.removeClass( 'give-hidden' );
		});

		// When an image is selected in the media $upload_image_frame...
		$upload_image_frame.on( 'open', function() {
			$('a.media-menu-item').each(function(){
				switch ( $(this).text().trim() ) {
					case 'Create Gallery':
					case 'Insert from URL':
						$(this).hide();
				}
			});
		});

		$('body').on( 'click', '.thumbnail', function(e){
			var $attachment_display_setting = $('.attachment-display-settings');

			if( $attachment_display_setting.length ) {
				$( '.alignment', $attachment_display_setting ).closest('label').hide();
				$( '.link-to', $attachment_display_setting ).closest('label').hide();
				$( '.attachment-details label' ).hide();
			}

		});

		// Finally, open the modal on click
		$upload_image_frame.open();
	});


	// DELETE IMAGE LINK
	$( 'span.give-delete-image-thumb', '.give-image-thumb').on( 'click', function( event ){

		event.preventDefault();

		var $parent = $(this).parents('.give-field-wrap'),
			$image_container = $(this).parent(),
			$image_input_field = $('input[type="text"]', $parent);

		// Clear out the preview image
		$image_container.addClass( 'give-hidden' );

		// Remove image link from input field.
		$image_input_field.val('');

		// Hide the add image link
		$( 'img', $image_container ).attr( 'src', '' );
	});

});
