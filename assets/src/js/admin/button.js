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
		this.root.innerHTML = `${busyIcon} ${this.buttonText}`;
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
			this.root.innerHTML = `${this.buttonText} ${icon}`;
		} else {
			this.root.innerHTML = `${icon} ${this.buttonText}`;
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
