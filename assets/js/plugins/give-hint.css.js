var Give_HintCSS       = Give_HintCSS || {};
jQuery.fn.give_hintcss = function(action, settings ) {
	return this.each(function(){
		var $this = jQuery(this);
		settings = jQuery.extend({
			label: ''
		}, settings );

		var $tooltip = $this.next('span.give-fake-hint-tooltip-js');
		if( ! $tooltip.length ){
			var label = ! settings.label.length ? $this.data('hint-aria-label') : settings.label;

			// bailout.
			if( ! label.length ) {
				return;
			}

			// Add custom style.
			if( undefined == Give_HintCSS.style_loaded ){
				var styles = '.give-fake-hintcss-js:before, .give-fake-hintcss-js:after{visibility:visible !important; opacity:1!important;}';
				jQuery('<style>'+ styles +'</style>').appendTo(document.head);

				Give_HintCSS.style_loaded = 1;
			}

			$this.after( '<span class="give-fake-hint-tooltip-js hint--top hint--medium give-fake-hintcss-js" aria-label="' + label + '"></span>' );
			$tooltip = $this.next();

			$tooltip.css({
				top:-( $this.outerHeight() ),
				left: - ($this.outerWidth()/2 )
			});
		}


		if ( action === 'show' ) {
			$tooltip.addClass( 'give-fake-hintcss-js' );
		}else if ( action === 'hide' ) {
			$tooltip.removeClass( 'give-fake-hintcss-js' );
		}
	});
};

// Qtip2 backward compatibility.
jQuery(document).ready(function ($) {
	var qtip_tooltips = $('[data-tooltip]');

	// Add hintcss tooltip to existing qtip.
	if (qtip_tooltips.length) {
		qtip_tooltips.each(function (index, tooltip) {
			tooltip = ( tooltip instanceof jQuery ) ? tooltip : $(tooltip);
			reset_qtip(tooltip);
		});
	}

	// Add hintcss tooltip to dynamically created qtip.
	$('body').on('hover', '[data-tooltip]', function () {
		reset_qtip($(this));
	});

	/**
	 * Reset qtip to hintcss
	 * @param tooltip
	 */
	function reset_qtip(tooltip) {
		if (!tooltip.is('[class*="hint"]')) {
			var classes      = tooltip.attr('class'),
				icon_classes = [],
				label_length = tooltip.data('tooltip').split( ' ' ).length;

			if (classes) {
				classes      = classes.split(' ');
				icon_classes = $.grep(classes, function (item) {
					return ( -1 !== item.indexOf('give-icon') );
				});

				if (icon_classes.length) {
					// Set icon classes string.
					icon_classes = icon_classes.join(' ');

					// Remove icon class.
					tooltip.removeClass(icon_classes);

					// Add icon.
					tooltip.append('<i class="' + icon_classes + '"></i>');
				}
			}

			// Add hint.css related classes.
			tooltip.addClass('hint--top');

			if( 15 < label_length ) {
				tooltip.addClass('hint--large');
			}else if( 7 < label_length ) {
				tooltip.addClass('hint--medium');
			}

			tooltip.attr('aria-label', tooltip.data('tooltip') );
		}
	}
});