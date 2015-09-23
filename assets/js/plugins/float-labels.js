/*!
 * Float Labels
 *
 * Version: 1.0.6
 * Author: Paul Ryley (http://geminilabs.io)
 * URL: https://github.com/geminilabs/float-labels.js
 * License: MIT
 */

/**
 * This plugin applies the float label pattern to a form.
 *
 * The float label pattern floats the inline label up above the input after the user focuses on the
 * form field or enters a value.
 *
 * Pros:
 * - User keeps context
 *   The user keeps the field’s context after they’ve focused and entered a value. This provides
 *   for a more accessible, less frustrating experience.
 * - Clean and scannable by default
 *   The pattern allows for a clean inline label experience by default, and only becomes a little
 *   more cluttered once the user has filled things out.
 * - Elegant
 *
 * Cons:
 * - Doesn’t provide room for both label and placeholder
 *   Because the label is occupying the same space as the placeholder, there’s no room for
 *   additional hinting.
 * - Small Label
 *   The label becomes small and possibly hard to read, but at the same time it’s not as big a deal.
 *   Once the user has interacted with the input, the label becomes a reference rather than an
 *   instruction.
 *
 * Links:
 * - http://bradfrost.com/blog/post/float-label-pattern/
 * - https://dribbble.com/shots/1254439--GIF-Mobile-Form-Interaction
 */

(function()
{
	var $, floatLabel, keyPress, form, opts;

	$ = window.jQuery || window.Zepto || window.$;

	/**
	 * Floating Labels
	 *
	 * @param array options [plugin options array]
	 */
	$.fn.floatlabels = function( options )
	{
		opts = $.extend({
			regex       : /text|password|email|number|search|url|tel/,
			exclude     : [],
			customLabel : function(){},
			customEvent : function(){},
		}, options );

		form = $( this );

		if( form.length ) {

			if( form.get(0).tagName !== 'FORM' ) {
				var forms = form.find( 'form' );

				if( forms.length === 0 ) {
					forms = form.closest( 'form' );
				}
				if( forms.length === 0 ) {
					return;
				}

				form = forms;
			}

			form.addClass( 'floated-labels' );

			opts.exclude.push( '.no-label' );
			opts.exclude = opts.exclude.join( ',' );

			// float input labels
			form.find( 'input:not(' + opts.exclude + ')' ).each( function()
			{
				if( opts.regex.test( $( this ).attr( 'type' ) ) ) {
					floatLabel( this );
				}
			});

			// float textarea labels
			form.find( 'textarea:not(' + opts.exclude + ')' ).each( function()
			{
				floatLabel( this );
			});

			// float select labels
			form.find( 'select:not(' + opts.exclude + ')' ).each( function()
			{
				floatLabel( this, 'formSelect' );

				$( this ).parent().addClass( 'styled select' );
			});
		}
	};

	/**
	 * Modifies a form element for floatlabels CSS styling
	 *
	 * @param object el          [the :input element]
	 * @param string placeholder [the :input placeholder] // @todo: need to detect if it exists!!
	 */
	floatLabel = function( el, placeholder )
	{
		var id, label, label_el, floatlabel = 'floatlabel';

		el = $( el );
		id = el.attr( 'id' );

		if( id !== undefined ) {
			label_el = $( 'label[for="' + id + '"]' );
		}

		// only proceed if element label exists
		if( label_el && label_el.length ) {

			label = label_el.text().replace( '*', '' ).trim();

			// add a placholder option to the select if it doesn't already exist
			if( placeholder === 'formSelect' ) {
				var first = el.children().first();

				if( label.length && first.val() === '' && first.text() === '' ) {
					first.text( label );
				}
			}

			if( !el.parent().hasClass( floatlabel ) ) {

				if( !label.length ) {
					label = el.attr( 'placeholder' );
				}

				el.addClass( floatlabel + '-input' ).wrap( _pf( '<div class="{0} {0}-{1}"/>', floatlabel, id ) );

				// allow for custom defined events
				opts.customEvent.call( this, el );

				// allow for custom defined labels
				var custom_label = opts.customLabel.call( this, el, label );

				if( custom_label !== undefined ) {
					label = custom_label;
				}

				if( label_el.length ) {
					label_el.remove();
				}

				el.after( _pf('<label for="{0}" class="{1}-label">{2}</label>', id, floatlabel, label ) );
			}

			if( el.val().length ) {
				el.parent().addClass( 'is-active' );
			}

			// Events
			el.on( 'focus', function()
			{
				el.parent().addClass( 'is-focused' );
			});

			el.on( 'blur', function()
			{
				el.parent().removeClass( 'is-focused' );
			});

			el.on( 'keyup blur change', function( ev )
			{
				keyPress( el, ev );
			});
		}
	};

	/**
	 * Fired when the :input value has changed or when it loses focus
	 *
	 * @param object el [the :input element]
	 * @param event  ev [the event that is fired on keyup|blur|change]
	 */
	keyPress = function( el, ev )
	{
		if( ev ) {
			var key = ev.keyCode || ev.which;
			if( 9 === key ) return;
		}

		if( el.val().length ) {
			el.parent().addClass( 'is-active' );
		}
		else {
			el.parent().removeClass( 'is-active' );
		}
	};

	/**
	 * Simplified printf implementation
	 */
	_pf = function( format )
	{
		var args = [].slice.call( arguments, 1, arguments.length );

		return format.replace( /{(\d+)}/g, function ( match, number )
		{
			return typeof args[ number ] !== undefined ? args[ number ] : match;
		});
	};

}).call( this );
