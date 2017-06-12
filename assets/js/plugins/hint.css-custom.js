jQuery(document).ready(function($){
	// Add custom style.
	var styles = '.give-fake-hintcss-js:before, .give-fake-hintcss-js:after{visibility:visible !important; opacity:1!important;}';
	$('<style>'+ styles +'</style>').appendTo(document.head);
});

jQuery.fn.give_fakehint = function( action, settings ) {
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