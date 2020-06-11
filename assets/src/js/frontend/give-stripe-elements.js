/**
 * Give - Stripe Elements.
 *
 * @since 2.7.1
 */
class GiveStripeElements {
	/**
	 * Stripe Elements constructor.
	 *
	 * @param formElement
	 *
	 * @since 2.7.1
	 */
	constructor( formElement ) {
		// Don't load JS if `formElement` or `publishableKey` is not present.
		if ( ! formElement ) {
			return;
		}

		// Setup configuration.
		this.formElement = formElement;
		this.publishableKey = formElement.getAttribute( 'data-publishable-key' );
		this.accountId = formElement.getAttribute( 'data-account' ) ? formElement.getAttribute( 'data-account' ) : '';
		this.idPrefix = formElement.getAttribute( 'data-id' ) ? formElement.getAttribute( 'data-id' ) : '';
		this.locale = give_stripe_vars.preferred_locale;
		this.fieldsFormat = give_stripe_vars.cc_fields_format;
		this.isMounted = false;
	}

	/**
	 * Setup Stripe Element.
	 *
	 * @since 2.7.1
	 *
	 * @returns {*}
	 */
	setupStripeElement() {
		let args = {};

		if ( this.accountId.trim().length !== 0 ) {
			args = {
				stripeAccount: this.accountId,
			};
		}

		return Stripe( this.publishableKey, args );
	}

	/**
	 * Get Stripe Elements.
	 *
	 * @param stripeElement
	 *
	 * @since 2.7.1
	 *
	 * @returns {*}
	 */
	getElements( stripeElement ) {
		const args = {
			locale: this.locale,
		};

		return stripeElement.elements( args );
	}

	/**
	 * Create Card Element.
	 *
	 * @param stripeElement
	 *
	 * @since 2.7.1
	 *
	 * @returns {[]}
	 */
	createElement( stripeElement ) {
		const paymentElement = [];

		this.getElementsToMountOn().forEach( ( element ) => {
			paymentElement.push( stripeElement.create( element[ 0 ], {
				style: this.getElementStyles(),
				classes: this.getElementClasses(),
			} ) );
		} );

		return paymentElement;
	}

	/**
	 * Get Element Styles.
	 *
	 * @since 2.7.1
	 *
	 * @returns {{invalid: *, complete: *, base: *, empty: *}}
	 */
	getElementStyles() {
		const baseStyles = give_stripe_vars.element_base_styles;
		const completeStyles = give_stripe_vars.element_complete_styles;
		const emptyStyles = give_stripe_vars.element_empty_styles;
		const invalidStyles = give_stripe_vars.element_invalid_styles;

		return {
			base: baseStyles,
			complete: completeStyles,
			empty: emptyStyles,
			invalid: invalidStyles,
		};
	}

	/**
	 * Get Element Classes.
	 *
	 * @since 2.7.1
	 *
	 * @returns {{invalid: string, focus: string, empty: string}}
	 */
	getElementClasses() {
		return {
			focus: 'focus',
			empty: 'empty',
			invalid: 'invalid',
		};
	}

	/**
	 * Get Card Elements to Mount on.
	 *
	 * @since 2.7.1
	 *
	 * @returns {[string, string][]}
	 */
	getElementsToMountOn() {
		let elementsToMountOn = {
			cardNumber: `#give-card-number-field-${ this.idPrefix }`,
			cardCvc: `#give-card-cvc-field-${ this.idPrefix }`,
			cardExpiry: `#give-card-expiration-field-${ this.idPrefix }`,
		};

		if ( 'single' === this.fieldsFormat ) {
			elementsToMountOn = {
				card: `#give-stripe-single-cc-fields-${ this.idPrefix }`,
			};
		}

		return Object.entries( elementsToMountOn );
	}

	/**
	 * Mount Element.
	 *
	 * @param stripeElement
	 *
	 * @since 2.7.1
	 */
	mountElement( stripeElement ) {
		const mountOnElement = this.getElementsToMountOn();

		Array.from( stripeElement ).forEach( ( element, index ) => {
			element.mount( mountOnElement[ index ][ 1 ] );
		} );
	}

	/**
	 * UnMount Element.
	 *
	 * @param stripeElement
	 *
	 * @since 2.7.1
	 */
	unmountElement( stripeElement ) {
		const unMountOnElement = this.getElementsToMountOn();

		Array.from( stripeElement ).forEach( ( element, index ) => {
			element.unmount( unMountOnElement[ index ][ 1 ] );
		} );
	}
}

export { GiveStripeElements };
