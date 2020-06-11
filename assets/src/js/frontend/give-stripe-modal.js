class GiveStripeModal extends GiveModal {
	constructor() {
		obj.type = 'stripe';
		super( obj );

		if ( 'undefined' !== typeof ( obj.modalWrapper ) && '' !== obj.modalWrapper ) {
			this.config.classes.modalWrapper = obj.modalWrapper;
		}

		this.init();
	}
}

export { GiveStripeModal };
