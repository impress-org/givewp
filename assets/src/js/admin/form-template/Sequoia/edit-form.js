/* globals Give, jQuery */
( function( $ ) {
	/**
	 * Return whether or not recurring addon enabled or not.
	 */
	const isRecurringAddonActive = function() {
		return !! document.querySelector( '.give-recurring-row' );
	};

	/**
	 * Return whether or not Sequoia form template active.
	 */
	const isSequoiaFromTemplateActive = function() {
		return 'sequoia' === $( 'input[name="_give_form_template"]', '.give-metabox-panel-wrap' ).val();
	};

	/**
	 * Only `Yes - Donor's Choice` recurring donation type allow for sEquoia form template.
	 */
	const showRecurringAddonNotice = function() {
		const $templateList = $( 'div.templates-list', '.form_template_options_wrap' ),
			$recurringNotices = $( '.js-sequoia-form-template-recurring-addon-notice', '#form_template_options' );

		if ( $recurringNotices.length ) {
			$recurringNotices.removeClass( 'give-hidden' );
			return;
		}

		$templateList.before( `<div class="give-notice notice warning notice-warning inline js-sequoia-form-template-recurring-addon-notice"><p>${ Give.fn.getGlobalVar( 'formTemplate' ).Sequoia.donorChoiceRecurringDonationType }</p></div>` );
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
