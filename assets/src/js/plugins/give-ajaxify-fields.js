/**
 * Note: This script is under development. We are using this inside core only and can be update in future. Currently uses only for admin purpose.
 */
( function( $ ) {
	'use strict';

	$.extend( {
		giveAjaxifyFields: function( customSettings ) {
			let $countryField,
				$parentWrapper,
				defaultSettings = {

					// Supported types: country_state.
					type: '',

					// Define these setting only if type set to country_state.
					parentWrapper: 'form',
					countryFieldName: 'country',
					stateFieldName: 'state',
					stateFieldWrapper: '.give-field-wrap',
					chosenState: true,
				};

			const settings = $.extend( {}, defaultSettings, ( customSettings || {} ) );

			switch ( settings.type ) {
				case 'country_state':
					$countryField = $( 'select[name="' + settings.countryFieldName + '"]' );
					$parentWrapper = $countryField.closest( settings.parentWrapper );

					// Bailout.
					if (
						! $countryField.length ||
						'Country_state' === $countryField.data( 'give-ajaxify-field' )
					) {
						return;
					}

					// Set data attribute.
					$countryField.data( 'give-ajaxify-field', 'country_state' );

					// Update base state field based on selected base country
					$countryField.change( function() {
						let $this = $( this ),
							$stateField = $this.closest( settings.parentWrapper )
								.find( '[name="' + settings.stateFieldName + '"]' ),
							$stateFieldWrapper = $stateField.closest( settings.stateFieldWrapper ),
							$stateFieldLabel = $( 'label', $stateFieldWrapper );

						// If state does not has wrapper then find it's label
						if ( ! settings.stateFieldWrapper ) {
							$stateFieldLabel = $( 'label[for="' + settings.stateFieldName + '"]', $parentWrapper );
							$stateFieldWrapper = $stateField.parent();
						}

						const data = {
							action: 'give_get_states',
							country: $this.val(),
							field_name: settings.stateFieldName,
						};

						$.post( ajaxurl, data, function( response ) {
							// Bailout.
							if ( ! response.show_field ) {
								if ( settings.stateFieldWrapper ) {
									$stateFieldWrapper.addClass( 'give-hidden' );
								} else {
									$stateField.addClass( 'give-hidden' );
								}
								return;
							}

							if ( $stateFieldLabel.length ) {
								$stateFieldLabel.text( response.states_label );
							}

							if ( settings.chosenState ) {
								$stateField.chosen( 'destroy' );
							}

							// Show field.
							if ( settings.stateFieldWrapper ) {
								$stateFieldWrapper.removeClass( 'give-hidden' );
							} else {
								$stateField.removeClass( 'give-hidden' );
							}

							if (
								typeof ( response.states_found ) !== undefined &&
								true === response.states_found
							) {
								// Update html.
								$stateField.replaceWith( response.data );

								// Update selector.
								$stateField = $( '[name="' + settings.stateFieldName + '"]', $stateFieldWrapper );

								// Reset chosenState
								if ( settings.chosenState ) {
									$stateField.chosen();
								}
							} else {
								$stateField.replaceWith( '<input type="text" name="' + settings.stateFieldName + '" value="' + response.default_state + '" class="medium-text"/>' );

								// Update selector.
								$stateField = $( '[name="' + settings.stateFieldName + '"]', $stateFieldWrapper );
							}
						} );
					} );

					break;
			}

			return this;
		},
	} );
}( jQuery ) );
