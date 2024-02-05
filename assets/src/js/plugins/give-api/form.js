/* globals Give, jQuery */
import Util from './util';

export default {
	init: function() {
		this.fn.field.formatCreditCard( jQuery( 'form.give-form' ) );
		this.fn.__initialize_cache();

		// Run code on after window load.
		// If the window has already loaded then call directly.
		if( window.Give.WINDOW_IS_LOADED ) {
			Give.form.fn.__sendBackToForm();
		} else {
			window.addEventListener('load', function () {
				Give.form.fn.__sendBackToForm();
			});
		}
	},

	fn: {

		/**
		 * Check if donation form exist on page or not
		 *
		 * @since 2.2.2
		 *
		 * @return {boolean}
		 */
		isFormExist: function() {
			return !! document.getElementsByName( 'give-form-hash' ).length;
		},

		/**
		 * Return whether or not container has a donation form.
		 *
		 * @since 2.9.0
		 * @param {Element} $container Form container.
		 *
		 * @return {boolean} Boolean value.
		 */
		hasDonationForm: function( $container ) {
			const actionHiddenField = $container.querySelector( 'form input[name="give_action"]' );
			return actionHiddenField && 'purchase' === actionHiddenField.value;
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
		 * Show processing state template.
		 *
		 * @since 2.8.0
		 * @since {string} html Message html string or plain text.
		 */
		showProcessingState: function( html ) {
			Util.fn.showOverlay( html );
		},

		/**
		 * Hide processing state template.
		 *
		 * @since 2.8.0
		 */
		hideProcessingState: function( ) {
			Util.fn.hideOverlay();
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
			let data = '';

			// Check if form is provided and wrap in jQuery in case its a node
			$form = 'undefined' !== typeof $form ? jQuery( $form ) : {};

			// Bailout.
			if ( ! str.length || ! $form.length ) {
				return data;
			}

			switch ( str ) {
				case 'gateways':
					data = [];
					jQuery.each( $form.find( 'input[name="payment-mode"]' ), function( index, gateway ) {
						gateway = ! ( gateway instanceof jQuery ) ? jQuery( gateway ) : gateway;
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
			let gateway = '';

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
			let variable_prices = [],
				formLevels;

			// check if correct form type is multi or not.
			if (
				! $form.length ||
				! $form.hasClass( 'give-form-type-multi' ) ||
				! ( formLevels = $form.find( '.give-donation-levels-wrap [data-price-id] ' ) )
			) {
				return variable_prices;
			}

			jQuery.each( formLevels, function( index, item ) {
				// Get Jquery instance for item.
				item = ! ( item instanceof jQuery ) ? jQuery( item ) : item;

				const decimal_separator = Give.form.fn.getInfo( 'decimal_separator', $form );

				// Add price id and amount to collector.
				variable_prices.push( {
					price_id: item.data( 'price-id' ),
					amount: Give.fn.unFormatCurrency( item.val(), decimal_separator ),
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
			const variable_prices = this.getVariablePrices( $form ),
				current_amount = Give.fn.unFormatCurrency(
					$form.find( 'input[name="give-amount"]' ).val(),
					this.getInfo( 'decimal_separator', $form )
				);

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
			let	price_id = ! ! Give.fn.getCache( 'amount_' + current_amount, $form ) ? Give.fn.getCache( 'amount_' + current_amount, $form ) : -1;

			// Flag to decide on which param we want to find price_id
			is_amount = 'undefined' === typeof is_amount ? true : is_amount;

			// Find price id with amount in variable prices.
			if ( variable_prices.length ) {
				// Get recent selected price id for same amount.
				if ( -1 === price_id ) {
					if ( is_amount ) {
						// Find amount in donation levels.
						jQuery.each( variable_prices, function( index, variable_price ) {
							if ( variable_price.amount === current_amount ) {
								price_id = variable_price.price_id;

								return false;
							}
						} );

						// Set level to custom.
						if ( -1 === price_id && ( this.getMinimumAmount( $form ) <= current_amount && ( this.getMaximumAmount( $form ) >= current_amount ) && this.getMinimumAmount( $form ) <= current_amount ) ) {
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

			let amount = $form.find( 'input[name="give-amount"]' ).val();

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
		 * Get form's nonce information
		 *
		 * @since 2.3.1
		 *
		 * @param {object} $form
		 *
		 * @return {object}
		 */
		getNonceInfo: function( $form ) {
			let nonce = {},
				$nonceField;

			// Bailout
			if ( ! $form.length ) {
				return nonce;
			}

			nonce.el = $form.find( 'input[name="give-form-hash"]' );

			if ( ! nonce.el.length ) {
				return nonce;
			}

			nonce.value = $form.find( 'input[name="give-form-hash"]' ).val();
			nonce.value = 'undefined' === typeof nonce.value || ! nonce.value ? '' : nonce.value;

			nonce.createdInDonorSession = '1' === nonce.el.attr( 'data-donor-session' );

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
			jQuery.post( Give.fn.getGlobalVar( 'ajaxurl' ), {
				action: 'give_donation_form_nonce',
				give_form_id: Give.form.fn.getInfo( 'form-id', $form ),
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
		 *
		 * @returns {object}
		 */
		resetAllNonce: function( $form ) {
			// Return false, if form is missing.
			if ( ! $form.length ) {
				return false;
			}

			Give.form.fn.disable( $form, true );

			return new Promise( ( resolve, reject ) => {
				//Post via AJAX to Give
				jQuery.post(
					Give.fn.getGlobalVar( 'ajaxurl' ),
					{
						action: 'give_donation_form_reset_all_nonce',
						give_form_id: Give.form.fn.getInfo( 'form-id', $form ),
					},
					function( response ) {
						// Process only if get response successfully.
						if ( ! response.success ) {
							return reject( response );
						}

						const createUserNonceField = $form.find( 'input[name="give-form-user-register-hash"]' );

						// Update nonce field.
						Give.form.fn.setInfo( 'nonce', response.data.give_form_hash, $form, '' );

						// Update create user nonce field.
						if ( createUserNonceField.length ) {
							createUserNonceField.val( response.data.give_form_user_register_hash );
						}

						Give.form.fn.disable( $form, false );

						/**
						 * Fire custom event handler when update all nonce of donation form
						 *
						 * @since  2.2.0
						 * @access access
						 */
						jQuery( document ).trigger( 'give_reset_all_nonce', [ response.data ] );

						return resolve( response );
					}
				).done( function() {
					Give.form.fn.disable( $form, false );
				} );
			} );
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

			price_id = ( 'undefined' === typeof price_id ) ? this.getPriceID( $form, false ) : price_id;

			switch ( true ) {
				// Auto select radio button.
				case ( ! ! $form.find( '.give-radio-input' ).length ) :
					$form.find( '.give-radio-input' )
						.prop( 'checked', false );
					$form.find( '.give-radio-input[data-price-id="' + price_id + '"]' )
						.prop( 'checked', true )
						.addClass( 'give-default-level' );
					break;

				// Set focus to price id button.
				case ( ! ! $form.find( 'button.give-donation-level-btn' ).length ) :
					$form.find( 'button.give-donation-level-btn' )
						.blur();
					$form.find( 'button.give-donation-level-btn[data-price-id="' + price_id + '"]' )
						.addClass( 'give-default-level' );
					break;

				// Auto select option.
				case ( ! ! $form.find( 'select.give-select-level' ).length ) :
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
			let $form = $level.parents( 'form' ),
				level_amount = $level.val(),
				level_price_id = $level.data( 'price-id' );

			// Check if price ID blank because of dropdown type
			if ( 'undefined' === typeof level_price_id ) {
				level_price_id = $level.find( 'option:selected' ).data( 'price-id' );
			}

			// Is this a custom amount selection?
			if ( 'custom' === level_price_id ) {
				level_amount = Give.fn.getParameterByName( 'custom-amount' );
			}

			// Update custom amount field
			$form.find( '.give-amount-top' ).val( level_amount );
			$form.find( 'span.give-amount-top' ).text( level_amount );

			const decimal_separator = Give.form.fn.getInfo( 'decimal_separator', $form );

			// Cache previous amount and set data amount.
			jQuery( '.give-donation-amount .give-text-input', $form )
				.attr(
					'data-amount',
					Give.fn.unFormatCurrency(
						$form.find( '.give-final-total-amount' ).attr( 'data-total' ),
						decimal_separator
					)
				);

			if( 'custom' === level_price_id && ! level_amount ) {
				// If level amount is empty for custom field that means donor clicked on button.
				// Set focus on amount field and allow donor to add custom donation amount.
				$form.find( '.give-donation-amount .give-text-input' ).focus();
			} else{
				// Manually trigger blur event with two params:
				// (a) form jquery object
				// (b) price id
				// (c) donation amount
				// Note: "custom" donation level id has donation amount only if donor redirect back to donation form with error.
				// Dummy url: http://give.test/donations/help-feed-america/?form-id=16&payment-mode=manual&level-id=custom&custom-amount=555,00
				$form.find( '.give-donation-amount .give-text-input' )
					.trigger( 'blur', [ $form, level_amount, level_price_id ] );
			}
		},

		/**
		 * Donor sent back to the form
		 *
		 * @since 1.8.17
		 * @access private
		 */
		__sendBackToForm: function() {
			const form_id = Give.fn.getParameterByName( 'form-id' ),
				payment_mode = Give.fn.getParameterByName( 'payment-mode' );

			// Sanity check - only proceed if query strings in place.
			if ( ! form_id || ! payment_mode ) {
				return false;
			}

			const $form_wrapper = jQuery( 'body' ).find( '#give-form-' + form_id + '-wrap' ),
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
			const level_id = Give.fn.getParameterByName( 'level-id' ),
				level_field = $form.find( '*[data-price-id="' + level_id + '"]' );

			if ( level_field.length > 0 ) {
				this.autoSetMultiLevel( level_field );
			}

			const give_form_wrap = jQuery( '.give-form-wrap' ),
				is_form_grid = give_form_wrap.hasClass( 'give-form-grid-wrap' );

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
				} );

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

			const min_amount = this.getMinimumAmount( $form ),
				max_amount = this.getMaximumAmount( $form ),
				amount = this.getAmount( $form ),
				price_id = this.getPriceID( $form, true );

			// Don't allow zero donation amounts.
			// https://github.com/impress-org/give/issues/3181
			if ( 0 === amount ) {
				return false;
			}

			return (
				( ( -1 < amount ) && amount >= min_amount && amount <= max_amount ) ||
				( -1 !== price_id )
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

		/**
		 * Check donation form pass HTML5 validation.
		 *
		 * @since 2.8.0
		 *
		 * @param {object} $form
		 * @param {boolean} reportValidity Set to true if want to show HTML5 error notices on form field.
		 * @return {boolean}
		 */
		isDonationFormHtml5Valid: function( $form, reportValidity = false ) {
			if ( typeof $form.checkValidity === 'function' && $form.checkValidity() === false ) {
				//Check for Safari (doesn't support HTML5 required)
				if ( ( navigator.userAgent.indexOf( 'Safari' ) != -1 && navigator.userAgent.indexOf( 'Chrome' ) == -1 ) === false ) {
					if ( reportValidity ) {
						$form.reportValidity();
					}

					//Not safari: Support HTML5 "required" so skip the rest of this function
					return false;
				}
			}

			return true;
		},

		/**
		 * Check donation form pass HTML5 validation.
		 *
		 * @since 2.8.0
		 *
		 * @param {object} $form
		 * @param {FormData} formData
		 * @return {string}
		 */
		isDonorFilledValidData: async function( $form, formData = {} ) {
			formData = formData instanceof FormData ? formData : new FormData( $form );

			formData.append( 'action', 'give_process_donation' );
			formData.append( 'give_ajax', true );

			const response = await fetch( `${ Give.fn.getGlobalVar( 'ajaxurl' ) }`, {
				method: 'POST',
				body: formData,
			} );
			const result = await response.text();

			return result.trim();
		},

		/**
		 * Add error notices to donation form.
		 * Note: this function will add error before "Donate Now" button.
		 *
		 * @since 2.8.0
		 *
		 * @param {object} $form Jquery Form object
		 * @param {string} errors Error list HTML.
		 */
		addErrors: function( $form, errors ) {
			$form.find( '#give-purchase-button' ).before( errors );
		},

		/**
		 * Remove error notices to donation form.
		 *
		 * @since 2.8.0
		 *
		 * @param {object} $form Jquery Form object
		 */
		removeErrors: function( $form ) {
			$form.find( '.give_errors' ).remove();
		},

		/**
		 * Get error HTML.
		 *
		 * @since 2.8.0
		 *
		 * @param {array} errors List of Error messages.
		 *
		 * @return {Element} Error HTML object.
		 */
		getErrorHTML: function( errors ) {
			const $errorContainer = document.createElement( 'div' );

			$errorContainer.classList.add( 'give_errors' );

			errors.forEach( error => {
				const $error = document.createElement( 'p' );
				$error.classList.add( 'give_error' );

				$error.innerHTML = error.message;

				$errorContainer.append( $error );
			} );

			return $errorContainer;
		},

		/**
		 * Add errors to donation form and reset "Donate Now" button state.
		 *
		 * @since 2.8.0
		 *
		 * @param {object} $form Javascript form selector.
		 * @param {*} $errors Errors list.
		 */
		addErrorsAndResetDonationButton: function( $form, $errors = null ) {
			this.resetDonationButton( $form );
            $errors && this.addErrors( $form, $errors );
        },

		/**
		 * Reset "Donate Now" button state.
		 *
		 * @since 2.8.0
		 *
		 * @param {object} $form Javascript form selector.
		 */
		resetDonationButton: function( $form ) {
			const $submitButton = $form.find( '#give-purchase-button' );
			const $container = $submitButton.closest( 'div' );

			//There was an error / remove old errors and prepend new ones
			$submitButton.val( $submitButton.data( 'before-validation-label' ) );
			$container.find( '.give-loading-animation' ).fadeOut();
			$form.find( '.give_errors' ).remove();

			// Enable the form donation button.
			Give.form.fn.disable( $form, false );
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
					const card_number = form.find( '.card-number' ),
						card_cvc = form.find( '.card-cvc' ),
						card_expiry = form.find( '.card-expiry' );

					//Only validate if there is a card field
					if ( card_number.length ) {
						card_number.payment( 'formatCardNumber' );
						card_cvc.payment( 'formatCardCVC' );
						card_expiry.payment( 'formatCardExpiry' );
					}
				} );
			},
		},
	},
};
