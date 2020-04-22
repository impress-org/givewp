/* globals Give, jQuery */
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

	$( document ).ready( function() {
		handleFormTemplateActivation();
		handleFormTemplateDeactivation();
		saveFormSettingOnlyIfFormTemplateSelected();
	} );
}( jQuery ) );
