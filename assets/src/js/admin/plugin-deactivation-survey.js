import {GiveWarningAlert, GiveErrorAlert, GiveConfirmModal, GiveSuccessAlert, GiveFormModal} from '../plugins/modal';

jQuery.noConflict();

jQuery( document ).ready( function( $ ) {

	// Clicking Give Core's 'deactivate' button.
	$( 'tr[data-slug="give"] .deactivate a' ).on( 'click', function( e ) {

		e.preventDefault();

		// AJAX call to render the deactivation survey form.
		$.ajax({
			url: ajaxurl,
			type: 'POST',
			data: {
				'action': 'deactivation_popup',
			}
		}).done( function( response ) {
			new GiveFormModal({
				classes: {
					modalWrapper: 'deactivation-survey-wrap',
				},

				modalContent:{
					desc: response,
					cancelBtnTitle: 'Cancel',
					confirmBtnTitle: 'Submit & Deactivate',
				},

				successConfirm: function ( args ) {

					// Deactivation Error admin notice.
					let deactivation_error = $( '.deactivation-error' );

					// If no radio button is selected then throw error.
					if ( 0 === $( 'input[name="give-survey-radios"]:checked' ).length && 0 === deactivation_error.length ) {
						$( '.deactivation-survey-form' ).append( `
							<div class="notice notice-error deactivation-error">
								<strong>Error: </strong>Please select at least one option.
							</div>
						`);

						return;
					}

					/* If a radio button is assosciated with additional field
					 * and if that field is empty, then throw error.
					 */
					let user_reason_field = $( 'input[name="give-survey-radios"]:checked' )
						.closest( '.give-field-description' )
						.siblings( '.give-survey-extra-field' )
						.find( 'input, textarea' );

					if ( 0 < user_reason_field.length && ! user_reason_field.val() && 0 === deactivation_error.length ) {
						$( '.deactivation-survey-form' ).append( `
							<div class="notice notice-error deactivation-error">
								<strong>Error: </strong>Please fill the field.
							</div>
						`);

						return;
					}

					/**
					 * If form is properly filled, then serialize form data and
					 * pass it to the AJAX callback for processing.
					 */
					let form_data = $('.deactivation-survey-form').serialize();

					$.ajax({
						url: ajaxurl,
						type: 'POST',
						data: {
							'action': 'deactivation_form_submit',
							'form-data': form_data,
						}
					}).done( function( response ) {

					});
				}
			}).render();
		});
	})

	// Clicking the radio buttons in the form.
	$( this ).on( 'click', 'input[name="give-survey-radios"]', function() {
		let deactivation_error = $( '.deactivation-error' );

		if ( deactivation_error.length > 0 ) {
			deactivation_error.remove();
		}

		$( '.give-survey-extra-field' ).hide();

		$( '.give-survey-extra-field' )
			.find( 'input, textarea' )
			.attr( 'disabled', 'disabled' );

		$( this )
			.closest( '.give-field-description' )
			.siblings( '.give-survey-extra-field' )
			.show();

		$( this )
			.closest( '.give-field-description' )
			.siblings( '.give-survey-extra-field' )
			.find( 'input, textarea' )
			.removeAttr( 'disabled' );
	});
});