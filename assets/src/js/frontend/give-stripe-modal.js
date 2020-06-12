import {GiveModal} from "../plugins/modal";

class GiveStripeModal extends GiveModal {
	constructor( obj ) {
		obj.type = 'alert';
		super( obj );

		if ( 'undefined' !== typeof ( obj.modalWrapper ) && '' !== obj.modalWrapper ) {
			this.config.classes.modalWrapper = obj.modalWrapper;
		}

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
					${ this.config.modalContent.price ? `<div class="give-modal__price">${ this.config.modalContent.price }</div>` : '' }
					${ this.config.modalContent.email ? `<div class="give-modal__email">${ this.config.modalContent.email }</div>` : '' }
					${ this.config.modalContent.formTitle ? `<div class="give-modal__form-title">${ this.config.modalContent.formTitle }</div>` : '' }
				</div>
				<div class="give-modal__body">
					<div id="give-card-number-wrap" class="form-row form-row-two-thirds form-row-responsive give-stripe-cc-field-wrap">
						<div>
							<label for="give-card-number-field-9-1" class="give-label">
								Card Number
								<span class="give-required-indicator">*</span>
								<span class="give-tooltip give-icon give-icon-question" data-tooltip="The (typically) 16 digits on the front of your credit card."></span>
								<span class="card-type"></span>
							</label>
							<div id="give-card-number-field-9-1" class="input empty give-stripe-cc-field give-stripe-card-number-field"></div>
						</div>
					</div>
				</div>

				<div class="give-modal__controls">
					<button class="give-button give-button--primary">
						${ this.config.modalContent.btnTitle ? this.config.modalContent.btnTitle : '' }
					</button>
				</div>

			</div>`;
		}

		return template;
	}
}

export { GiveStripeModal };
