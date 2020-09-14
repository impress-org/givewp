/* globals paypal, Give, givePayPalCommerce, Event */
import DonationForm from './DonationForm';
import PaymentMethod from './PaymentMethod';
import CustomCardFields from './CustomCardFields';

class AdvancedCardFields extends PaymentMethod {
	/**
	 * @since 2.9.0
	 *
	 * @param {CustomCardFields} customCardFields CustomCardFields class object.
	 */
	constructor( customCardFields ) {
		super( customCardFields.form );

		this.customCardFields = customCardFields;
		this.cardFields = {};
		this.hostedCardFieldsContainers = {};
		this.hostedFieldContainerStyleProperties = [
			'background-color',
			'box-sizing',
			'box-shadow',
			'border',
			'border-radius',
			'margin',
			'height',
		];

		this.hostedInputFieldStyleProperties = [
			'color',
			'direction',
			'font-size',
			'letter-spacing',
			'line-height',
			'padding',
		];

		this.hostedFocusedInputFieldStyleProperties = [ 'color', 'border' ];

		this.hostedInputFieldPlaceholderStyleProperties = [ 'color' ];

		this.styles = {
			container: {},
			input: {},
			'input:focus': {},
			'input:placeholder': {},
		};

		this.setFocusStyle();
	}

	/**
	 * Return whether or not render credit card fields.
	 *
	 * @since 2.9.0
	 *
	 * @return {boolean} Return boolean value whether we can render card fields or not.
	 */
	static canShow() {
		return paypal.HostedFields.isEligible() === true && !! window.givePayPalCommerce.supportsCustomPayments;
	}

	/**
	 * Render payment method.
	 *
	 * @since 2.9.0
	 */
	async renderPaymentMethodOption() {
		this.setupContainerForHostedCardFields();
		this.applyStyleToContainer();

		const createOrder = this.createOrderHandler.bind( this );
		const styles = await this.getComputedInputFieldForHostedField();
		const fields = this.getPayPalHostedCardFields();
		const hostedCardFields = await paypal.HostedFields.render( { createOrder, styles, fields } );
		const onSubmitHandlerForDonationForm = this.onSubmitHandlerForDonationForm.bind( this );

		this.addEventToHostedFields( hostedCardFields );
		this.jQueryForm.on( 'submit', { hostedCardFields }, onSubmitHandlerForDonationForm );

		if ( this.customCardFields.recurringChoiceHiddenField ) {
			DonationForm.trackRecurringHiddenFieldChange( this.customCardFields.recurringChoiceHiddenField, this.toggleFields.bind( this ) );
			DonationForm.trackRecurringHiddenFieldChange( this.customCardFields.recurringChoiceHiddenField, () => {
				DonationForm.toggleDonateNowButton( this.form );
			} );
		}
	}

	/**
	 * Apply style when hosted card field container rendered.
	 *
	 * @since 2.9.0
	 */
	applyStyleToContainer() {
		this.setStyles();
		this.addInitialStyleToHostedFieldsContainer();
		this.setHostedFieldContainerHeight();

		window.addEventListener( 'load', this.setHostedFieldContainerHeight.bind( this ) );
	}

	/**
	 * Set container for histed card fields.
	 *
	 * @since 2.9.0
	 */
	setupContainerForHostedCardFields() {
		const cardFields = this.customCardFields.cardFields;
		let fieldType = '';

		for ( const cardFieldsKey in cardFields ) {
			const container = document.createElement( 'div' );

			fieldType = cardFields[ cardFieldsKey ].el.getAttribute( 'name' );
			container.setAttribute( 'id', `give-${ cardFields[ cardFieldsKey ].el.getAttribute( 'id' ) }` );
			container.setAttribute( 'class', 'give-paypal-commerce-cc-field' );

			this.hostedCardFieldsContainers[ this.getFieldTypeByFieldName( fieldType ) ] = cardFields[ cardFieldsKey ].el.parentElement.appendChild( container );
		}

		this.toggleFields();
	}

	/**
	 * Create order event handler for smart buttons.
	 *
	 * @since 2.9.0
	 *
	 * @param {object} data PayPal button data.
	 * @param {object} actions PayPal button actions.
	 *
	 * @return {Promise<unknown>} Return PayPal order id.
	 */
	async createOrderHandler( data, actions ) { // eslint-disable-line
		// eslint-disable-next-line
		const response = await fetch( `${ Give.fn.getGlobalVar( 'ajaxurl' ) }?action=give_paypal_commerce_create_order`, {
			method: 'POST',
			body: DonationForm.getFormDataWithoutGiveActionField( this.form ),
		} );

		const responseJson = await response.json();

		return responseJson.data.id;
	}

