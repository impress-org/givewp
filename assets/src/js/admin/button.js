class GiveButton {
	constructor( element ) {
		this.root          = element;
		this.buttonText    = this.root.textContent.trim();
		this.iconPosition  = 'before';
		this.text = this.root.querySelector('.give-button-text') ? `<span class="give-button-text">${this.buttonText}</span>` : this.buttonText;

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
		const busyIcon      = '<span class="dashicons dashicons-marker"></span>';
		this.root.innerHTML = `${busyIcon} ${this.text}`;
		this.disable();
	}

	removeBusyState() {
		this.enable();

		if ( this.iconClassName ) {
			this.updateIcon( this.iconClassName );
		}
	}

	updateIcon( className, position = 'before' ) {
		const icon = `<span class="${className}"></span>`;

		if ( 'after' === position ) {
			this.root.innerHTML = `${this.text} ${icon}`;
		} else {
			this.root.innerHTML = `${icon} ${this.text}`;
		}

		this.iconClassName = className;
		this.iconPosition  = position;
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
