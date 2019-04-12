/* globals jQuery, ajaxurl, give_addon_var */

( function( $ ) {
	$( document ).ready( function() {
		const $container = $( '#give-license-activator-wrap' ),
			  $form = $( 'form', $container ),
			  $license = $( 'input[name="give_license_key"]', $container ),
			  $submitBtn = $( 'input[type="submit"]', $form ),
			  $noticeContainer = $( '.give-notices', $container );

		/**
		 * License form submit button handler
		 */
		$license.on( 'change keyup', function() {
			if ( ! $( this ).val().trim() ) {
				$submitBtn.prop( 'disabled', true );
				return;
			}

			$submitBtn.prop( 'disabled', false );
		} ).change();

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
							response.data.hasOwnProperty( 'download_file' ) &&
							response.data.download_file
						) {
							$noticeContainer.html( `<div class="give-notice notice notice-success"><p>${ give_addon_var.notices.download_file.replace( '{link}', response.data.download_file ) }</p></div>` );
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