	/**
	 * Get fields.
	 *
	 * @since 2.9.0
	 * @return {object} Return object of card input field container details.
	 */
	getPayPalHostedCardFields() {
		return {
			number: {
				selector: `#${ this.hostedCardFieldsContainers.number.getAttribute( 'id' ) }`,
				placeholder: givePayPalCommerce.cardFieldPlaceholders.cardNumber,
			},
			cvv: {
				selector: `#${ this.hostedCardFieldsContainers.cvv.getAttribute( 'id' ) }`,
				placeholder: givePayPalCommerce.cardFieldPlaceholders.cardCvc,
			},
			expirationDate: {
				selector: `#${ this.hostedCardFieldsContainers.expirationDate.getAttribute( 'id' ) }`,
				placeholder: givePayPalCommerce.cardFieldPlaceholders.expirationDate,
			},
		};
	}

	/**
	 * Approve PayPal payment after successfully payment.
	 *
	 * @since 2.9.0
	 *
	 * @param {string} orderId Order id.
	 *
	 * @return {Promise<any>} Return request response.
	 */
	async approvePayment( orderId ) {
		// eslint-disable-next-line
		const response = await fetch( `${ this.ajaxurl }?action=give_paypal_commerce_approve_order&order=` + orderId, {
			method: 'POST',
			body: DonationForm.getFormDataWithoutGiveActionField( this.form ),
		} );

		return await response.json();
	}

	/**
	 * Get computed style for hosted card fields.
	 *
	 * List of style properties support by PayPal for advanced card fields: https://developer.paypal.com/docs/business/checkout/reference/style-guide/#style-the-card-payments-fields
	 *
	 * @since 2.9.0
	 *
	 * @return {object} Return object of style properties.
	 */
	getComputedInputFieldForHostedField() {
		const input = {
			...this.styles.input,
			...givePayPalCommerce.hostedCardFieldStyles.input,
		};

		return {
			input,
			':focus': {
				color: this.styles[ 'input:focus' ].color,
				...givePayPalCommerce.hostedCardFieldStyles[ ':focus' ],
			},
			':placeholder': {
				color: this.styles[ 'input:placeholder' ].color,
				...givePayPalCommerce.hostedCardFieldStyles[ ':placeholder' ],
			},
		};
	}

	/**
	 * Handle donation form submit event.
	 *
	 * @since 2.9.0
	 *
	 * @param {object} event jQuery event object.
	 *
	 * @return {boolean} Return boolean false value to stop form submission.
	 */
	async onSubmitHandlerForDonationForm( event ) {
		if ( ! DonationForm.isPayPalCommerceSelected( this.jQueryForm ) ) {
			return true;
		}

		// If donor opted in for recurring donation then submit donation form because PayPal advanced card fields does not support subscription
		// So, we'll create subscription on server with PayPal Subscription API.
		if ( DonationForm.isRecurringDonation( this.form ) ) {
			this.submitDonationForm();
			return;
		}

		const hostedFieldOnSubmitErrorHandler = this.hostedFieldOnSubmitErrorHandler.bind( this );

		event.preventDefault();
		Give.form.fn.removeErrors( this.jQueryForm );

		const { hostedCardFields } = event.data;
		const getExtraCardDetails = this.getExtraCardDetails.bind( this );

		const payload = await hostedCardFields.submit(
			{
			// Trigger 3D Secure authentication
				contingencies: [ '3D_SECURE' ],
				...getExtraCardDetails,
			}
		).catch( hostedFieldOnSubmitErrorHandler );

		if ( ! payload ) {
			return false;
		}

		if ( this.canThreeDsAuthorizeCard( payload ) && ! this.IsCardThreeDsAuthorized( payload ) ) {
			// Handle no 3D Secure contingency passed scenario
			Give.form.fn.addErrorsAndResetDonationButton(
				this.jQueryForm,
				Give.form.fn.getErrorHTML( [ {
					message: givePayPalCommerce.threeDsCardAuthenticationFailedNotice,
				} ] )
			);

			return false;
		}

		// Approve payment on if we did not get any error.
		await this.onApproveHandler( payload );

		return false;
	}

	/**
	 * Handle PayPal payment on approve event.
	 *
	 * @since 2.9.0
	 *
	 * @param {object} payload PayPal response object after payment completion.
	 */
	async onApproveHandler( payload ) {
		Give.form.fn.showProcessingState();

		const result = await this.approvePayment( payload.orderId );

		if ( ! result.success ) {
			Give.form.fn.hideProcessingState();

			if ( null === result.data.error ) {
				Give.form.fn.addErrorsAndResetDonationButton(
					this.jQueryForm,
					Give.form.fn.getErrorHTML( [ { message: givePayPalCommerce.defaultDonationCreationError } ] )
				);

				return;
			}

			const errorDetail = result.data.error.details[ 0 ];
			Give.form.fn.addErrorsAndResetDonationButton(
				this.jQueryForm,
				Give.form.fn.getErrorHTML( [ { message: errorDetail.description } ] )
			);

			return;
		}

		await DonationForm.attachOrderIdToForm( this.form, result.data.order.id );

		this.submitDonationForm();
	}

