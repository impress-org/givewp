/* globals Give, jQuery */
/* eslint-disable */
( function( $ ) {
	/**
	 * Handle form template activation
	 * @since: 2.7.0
	 */
	const handleFormTemplateActivation = function() {
		$( '#form_template_options' ).on( 'click', '.js-template--activate', function( ev ) {
			ev.preventDefault();

			const $templatesList = $( this ).parents( '.templates-list' ),
				  $innerContainer = $templatesList.parent(),
				  $parent = $( this ).parents( '.template-info' ),
				  activatedTemplateId = $parent.attr( 'data-id' );

			// Deactivate existing activated template.
			$( '.template-info', $templatesList ).removeClass( 'active' );

			// Show Settings.
			$innerContainer.find( `.template-options.${ activatedTemplateId }` ).addClass( 'active' );

			$( this ).text( Give.fn.getGlobalVar( 'deactivate' ) );
			$( this ).removeClass( 'js-template--activate' );
			$( this ).addClass( 'js-template--deactivate' );

			$( this ).parents( '.template-info' ).addClass( 'active' );
			$innerContainer.addClass( 'has-activated-template' );

			$innerContainer.prev( 'input[name=_give_form_template]' ).val( activatedTemplateId ).trigger( 'change' );
		} );
	};

	/**
	 * Handle form template deactivation
	 * @since: 2.7.0
	 */
	const handleFormTemplateDeactivation = function() {
		$( '#form_template_options' ).on( 'click', '.js-template--deactivate', function( ev ) {
			ev.preventDefault();

			const $templatesList = $( this ).parents( '.templates-list' ),
				  $innerContainer = $templatesList.parent(),
				  $parent = $( this ).parents( '.template-info' ),
				  activatedTemplateId = $parent.attr( 'data-id' );

			// Deactivate existing activated template.
			$( '.template-info', $templatesList ).removeClass( 'active' );

			// Hide Settings.
			$innerContainer.find( `.template-options.${ activatedTemplateId }` ).removeClass( 'active' );

			$( this ).text( Give.fn.getGlobalVar( 'activate' ) );
			$( this ).removeClass( 'js-template--deactivate' );
			$( this ).addClass( 'js-template--activate' );

			$innerContainer.removeClass( 'has-activated-template' );

			$innerContainer.prev( 'input[name=_give_form_template]' ).val( '' ).trigger( 'change' );
		} );
	};

	/**
	 * Handle form template setting vlaidation
	 *
	 * @since 2.7.0
	 */
	const saveFormSettingOnlyIfFormTemplateSelected = function() {
		$( '.post-type-give_forms' ).on( 'click', '#publishing-action input[type=submit]', function() {
			const activatedTemplate = $( 'input[name=_give_form_template]', '#form_template_options' ).val();

			if ( ! activatedTemplate ) {
				new Give.modal.GiveNoticeAlert( {
					type: 'warning',
					modalContent: {
						desc: Give.fn.getGlobalVar( 'form_template_required' ),
					},
				} ).render();

				// Open form template settings.
				if ( 'form_template_options' !== Give.fn.getParameterByName( 'give_tab' ) ) {
					$( 'a[href="#form_template_options"]' ).trigger( 'click' );
				}

				return false;
			}

			return true;
		} );
	};

	/**
	 * Handle conditional form template fields
	 *
	 * @since 2.7.0
	 */
	const handleConditionalFormTemplateFields = function() {
		updateIntroductionFields();
		$( 'input[name="sequoia[introduction][enabled]"]' ).on( 'change', function() {
			updateIntroductionFields();
		} );
	};

	/**
	 * Update introduciton fields
	 * Hide or show introduction fields if enabled
	 *
	 * @since 2.7.0
	 */
	const updateIntroductionFields = function() {
		const introductionFields = $( '[class*="sequoia[introduction][headline]_field"], [class*="sequoia[introduction][description]_field"], [class*="sequoia[introduction][image]_field"], [class*="sequoia[introduction][primary_color]_field"], [class*="sequoia[introduction][donate_label]_field"]' );

		if ( $( 'input[name="sequoia[introduction][enabled]"]' ).length !== 0 && ! $( 'input[name="sequoia[introduction][enabled]"]' ).prop( 'checked' ) ) {
			$( introductionFields ).hide();
		} else {
			$( introductionFields ).show();
		}
	};

	$( document ).ready( function() {
		handleFormTemplateActivation();
		handleFormTemplateDeactivation();
		saveFormSettingOnlyIfFormTemplateSelected();
		handleConditionalFormTemplateFields();
	} );
}( jQuery ) );
/* eslint-enable */
