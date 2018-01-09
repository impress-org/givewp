/*!
 * Float Labels
 *
 * @version: 3.3.2
 * @author: Paul Ryley (http://geminilabs.io)
 * @url: https://geminilabs.github.io/float-labels.js
 * @license: MIT
 */

/** global: NodeList, Option */

;(function( window, document, undefined )
{
	"use strict";

	var Plugin = function( el, options )
	{
		this.el = this.isString( el ) ? document.querySelectorAll( el ) : [el];
		this.config = [];
		this.options = options;
		this.selectors = [];
		this.init();
		this.destroy = function() {
			this.loop( function( el ) {
				el.removeEventListener( 'reset', this.events.reset );
				this.removeClasses( el );
			}, function( field ) {
				this.reset( field );
			});
		};
		this.rebuild = function() {
			this.loop( null, function( field ) {
				this.floatLabel( field, true );
			});
		};
	};

	Plugin.prototype = {

		defaults: {
			customEvent: null,
			customLabel: null,
			customPlaceholder: null,
			exclude: '.no-label',
			inputRegex: /email|number|password|search|tel|text|url/,
			prefix: 'fl-',
			prioritize: 'label', // label|placeholder
			requiredClass: 'required',
			style: 0, // 0|1|2
			transform: 'input,select,textarea'
		},

		/** @return void */
		init: function()
		{
			this.initEvents();
			this.loop( function( el, i ) {
				var style = this.config[i].style;
				el.addEventListener( 'reset', this.events.reset );
				el.classList.add( this.prefixed( 'form' ));
				if( style ) {
					el.classList.add( this.prefixed( 'style-' + style ));
				}
			}, function( field ) {
				this.floatLabel( field );
			});
		},

		/** @return void */
		initEvents: function()
		{
			this.events = {
				blur: this.onBlur.bind( this ),
				change: this.onInput.bind( this ),
				focus: this.onFocus.bind( this ),
				input: this.onInput.bind( this ),
				reset: this.onReset.bind( this ),
			};
		},

		/** @return string */
		addRemove: function( bool )
		{
			return bool ? 'add' : 'remove';
		},

		/** @return null|void */
		build: function( el )
		{
			var labelEl = this.getLabel( el );
			if( !labelEl )return;
			el.classList.add( this.prefixed( el.tagName.toLowerCase() ));
			this.setLabel( labelEl, el );
			this.setPlaceholder( labelEl, el );
			this.wrapLabel( labelEl, el );
			this.handleEvents( el, 'add' );
			if( typeof this.config[this.current].customEvent === 'function' ) {
				this.config[this.current].customEvent.call( this, el );
			}
		},

		/** @return Element */
		createEl: function( tag, attributes )
		{
			var el = ( typeof tag === 'string' ) ? document.createElement( tag ) : tag;
			attributes = attributes || {};
			for( var key in attributes ) {
				if( !attributes.hasOwnProperty( key ))continue;
				el.setAttribute( key, attributes[key] );
			}
			return el;
		},

		/** @return object */
		extend: function()
		{
			var args = [].slice.call( arguments );
			var result = args[0];
			var extenders = args.slice(1);
			Object.keys( extenders ).forEach( function( i ) {
				for( var key in extenders[i] ) {
					if( !extenders[i].hasOwnProperty( key ))continue;
					result[key] = extenders[i][key];
				}
			});
			return result;
		},

		/** @return null|void */
		floatLabel: function( el, rebuild )
		{
			if( !el.getAttribute( 'id' ) || (
				el.tagName === 'INPUT' && !this.config[this.current].inputRegex.test( el.getAttribute( 'type' ))
			))return;
			if( this.hasParent( el )) {
				if( rebuild !== true )return;
				this.reset( el );
			}
			this.build( el );
		},

		/** @return string|false */
		getLabel: function( el )
		{
			var label = 'label[for="' + el.getAttribute( 'id' ) + '"]';
			var labelEl = this.el[this.current].querySelectorAll( label );
			// check for multiple labels with identical 'for' attributes
			if( labelEl.length > 1 ) {
				labelEl = el.parentNode.querySelectorAll( label );
			}
			if( labelEl.length === 1 ) {
				return labelEl[0];
			}
			return false;
		},

		/** @return string */
		getLabelText: function( labelEl, el )
		{
			var labelText = labelEl.textContent.replace( /[*:]/g, '' ).trim();
			var placeholderText = el.getAttribute( 'placeholder' );
			if( !labelText || ( labelText && placeholderText && this.config[this.current].prioritize === 'placeholder' )) {
				labelText = placeholderText;
			}
			return labelText;
		},

		/** @return void */
		handleEvents: function( el, action )
		{
			var events = this.events;
			['blur','input','focus'].forEach( function( event ) {
				if( el.type === 'file' && event === 'input' ) {
					event = 'change';
				}
				el[ action + 'EventListener']( event, events[event] );
			});
		},

		/** @return bool */
		hasParent: function( el )
		{
			return el.parentNode.classList.contains( this.prefixed( 'wrap' ));
		},

		/** @return bool */
		isString: function( str ) {
			return Object.prototype.toString.call( str ) === "[object String]";
		},

		/** @return void */
		loop: function( elCallback, fieldCallback ) {
			for( var i = 0; i < this.el.length; ++i ) {
				if( typeof this.selectors[i] === 'undefined' ) {
					var config = this.extend( {}, this.defaults, this.options, this.el[i].getAttribute( 'data-options' ));
					var exclude = ':not(' + config.exclude.split( /[\s,]+/ ).join( '):not(' ) + ')';
					this.selectors[i] = config.transform.replace( /,/g, exclude + ',' ) + exclude;
					this.config[i] = config;
				}
				var fields = this.el[i].querySelectorAll( this.selectors[i] );
				this.current = i;
				if( typeof elCallback === 'function' ) {
					elCallback.call( this, this.el[i], i );
				}
				for( var x = 0; x < fields.length; ++x ) {
					if( typeof fieldCallback === 'function' ) {
						fieldCallback.call( this, fields[x], i );
					}
				}
			}
		},

		/** @return void */
		onBlur: function( ev )
		{
			ev.target.parentNode.classList.remove( this.prefixed( 'has-focus' ));
		},

		/** @return void */
		onInput: function( ev )
		{
			ev.target.parentNode.classList[
				this.addRemove( ev.target.value.length )
			]( this.prefixed( 'is-active' ));
		},

		/** @return void */
		onFocus: function( ev )
		{
			ev.target.parentNode.classList.add( this.prefixed( 'has-focus' ));
		},

		/** @return void */
		onReset: function()
		{
			setTimeout( this.resetFields.bind( this ));
		},

		/** @return string */
		prefixed: function( value )
		{
			return this.config[this.current].prefix + value;
		},

		/** @return void */
		removeClasses: function( el )
		{
			var prefix = this.config[this.current].prefix;
			var classes = el.className.split( ' ' ).filter( function( c ) {
				return c.lastIndexOf( prefix, 0 ) !== 0;
			});
			el.className = classes.join( ' ' ).trim();
		},

		/** @return null|void */
		reset: function( el )
		{
			var parent = el.parentNode;
			if( !this.hasParent( el ))return;
			var fragment = document.createDocumentFragment();
			while( parent.firstElementChild ) {
				var childEl = parent.firstElementChild;
				this.removeClasses( childEl );
				fragment.appendChild( childEl );
			}
			parent.parentNode.replaceChild( fragment, parent );
			this.resetPlaceholder( el );
			this.handleEvents( el, 'remove' );
		},

		/** @return void */
		resetFields: function()
		{
			var fields = this.el[this.current].querySelectorAll( this.selectors[this.current] );
			for( var i = 0; i < fields.length; ++i ) {
				fields[i].parentNode.classList[
					this.addRemove( fields[i].tagName === 'SELECT' && fields[i].value !== '' )
				]( this.prefixed( 'is-active' ));
			}
		},

		/** @return void */
		resetPlaceholder: function( el )
		{
			var dataPlaceholder = 'data-placeholder';
			var originalPlaceholder = el.getAttribute( dataPlaceholder );
			if( originalPlaceholder !== null ) {
				el.removeAttribute( dataPlaceholder );
				el.setAttribute( 'placeholder', originalPlaceholder );
			}
		},

		/** @return void */
		setLabel: function( labelEl, el )
		{
			labelEl.classList.add( this.prefixed( 'label' ));
			labelEl.textContent = this.getLabelText( labelEl, el );
			if( typeof this.config[this.current].customLabel === 'function' ) {
				labelEl.textContent = this.config[this.current].customLabel.call( this, labelEl, el );
			}
		},

		/** @return void */
		setPlaceholder: function( labelEl, el )
		{
			var placeholderText = el.getAttribute( 'placeholder' );
			if( this.config[this.current].prioritize === 'label' || !placeholderText ) {
				if( placeholderText ) {
					el.setAttribute( 'data-placeholder', placeholderText );
				}
				placeholderText = this.getLabelText( labelEl, el );
			}
			if( typeof this.config[this.current].customPlaceholder === 'function' ) {
				placeholderText = this.config[this.current].customPlaceholder.call( this, placeholderText, el, labelEl );
			}
			if( el.tagName === 'SELECT' ) {
				this.setSelectPlaceholder( el, placeholderText );
			}
			else {
				el.setAttribute( 'placeholder', placeholderText );
			}
		},

		/** @return void */
		setSelectPlaceholder: function( el, placeholderText )
		{
			var childEl = el.firstElementChild;
			if( childEl.hasAttribute( 'value' ) && childEl.value ) {
				var selected = el.options[el.selectedIndex].defaultSelected !== true ? true : false;
				el.insertBefore( new Option( placeholderText, '', selected, selected ), childEl );
			}
			else {
				childEl.setAttribute( 'value', '' );
			}
			if( childEl.textContent === '' ) {
				childEl.textContent = placeholderText;
			}
		},

		/** @return void */
		wrapLabel: function( labelEl, el )
		{
			var wrapper = this.createEl( 'div', {
				class: this.prefixed( 'wrap' ) + ' ' + this.prefixed( 'wrap-' + el.tagName.toLowerCase() ),
			});
			if( el.value !== undefined && el.value.length ) {
				wrapper.classList.add( this.prefixed( 'is-active' ));
			}
			if( el.getAttribute( 'required' ) !== null || el.classList.contains( this.config[this.current].requiredClass )) {
				wrapper.classList.add( this.prefixed( 'is-required' ));
			}
			el.parentNode.insertBefore( wrapper, el );
			wrapper.appendChild( labelEl );
			wrapper.appendChild( el );
		},
	};

	window.FloatLabels = Plugin;

})( window, document );
