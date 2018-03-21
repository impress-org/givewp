/**
 * # All popups are an instance of GiveModal class
 *
 * # The minimum syntax required to instantiate a popup
 * depending on the source are of 2 types:
 *
 * # `new GiveModal()` should be the first method and `popup()` should be the last method.
 *
 * -- 1. From an HTML entity:
 *    `new GiveModal( '#test-popup' ).popup()`
 *
 * -- 2. Dynamically generated content:
 *    `new GiveModal().customContent({ title: 'Some title', content: 'Some Content' }).popup()`
 *
 * # Set the type of popup by chaining one of the following suitable methods:
 * -- 1. success()
 * -- 2. notice()
 * -- 3. warning()
 * -- 4. error()
 *
 * # Chain the `addButton()` to add more buttons to the popup
 */

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
			focus: '#give-popup-close-button',
			removalDelay: 300,
			fixedContentPos: true,
			fixedBgPos: true,
			alignTop: true,
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
			 * @param string   name   Name of the filter hook.
			 * @param function filter Callback function to filter the input
			 */
			add: function( name, filter ) {
				( this.filter_obj[ name ] || ( this.filter_obj[ name ] = [] ) ).push( filter );

				// return that;
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
		this.config.mainClass = this.config.mainClass || 'popup-fade-slide give-error-popup';

		return this;
	}


	/**
	 * Adds necessary classes for a warning popup.
	 */
	GiveModal.prototype.warning = function() {
		this.config.mainClass = this.config.mainClass || 'popup-fade-slide give-warning-popup';

		return this;
	}


	/**
	 * Adds necessary classes for a notice popup.
	 */
	GiveModal.prototype.notice = function() {
		this.config.mainClass = this.config.mainClass || 'popup-fade-slide give-notice-popup';

		return this;
	}


	/**
	 * Adds necessary classes for a success popup.
	 */
	GiveModal.prototype.success = function() {
		this.config.mainClass = this.config.mainClass || 'popup-fade-slide give-success-popup';

		return this;
	}


	/**
	 * Appends new buttons after 'OK' button.
	 *
	 * @param string id         ID of the button.
	 * @param string buttonType Type of the button: primary|secondary.
	 * @param string textNode   Button Text. 
	 */
	GiveModal.prototype.addButton = function( id, buttonType, textNode ) {
		this.filter.add( 'filter_notice_fields', val => { return `${ val } <button id="${ id }" class="give-button give-button-${ buttonType }">${ textNode }</button>` } );

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
	 *
	 * @param string popupData.title   Title of the popup.
	 * @param string popupData.content Content of the popup.
	 */
	GiveModal.prototype.customContent = function( popupData = {} ) {
		let title   = !! popupData.title && `<h2>${ popupData.title }</h2>`,
		    content = !! popupData.content && popupData.content;
		this.config.items.src = `<div class="white-popup zoom-animation">${ !! title ? title : '' }${ !! content ? content : '' }</div>`;

		return this;
	}


	/**
	 * Wrapper function to add a filter.
	 *
	 * @param string   name   Name of the filter hook.
	 * @param function filter Callback function to filter the input
	 */
	GiveModal.prototype.addFilter = function( name, filter ) {
		this.filter.add( name, filter );

		return this;
	}


	/**
	 * Wrapper function to apply a filter.
	 *
	 * @param string name  Name of the filter hook.
	 * @param mixed  value Value to filter.
	 */
	GiveModal.prototype.applyFilter = function( name, value ) {
		let data = this.filter.apply( name, value );

		return data;
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


					// Reference to MagnificPopup object.
					reference.callback = this;

					// Reference to $( '.white-popup' )
					reference.callback.content = $( this.content );

					let html = reference.callback.content.html();
					reference.callback.content.html( '' );
					reference.callback.content.append( `<div class="white-popup-child-container">${ html }</div>` );

					let cancelTextNode = reference.root.applyFilter( 'cancel_text_node', 'Close' );

					let fields = '<div class="give-appended-controls">';
					fields += '<div class="give-popup-buttons-wrap">';
					fields += reference.root.applyFilter( 'filter_notice_fields', `<button id="give-popup-close-button" class="give-button give-button-secondary give-popup-close-button">${ cancelTextNode }</button>` );
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
					reference.root.filter.filter_obj = {};
				},
			}
		});


		// Open the popup.
		$.magnificPopup.open( this.config );


		/**
		 * Event handler to close the popup after
		 * clicking the close button.
		 */
		this.doc.on( 'click', '#give-popup-close-button', function() {
			reference.root.close();
		})

		return this;
	}

	return GiveModal;

})( jQuery );