	/**
	 * Get extra card detail form like card name etc.
	 *
	 * In future we can add billing field: https://developer.paypal.com/docs/business/checkout/advanced-card-payments/
	 *
	 * @since 2.9.0
	 *
	 * @return {{cardholderName: *}} Card details object.
	 */
	getExtraCardDetails() {
		return {
			cardholderName: this.form.getElementById( '#card_name' ).value,
		};
	}

	/**
	 * Add style to hosted field's container.
	 *
	 * @since 2.9.0
	 */
	addInitialStyleToHostedFieldsContainer() {
		// Apply styles
		for ( const fieldKey in this.hostedCardFieldsContainers ) {
			this.hostedFieldContainerStyleProperties.forEach( property => {
				if ( 'height' === property && [ 'auto', '0px' ].includes( this.styles.container[ property ] ) ) {
					return;
				}
				this.hostedCardFieldsContainers[ fieldKey ].style.setProperty( property, this.styles.container[ property ] );
			} );
		}
	}

	/**
	 * Add event to hosted card fields.
	 *
	 * @since 2.9.0
	 *
	 * @param {object} hostedCardFields Hosted card field object
	 */
	addEventToHostedFields( hostedCardFields ) {
		const self = this;

		hostedCardFields.on( 'focus', function( event ) {
			self.hostedCardFieldsContainers[ event.emittedBy ].style.border = self.styles[ 'input:focus' ].border;
		} );

		hostedCardFields.on( 'blur', function( event ) {
			self.hostedCardFieldsContainers[ event.emittedBy ].style.border = self.styles.container.border;
		} );
	}

	/**
	 * Set style properties for hosted card field and its container.
	 *
	 * @since 2.9.0
	 */
	setStyles() {
		this.computedStyles();

		const event = new Event( 'blur' );
		const cardField = this.form.querySelector( 'input[name="card_name"]' );
		cardField.dispatchEvent( event );
	}

	/**
	 * Computed styles for hosted card field container and iframe input field.
	 *
	 * @since 2.9.0
	 */
	computedStyles() {
		const cardField = this.form.querySelector( 'input[name="card_name"]' );
		const computedStyle = window.getComputedStyle( cardField, null );

		if ( ! Array.from( this.styles.container ).length ) {
			this.hostedFieldContainerStyleProperties.forEach( property => {
				this.styles.container = {
					[ property ]: computedStyle.getPropertyValue( property ),
					...	this.styles.container,
				};
			} );

			this.hostedInputFieldStyleProperties.forEach( property => {
				this.styles.input = {
					[ property ]: computedStyle.getPropertyValue( property ),
					...	this.styles.input,
				};
			} );

			this.hostedInputFieldPlaceholderStyleProperties.forEach( property => {
				this.styles[ 'input:placeholder' ] = {
					[ property ]: computedStyle.getPropertyValue( property ),
					...	this.styles[ 'input:placeholder' ],
				};
			} );
		}
	}

	/**
	 * Set style properties for hosted card field and its container for focus state
	 *
	 * @since 2.9.0
	 */
	setFocusStyle() {
		const sources = this.form.querySelectorAll( 'input[type="text"]' );
		sources.forEach( source => {
			// Get style properties for focused input field.
			source.addEventListener( 'focus', event => {
				if ( Array.from( this.styles[ 'input:focus' ] ).length ) {
					return;
				}

				const computedStyle = window.getComputedStyle( event.target, null );

				this.hostedFocusedInputFieldStyleProperties.forEach( property => {
					this.styles[ 'input:focus' ] = {
						[ property ]: computedStyle.getPropertyValue( property ),
						...	this.styles[ 'input:focus' ],
					};
				} );
			}, { once: true } );
		} );
	}

	/**
	 * Set hosted field's container height.
	 *
	 * @since 2.9.0
	 */
	setHostedFieldContainerHeight() {
		this.styles.container.height = `${ this.form.querySelector( 'input[name="card_name"]' ).offsetHeight }px`;

		if ( [ 'auto', '0px' ].includes( this.styles.container.height ) ) {
			return;
		}

		// Apply styles
		for ( const fieldKey in this.hostedCardFieldsContainers ) {
			this.hostedCardFieldsContainers[ fieldKey ]
				.style
				.setProperty( 'height', this.styles.container.height );
		}
	}

	/**
	 * Return whether or not 3ds authorize card Can authorize card.
	 *
	 * @since 2.9.0
	 *
	 * @param {object} payload Hosted field response
	 * @return {boolean} true if card can be authorize with 3ds or vice versa
	 */
	canThreeDsAuthorizeCard( payload ) {
		return [ 'NO', 'POSSIBLE' ].includes( payload.liabilityShift );
	}

