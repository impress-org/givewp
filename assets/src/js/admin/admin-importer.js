/**
 * Give Admin JS
 *
 * @package:     Give
 * @subpackage:  Assets/JS
 * @copyright:   Copyright (c) 2016, GiveWP
 * @license:     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 */

/* globals Give, jQuery*/

/**
 * Do not allow user to reload the page
 *
 * @since 1.8.14
 */
import { GiveWarningAlert, GiveErrorAlert, GiveConfirmModal } from '../plugins/modal';

let give_setting_edit = true;

document.addEventListener('DOMContentLoaded', function(event) {
	give_import_donation_onload();
})

/**
 * Run when user click on submit button.
 *
 * @since 1.8.17
 */
function give_on_core_settings_import_start() {
	const import_step = 'body.give_forms_page_give-tools .give-tools-import-tab #give-import-core-settings-form table.step-2';
	if ( jQuery( import_step ).length > 0 ) {
		const $form = jQuery( 'form.tools-setting-page-import' );
		const progress = $form.find( '.give-progress' );

		give_setting_edit = true;

		jQuery.ajax( {
			type: 'POST',
			url: ajaxurl,
			data: {
				action: Give.fn.getGlobalVar( 'core_settings_import' ),
				fields: $form.serialize(),
			},
			dataType: 'json',
			success: function( response ) {
				give_setting_edit = false;
				if ( true === response.success ) {
					jQuery( progress ).find( 'div' ).width( response.percentage + '%' );
				} else {
					new GiveErrorAlert( {
						modalContent: {
							title: Give.fn.getGlobalVar( 'import_failed' ),
							desc: Give.fn.getGlobalVar( 'error_message' ),
							cancelBtnTitle: Give.fn.getGlobalVar( 'ok' ),
						},
					} ).render();

					return;
				}
				window.location = response.url;
			},
			error: function() {
				give_setting_edit = false;

				new GiveErrorAlert( {
					modalContent: {
						title: Give.fn.getGlobalVar( 'import_failed' ),
						desc: Give.fn.getGlobalVar( 'error_message' ),
						cancelBtnTitle: Give.fn.getGlobalVar( 'ok' ),
					},
				} ).render();
			},
		} );
	}
}

/**
 * Check if admin is on step 1 and file is invalid
 *
 * @since 2.1
 */
function give_import_core_settings_json_is_valid() {
	const import_step = 'body.give_forms_page_give-tools .give-tools-import-tab #give-import-core-settings-form table.step-1 .is_json_valid';
	if ( jQuery( import_step ).length > 0 ) {
		window.location = jQuery( import_step ).val();
	}
}

/**
 * Check if admin is on step 3 where we start imporing Donation from CSV via AJAX
 *
 * @since 2.1
 */
function give_start_importing_donations() {
	const import_step = 'body.give_forms_page_give-tools .give-tools-import-tab #give-import-donations-form table.step-3';
	if ( jQuery( import_step ).length > 0 ) {
		give_on_donation_import_ajax();
	}
}

/**
 * Check if admin is on step 2 and CSV is invalid
 *
 * @since 2.1
 */
function give_import_donation_csv_not_valid() {
	const import_step = 'body.give_forms_page_give-tools .give-tools-import-tab #give-import-donations-form table.step-2 .csv_not_valid';
	if ( jQuery( import_step ).length > 0 ) {
		window.location = jQuery( import_step ).val();
	}
}

/**
 * Check if admin is on step 1 and csv is valid
 *
 * @since 2.1
 */
function give_import_donation_valid_csv() {
	const import_step = 'body.give_forms_page_give-tools .give-tools-import-tab #give-import-donations-form table.step-1 .is_csv_valid';

	if ( jQuery( import_step ).length > 0 ) {
		window.location = jQuery( import_step ).val();
	}
}

/**
 * Upload CSV ajax
 *
 * @since 1.8.13
 */
