class GiveButton {
	constructor( element ) {
		this.root = element;
		this.buttonText = this.root.textContent.trim();
		this.iconPosition = 'before';

		// Note: this property is for internal use. It can be change in future.
		this._buttonTextWithWrapper = this.root.querySelector( '.give-button-text' ) ?
			`<span class="give-button-text">${ this.buttonText }</span>` :
			this.buttonText;

		const icon = this.root.querySelector( '.dashicons' );

		if ( icon ) {
			this.iconClassName = icon.className;
		}
	}

	enable() {
		this.root.disabled = false;
	}

	disable() {
		this.root.disabled = true;
	}

	setBusyState() {
		const busyIcon = '<span class="dashicons dashicons-marker"></span>';
		this.root.innerHTML = `${ busyIcon } ${ this._buttonTextWithWrapper }`;
		this.disable();
	}

	removeBusyState() {
		this.enable();

		if ( this.iconClassName ) {
			this.updateIcon( this.iconClassName );
		}
	}

	updateIcon( className, position = 'before' ) {
		const icon = `<span class="${ className }"></span>`;

		this.root.innerHTML = 'after' === position ?
			`${ this._buttonTextWithWrapper } ${ icon }` :
			`${ icon } ${ this._buttonTextWithWrapper }`;

		this.iconClassName = className;
		this.iconPosition = position;
	}

	updateButtonText( text ) {
		this.buttonText = text;

		if ( this.iconClassName ) {
			this.updateIcon( this.iconClassName, this.iconPosition );
		} else {
			this.root.textContent = text;
		}
	}
}

export default GiveButton;
