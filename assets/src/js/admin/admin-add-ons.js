/* globals jQuery, ajaxurl, give_addon_var */

( function( $ ) {
	$( document ).ready( function() {
		const $licensesContainer = $( '#give-licenses-container' ),
			  $licenseActivationFormContainer = $( '#give-license-activator-wrap' ),
			  $container = $( '#give-license-activator-wrap' ),
			  $form = $( 'form', $container ),
			  $license = $( 'input[name="give_license_key"]', $container ),
			  $submitBtn = $( 'input[type="submit"]', $form ),
			  $noticeContainer = $( '.give-notices', $container );

		/**
		 * License form submit button handler
		 */
		function giveDisableActivateLicenseButton() {
			const $btn = $( this ).next();

			if ( ! $( this ).val().trim() ) {
				$btn.prop( 'disabled', true );
				return;
			}

			$btn.prop( 'disabled', false );
		}

		$licensesContainer.on( 'change keyup', '.give-license__key input[type="text"]', giveDisableActivateLicenseButton ).change();
		$licenseActivationFormContainer.on( 'change keyup', 'input[name="give_license_key"]', giveDisableActivateLicenseButton ).change();

		/**
		 * Deactivate license
		 */
		$licensesContainer.on( 'click', '.give-button__license-activate', function( e ) {
			e.preventDefault();

			const $this = $( this ),
				$container = $this.parents( '.give-addon-wrap' );

			$.ajax( {
				url: ajaxurl,
				method: 'POST',
				data: {
					action: 'give_get_license_info',
					license: $this.prev( '.give-license__key input[type="text"]' ).val().trim(),
					item_name: $this.attr( 'data-item-name' ),
					_wpnonce: $( '#give_license_activator_nonce' ).val().trim(),
				},
				beforeSend: function() {
					loader( $container );
				},
				success: function( response ) {
					if ( true === response.success ) {
						$container.replaceWith( response.data.html );
					}
				},
			} ).done( function() {
				loader( $container, false );
			} );
		} );

		/**
		 * Deactivate license
		 */
		$licensesContainer.on( 'click', '.give-license__deactivate', function( e ) {
			e.preventDefault();

			const $this = $( this ),
				  $container = $this.parents( '.give-addon-wrap' );

			$.ajax( {
				url: ajaxurl,
				method: 'POST',
				data: {
					action: 'give_deactivate_license',
					license: $this.attr( 'data-license-key' ),
					item_name: $this.attr( 'data-item-name' ),
					_wpnonce: $this.attr( 'data-nonce' ),
				},
				beforeSend: function() {
					loader( $container );
				},
				success: function( response ) {
					if ( true === response.success ) {
						$container.replaceWith( response.data.html );
						return;
					}
				},
			} ).done( function() {
				loader( $container, false );
			} );
		} );

		/**
		 * License form validation handler
		 */
		$form.on( 'submit', function() {
			const license = $license.val().trim(),
				  action = 'give_get_license_info',
				  _wpnonce = $( 'input[name="give_license_activator_nonce"]', $( this ) ).val().trim();

			// Remove all errors.
			$noticeContainer.empty();

			if ( ! license ) {
				$noticeContainer.html( `<div class="give-notice notice notice-error"><p>${ give_addon_var.notices.invalid_license }</p></div>` );
				return false;
			}

			$.ajax( {
				url: ajaxurl,
				method: 'POST',
				data: {
					action,
					license,
					_wpnonce,
				},
				beforeSend: function() {
					$submitBtn.val( $submitBtn.attr( 'data-activating' ) );
				},
				success: function( response ) {
					$submitBtn.val( $submitBtn.attr( 'data-activate' ) );

					if ( true === response.success ) {
						if (
							response.data.hasOwnProperty( 'download' ) &&
							response.data.download
						) {
							$noticeContainer.html( `<div class="give-notice notice notice-success"><p>${ give_addon_var.notices.download_file.replace( '{link}', response.data.download ) }</p></div>` );
							$licensesContainer.html( response.data.html );
						} else {
							$noticeContainer.html( `<div class="give-notice notice notice-error"><p>${ give_addon_var.notices.invalid_license }</p></div>` );
						}

						return;
					}

					if (
						response.data.hasOwnProperty( 'errorMsg' ) &&
						response.data.errorMsg
					) {
						$noticeContainer.html( `<div class="give-notice notice notice-error"><p>${ response.data.errorMsg }</p></div>` );
					} else {
						$noticeContainer.html( `<div class="give-notice notice notice-error"><p>${ give_addon_var.notices.invalid_license }</p></div>` );
					}
				},
			} ).always( function() {
				$submitBtn.val( $submitBtn.attr( 'data-activate' ) );
			} );

			return false;
		} );

		/**
		 * Show/Hide loader
		 *
		 * @since 2.5.0
		 * @param {object} $container
		 * @param {boolean} set
		 */
		function loader( $container, set = true ) {
			if ( set ) {
				$container.prepend( '<div class="give-spinner-wrap"><span class="is-active spinner"></span></div>' );
				return;
			}

			$( '.give-spinner-wrap', $container ).remove();
		}
	} );

	$( document ).ready( function() {
		const $container = $( '#give-addon-uploader-wrap' ),
			  $formContainer = $( '.give-form-wrap', $container ),
			  $form = $( 'form', $container ),
			  $file = $( 'input[type="file"]', $form ),
			  $activateBtnContainer = $( '.give-activate-addon-wrap', $container ),
			  $activateBtn = $( 'button', $activateBtnContainer ),
			  $noticeContainer = $( '.give-notices', $container );

		/**
		 * File drop handler
		 */
		$container.on( 'drop', function( e ) {
			e.stopPropagation();
			e.preventDefault();

			$( this ).removeClass( 'thick-border' );

			const file = e.originalEvent.dataTransfer.files,
				  fd = new FormData();

			fd.append( 'file', file[ 0 ] );

			giveUploadData( fd );
		} );

		// Drag over
		$container.on( 'dragover', function( e ) {
			$( this ).addClass( 'thick-border' );
		} ).on( 'dragleave', function( e ) {
			$( this ).removeClass( 'thick-border' );
		} );

		/**
		 * File change handler
		 */
		$file.on( 'change', function( e ) {
			e.stopPropagation();
			e.preventDefault();

			const fd = new FormData(),
				  files = $file[ 0 ].files[ 0 ];

			if ( ! files ) {
				return false;
			}

			fd.append( 'file', files );
			giveUploadData( fd );
		} );

		/**
		 * Activate button event handle
		 */
		$activateBtn.on( 'click', function( e ) {
			e.preventDefault();

			$noticeContainer.empty();

			$.ajax( {
				url: ajaxurl,
				method: 'POST',
				data: {
					action: 'give_activate_addon',
					plugin: $activateBtn.attr( 'data-pluginPath' ),
					_wpnonce: $activateBtn.attr( 'data-nonce' ),
				},
				beforeSend: function() {
					$activateBtn.text( $activateBtn.attr( 'data-activating' ) );
				},
				success: function( response ) {
					if ( true === response.success ) {
						const msg = give_addon_var.notices.addon_activated.replace( '{pluginName}', $activateBtn.attr( 'data-pluginName' ) );
						$noticeContainer.html( `<div class="give-notice notice notice-success"><p>${ msg }</p></div>` );

						return;
					}

					if (
						response.data.hasOwnProperty( 'errorMsg' ) &&
						response.data.errorMsg
					) {
						$noticeContainer.html( `<div class="give-notice notice notice-error"><p>${ response.data.errorMsg }</p></div>` );
					} else {
						$noticeContainer.html( '<div class="give-notice notice notice-error"><p>Plugin does not activated successfully.</p></div>' );
					}
				},
			} ).always( function() {
				$activateBtn.text( $activateBtn.attr( 'data-activate' ) );

				$activateBtnContainer.hide();
				$formContainer.show();
			} );
		} );

		/**
		 * Sending AJAX request and upload file
		 *
		 * @since 2.5.0
		 * @param {FormData} formData Form Data.
		 */
		function giveUploadData( formData ) {
			$noticeContainer.empty();

			$.ajax( {
				url: `${ ajaxurl }?action=give_upload_addon&_wpnonce=${ $( 'input[name="_give_upload_addon"]', $form ).val().trim() }`,
				method: 'POST',
				data: formData,
				contentType: false,
				processData: false,
				dataType: 'json',
				beforeSend: function() {
					$noticeContainer.html( `<div class="give-notice notice notice-info"><p>${ give_addon_var.notices.uploading }</p></div>` );
				},
				success: function( response ) {
					if ( true === response.success ) {
						$activateBtnContainer.show();
						$formContainer.hide();

						$activateBtn.attr( 'data-pluginPath', response.data.pluginPath );
						$activateBtn.attr( 'data-pluginName', response.data.pluginName );
						$activateBtn.attr( 'data-nonce', response.data.nonce );
						$noticeContainer.html( `<div class="give-notice notice notice-success"><p>${ give_addon_var.notices.uploaded }</p></div>` );

						return;
					}

					$noticeContainer.html( `<div class="give-notice notice notice-error"><p>${ response.data.errorMsg }</p></div>` );
				},
			} );
		}
	} );
}( jQuery ) );
