import GiveButton from './button';
import ClipboardJS from 'clipboard';

class GiveShortcodeButton extends GiveButton {
	constructor( element ) {
		super( element );
		this.shortcode = this.root.dataset.giveShortcode;
		this.clipboard = new ClipboardJS( this.root, {
			text: function( trigger ) {
				return trigger.dataset.giveShortcode;
			},
		} );
		this.reset = this.reset.bind( this );
	}

	init() {
		this.registerEventHandlers();
	}

	registerEventHandlers() {
		this.clipboard.on( 'success', () => this.handleSuccessClick() );
		this.clipboard.on( 'error', () => this.handleErrorClick() );
	}

	handleSuccessClick( event ) {
		this.updateIcon( 'dashicons dashicons-yes' );
		this.root.setAttribute( 'aria-label', give_vars.copied );
		this.root.addEventListener( 'mouseout', this.reset );
	}

	handleErrorClick( event ) {
		this.updateIcon( 'dashicons dashicons-warning' );
		this.root.setAttribute( 'aria-label', 'Shortcode could not be copied.' );
	}

	reset( event ) {
		this.updateIcon( 'dashicons dashicons-admin-page' );
		this.root.setAttribute( 'aria-label', this.shortcode );
		this.root.removeEventListener( 'mouseout', this.reset );
	}
}

export { GiveShortcodeButton };
