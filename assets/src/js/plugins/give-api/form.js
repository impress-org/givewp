export default {
	init: function() {
		this.fn.field.formatCreditCard( jQuery( 'form.give-form' ) );
		this.fn.__initialize_cache();

		window.onload = function() {
			Give.form.fn.__sendBackToForm();
		};
	},

	fn: {

		/**
		 * Check if donation form exist on page or not
		 *
		 * @since 2.2.2
		 *
		 * @return {boolean}
		 */
		isFormExist: function(){
			return !! document.getElementsByName('give-form-hash').length;
		},

		/**
		 * Disable donation form.
		 *
		 * @param {object} $form
		 * @param {boolean} is_disable
		 *
		 * @return {*}
		 */
		disable: function( $form, is_disable ) {
			if ( ! $form.length ) {
				return false;
			}

			$form.find( '.give-submit' ).prop( 'disabled', is_disable );
		},

		/**
		 * Get formatted amount
		 *
		 * @param {string/number} amount
		 * @param {object} $form
		 * @param {object} args
		 */
		formatAmount: function( amount, $form, args ) {
			// Do not format amount if form did not exist.
			if ( ! $form.length ) {
				return amount;
			}

			return Give.fn.formatCurrency( amount, args, $form );
		},

		/**
		 * Get form information
		 *
		 * @since 1.8.17
		 * @param {string} str
		 * @param {object} $form
		 *
		 * @return {string}
		 */
		getInfo: function( str, $form ) {
			var data = '';
			$form = 'undefined' !== typeof $form ? $form : {};

			// Bailout.
			if ( ! str.length || ! $form.length ) {
				return data;
			}

			switch ( str ) {
				case 'gateways':
					data = [];
					jQuery.each( $form.find( 'input[name="payment-mode"]' ), function( index, gateway ) {
						gateway = ! (gateway instanceof jQuery) ? jQuery( gateway ) : gateway;
						data.push( gateway.val().trim() );
					} );
					break;

				case 'form-type':
					if ( $form.hasClass( 'give-form-type-set' ) ) {
						data = 'set';
					} else if ( $form.hasClass( 'give-form-type-multi' ) ) {
						data = 'multi';
					}
					break;

				case 'form-id':
					data = $form.find( 'input[name="give-form-id"]' ).val();
					break;

				default:
					if ( $form.get( 0 ).hasAttribute( 'data-' + str ) ) {
						data = $form.attr( 'data-' + str );
					} else {
						data = $form.attr( str );
					}

					'undefined' !== typeof data ? data.trim() : data;
			}

			return data;
		},

		/**
		 * Set form information
		 *
		 * @since 1.8.17
		 * @param {string} str
		 * @param {string} val
		 * @param {object} $form
		 * @param {string} type
		 *
		 * @return {string|boolean}
		 */
		setInfo: function( type, val, $form, str ) {
			// Bailout.
			if ( ! $form.length ) {
				return false;
			}

			type = 'undefined' === typeof type ? 'data' : type;

			switch ( type ) {
				case 'nonce':
					$form.find( 'input[name="give-form-hash"]' ).val( val );
					break;
			}

			// Bailout.
			if ( 'undefined' !== typeof str && ! str.length ) {
				return false;
			}

			switch ( type ) {
				case 'attr':
					$form.attr( str, val );
					break;

				default:
					$form.data( str, val );
					break;
			}

			return true;
		},

		/**
		 * Get formatted amount
		 *
		 * @since 1.8.17
		 * @param {object} $form
		 */
		getGateway: function( $form ) {
			var gateway = '';

			if ( ! $form.length ) {
				return gateway;
			}

			gateway = $form.find( 'input[name="payment-mode"]:checked' ).val().trim();

			return 'undefined' !== typeof gateway ? gateway : '';
		},

		/**
		 * Get Price ID and levels for multi donation form
		 *
		 * @param   {Object} $form Form jQuery object
		 *
		 * @returns {Object}
		 */
		getVariablePrices: function( $form ) {
			var variable_prices = [], formLevels;

			// check if correct form type is multi or not.
			if (
				! $form.length ||
				! $form.hasClass( 'give-form-type-multi' ) ||
				! (formLevels = $form.find( '.give-donation-levels-wrap [data-price-id] ' ))
			) {
				return variable_prices;
			}

			jQuery.each( formLevels, function( index, item ) {
				// Get Jquery instance for item.
				item = ! (item instanceof jQuery) ? jQuery( item ) : item;

				var decimal_separator = Give.form.fn.getInfo( 'decimal_separator', $form );

				// Add price id and amount to collector.
				variable_prices.push( {
					price_id: item.data( 'price-id' ),
					amount: Give.fn.unFormatCurrency( item.val(), decimal_separator )
				} );
			} );

			return variable_prices;
		},

		/**
		 * Get form price ID
		 *
		 * @since 1.8.17
		 * @param {object} $form
		 * @param {boolean} is_amount
		 *
		 * @return {string}
		 */
		getPriceID: function( $form, is_amount ) {

			var variable_prices = this.getVariablePrices( $form ),
				current_amount = Give.fn.unFormatCurrency(
					$form.find( 'input[name="give-amount"]' ).val(),
					this.getInfo( 'decimal_separator', $form )
				),

				/**
				 * Flag Multi-levels for min. donation conditional.
				 *
				 * Note: Value of this variable will be:
				 *  a. -1      if no any level found.
				 *  b. [0-*]   Any number from zero if donation level found.
				 *  c  custom  if donation level not found and donation amount is greater than the custom minimum amount.
				 *
				 * @type {number/string} Donation level ID.
				 */
				price_id = ! ! Give.fn.getCache( 'amount_' + current_amount, $form ) ? Give.fn.getCache( 'amount_' + current_amount, $form ) : - 1;

			// Flag to decide on which param we want to find price_id
			is_amount = 'undefined' === typeof is_amount ? true : is_amount;

			// Find price id with amount in variable prices.
			if ( variable_prices.length ) {

				// Get recent selected price id for same amount.
				if ( - 1 === price_id ) {
					if ( is_amount ) {
						// Find amount in donation levels.
						jQuery.each( variable_prices, function( index, variable_price ) {
							if ( variable_price.amount === current_amount ) {
								price_id = variable_price.price_id;

								return false;
							}
						} );

						// Set level to custom.
						if ( - 1 === price_id && (this.getMinimumAmount( $form ) <= current_amount && (this.getMaximumAmount( $form ) >= current_amount) && this.getMinimumAmount( $form ) <= current_amount) ) {
							price_id = 'custom';
						}
					} else {
						price_id = jQuery( 'input[name="give-price-id"]', $form ).val();
					}
				}
			}

			return price_id;
		},

		/**
		 * Get form minimum amount
		 *
		 * @since 1.8.17
		 * @param {object} $form
		 *
		 * @return {string}
		 */
		getMinimumAmount: function( $form ) {
			return $form.find( 'input[name="give-form-minimum"]' ).val();
		},

		/**
		 * Get form maximum amount
		 *
		 * @since 2.1
		 * @param {object} $form
		 *
		 * @return {string}
		 */
		getMaximumAmount: function( $form ) {
			return $form.find( 'input[name="give-form-maximum"]' ).val();
		},

		/**
		 * Get form amount
		 *
		 * @since 1.8.17
		 * @param $form
		 * @return {*}
		 */
		getAmount: function( $form ) {
			// Bailout
			if ( ! $form.length ) {
				return null;
			}

			var amount = $form.find( 'input[name="give-amount"]' ).val();

			if ( 'undefined' === typeof amount || ! amount ) {
				amount = 0;
			}

			return Give.fn.unFormatCurrency( amount, this.getInfo( 'decimal_separator', $form ) );
		},

		/**
		 * Get form security nonce
		 *
		 * @since 1.8.17
		 * @param {object} $form
		 * @return {string}
		 */
		getNonce: function( $form ) {
			// Bailout
			if ( ! $form.length ) {
				return '';
			}

			let nonce = $form.find( 'input[name="give-form-hash"]' ).val();

			if ( 'undefined' === typeof nonce || ! nonce ) {
				nonce = '';
			}

			return nonce;
		},

		/**
		 * Reset form nonce.
		 *
		 * @since 2.0
		 *
		 * @param {object} $form Donation form object.
		 * @returns {boolean}
		 */
		resetNonce: function( $form ) {
			// Return false, if form is missing.
			if ( ! $form.length || ! jQuery( 'input[name="give-form-hash"]', $form ).length ) {
				return false;
			}

			Give.form.fn.disable( $form, true );

			//Post via AJAX to Give
			jQuery.post( give_global_vars.ajaxurl, {
					action: 'give_donation_form_nonce',
					give_form_id: Give.form.fn.getInfo( 'form-id', $form )
				},
				function( response ) {
					// Update nonce field.
					Give.form.fn.setInfo( 'nonce', response.data, $form, '' );

					Give.form.fn.disable( $form, false );
				}
			);
		},

		/**
		 * Reset form all nonce.
		 *
		 * @since 2.2.0
		 *
		 * @param {object} $form Donation form object.
		 * @returns {boolean}
		 */
		resetAllNonce: function( $form ) {
			// Return false, if form is missing.
			if ( ! $form.length ) {
				return false;
			}

			Give.form.fn.disable( $form, true );

			//Post via AJAX to Give
			jQuery.post( give_global_vars.ajaxurl, {
					action: 'give_donation_form_reset_all_nonce',
					give_form_id: Give.form.fn.getInfo( 'form-id', $form )
				},
				function( response ) {
					// Process only if get response successfully.
					if( ! response.success ) {
						return;
					}

					const createUserNonceField = $form.find( 'input[name="give-form-user-register-hash"]' );

					// Update nonce field.
					Give.form.fn.setInfo( 'nonce', response.data.give_form_hash, $form, '' );

					// Update create user nonce field.
					if( createUserNonceField.length ){
						createUserNonceField.val( response.data.give_form_user_register_hash );
					}

					Give.form.fn.disable( $form, false );

					/**
					 * Fire custom event handler when update all nonce of donation form
					 *
					 * @since  2.2.0
					 * @access access
					 */
					jQuery(document).trigger( 'give_reset_all_nonce', [response.data] );
				}
			).done(function(){
				Give.form.fn.disable( $form, false );
			});
		},

		/**
		 * Auto select donation level
		 *
		 * @since 1.8.17
		 * @param {object} $form
		 * @param {string} price_id
		 *
		 * @return {boolean}
		 */
		autoSelectDonationLevel: function( $form, price_id ) {

			if ( ! $form.length || 'multi' !== this.getInfo( 'form-type', $form ) ) {
				return false;
			}

			price_id = ('undefined' === typeof price_id) ? this.getPriceID( $form, false ) : price_id;

			switch ( true ) {

				// Auto select radio button.
				case (! ! $form.find( '.give-radio-input' ).length) :
					$form.find( '.give-radio-input' )
						.prop( 'checked', false );
					$form.find( '.give-radio-input[data-price-id="' + price_id + '"]' )
						.prop( 'checked', true )
						.addClass( 'give-default-level' );
					break;

				// Set focus to price id button.
				case (! ! $form.find( 'button.give-donation-level-btn' ).length) :
					$form.find( 'button.give-donation-level-btn' )
						.blur();
					$form.find( 'button.give-donation-level-btn[data-price-id="' + price_id + '"]' )
						.focus()
						.addClass( 'give-default-level' );
					break;

				// Auto select option.
				case (! ! $form.find( 'select.give-select-level' ).length) :
					$form.find( 'select.give-select-level option' )
						.prop( 'selected', false );
					$form.find( 'select.give-select-level option[data-price-id="' + price_id + '"]' )
						.prop( 'selected', true )
						.addClass( 'give-default-level' );
					break;

			}
		},

		/**
		 * Update level values
		 *
		 * Helper function: Sets the multi-select amount values
		 *
		 * @since 1.8.17
		 * @param {object} $level
		 * @returns {boolean}
		 */
		autoSetMultiLevel: function( $level ) {

			var $form = $level.parents( 'form' ),
				level_amount = $level.val(),
				level_price_id = $level.data( 'price-id' );

			// Check if price ID blank because of dropdown type
			if ( 'undefined' === typeof  level_price_id ) {
				level_price_id = $level.find( 'option:selected' ).data( 'price-id' );
			}

			// Is this a custom amount selection?
			if ( 'custom' === level_price_id ) {
				// It is, so focus on the custom amount input.
				$form.find( '.give-amount-top' ).val( '' ).focus();
				return false; // Bounce out
			}

			// Update custom amount field
			$form.find( '.give-amount-top' ).val( level_amount );
			$form.find( 'span.give-amount-top' ).text( level_amount );

			var decimal_separator = Give.form.fn.getInfo( 'decimal_separator', $form );

			// Cache previous amount and set data amount.
			jQuery( '.give-donation-amount .give-text-input', $form )
				.attr(
					'data-amount',
					Give.fn.unFormatCurrency(
						$form.find( '.give-final-total-amount' ).attr( 'data-total' ),
						decimal_separator
					)
				);

			// Manually trigger blur event with two params:
			// (a) form jquery object
			// (b) price id
			// (c) donation amount
			$form.find( '.give-donation-amount .give-text-input' )
				.trigger( 'blur', [ $form, level_amount, level_price_id ] );
		},

		/**
		 * Donor sent back to the form
		 *
		 * @since 1.8.17
		 * @access private
		 */
		__sendBackToForm: function() {

			let form_id = Give.fn.getParameterByName( 'form-id' ),
				payment_mode = Give.fn.getParameterByName( 'payment-mode' );

			// Sanity check - only proceed if query strings in place.
			if ( ! form_id || ! payment_mode ) {
				return false;
			}

			let $form_wrapper = jQuery( 'body' ).find( '#give-form-' + form_id + '-wrap' ),
				$form = $form_wrapper.find( 'form.give-form' ),
				display_modal = $form_wrapper.hasClass( 'give-display-modal' ),
				display_button = $form_wrapper.hasClass( 'give-display-button' ),
				display_reveal = $form_wrapper.hasClass( 'give-display-reveal' );

			// Update payment mode radio so it's correctly checked.
			$form.find( '#give-gateway-radio-list label' )
				.removeClass( 'give-gateway-option-selected' );
			$form.find( 'input[name=payment-mode][value=' + payment_mode + ']' )
				.prop( 'checked', true )
				.parent()
				.addClass( 'give-gateway-option-selected' );

			// Select the proper level for Multi-level forms.
			// It can either be a dropdown, buttons, or radio list. Default is buttons field type.
			let level_id = Give.fn.getParameterByName( 'level-id' ),
				level_field = $form.find( '*[data-price-id="' + level_id + '"]' );

			if ( level_field.length > 0 ) {
				this.autoSetMultiLevel( level_field );
			}

			let give_form_wrap = jQuery( '.give-form-wrap' ),
				is_form_grid   = give_form_wrap.hasClass( 'give-form-grid-wrap' );

			if ( is_form_grid && 1 === jQuery( '#give-modal-form-' + form_id ).length ) {
				jQuery.magnificPopup.open( {
					items: {
						type: 'inline',
						src: '#give-modal-form-' + form_id,
					},
					fixedContentPos: true,
					fixedBgPos: true,
					closeBtnInside: true,
					midClick: true,
					removalDelay: 300,
					mainClass: 'modal-fade-slide',
				});

				return;
			}

			// This form is modal display so show the modal.
			if ( display_modal || display_button ) {
				give_open_form_modal( $form_wrapper, $form );
			} else if ( display_reveal ) {
				// This is a reveal form, show it.
				$form.find( '.give-btn-reveal' ).hide();
				$form.find( '#give-payment-mode-select, #give_purchase_form_wrap' ).slideDown();
			}

		},

		/**
		 * Check if donation amount valid or not
		 * @since 1.8.17
		 *
		 * @param {object} $form
		 *
		 * @return {boolean}
		 */
		isValidDonationAmount: function( $form ) {

			// Return true, if custom amount is not enabled.
			if ( $form.find( 'input[name="give-form-minimum"]' ).length <= 0 ) {
				return true;
			}

			let min_amount = this.getMinimumAmount( $form ),
				max_amount = this.getMaximumAmount( $form ),
				amount = this.getAmount( $form ),
				price_id = this.getPriceID( $form, true );

			// Don't allow zero donation amounts.
			// https://github.com/WordImpress/Give/issues/3181
			if( 0 === amount ) {
				return false
			}

			return (
				((- 1 < amount) && amount >= min_amount && amount <= max_amount)
				|| (- 1 !== price_id)
			);
		},

		/**
		 * Initialize cache.
		 *
		 * @since 1.8.17
		 * @private
		 */
		__initialize_cache: function() {
			jQuery.each( jQuery( '.give-form' ), function( index, $item ) {
				$item = $item instanceof jQuery ? $item : jQuery( $item );

				Give.cache[ 'form_' + Give.form.fn.getInfo( 'form-id', $item ) ] = [];
			} );
		},

		field: {

			/**
			 * Format CC Fields
			 *
			 * Set variables and format cc fields.
			 *
			 * @since 1.2
			 *
			 * @param {object} $forms
			 */
			formatCreditCard: function( $forms ) {
				//Loop through forms on page and set CC validation
				$forms.each( function( index, form ) {
					form = jQuery( form );
					var card_number = form.find( '.card-number' ),
						card_cvc = form.find( '.card-cvc' ),
						card_expiry = form.find( '.card-expiry' );

					//Only validate if there is a card field
					if ( card_number.length ) {
						card_number.payment( 'formatCardNumber' );
						card_cvc.payment( 'formatCardCVC' );
						card_expiry.payment( 'formatCardExpiry' );
					}
				} );
			}
		}
	}
};
