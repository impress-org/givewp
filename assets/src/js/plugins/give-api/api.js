/* global give_global_vars, jQuery */
import GiveNotice from './notice';
import GiveForm from './form';
import GiveDonor from './donor';
import GiveUtil from './util';
import GiveShare from './share';
import accounting from 'accounting';

/**
 *  This API is under development.
 *
 *  Currently used only for internal purpose.
 */
const Give = {
	init: function() {
		let subHelperObjs = [ 'form' ],
			counter = 0;
		jQuery( document ).trigger( 'give:preInit' );

		// Initialize all init methods of sub helper objects.
		while ( counter < subHelperObjs.length ) {
			if ( !! Give[ subHelperObjs[ counter ] ].init ) {
				Give[ subHelperObjs[ counter ] ].init();
			}
			counter++;
		}

		jQuery( document ).trigger( 'give:postInit' );
	},

	fn: {
		/**
		 * Format Currency
		 *
		 * Formats the currency with accounting.js
		 *
		 * @param {string} price
		 * @param {object}  args
		 * @param {object} $form
		 * @returns {*|string}
		 */
		formatCurrency: function( price, args, $form ) {
			// Global currency setting.
			let format_args = {
				symbol: '',
				decimal: this.getGlobalVar( 'decimal_separator' ),
				thousand: this.getGlobalVar( 'thousands_separator' ),
				precision: parseInt( this.getGlobalVar( 'number_decimals' ) ),
				currency: this.getGlobalVar( 'currency' ),
			};

			price = price.toString().trim();
			$form = 'undefined' === typeof $form ? {} : $form;

			// Form specific currency setting.
			if ( $form.length ) {
				//Set the custom amount input value format properly
				format_args = {
					symbol: '',
					decimal: Give.form.fn.getInfo( 'decimal_separator', $form ),
					thousand: Give.form.fn.getInfo( 'thousands_separator', $form ),
					precision: Give.form.fn.getInfo( 'number_decimals', $form ),
					currency: Give.form.fn.getInfo( 'currency_code', $form ),
				};
			}

			args = jQuery.extend( format_args, args );

			// Make sure precision is integer type
			args.precision = parseInt( args.precision );

			if ( 'INR' === args.currency ) {
				let actual_price = accounting.formatNumber( price, { precision: format_args.precision, decimal: '.' } ),
					afterPoint = args.precision ? '.0' : '',
					lastThree = '',
					otherNumbers = '',
					result = '',
					lastDotPosition = '';

				actual_price = accounting.unformat( actual_price, '.' ).toString();
				actual_price = actual_price.toString();

				if ( actual_price.indexOf( '.' ) > 0 ) {
					afterPoint = actual_price.substring( actual_price.indexOf( '.' ), actual_price.length );
				}

				actual_price = Math.floor( actual_price ).toString();
				lastThree = actual_price.substring( actual_price.length - 3 );
				otherNumbers = actual_price.substring( 0, actual_price.length - 3 );

				if ( '' !== otherNumbers ) {
					lastThree = format_args.thousand + lastThree;
				}

				result = otherNumbers.replace( /\B(?=(\d{2})+(?!\d))/g, format_args.thousand ) + lastThree + afterPoint;
				lastDotPosition = result.lastIndexOf( '.' );
				result = result.slice( 0, lastDotPosition ) + ( ( result.slice( lastDotPosition ) + '000000000000' ).substr( 0, args.precision + 1 ) );
				price = result;

				if ( undefined !== args.symbol && args.symbol.length ) {
					if ( 'after' === args.position ) {
						price = price + args.symbol;
					} else {
						price = args.symbol + price;
					}
				}
			} else {
				// Properly position symbol after if selected.
				if ( 'after' === args.position ) {
					args.format = '%v%s';
				}

				price = accounting.formatMoney( price, args );
			}

			return price;
		},

		/**
		 * Unformat Currency
		 *
		 * @param price
		 * @param {string} decimal_separator
		 * @returns {number}
		 */
		unFormatCurrency: function( price, decimal_separator ) {
			if ( 'string' === typeof price ) {
				const regex = ',' === decimal_separator.trim() ? /[^0-9\,-]+/g : /[^0-9\.-]+/g;

				price = price.replace( regex, '' );

				if ( 0 === price.indexOf( decimal_separator ) ) {
					price = price.substr( 1 );
				} else if ( ( price.length - 1 ) === price.indexOf( decimal_separator ) ) {
					price = price.slice( 0, -1 );
				}
			}

			return Math.abs( parseFloat( accounting.unformat( price, decimal_separator ) ) );
		},

		/**
		 * Get Parameter by Name
		 *
		 * @see: http://stackoverflow.com/questions/901115/how-can-i-get-query-string-values-in-javascript
		 * @param name
		 * @param url
		 * @since 1.4.2
		 * @returns {*}
		 */
		getParameterByName: function( name, url ) {
			if ( ! url ) {
				url = window.location.href;
			}

			url = decodeURIComponent( url );
			name = name.replace( /[\[\]]/g, '\\$&' );

			const regex = new RegExp( '[?&]' + name + '(=([^&#]*)|&|#|$)' ),
				results = regex.exec( url );

			if ( ! results ) {
				return null;
			}

			if ( ! results[ 2 ] ) {
				return '';
			}

			return decodeURIComponent( results[ 2 ].replace( /\+/g, ' ' ) );
		},

		/**
		 * Get information from global var
		 *
		 * @since 1.8.17
		 * @param {string} str Variable in global param.
		 *
		 * @return {string}
		 */
		getGlobalVar: function( str ) {
			const giveGlobals = this.getGlobal();

			return ( 'undefined' === typeof giveGlobals[ str ] ? '' : giveGlobals[ str ] );
		},

		/**
		 * Get global param
		 *
		 * @since 2.33.0  Return default value of global param if param not found.
		 * @since 2.2.4
		 *
		 * @return {object} WordPress localized global param.
		 */
		getGlobal: function () {
			return ('undefined' === typeof give_global_vars)
				? (window.give_vars || {})
				: (window.give_global_vars || {});
		},

		/**
		 * set cache
		 *
		 * @since 1.8.17
		 *
		 * @param {string} key
		 * @param {string} value
		 * @param {object} $form
		 */
		setCache: function( key, value, $form ) {
			if ( $form.length ) {
				Give.cache[ 'form_' + Give.form.fn.getInfo( 'form-id', $form ) ][ key ] = value;
			} else {
				Give.cache[ key ] = value;
			}
		},

		/**
		 * Get cache
		 *
		 * @since 1.8.17
		 * @param key
		 * @param $form
		 * @return {string|*}
		 */
		getCache: function( key, $form ) {
			let cache,
				formObj = Give.cache[ 'form_' + Give.form.fn.getInfo( 'form-id', $form ) ];

			if ( $form.length ) {
				cache = 'undefined' !== typeof formObj ? formObj[ key ] : '';
			} else {
				cache = Give.cache[ key ];
			}

			cache = 'undefined' === typeof cache ? '' : cache;

			return cache;
		},

		/**
		 * Get cookie
		 * Note: only for internal use
		 *
		 * @since 2.2.20
		 * @private
		 *
		 * @param {string} name Cookie name
		 * @return {string}
		 */
		__getCookie: function( name ) {
			const value = '; ' + document.cookie,
				parts = value.split( '; ' + name + '=' );

			let cookie = '';

			if ( 2 === parts.length ) {
				cookie = parts.pop().split( ';' ).shift();
			}

			return cookie;
		},

		/**
		 * Show and hide spinner
		 * Note: use only in WP Backend
		 *
		 * @since 2.5.0
		 * @public
		 *
		 * @param {object} $container Container where you wan to prepend spinner.
		 * @param {object} args argument to change loader output.
		 */
		loader: function( $container, args = {} ) {
			args = Object.assign( { show: true, loadingAnimation: true, loadingText: null }, args );

			const spinner = args.loadingAnimation ? '<span class="is-active spinner"></span>' : '',
				  text = null !== args.loadingText ? args.loadingText : Give.fn.getGlobalVar( 'loader_translation' ).updating;

			let classes, spinnerHTML;

			if ( false === args.show ) {
				jQuery( '.give-spinner-wrap', $container ).remove();

				return false;
			}

			classes = spinner.length ? 'give-has-spinner' : '';
			classes += text.length ? ' give-has-text' : '';
			classes = classes.length ? ' ' + classes.trim() : '';

			spinnerHTML = `<div class="give-spinner-wrap${ classes }"><div class="give-spinner-inner">${ ( text + spinner ).trim() }</div></div>`;

			// return spinner HTML.
			if ( null === args.show ) {
				return spinnerHTML;
			}

			$container.prepend( spinnerHTML );

			return true;
		},

		/**
		 * Remove parameter from url
		 *
		 * @since 2.7
		 * @param {string} url
		 * @param {string} parameter
		 * @return {string|*}
		 */
		removeURLParameter: function ( url, parameter ) {
			//prefer to use l.search if you have a location/link object
			const urlparts = url.split( '?' );
			if ( urlparts.length >= 2 ) {
				const prefix = encodeURIComponent( parameter ) + '=';
				const pars = urlparts[ 1 ].split( /[&;]/g );

				//reverse iteration as may be destructive
				for ( let i = pars.length; i-- > 0; ) {
					//idiom for string.startsWith
					if ( pars[ i ].lastIndexOf( prefix, 0 ) !== -1 ) {
						pars.splice( i, 1 );
					}
				}

				return urlparts[ 0 ] + ( pars.length > 0 ? '?' + pars.join( '&' ) : '' );
			}
			return url;
		},

		/**
		 * Helper function used to determine if the given number has decimal
		 * @param value
		 * @returns {boolean}
		 */
		numberHasDecimal: function( value ) {
			return Math.floor( value ) !== Number( value );
		}
	},

	/**
	 * This object key will be use to cache predicted data or donor activity.
	 *
	 * @since 1.8.17
	 */
	cache: {},
};

Give.notice = GiveNotice;
Give.form = GiveForm;
Give.donor = GiveDonor;
Give.util = GiveUtil;
Give.share = GiveShare;

export default Give;
