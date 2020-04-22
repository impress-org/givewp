/* globals Give, jQuery */
( function( $ ) {
	/**
	 * Return whether or not recurring addon enabled or not.
	 *
	 * @since 2.7.0
	 */
	const isRecurringAddonActive = function() {
		return !! document.querySelector( '.give-recurring-row' );
	};

	/**
	 * Return whether or not Sequoia form template active.
	 *
	 * @since 2.7.0
	 */
	const isSequoiaFromTemplateActive = function() {
		return 'sequoia' === $( 'input[name="_give_form_template"]', '.give-metabox-panel-wrap' ).val();
	};

	/**
	 * Show recurring related notices.
	 *
	 * @since 2.7.0
	 * 1. Only `Yes - Donor's Choice` recurring donation type allow for Sequoia form template.
	 */
	const showRecurringAddonNotice = function() {
		const $templateList = $( 'div.templates-list', '.form_template_options_wrap' ),
			  recurringDonationType = $( 'input[name="_give_recurring"]:checked', '._give_recurring_field' ).val(),
			  $recurringNotices = $( '.js-sequoia-form-template-recurring-addon-notice', '#form_template_options' );

		// Show already added notices.
		if ( $recurringNotices.length ) {
			$recurringNotices.removeClass( 'give-hidden' );
			return;
		}

		if ( 'yes_admin' === recurringDonationType ) {
			$templateList.before( `<div class="give-notice notice warning notice-warning inline js-sequoia-form-template-recurring-addon-notice js-has-compatibility-issue"><p>${ Give.fn.getGlobalVar( 'formTemplate' ).Sequoia.donorChoiceRecurringDonationType }</p></div>` );
		}
	};

	$( document ).ready( function() {
		// Recurring add-on related functionality.
		if ( isRecurringAddonActive() && isSequoiaFromTemplateActive() ) {
			showRecurringAddonNotice();

			$( 'input[name="_give_form_template"]', '#form_template_options' ).on( 'change', function() {
				if ( 'sequoia' === $( this ).val() ) {
					showRecurringAddonNotice();
					return;
				}

				$( '.js-sequoia-form-template-recurring-addon-notice', '#form_template_options' ).addClass( 'give-hidden' );
			} );
		}
	} );
}( jQuery ) );
