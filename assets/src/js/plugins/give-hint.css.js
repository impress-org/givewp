jQuery.fn.giveHintCss = function( action, settings ) {
	return this.each( function() {
		const $this = jQuery( this );
		settings = jQuery.extend( {
			label: '',
		}, settings );

		let $tooltip = $this.next( 'span.give-hint-tooltip-js' );
		if ( ! $tooltip.length ) {
			const label = ! settings.label.length ? $this.data( 'hint-aria-label' ) : settings.label;

			// bailout.
			if ( ! label.length ) {
				return;
			}

			$this.after( '<span class="give-hint-tooltip-js hint--top hint--medium" aria-label="' + label + '"></span>' );
			$tooltip = $this.next();

			$tooltip.css( {
				top: -( $this.outerHeight() ),
				left: -( $this.outerWidth() / 2 ),
			} );
		}

		if ( action === 'show' ) {
			$tooltip.addClass( 'hint--always' );
		} else if ( action === 'hide' ) {
			$tooltip.removeClass( 'hint--always' );
		}
	} );
};

// Qtip2 backward compatibility.
jQuery( document ).ready( function( $ ) {
	const qtip_tooltips = $( '[data-tooltip]' );

	// Add hintcss tooltip to existing qtip.
	if ( qtip_tooltips.length ) {
		qtip_tooltips.each( function( index, tooltip ) {
			tooltip = ( tooltip instanceof jQuery ) ? tooltip : $( tooltip );
			reset_qtip( tooltip );
		} );
	}

	// Add hintcss tooltip to dynamically created qtip.
	$( 'body' ).on( 'mouseenter mouseleave', '[data-tooltip]', function() {
		reset_qtip( $( this ) );
	} );

	/**
	 * Reset qtip to hintcss
	 * @param tooltip
	 */
	function reset_qtip( tooltip ) {
		if ( ! tooltip.is( '[class*="hint"]' ) ) {
			let classes = tooltip.attr( 'class' ),
				icon_classes = [],
				label_length = tooltip.data( 'tooltip' ).split( ' ' ).length;

			if ( classes ) {
				classes = classes.split( ' ' );
				icon_classes = $.grep( classes, function( item ) {
					return ( -1 !== item.indexOf( 'give-icon' ) );
				} );

				if ( icon_classes.length ) {
					// Set icon classes string.
					icon_classes = icon_classes.join( ' ' );

					// Remove icon class.
					tooltip.removeClass( icon_classes );

					// Add icon.
					tooltip.append( '<i class="' + icon_classes + '"></i>' );
				}
			}

			// Add hint.css related classes.
			tooltip.addClass( 'hint--top' );

			if ( 15 < label_length ) {
				tooltip.addClass( 'hint--large' );
			} else if ( 7 < label_length ) {
				tooltip.addClass( 'hint--medium' );
			}

			tooltip.attr( 'aria-label', tooltip.data( 'tooltip' ) );
		}
	}
} );
