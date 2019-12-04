class GiveButton {
	constructor( element ) {
		this.root          = element;
		this.buttonText    = this.root.textContent.trim();
		this.iconPosition  = 'before';

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
		const text = this.root.querySelector('.give-button-text') ? `<span class="${this.buttonText}"></span>` : this.buttonText;
		this.root.innerHTML = `${busyIcon} ${text}`;
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
		const text = this.root.querySelector('.give-button-text') ? `<span class="give-button-text">${this.buttonText}</span>` : this.buttonText;
		
		if ( 'after' === position ) {
			this.root.innerHTML = `${text} ${icon}`;
		} else {
			this.root.innerHTML = `${icon} ${text}`;
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
