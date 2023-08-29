/**
 * This API is under development, so do not use this in production.
 * We will open this API for use after some testing (coming releases).
 */
/* globals Give, jQuery */
import 'magnific-popup';
import './dynamicListener.js';

/**
 * This abstract class is base for modal
 *
 * @since 2.1.0
 */
class GiveModal {
	constructor( obj ) {
		if ( GiveModal === this.constructor ) {
			throw new Error( 'Abstract classes can\'t be instantiated.' );
		}

		this.config = Object.assign(
			{
				type: '',
				triggerSelector: '',
				externalPlugin: 'magnificPopup',
				classes: { modalWrapper: '', cancelBtn: '' },
				modalContent: {},
			},
			obj
		);

		// Set main class.
		this.config.mainClass = `${ this.config.mainClass ? this.config.mainClass : '' } modal-fade-slide`.trim();
	}

	/**
	 * Bootstrap
	 */
	init() {
		this.setupTemplate();
		this.popupConfig();
		this.__setupClickEvent();
	}

	/**
	 * Get template
	 *
	 * @since 2.8.0 add support for modalContent.body for full body markup
	 * @since 2.1.0
	 *
	 * @return {string} Template HTML.
	 */
	getTemplate() {
		let template = '<div class="give-hidden"></div>';

		if ( this.config.type.length ) {
			template = `<div class="give-modal give-modal--zoom ${ this.config.classes.modalWrapper ? `${ this.config.classes.modalWrapper }`.trim() : '' }">

				<div class="give-modal__body">
					${ this.config.modalContent.title ? `<h2 class="give-modal__title">${ this.config.modalContent.title }</h2>` : '' }
					${ this.config.modalContent.desc ? `<p class="give-modal__description">${ this.config.modalContent.desc }</p>` : '' }
					${ this.config.modalContent.body ? this.config.modalContent.body : '' }
				</div>

				<div class="give-modal__controls">

					${ ( 'form' === this.config.type ) ? '<div class="spinner"></div>' : '' }
					${ ( 'form' === this.config.type && 'undefined' !== this.config.modalContent.link ) ? `<a class="give-modal--additional-link" href="${ this.config.modalContent.link }" target="${ ( 'undefined' !== this.config.modalContent.link_self && this.config.modalContent.link_self ) ? '_self' : '_blank' }">${ this.config.modalContent.link_text }</a>` : '' }

					<button class="give-button give-popup-close-button${ this.config.classes.cancelBtn ? ` ${ this.config.classes.cancelBtn }` : ' give-button--secondary' }">
						${ this.config.modalContent.cancelBtnTitle ? this.config.modalContent.cancelBtnTitle : ( 'confirm' === this.config.type ? Give.fn.getGlobalVar( 'cancel' ) : Give.fn.getGlobalVar( 'close' ) ) }
					</button>

					${ ( 'confirm' !== this.config.type && 'form' !== this.config.type ) ? '' : `<button class="give-button give-button--primary give-popup-${ this.config.type }-button">

						${ this.config.modalContent.confirmBtnTitle ? this.config.modalContent.confirmBtnTitle : Give.fn.getGlobalVar( 'confirm' ) }
					</button>` }
				</div>

			</div>`;
		}

		return template;
	}

	/**
	 * Setup template
	 *
	 * @since 2.1.0
	 */
	setupTemplate() {
		this.config.template = this.getTemplate();
	}

	/**
	 * Handle click event if triggerSelector is set.
	 *
	 * @since 2.1.0
	 * @private
	 */
	__setupClickEvent() {
		// Bailout.
		if ( ! this.config.triggerSelector.length ) {
			return;
		}

		jQuery( this.config.triggerSelector ).magnificPopup( this.config );
	}

	/**
	 * Setup popup params
	 *
	 * Note: only for internal purpose
	 *
	 * @since 2.1.0
	 * @private
	 */
	popupConfig() {
		if ( 'magnificPopup' === this.config.externalPlugin ) {
			this.config.items = this.config.items || {
				src: this.config.template,
				type: 'inline',
			};

			this.config = Object.assign(
				{
					removalDelay: 300,
					fixedContentPos: true,
					fixedBgPos: true,
					alignTop: true,
					showCloseBtn: false,
					closeOnBgClick: false,
					enableEscapeKey: true,
					focus: '.give-popup-close-button',
				},
				this.config
			);
		}
	}

	/**
	 * Click close button event handler
	 *
	 * @since 2.1.0
	 * @private
	 *
	 * @param {object} event Event object.
	 */
	static __closePopup( event ) {
		event.preventDefault();

		if( ! event.target.classList.contains( 'js-has-event-handler' )){
			jQuery.magnificPopup.instance.close();
		}
	}

	/**
	 * Give's Notice Popup
	 *
	 * @since 2.1.0
	 *
	 * @return {object} GiveModal class object.
	 */
	render() {
		switch ( this.config.externalPlugin ) {
			case 'magnificPopup':
				if ( ! this.config.triggerSelector ) {
					jQuery.magnificPopup.open( this.config );
				}

				break;
		}

		return this;
	}

