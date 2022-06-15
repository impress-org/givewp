/*!
 * Give Admin Export JS
 *
 * @description: The Give Admin Settings scripts. Only enqueued on the give-settings and give-tools page; used for exporting CSV
 * @package:     Give
 * @since:       2.1
 * @subpackage:  Assets/JS
 * @copyright:   Copyright (c) 2016, GiveWP
 * @license:     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 */
/* globals Give, jQuery */
jQuery( document ).ready( function( $ ) {
	/**
	 * Update Export Donation Form
	 *
	 * @since 2.1
	 */
	function give_export_update_donation_form() {
		const $form = $( 'form#give-export_donations-form' ),
			$container = $( $form ).find( 'tr.give-export-donation-form .give-select-chosen' ),
			select = $container.prev(),
			$search_field = $container.find( 'input[type="text"]' ),
			variations = $container.hasClass( 'variations' ),
			response = '';

		$( '.give-export-donations-hide' ).addClass( 'give-hidden' );
		$( 'li.give-export-donation-checkbox-remove' ).remove();

		$.ajax( {
			type: 'POST',
			url: ajaxurl,
			data: {
				action: 'give_form_search',
				s: '',
				fields: $( $form ).serialize(),
			},
			beforeSend: function() {
				select.closest( 'ul.chosen-results' ).empty();
			},
			success: function( data ) {
				// Remove all options but those that are selected.
				$( 'option', select ).remove();
				const form_ids = [];

				if ( data.length ) {
					$form.find( '.give-export-donation-button' ).prop( 'disabled', false );
					$.each( data, function( key, item ) {
						select.prepend( '<option value="' + item.id + '">' + item.name + '</option>' );
						form_ids.push( item.id );
					} );

                    select.prepend('<option value="0" selected>' + select.data('placeholder') + '</option>');
                } else {
                    // Trigger no result message event.
                    select.prepend('<option value="0">' + select.data('no-form') + '</option>');

                    $form.find('.give-export-donation-button').prop('disabled', true);
                }

                $form.find('.form_ids').val(form_ids.join());

                // Trigger update event.
                $container.prev('select.give-select-chosen').trigger('chosen:updated');

                output_give_donations_fields(response);
            },
        });
    }

    /**
     * Update export Donation Form when cat or tag are change
     *
     * @since 2.1
     */
    $('.give-export_donations #give-export_donations-form .give_forms_categories , .give-export_donations #give-export_donations-form .give_forms_tags').change(function () {
        give_export_update_donation_form();
    });

    /**
     * Ajax call to get donation fields.
     */
    $('.give-export_donations #give-export_donations-form #give_payment_form_select').change(function () {
        $('.give-export-donations-hide').addClass('give-hidden');

        $('li.give-export-donation-checkbox-remove').remove();

        jQuery(document).trigger('give_export_donations_form_change');

		let give_form_id;

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
				action: 'give_export_donations_get_custom_fields',
			},
			success: function( response ) {
				if ( response ) {
					output_give_donations_fields( response );
				} else {
					alert( Give.fn.getGlobalVar( 'error_message' ) );
				}

				jQuery( document ).trigger( 'give_export_donations_form_response', response );
			},
		} );
	} );

	/**
	 * Outputs the custom field checkboxes.
	 *
	 * @param response
	 */
	function output_give_donations_fields( response ) {
		/**
		 * Standard Fields
		 */
		const standard_fields = 'undefined' !== typeof response.standard_fields && null !== response.standard_fields ? response.standard_fields : '';
		const standard_field_list = $( '.give-export-donations-standard-fields ul' );
		if ( standard_fields.length > 0 ) {
			standard_field_list.closest( 'tr' ).removeClass( 'give-hidden' );
			// Loop through STANDARD fields & output
			$( standard_fields ).each( function( index, value ) {
				standard_field_list.append( '<li class="give-export-donation-checkbox-remove"><label for="give-give-donations-standard-field-' + value + '"><input type="checkbox" name="give_give_donations_export_option[' + value + ']" id="give-give-donations-standard-field-' + value + '">' + value + '</label> </li>' );
			} );
		}

		/**
		 * Hidden Fields
		 */
		const hidden_fields = 'undefined' !== typeof response.hidden_fields && null !== response.hidden_fields ? response.hidden_fields : '';
		const hidden_field_list = $( '.give-export-donations-hidden-fields ul' );

		if ( hidden_fields ) {
			hidden_field_list.closest( 'tr' ).removeClass( 'give-hidden' );

			// Loop through HIDDEN fields & output.
			$( hidden_fields ).each( function( index, value ) {
				hidden_field_list.append( '<li class="give-export-donation-checkbox-remove"><label for="give-give-donations-hidden-field-' + value + '"><input type="checkbox" name="give_give_donations_export_option[' + value + ']" id="give-give-donations-hidden-field-' + value + '">' + value + '</label> </li>' );
			} );
		}
	}

	/**
	 * Checks/Unchecks checkboxes on exporter page.
	 */
	const checkboxes = $( '.give-export-option-fields input[type="checkbox"]' );

	$( '.give-toggle-checkbox-selection' ).click( function() {
		$( this ).data( 'clicked', ! $( this ).data( 'clicked' ) );
		checkboxes.prop( 'checked', ! $( this ).data( 'clicked' ) );
		swapAndUpdateAttribute( this );
	} );

	/**
	 * Swaps and updates attributes on the button which is used
	 * to check/uncheck checkboxes.
	 */
	function swapAndUpdateAttribute( reference ) {
		let deselectAll = $( reference ).val(),
		    selectAll = $( reference ).attr( 'data-value' );

		[ deselectAll, selectAll ] = [ selectAll, deselectAll ];

		$( reference ).attr( 'value', deselectAll );
		$( reference ).attr( 'data-value', selectAll );
	}
} );
