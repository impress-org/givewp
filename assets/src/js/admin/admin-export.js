/*!
 * Give Admin Export JS
 *
 * @description: The Give Admin Settings scripts. Only enqueued on the give-settings and give-tools page; used for exporting CSV
 * @package:     Give
 * @since:       2.1
 * @subpackage:  Assets/JS
 * @copyright:   Copyright (c) 2016, WordImpress
 * @license:     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 */

jQuery.noConflict();
jQuery( document ).ready( function ( $ ) {

	/**
	 * Update Exort Donation Form
	 *
	 * @since 2.1
	 */
	function give_update_donation_form() {

		var $form = $( 'form#give-export_donations-form' ),
			$container = $( $form ).find( 'tr.give-export-donation-form .give-select-chosen' ),
			select = $container.prev(),
			$search_field = $container.find( 'input[type="text"]' ),
			variations = $container.hasClass( 'variations' );

		$.ajax( {
			type: 'POST',
			url: ajaxurl,
			data: {
				action: 'give_form_search',
				s: '',
				fields: $( $form ).serialize()
			},
			beforeSend: function () {
				select.closest( 'ul.chosen-results' ).empty();
			},
			success: function ( data ) {

				// Remove all options but those that are selected.
				$( 'option', select ).remove();

				if ( data.length ) {

					$form.find( '.give-export-donation-button' ).prop('disabled', false);

					select.prepend( '<option value="0">' + select.data( 'placeholder' ) + '</option>' );

					$.each( data, function ( key, item ) {
						select.prepend( '<option value="' + item.id + '">' + item.name + '</option>' );
					} );
				} else {
					// Trigger no result message event.
					select.prepend( '<option value="0">' + select.data( 'no-form' ) + '</option>' );

					$form.find( '.give-export-donation-button' ).prop('disabled', true);
				}

				// Trigger update event.
				$container.prev( 'select.give-select-chosen' ).trigger( 'chosen:updated' );
			}
		} )
	}

	$( '.give-export_donations #give-export_donations-form .give_forms_categories , .give-export_donations #give-export_donations-form .give_forms_tags' ).change( function () {
		give_update_donation_form();
	} );

	/**
	 * Ajax call to get donation fields.
	 */
	$( '.give-export_donations #give-export_donations-form #give_payment_form_select1' ).chosen().change( function () {

		$( '.give-export-donations-hide' ).addClass( 'give-hidden' );

		$( 'li.give-export-donation-checkbox-remove' ).remove();

		jQuery( document ).trigger( 'give_export_donations_form_change' );

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
					alert( give_vars.error_message );
				}

				jQuery( document ).trigger( 'give_export_donations_form_response', response );
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

			// Loop through FFM fields & output
			$( ffm_fields ).each( function ( index, value ) {

				// Repeater sections.
				var repeater_sections = (
					typeof value.repeaters !== 'undefined'
				) ? value.repeaters : '';

				if ( repeater_sections ) {

					ffm_field_list.closest( 'tr' ).removeClass( 'give-hidden' );

					var parent_title = '';
					// Repeater section field.
					$( repeater_sections ).each( function ( index, value ) {
						if ( parent_title !== value.parent_title ) {
							ffm_field_list.append( '<li class="give-export-donation-checkbox-remove repeater-section-title" data-parent-meta="' + value.parent_meta + '"><label for="give-give-donations-ffm-field-' + value.parent_meta + '"><input type="checkbox" name="give_give_donations_export_parent[' + value.parent_meta + ']" id="give-give-donations-ffm-field-' + value.parent_meta + '">' + value.parent_title + '</label></li>' );
						}
						parent_title = value.parent_title;
						ffm_field_list.append( '<li class="give-export-donation-checkbox-remove repeater-section repeater-section-' + value.parent_meta + '"><label for="give-give-donations-ffm-field-' + value.subkey + '"><input type="checkbox" name="give_give_donations_export_option[' + value.subkey + ']" id="give-give-donations-ffm-field-' + value.subkey + '">' + value.label + '</label></li>' );
					} );
				}
				// Repeater sections.
				var single_repeaters = (
					typeof value.single !== 'undefined'
				) ? value.single : '';

				if ( single_repeaters ) {

					ffm_field_list.closest( 'tr' ).removeClass( 'give-hidden' );

					// Repeater section field.
					$( single_repeaters ).each( function ( index, value ) {
						ffm_field_list.append( '<li class="give-export-donation-checkbox-remove"><label for="give-give-donations-ffm-field-' + value.subkey + '"><input type="checkbox" name="give_give_donations_export_option[' + value.metakey + ']" id="give-give-donations-ffm-field-' + value.subkey + '">' + value.label + '</label> </li>' );
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
				standard_field_list.append( '<li class="give-export-donation-checkbox-remove"><label for="give-give-donations-standard-field-' + value + '"><input type="checkbox" name="give_give_donations_export_option[' + value + ']" id="give-give-donations-standard-field-' + value + '">' + value + '</label> </li>' );
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
				hidden_field_list.append( '<li class="give-export-donation-checkbox-remove"><label for="give-give-donations-hidden-field-' + value + '"><input type="checkbox" name="give_give_donations_export_option[' + value + ']" id="give-give-donations-hidden-field-' + value + '">' + value + '</label> </li>' );
			} );
		}
	}
} );