	/**
	 * Open modal after getting content from ajax
	 *
	 * @since 2.5.0
	 * @private
	 */
	static __ajaxModalHandle( event ) {
		let $this = jQuery( event.target ),
			cache = $this.attr( 'data-cache' );

		event.preventDefault();

		// Load result from cache if any.
		if ( 'undefined' !== typeof cache ) {
			cache = decodeURI( cache );

			new Give.modal.GiveSuccessAlert( {
				modalContent: {
					title: $this.attr( 'title' ),
					desc: cache,
				},
				closeOnBgClick: true,
			} ).render();

			return;
		}

		jQuery.ajax( {
			url: $this.attr( 'href' ),
			method: 'GET',
			beforeSend: function() {
				new Give.modal.GiveSuccessAlert( {
					modalContent: {
						desc: Give.fn.loader( {}, { show: null, loadingText: Give.fn.getGlobalVar( 'loader_translation' ).loading } ),
					},
					closeOnBgClick: true,
				} ).render();
			},
			success: function( response ) {
				if ( response.length ) {
					$this.attr( 'data-cache', encodeURI( response ) );
				}

				// Do not re-open modal after successfully ajax response if modal already closed.
				if ( ! jQuery( '.mfp-wrap' ).length ) {
					return;
				}

				new Give.modal.GiveSuccessAlert( {
					modalContent: {
						title: $this.attr( 'title' ),
						desc: response,

					},
					closeOnBgClick: true,
				} ).render();
			},
		} );
	}
}

/**
 * This class will handle error alert modal
 *
 * @since 2.1.0
 */
class GiveErrorAlert extends GiveModal {
	constructor( obj ) {
		obj.type = 'alert';
		super( obj );
		this.config.classes.modalWrapper = 'give-modal--error';

		this.init();
	}
}

/**
 * This class will handle warning alert modal
 *
 * @since 2.1.0
 */
class GiveWarningAlert extends GiveModal {
	constructor( obj ) {
		obj.type = 'alert';
		super( obj );
		this.config.classes.modalWrapper = 'give-modal--warning';

		this.init();
	}
}

/**
 * This class will handle notice alert modal
 *
 * @since 2.1.0
 */
class GiveNoticeAlert extends GiveModal {
	constructor( obj ) {
		obj.type = 'alert';
		super( obj );
		this.config.classes.modalWrapper = 'give-modal--notice';

		this.init();
	}
}

/**
 * This class will handle success alert modal
 *
 * @since 2.8.0 extend the classes rather than override
 * @since 2.1.0
 */
class GiveSuccessAlert extends GiveModal {
	constructor( obj ) {
		obj.type = 'alert';
		super( obj );
		this.config.classes.modalWrapper += ' give-modal--success';

		this.init();
	}
}

/**
 * This class will handle confirm modal
 *
 * @since 2.1.0
 */
class GiveConfirmModal extends GiveModal {
	constructor( obj ) {
		obj.type = 'confirm';
		super( obj );

		if ( 'undefined' !== typeof ( obj.modalWrapper ) && '' !== obj.modalWrapper ) {
			this.config.classes.modalWrapper = obj.modalWrapper;
		}

		this.config.classes.modalWrapper += ' give-modal--confirm';

		this.init();
	}

	/**
	 * Confirm button click event handler
	 *
	 * Note: only for internal purpose
	 *
	 * @since 2.1.0
	 * @private
	 */
	static __confirmPopup() {
		if ( 'function' === typeof jQuery.magnificPopup.instance.st.successConfirm ) {
			jQuery.magnificPopup.instance.st.successConfirm( {
				el: jQuery.magnificPopup.instance.st.el,
			} );
			jQuery.magnificPopup.close();
		}
	}
}

/**
 * This class will handle Form modal
 *
 * @since 2.2.0
 */
class GiveFormModal extends GiveModal {
	constructor( obj ) {
		obj.type = 'form';
		super( obj );

		if ( 'undefined' !== typeof ( obj.modalWrapper ) && '' !== obj.modalWrapper ) {
			this.config.classes.modalWrapper = obj.modalWrapper;
		}

		this.init();
	}

	/**
	 * Submit button click event handler
	 *
	 * Note: only for internal purpose
	 *
	 * @since 2.2.0
	 * @private
	 */
	static __submitPopup() {
		if ( 'function' === typeof jQuery.magnificPopup.instance.st.successConfirm ) {
			jQuery.magnificPopup.instance.st.successConfirm( {
				el: jQuery.magnificPopup.instance.st.el,
			} );
		}
	}
}

/**
 * Add events
 */
window.addDynamicEventListener( document, 'click', '.give-popup-close-button', GiveModal.__closePopup, {} );
window.addDynamicEventListener( document, 'click', '.give-popup-confirm-button', GiveConfirmModal.__confirmPopup, {} );
window.addDynamicEventListener( document, 'click', '.give-popup-form-button', GiveFormModal.__submitPopup, {} );
window.addDynamicEventListener( document, 'click', '.give-ajax-modal', GiveModal.__ajaxModalHandle, {} );

export { GiveModal, GiveErrorAlert, GiveWarningAlert, GiveNoticeAlert, GiveSuccessAlert, GiveConfirmModal, GiveFormModal };
