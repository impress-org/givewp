const GiveModal = ( function( $ ) {

	/**
	 * Contructor function.
	 */
	function GiveModal( selector ) {

		// Configuration object for modal popup.
		this.config = {};

		// class or ID of the element.
		this.selector = selector;

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

		return this;
	}


	/**
	 * Adds necessary classes for a warning popup.
	 */
	GiveModal.prototype.warning = function() {
		this.config.mainClass = this.config.mainClass || 'give-warning-popup';

		return this;
	}


	/**
	 * Adds necessary classes for a notice popup.
	 */
	GiveModal.prototype.notice = function() {
		this.config.mainClass = this.config.mainClass || 'give-notice-popup';

		return this;
	}


	/**
	 * Adds necessary classes for a success popup.
	 */
	GiveModal.prototype.success = function() {
		this.config.mainClass = this.config.mainClass || 'give-success-popup';

		return this;
	}


	/**
	 * Passive modals are the ones that popup
	 * implicity without user input.
	 *
	 * Example, a modal that pops up after an
	 * AJAX call or after a failed upload.
	 */
	GiveModal.prototype.passive = function() {

		this.config.popupType = 'passive';
		this.config.items     = {
			src: this.selector,
			type: 'inline'
		};

		return this;
	}


	/**
	 * Active modals are the ones that popup
	 * explicity after a user input.
	 *
	 * Example, a modal that pops up after a
	 * user clicks on a button or a link.
	 */
	GiveModal.prototype.active = function() {

		this.config.popupType = 'active';
		this.config.type      = 'inline';

		return this;
	}


	/**
	 * Give's Notice Popup
	 *
	 * @param object extraConfig Extar configuration parameters for Give's popup. 
	 */
	GiveModal.prototype.popup = function( extraConfig = { behaviour: 'passive' } ) {

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

					let fields = '<div class="appended-controls">';
					fields += reference.root.filter.apply( 'filter_notice_fields', '<button class="button button-primary popup-close-button">Ok</button>' );
					fields += '</div>';
					reference.callback.content.append( fields );
				},


				/**
				 * Callback fires after closing the popup.
				 */
				close: function() {
					reference.callback.content.find( '.appended-controls' ).remove();
				},
			}
		});


		switch ( this.config.popupType ) {
			case 'passive':
				$.magnificPopup.open( this.config );
				break;

			case 'active':
				$( this.selector ).magnificPopup( this.config );
				break;
		}

		/**
		 * Event handler to close the popup after
		 * clicking the close button.
		 */
		this.doc.on( 'click', '.popup-close-button', function() {
			$.magnificPopup.close();
		})
	}

	return GiveModal;
})( jQuery );