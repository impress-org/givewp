/* globals jQuery, Give */
/**
 * This function use to initialize chosen js on select fields.
 *
 * Note: only for internal use. This can be updated or removed in the future.
 *
 * @since 2.7.0
 * @param $els
 */
export default function setupChosen( $els ) {
	// Add loader with each input field.
	$els.on( 'chosen:ready', function() {
		jQuery( this ).next( '.chosen-container' )
			.find( 'input.chosen-search-input' )
			.after( '<span class="spinner"></span>' );
	} );

	// Initiate chosen.
	$els.chosen( {
		inherit_select_classes: true,
		placeholder_text_single: Give.fn.getGlobalVar( 'one_option' ),
		placeholder_text_multiple: Give.fn.getGlobalVar( 'one_or_more_option' ),
	} );

	// No results returned from search trigger.
	$els.on( 'chosen:no_results', function() {
		let $container = jQuery( this ).next( '.chosen-container' ),
			$no_results_li = $container.find( 'li.no-results' ),
			error_string = '';

		const ajax_msg = Give.fn.getGlobalVar( 'chosen' );
		if ( $container.hasClass( 'give-select-chosen-ajax' ) && $no_results_li.length ) {
			error_string = ajax_msg.ajax_search_msg.replace( '{search_term}', '"' + jQuery( 'input', $container ).val() + '"' );
		} else {
			error_string = ajax_msg.no_results_msg.replace( '{search_term}', '"' + jQuery( 'input', $container ).val() + '"' );
		}

		$no_results_li.html( error_string );

		// Variables for setting up the typing timer.
		const doneTypingInterval = 342; // Time in ms, Slow - 521ms, Moderate - 342ms, Fast - 300ms.

		// Replace options with search results.
		jQuery( document.body ).on( 'keyup', '.give-select.chosen-container .chosen-search input, .give-select.chosen-container .search-field input', function( e ) {
			let val = jQuery( this ).val(),
				$container = jQuery( this ).closest( '.give-select-chosen' ),
				select = $container.prev(),
				$search_field = $container.find( 'input[type="text"]' ),
				variations = $container.hasClass( 'variations' ),
				lastKey = e.which,
				search_type = 'give_form_search',
				$this = this;

			// Detect if we have a defined search type, otherwise default to donation forms.
			if ( $container.prev().data( 'search-type' ) ) {
				// Don't trigger AJAX if this select has all options loaded.
				if ( 'no_ajax' === select.data( 'search-type' ) ) {
					return;
				}
				search_type = 'give_' + select.data( 'search-type' ) + '_search';
			}

			// Don't fire if short or is a modifier key (shift, ctrl, apple command key, or arrow keys).
			if (
				( val.length > 0 && val.length <= 3 ) ||
				! search_type.length ||
				(
					( 9 === lastKey ) || // Tab.
					( 13 === lastKey ) || // Enter.
					( 16 === lastKey ) || // Shift.
					( 17 === lastKey ) || // Ctrl.
					( 18 === lastKey ) || // Alt.
					( 19 === lastKey ) || // Pause, Break.
					( 20 === lastKey ) || // CapsLock.
					( 27 === lastKey ) || // Esc.
					( 33 === lastKey ) || // Page Up.
					( 34 === lastKey ) || // Page Down.
					( 35 === lastKey ) || // End.
					( 36 === lastKey ) || // Home.
					( 37 === lastKey ) || // Left arrow.
					( 38 === lastKey ) || // Up arrow.
					( 39 === lastKey ) || // Right arrow.
					( 40 === lastKey ) || // Down arrow.
					( 44 === lastKey ) || // PrntScrn.
					( 45 === lastKey ) || // Insert.
					( 144 === lastKey ) || // NumLock.
					( 145 === lastKey ) || // ScrollLock.
					( 91 === lastKey ) || // WIN Key (Start).
					( 93 === lastKey ) || // WIN Menu.
					( 224 === lastKey ) || // Command key.
					( 112 <= lastKey && 123 >= lastKey ) // F1 to F12 lastKey.
				)
			) {
				return;
			}

			clearTimeout( Give.cache.chosenSearchTypingTimer );
			$container.addClass( 'give-select-chosen-ajax' );

			Give.cache.chosenSearchTypingTimer = setTimeout(
				function() {
					jQuery.ajax( {
						type: 'POST',
						url: ajaxurl,
						data: {
							action: search_type,
							s: val,
							fields: jQuery( $this ).closest( 'form' ).serialize(),
						},
						dataType: 'json',
						beforeSend: function() {
							select.closest( 'ul.chosen-results' ).empty();
							$search_field.prop( 'disabled', true );
						},
						success: function( data ) {
							$container.removeClass( 'give-select-chosen-ajax' );

							// Remove all options but those that are selected.
							jQuery( 'option:not(:selected)', select ).remove();

							if ( data.length ) {
								jQuery.each( data, function( key, item ) {
									// Add any option that doesn't already exist.
									if ( ! jQuery( 'option[value="' + item.id + '"]', select ).length ) {
										if ( 0 === val.length ) {
											select.append( `<option value="${ item.id }">${ item.name }</option>` );
										} else {
											select.prepend( `<option value="${ item.id }">${ item.name }</option>` );
										}
									}
								} );

								// Trigger update event.
								$container.prev( 'select.give-select-chosen' ).trigger( 'chosen:updated' );
							} else {
								// Trigger no result message event.
								$container.prev( 'select.give-select-chosen' ).trigger( 'chosen:no_results' );
							}

							// Ensure the original query is retained within the search input.
							$search_field.prop( 'disabled', false );
							$search_field.val( val ).focus();
						},
					} ).fail( function( response ) {
						if ( window.console && window.console.log ) {
							console.log( response );
						}
					} ).done( function( response ) {
						$search_field.prop( 'disabled', false );
					} );
				},
				doneTypingInterval
			);
		} );

		jQuery( '.give-select-chosen .chosen-search input' ).each( function() {
			let type = jQuery( this ).parent().parent().parent().prev( 'select.give-select-chosen' ).data( 'search-type' );
			let placeholder = '';

			if ( 'form' === type ) {
				placeholder = Give.fn.getGlobalVar( 'search_placeholder' );
			} else {
				type = 'search_placeholder_' + type;
				if ( Give.fn.getGlobalVar( type ) ) {
					placeholder = Give.fn.getGlobalVar( type );
				}
			}
			jQuery( this ).attr( 'placeholder', placeholder );
		} );
	} );
}
