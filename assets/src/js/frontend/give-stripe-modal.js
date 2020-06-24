import { GiveModal } from '../plugins/modal';
import { GiveStripeElements } from './give-stripe-elements';

class GiveStripeModal extends GiveModal {
	constructor( obj ) {
		obj.type = 'alert';
		super( obj );

		if ( 'undefined' !== typeof ( obj.modalWrapper ) && '' !== obj.modalWrapper ) {
			this.config.classes.modalWrapper = obj.modalWrapper;
		}

		this.config.closeBtnInside = true;
		this.config.showCloseBtn = true;
		this.config.mainClass = 'give-modal give-stripe-modal';

		this.init();
	}

	/**
	 * Get template
	 *
	 * @since 2.1.0
	 *
	 * @return {string} Template HTML.
	 */
	getTemplate() {
		let template = '<div class="give-hidden"></div>';

		if ( this.config.type.length ) {
			template = `<div class="give-modal give-modal--zoom ${ this.config.classes.modalWrapper ? `${ this.config.classes.modalWrapper }`.trim() : '' }">

				<div class="give-modal__header">
					${ this.config.modalContent.title ? `<h2 class="give-modal__title">${ this.config.modalContent.title }</h2>` : '' }
					${ this.config.modalContent.formTitle ? `<div class="give-modal__form-title">${ this.config.modalContent.formTitle }</div>` : '' }
					${ this.config.modalContent.price ? `<div class="give-modal__price">${ this.config.modalContent.price }</div>` : '' }
					${ this.config.modalContent.email ? `<div class="give-modal__email">${ this.config.modalContent.email }</div>` : '' }

				</div>
				<div class="give-modal__body">

				</div>

				<div class="give-modal__footer">
					<input
						type="submit"
						class="give-submit give-btn give-stripe-checkout-donate-btn"
						id="give-stripe-checkout-donate-btn-${ this.config.modalContent.idPrefix }"
						value="${ this.config.modalContent.btnTitle ? this.config.modalContent.btnTitle : '' }"
					/>
				</div>

			</div>`;
		}

		return template;
	}

	triggerAjax() {
		const verifyPayment = new XMLHttpRequest();
		const formElement = this.config.modalContent.formElement;
		const formData = new FormData();

		formData.append( 'action', 'load_checkout_fields' );
		formData.append( 'idPrefix', this.config.modalContent.idPrefix );

		// Do something on Ajax on state change.
		verifyPayment.onreadystatechange = function( evt ) {
			if (
				4 === this.readyState &&
				200 === this.status &&
				'success' !== this.responseText
			) {
				evt.preventDefault();
				const response = JSON.parse( this.response );

				document.querySelector( '.give-modal--stripe-checkout' ).querySelector( '.give-modal__body' ).innerHTML = response.data.html;

				const formGateway = formElement.querySelector( 'input[name="give-gateway"]' );
				const gateways = Array.from( formElement.querySelectorAll( '.give-gateway' ) );

				const stripeElements = new GiveStripeElements( formElement );
				const cardElements = stripeElements.createElement( stripeElements.getElements( stripeElements.setupStripeElement() ) );

				if ( formGateway && 'stripe_checkout' === formGateway.value ) {
					stripeElements.mountElement( cardElements );
				}

				const checkoutDonateBtn = document.querySelector( '.give-stripe-checkout-donate-btn' );

				checkoutDonateBtn.addEventListener( 'click', ( e ) => {
					console.log( 'modal donate clicked' );
				} );
			} else {
				console.log( evt );
			}
		};
		verifyPayment.open( 'POST', give_global_vars.ajaxurl, false );
		verifyPayment.send( formData );
	}

	render() {
		const config = this.config;
		const myself = this;

		jQuery.magnificPopup.close();
		setTimeout( function( ) {
			jQuery.magnificPopup.open( config );
		}, 100 );

		setTimeout( function( ) {
			myself.triggerAjax();
		}, 100 );

		return this;
	}
}

export { GiveStripeModal };