function give_on_donation_import_ajax() {
	const $form = jQuery( 'form.tools-setting-page-import' );

	/**
	 * Do not allow user to reload the page
	 *
	 * @since 1.8.14
	 */
	give_setting_edit = true;

	const progress = $form.find( '.give-progress' );

	const total_ajax = jQuery( progress ).data( 'total_ajax' ),
		current = jQuery( progress ).data( 'current' ),
		start = jQuery( progress ).data( 'start' ),
		end = jQuery( progress ).data( 'end' ),
		next = jQuery( progress ).data( 'next' ),
		total = jQuery( progress ).data( 'total' ),
		per_page = jQuery( progress ).data( 'per_page' );

	jQuery.ajax( {
		type: 'POST',
		url: ajaxurl,
		data: {
			action: Give.fn.getGlobalVar( 'give_donation_import' ),
			_wpnonce: Give.fn.getGlobalVar( 'give_donation_import_nonce' ),
			total_ajax: total_ajax,
			current: current,
			start: start,
			end: end,
			next: next,
			total: total,
			per_page: per_page,
			fields: $form.serialize(),
		},
		dataType: 'json',
		success: function( response ) {
			jQuery( progress ).data( 'current', response.current );
			jQuery( progress ).find( 'div' ).width( response.percentage + '%' );

			if ( response.next == true ) {
				jQuery( progress ).data( 'start', response.start );
				jQuery( progress ).data( 'end', response.end );

				if ( response.last == true ) {
					jQuery( progress ).data( 'next', false );
				}
				give_on_donation_import_ajax();
			} else {
				/**
				 * Now user is allowed to reload the page.
				 *
				 * @since 1.8.14
				 */
				give_setting_edit = false;
				window.location = response.url;
			}
		},
        error: function(xhr, textStatus, errorThrown) {
            /**
             * Now user is allowed to reload the page.
             *
             * @since 1.8.14
             */
            give_setting_edit = false;

            // Prepare the error message
            let errorMessage = Give.fn.getGlobalVar('error_message');
            if (xhr && xhr.responseText) {
                errorMessage += "\n\n" + xhr.responseText;
            } else {
                errorMessage += "\n\n" + textStatus + ": " + errorThrown;
            }

            new GiveErrorAlert({
                modalContent: {
                    title: Give.fn.getGlobalVar('import_failed'),
                    desc: errorMessage,
                    cancelBtnTitle: Give.fn.getGlobalVar('ok'),
                },
            }).render();
        },
	} );
}

/**
 * Give Import donation run on load once page is load completed.
 */
function give_import_donation_onload() {
	window.onload = function() {
		give_import_donation_required_fields_check();
		give_import_donation_on_drop_down_change();
		give_start_importing_donations();
		give_import_donation_valid_csv();
		give_import_donation_csv_not_valid();
		give_on_core_settings_import_start();
		give_import_core_settings_json_is_valid();
	};
}

/**
 * Give import donation on change of drop down and update the required fields.
 */
function give_import_donation_on_drop_down_change() {
	const fields = document.querySelector( '.give-tools-setting-page-import table.step-2 tbody select' );
	if ( fields !== 'undefined' && fields !== null ) {
		jQuery( '.give-tools-setting-page-import table.step-2 tbody' ).on( 'change', 'select', function() {
			give_import_donation_required_fields_check();
		} );
	}
}

/**
 * Give Import Donations check required fields
 */
function give_import_donation_required_fields_check() {
	const required_fields = document.querySelector( '.give-tools-setting-page-import table.step-2 .give-import-donation-required-fields' );
	if ( required_fields !== 'undefined' && required_fields !== null ) {
		let submit = true,
			email = false,
			first_name = false,
			amount = false,
			form = false;

		document.querySelectorAll( '.give-import-donation-required-fields li' ).forEach( function( value ) {
			value.querySelector( '.dashicons' ).classList.remove( 'dashicons-yes' );
			value.querySelector( '.dashicons' ).classList.add( 'dashicons-no-alt' );
		} );

		const select_fields = Array.from( document.querySelectorAll( 'table.step-2 tbody select' ) ).map( function( field ) {
			return field.value;
		} );

		if ( select_fields.includes( 'email' ) ) {
			email = true;
			document.querySelector( '.give-import-donation-required-email .dashicons' ).classList.remove( 'dashicons-no-alt' );
			document.querySelector( '.give-import-donation-required-email .dashicons' ).classList.add( 'dashicons-yes' );
		}

		if ( select_fields.includes( 'first_name' ) ) {
			first_name = true;
			document.querySelector( '.give-import-donation-required-first .dashicons' ).classList.remove( 'dashicons-no-alt' );
			document.querySelector( '.give-import-donation-required-first .dashicons' ).classList.add( 'dashicons-yes' );
		}

		if ( select_fields.includes( 'amount' ) ) {
			amount = true;
			document.querySelector( '.give-import-donation-required-amount .dashicons' ).classList.remove( 'dashicons-no-alt' );
			document.querySelector( '.give-import-donation-required-amount .dashicons' ).classList.add( 'dashicons-yes' );
		}

		if ( select_fields.includes( 'form_id' ) || select_fields.includes( 'form_title' ) ) {
			form = true;
			document.querySelector( '.give-import-donation-required-form .dashicons' ).classList.remove( 'dashicons-no-alt' );
			document.querySelector( '.give-import-donation-required-form .dashicons' ).classList.add( 'dashicons-yes' );
		}

		if ( email && first_name && amount && form ) {
			submit = false;
		}

		document.getElementById( 'recount-stats-submit' ).disabled = submit;
	}
}