	/**
	 * Return whether or not card 3ds authorized to process payment.
	 *
	 * @since 2.9.0
	 * @param {object} payload Hosted field response
	 * @return {boolean} true if card is 3ds authorized or vice versa
	 */
	IsCardThreeDsAuthorized( payload ) {
		return payload.liabilityShifted && 'POSSIBLE' === payload.liabilityShift;
	}

	/**
	 * Handle hosted fields on submit errors.
	 *
	 * @since 2.9.0
	 *
	 * @param {object} error Collection of hosted field on submit error
	 */
	hostedFieldOnSubmitErrorHandler( error ) {
		const errorStringByGroup = {};
		const errors = [];

		error.details.forEach( detail => {
			// If details is not about card field then insert notice into errors object.
			if ( ! detail.hasOwnProperty( 'field' ) ) {
				errors.push( {
					message: detail.description,
				} );

				return;
			}

			if ( ! errorStringByGroup.hasOwnProperty( `${ detail.field }` ) ) {
				// setup error label.
				let label = '';

				if ( -1 !== detail.field.indexOf( 'expiry' ) ) {
					label = givePayPalCommerce.paypalCardInfoErrorPrefixes.expirationDateField;
				} else if ( -1 !== detail.field.indexOf( 'number' ) ) {
					label = givePayPalCommerce.paypalCardInfoErrorPrefixes.cardNumberField;
				} else if ( -1 !== detail.field.indexOf( 'security_code' ) ) {
					label = givePayPalCommerce.paypalCardInfoErrorPrefixes.cardCvcField;
				} else {
					// Handle server errors.
					if ( detail.hasOwnProperty( 'description' ) ) {
						errors.push( {
							message: detail.description,
						} );

						return;
					}

					errors.push( {
						message: `${ givePayPalCommerce.failedPaymentProcessingNotice } ${ givePayPalCommerce.errorCodeLabel }: ${ detail.issue }`,
					} );
					return;
				}

				if ( label ) {
					errorStringByGroup[ `${ detail.field }` ] = [ `<strong>${ label }</strong>` ];
				} else {
					errorStringByGroup[ `${ detail.field }` ] = [];
				}
			}

			errorStringByGroup[ `${ detail.field }` ].push( `${ detail.description }.` );
		} );

		for ( const field in errorStringByGroup ) {
			errors.push( {
				message: errorStringByGroup[ field ].join( ' ' ),
			} );
		}

		Give.form.fn.addErrorsAndResetDonationButton(
			this.jQueryForm,
			Give.form.fn.getErrorHTML( errors )
		);
	}

	/**
	 * Submit donation form.
	 *
	 * @since 2.9.0
	 */
	submitDonationForm() {
		this.jQueryForm.off( 'submit' );
		this.jQueryForm.submit();
	}

	/**
	 * Toggle fields.
	 *
	 * @since 2.9.0
	 */
	toggleFields() {
		const display = DonationForm.isRecurringDonation( this.form ) ? 'none' : 'block';
		const canHideParentContainer = 'none' === display && ! CustomCardFields.canShow( this.form );

		this.toggleCardNameField( canHideParentContainer );

		for ( const key in this.hostedCardFieldsContainers ) {
			this.hostedCardFieldsContainers[ key ].style.display = display;

			// Hide parent container only if custom card fields is not available to process subscriptions.
			this.hostedCardFieldsContainers[ key ].parentElement.style.display = canHideParentContainer ? 'none' : 'block';
		}

		// Hide separator only if custom card is not available for subscription.
		this.customCardFields.separator.style.display = canHideParentContainer ? 'none' : 'flex';
	}

	/**
	 * Handle card name field display logic.
	 *
	 * @since 2.9.0
	 *
	 * @param {boolean} hide Flag to decide shor/hide card name field.
	 */
	toggleCardNameField( hide ) {
		const cardField = this.form.querySelector( 'input[name="card_name"]' );

		cardField.parentElement.style.display = hide ? 'none' : 'block';
		cardField.disabled = hide;
	}

	/**
	 * Get field type by field name.
	 *
	 * 2since 2.9.0
	 *
	 * @param {string} fieldName field name. Support only "card_number", "card_cvc", and "card_expiry"
	 *
	 * @return {string} field type.
	 */
	getFieldTypeByFieldName( fieldName ) {
		if ( 'card_number' === fieldName ) {
			return 'number';
		}

		if ( 'card_cvc' === fieldName ) {
			return 'cvv';
		}

		if ( 'card_expiry' === fieldName ) {
			return 'expirationDate';
		}
	}
}

export default AdvancedCardFields;
