const GiveModal = ( function( $ ) {

	/**
	 * Contructor function.
	 */
	function GiveModal( selector ) {


		// class or ID of the element.
		this.selector = selector;

		// Configuration object for modal popup.
		this.config = {
			mainClass: null,
			items: {
				src: this.selector,
				type: 'inline',
			},
			popupType: null,
		};

		/**
		 * Object that holds the means of creating
		 * filter hooks and its hookable functions.
		 */
		this.filter = {
			filter_obj: {},

			/**
			 * This function enables you to add a filter.
			 *
			 * @param name     Name of the filter hook.
			 * @param function Callback function to filter the input
			 */
			add: function( name, filter ) {
				( this.filter_obj[ name ] || ( this.filter_obj[ name ] = [] ) ).push( filter );
			},


			/**
			 * Used to create a filter hook.
			 *
			 * @param string name Name of the filter hook.
			 * @param mixed value Value to filter.
			 */
			apply: function( name, value ) {
				if ( this.filter_obj[name] ) {
					let filter = this.filter_obj[ name ];

					filter.map( current => { value = current( value ) } );
				}

				return value;
			}
		}

		return this;
	}


	/**
	 * Caching the document object.
	 */
	GiveModal.prototype.doc = $( document );


	/**
	 * Adds necessary classes for an error popup.
	 */
	GiveModal.prototype.error = function() {
		this.config.mainClass = this.config.mainClass || 'give-error-popup';
		this.config.popupType = 'Error';

		return this;
	}


	/**
	 * Adds necessary classes for a warning popup.
	 */
	GiveModal.prototype.warning = function() {
		this.config.mainClass = this.config.mainClass || 'give-warning-popup';
		this.config.popupType = 'Warning';

		return this;
	}


	/**
	 * Adds necessary classes for a notice popup.
	 */
	GiveModal.prototype.notice = function() {
		this.config.mainClass = this.config.mainClass || 'give-notice-popup';
		this.config.popupType = 'Notice';

		return this;
	}


	/**
	 * Adds necessary classes for a success popup.
	 */
	GiveModal.prototype.success = function() {
		this.config.mainClass = this.config.mainClass || 'give-success-popup';
		this.config.popupType = 'Success';

		return this;
	}


	/**
	 * Appends new buttons after 'OK' button.
	 *
	 * @param string id         ID of the button.
	 * @param string buttonType Type of the button: primary|secondary.
	 * @param string textNode   Button Text.
	 */
	GiveModal.prototype.appendButton = function( id, buttonType, textNode ) {
		this.filter.add( 'filter_notice_fields', val => { return `${ val } <button id="${ id }" class="give-button give-button-${ buttonType }">${ textNode }</button>` } );

		return this;
	}

	/**
	 * Replaces the 'OK' button with a custom button.
	 *
	 * @param string id         ID of the button.
	 * @param string buttonType Type of the button: primary|secondary.
	 * @param string textNode   Button Text.
	 */
	GiveModal.prototype.addButton = function( id, buttonType, textNode ) {
		this.filter.add( 'filter_notice_fields', val => { return `<button id="${ id }" class="give-button give-button-${ buttonType }">${ textNode }</button>` } );

		return this;
	}


	/**
	 * Closes the popup.
	 */
	GiveModal.prototype.close = function() {
		$.magnificPopup.close();
	}


	/**
	 * Add custom popup content.
	 */
	GiveModal.prototype.customContent = function( content ) {
		this.config.items.src = `<div class="white-popup">${ content }</div>`;
		return this;
	}


	/**
	 * Give's Notice Popup
	 *
	 */
	GiveModal.prototype.popup = function() {

		// reference.root is a pointer to an instance of GiveModal.
		const reference = {
			root: this,
		};


		// Configuration object for 'notice' Magnific Popup.
		$.extend( this.config, {
			showCloseBtn: false,
			callbacks: {

				/**
				 * Callback fires after opening the popup.
				 */
				open: function() {

					// Reference to $( '.mfp-content' )
					reference.callback = this;
					reference.callback.content = $( this.content );

					null !== reference.root.config.popupType && reference.callback.content.prepend( `<div class="give-popup-notice-type">${ reference.root.config.popupType }</div>` );

					let fields = '<div class="give-appended-controls">';
					fields += '<div class="give-popup-buttons-wrap">';
					fields += reference.root.filter.apply( 'filter_notice_fields', '<button class="give-button give-button-secondary popup-close-button">Close</button>' );
					fields += '</div>';
					fields += '</div>';

					// Appends the control buttons after the popup content.
					reference.callback.content.append( fields );
				},


				/**
				 * Callback fires after closing the popup.
				 */
				close: function() {
					reference.callback.content.find( '.give-appended-controls' ).remove();
				},
			}
		});


		// Open the popup.
		$.magnificPopup.open( this.config );


		/**
		 * Event handler to close the popup after
		 * clicking the close button.
		 */
		this.doc.on( 'click', '.popup-close-button', function() {
			reference.root.close();
		})
	}

	return GiveModal;

})( jQuery );
