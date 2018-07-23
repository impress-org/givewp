import GiveNotice from './notice';
import GiveForm from './form';

/**
 *  This API is under development.
 *
 *  Currently used only for internal purpose.
 */
let Give = {
	init: function() {
		let subHelperObjs = [ 'form' ],
			counter = 0;
		jQuery( document ).trigger( 'give:preInit' );

		// Initialize all init methods of sub helper objects.
		while ( counter < subHelperObjs.length ) {
			if ( ! ! Give[ subHelperObjs[ counter ] ].init ) {
				Give[ subHelperObjs[ counter ] ].init();
			}
			counter ++;
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
				decimal: give_global_vars.decimal_separator,
				thousand: give_global_vars.thousands_separator,
				precision: parseInt( give_global_vars.number_decimals ),
				currency: give_global_vars.currency
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
				let actual_price = accounting.unformat( price, '.' ).toString();

				let decimal_amount = '',
					result,
					amount,
					decimal_index = actual_price.indexOf( '.' );

				if ( (- 1 !== decimal_index) && args.precision ) {
					decimal_amount = Number( actual_price.substr( parseInt( decimal_index ) ) )
						.toFixed( args.precision )
						.toString()
						.substr( 1 );
					actual_price = actual_price.substr( 0, parseInt( decimal_index ) );

					if ( ! decimal_amount.length ) {
						decimal_amount = '.0000000000'.substr( 0, (parseInt( decimal_index ) + 1) );
					} else if ( (args.precision + 1) > decimal_amount.length ) {
						decimal_amount = (decimal_amount + '000000000').substr( 0, args.precision + 1 );
					}
				} else {
					decimal_amount = '.000000000'.substr( 0, args.precision + 1 );
				}

				// Extract last 3 from amount
				result = actual_price.substr( - 3 );
				amount = actual_price.substr( 0, parseInt( actual_price.length ) - 3 );

				// Apply digits 2 by 2
				while ( amount.length > 0 ) {
					result = amount.substr( - 2 ) + args.thousand + result;
					amount = amount.substr( 0, parseInt( amount.length ) - 2 );
				}

				if ( decimal_amount.length ) {
					result = result + decimal_amount;
				}

				price = result;

				if ( undefined !== args.symbol && args.symbol.length ) {
					if ( 'after' === args.position ) {
						price = price + args.symbol;
					} else {
						price = args.symbol + price;
					}
				}
			} else {
				//Properly position symbol after if selected
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
				let regex = ',' === decimal_separator.trim() ? /[^0-9\,-]+/g : /[^0-9\.-]+/g;

				price = price.replace( regex, '' );

				if ( 0 === price.indexOf( decimal_separator ) ) {
					price = price.substr( 1 );
				} else if ( (price.length - 1) === price.indexOf( decimal_separator ) ) {
					price = price.slice( 0, - 1 );
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

			name = name.replace( /[\[\]]/g, '\\$&' );

			let regex = new RegExp( '[?&]' + name + '(=([^&#]*)|&|#|$)' ),
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
		 * @param {string} str
		 *
		 * @return {string}
		 */
		getGlobalVar: function( str ) {
			if ( 'undefined' === typeof give_global_vars[ str ] ) {
				return '';
			}

			return give_global_vars[ str ];
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
				cookie = parts.pop().split(';').shift();
			}

			return cookie;
		},
	},

	/**
	 * This object key will be use to cache predicted data or donor activity.
	 *
	 * @since 1.8.17
	 */
	cache: {}
};

Give.notice = GiveNotice;
Give.form = GiveForm;

export default Give;
