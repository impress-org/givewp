/* globals jQuery, ajaxurl, give_addon_var, Give */

( function( $ ) {
	$( document ).ready( function() {
		const $licensesContainer = $( '#give-licenses-container' ),
			$licenseActivationFormContainer = $( '#give-license-activator-wrap' ),
			$form = $( 'form', $licenseActivationFormContainer ),
			$license = $( 'input[name="give_license_key"]', $licenseActivationFormContainer ),
			$submitBtn = $( 'input[type="submit"]', $form ),
			$noticeContainer = $( '.give-license-notices', $licenseActivationFormContainer );

		/**
		 * Allow dismissing upload notices for license widget.
		 */
		$noticeContainer.on( 'click', $( '.notice-dismiss', $noticeContainer ), function( event ) {
			$noticeContainer.empty().hide();
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

			// Must have entered a license key.
			if ( ! license ) {
				$noticeContainer.show();
				$noticeContainer.html( Give.notice.fn.getAdminNoticeHTML( give_addon_var.notices.invalid_license, 'error' ) );
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
					$submitBtn.prop( 'disabled', true );
					Give.fn.loader( $licensesContainer );
				},
				success: function( response ) {
					// Show notice container.
					$noticeContainer.show();
					$license.val( '' );

					if ( true === response.success ) {
						if (
							response.data.hasOwnProperty( 'download' ) &&
							response.data.download
						) {
							const msg = 'string' === typeof response.data.download ?
								give_addon_var.notices.download_file.replace( '{link}', response.data.download ) :
								give_addon_var.notices.download_file.substring( 0, give_addon_var.notices.download_file.indexOf( '.' ) + 1 );

							$noticeContainer.html( Give.notice.fn.getAdminNoticeHTML( msg, 'success' ) );
							$licensesContainer.parent().parent().removeClass( 'give-hidden' );
							$licensesContainer.html( response.data.html );
						} else {
							$noticeContainer.html( Give.notice.fn.getAdminNoticeHTML( give_addon_var.notices.invalid_license, 'error' ) );
						}

						return;
					}

					if (
						response.data.hasOwnProperty( 'errorMsg' ) &&
						response.data.errorMsg
					) {
						$noticeContainer.html( Give.notice.fn.getAdminNoticeHTML( response.data.errorMsg, 'error' ) );
					} else {
						$noticeContainer.html( Give.notice.fn.getAdminNoticeHTML( give_addon_var.notices.invalid_license, 'error' ) );
					}
				},
			} ).always( function() {
				Give.fn.loader( $licensesContainer, { show: false } );
				$submitBtn.val( $submitBtn.attr( 'data-activate' ) );
				$submitBtn.prop( 'disabled', false );
			} );

			return false;
		} );

		/**
		 * Activate license
		 */
		$licensesContainer.on( 'click', '.give-button__license-activate', function( e ) {
			e.preventDefault();

			const $this = $( this ),
				$container = $this.parents( '.give-addon-wrap' ),
				$noticeContainer = $( '.give-license-notice-container', $container ),
				license = $this.prev( '.give-license__key input[type="text"]' ).val().trim();

			// Remove errors if any.
			$noticeContainer
				.empty()
				.removeClass( 'give-addon-notice-shown' )
				.show();

			// Must have entered a license key.
			if ( ! license ) {
				$noticeContainer
					.addClass( 'give-addon-notice-shown' )
					.prepend( Give.notice.fn.getAdminNoticeHTML( give_addon_var.notices.invalid_license, 'error' ) );
			} else {
				$.ajax( {
					url: ajaxurl,
					method: 'POST',
					data: {
						action: 'give_get_license_info',
						license: license,
						single: 1,
						addon: $this.attr( 'data-addon' ),
						_wpnonce: $( '#give_license_activator_nonce' ).val().trim(),
					},
					beforeSend: function() {
						Give.fn.loader( $container );
					},
					success: function( response ) {
						if ( true === response.success ) {
							if ( response.data.hasOwnProperty( 'is_all_access_pass' ) && response.data.is_all_access_pass ) {
								$licensesContainer.html( response.data.html );
							} else {
								$container.replaceWith( response.data.html );
							}
							return;
						}

						$noticeContainer
							.addClass( 'give-addon-notice-shown' )
							.prepend( Give.notice.fn.getAdminNoticeHTML( response.data.errorMsg, 'error' ) );
					},
				} ).done( function() {
					Give.fn.loader( $container, { show: false } );
				} );
			}

			$licensesContainer.on( 'click', '.notice-dismiss', function() {
				$noticeContainer.slideUp( 150, function() {
					$noticeContainer.removeClass( 'give-addon-notice-shown' );
				} );
			} );
		} );

		/**
		 * Deactivate license
		 */
		$licensesContainer.on( 'click', '.give-license__deactivate', function( e ) {
			e.preventDefault();

			const $this = $( this );

			let $container = $this.parents( '.give-addon-wrap' ),
				$noticeContainer = $( '.give-license-notice-container', $container );

			const is_all_access_pass = 1 < $this.parents( '.give-addon-inner' ).find( '.give-addon-info-wrap' ).length,
				  index = $( '.give-addon-wrap' ).index( $container ); // Preserve select position to reset $container selector after replace it with new HTML;

			// Remove errors if any.
			$noticeContainer
				.empty()
				.removeClass( 'give-addon-notice-shown' )
				.show();

			$.ajax( {
				url: ajaxurl,
				method: 'POST',
				data: {
					action: 'give_deactivate_license',
					license: $this.attr( 'data-license-key' ),
					item_name: $this.attr( 'data-item-name' ),
					plugin_dirname: $this.attr( 'data-plugin-dirname' ),
					_wpnonce: $this.attr( 'data-nonce' ),
				},
				beforeSend: function() {
					if ( is_all_access_pass ) {
						Give.fn.loader( $licensesContainer );
					} else {
						Give.fn.loader( $container );
					}
				},
				success: function( response ) {
					if ( true === response.success ) {
						if ( is_all_access_pass ) {
							$licensesContainer.html( response.data.html );
						} else {
							$container.replaceWith( response.data.html );
						}

						// Update selector.
						$container = $( '.give-addon-wrap' ).get( index );
						$noticeContainer = $( '.give-license-notice-container', $container );

						$noticeContainer
							.addClass( 'give-addon-notice-shown' )
							.prepend( Give.notice.fn.getAdminNoticeHTML( response.data.msg, 'success' ) );

						// Hide license container if not any licens eexists.
						if ( ! $licensesContainer.html().trim().length ) {
							$licensesContainer.parent().parent().addClass( 'give-hidden' );
						}
					} else {
						$noticeContainer
							.addClass( 'give-addon-notice-shown' )
							.prepend( Give.notice.fn.getAdminNoticeHTML( response.data.errorMsg, 'error' ) );
					}
				},
			} ).done( function() {
				if ( is_all_access_pass ) {
					Give.fn.loader( $licensesContainer, { show: false } );
				} else {
					Give.fn.loader( $container, { show: false } );
				}
			} );

			$licensesContainer.on( 'click', '.notice-dismiss', function() {
				$noticeContainer.slideUp( 150, function() {
					$noticeContainer.removeClass( 'give-addon-notice-shown' );
				} );
			} );
		} );

		/**
		 * Reactivate license
		 */
		$licensesContainer.on( 'click', '.give-button__license-reactivate', function( e ) {
			e.preventDefault();

			const $this = $( this ),
				  license = $this.attr( 'data-license' ).trim(),
				  index = $( '.give-addon-wrap' ).index( $container ); // Preserve select position to reset $container selector after replace it with new HTML;;

			let $container = $this.parents( '.give-addon-wrap' ),
				$noticeContainer = $( '.give-license-notice-container', $container );

			// Remove errors if any.
			$noticeContainer
				.empty()
				.removeClass( 'give-addon-notice-shown' )
				.show();

			// Must have entered a license key.
			if ( ! license ) {
				$noticeContainer
					.addClass( 'give-addon-notice-shown' )
					.prepend( Give.notice.fn.getAdminNoticeHTML( give_addon_var.notices.invalid_license, 'error' ) );
			} else {
				$.ajax( {
					url: ajaxurl,
					method: 'POST',
					data: {
						action: 'give_get_license_info',
						license: license,
						single: 1,
						reactivate: 1,
						addon: $this.attr( 'data-addon' ),
						_wpnonce: $( '#give_license_activator_nonce' ).val().trim(),
					},
					beforeSend: function() {
						Give.fn.loader( $container );
					},
					success: function( response ) {
						if ( true === response.success ) {
							if ( response.data.hasOwnProperty( 'is_all_access_pass' ) && response.data.is_all_access_pass ) {
								$licensesContainer.html( response.data.html );
							} else {
								$container.replaceWith( response.data.html );
							}
							return;
						}

						if ( response.data.hasOwnProperty( 'html' ) && response.data.html.length ) {
							if ( response.data.hasOwnProperty( 'is_all_access_pass' ) && response.data.is_all_access_pass ) {
								$licensesContainer.html( response.data.html );
							} else {
								$container.replaceWith( response.data.html );
							}
						}

						// Update selector.
						$container = $( '.give-addon-wrap' ).get( index );
						$noticeContainer = $( '.give-license-notice-container', $container );

						$noticeContainer
							.addClass( 'give-addon-notice-shown' )
							.prepend( Give.notice.fn.getAdminNoticeHTML( response.data.errorMsg, 'error' ) );
					},
				} ).done( function() {
					Give.fn.loader( $container, { show: false } );
				} );
			}

			$licensesContainer.on( 'click', '.notice-dismiss', function() {
				$noticeContainer.slideUp( 150, function() {
					$noticeContainer.removeClass( 'give-addon-notice-shown' );
				} );
			} );
		} );

		/**
		 * Refresh all licenses
		 */
		$( '#give-button__refresh-licenses' ).on( 'click', function( e ) {
			e.preventDefault();

			const $this = $( this );

			$.ajax( {
				url: ajaxurl,
				method: 'POST',
				data: {
					action: 'give_refresh_all_licenses',
					_wpnonce: $this.attr( 'data-nonce' ),
				},
				beforeSend: function() {
					$this.text( $this.attr( 'data-activating' ) );
					Give.fn.loader( $licensesContainer );
				},
				success: function( response ) {
					if ( true === response.success ) {
						$licensesContainer.html( response.data.html );

						$( '#give-last-refresh-notice' ).text( response.data.lastUpdateMsg );
					}

					if ( ! response.success || response.data.refreshButton ) {
						$this.prop( 'disabled', true );
					}
				},
			} ).done( function() {
				Give.fn.loader( $licensesContainer, { show: false } );
				$this.text( $this.attr( 'data-activate' ) );
			} );
		} );
	} );

	/**
	 * Add-on upGive.fn.loader and activator.
	 */
	$( document ).ready( function() {
		const $container = $( '.give-upload-addon-form-wrap' ),
			  $form = $( 'form', $container ),
			  $file = $( 'input[type="file"]', $form ),
			  $activateBtnContainer = $( '.give-activate-addon-wrap', $form ),
			  $activateBtn = $( 'button', $form ),
			  $noticeContainer = $( '.give-addon-upload-notices', $form ),
			  $licensesContainer = $( '#give-licenses-container' );

		/**
		 * File drop handler
		 */
		$container.on( 'drop', function( e ) {
			e.stopPropagation();
			e.preventDefault();

			$( this ).removeClass( 'give-dropzone-active' );

			const file = e.originalEvent.dataTransfer.files,
				fd = new FormData();

			fd.append( 'file', file[ 0 ] );

			giveUploadData( fd );
		} );

		/**
		 * Drag over add classes for CSS
		 */
		$form.on( 'dragover', function( e ) {
			$( this ).addClass( 'give-dropzone-active' );
		} ).on( 'dragleave', function( e ) {
			$( this ).removeClass( 'give-dropzone-active' );
		} );

		/**
		 * Allow dismissing upload notices for the upload add-on widget.
		 */
		$noticeContainer.on( 'click', $( '.notice-dismiss', $noticeContainer ), function( event ) {
			$noticeContainer.empty().hide();
			$form.removeClass( 'give-dropzone-active' );
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
		 * Send AJAX request and upload file
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
					$noticeContainer.show();
					Give.fn.loader( $container, { loadingText: Give.fn.getGlobalVar( 'loader_translation' ).uploading } );
				},
				success: function( response ) {
					let errorMsg;

					if ( true === response.success ) {
						$noticeContainer.hide();
						$activateBtnContainer.show();
						$activateBtn.attr( 'data-pluginPath', response.data.pluginPath );
						$activateBtn.attr( 'data-pluginName', response.data.pluginName );
						$activateBtn.attr( 'data-nonce', response.data.nonce );
						$licensesContainer.html( response.data.licenseSectionHtml );

						return;
					}

					if (
						response.data.hasOwnProperty( 'errorMsg' ) &&
						response.data.errorMsg
					) {
						errorMsg = response.data.errorMsg;
					} else {
						errorMsg = response.data.error;
					}

					$noticeContainer.html( Give.notice.fn.getAdminNoticeHTML( errorMsg, 'error' ) );
				},
			} ).always( function() {
				Give.fn.loader( $container, { show: false } );
			} );
		}

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
					$noticeContainer.show();
					$activateBtn.text( $activateBtn.attr( 'data-activating' ) );
				},
				success: function( response ) {
					if ( true === response.success ) {
						const msg = give_addon_var.notices.addon_activated.replace( '{pluginName}', $activateBtn.attr( 'data-pluginName' ) );
						$noticeContainer.show();
						$noticeContainer.html( Give.notice.fn.getAdminNoticeHTML( msg, 'success' ) );
						$licensesContainer.html( response.data.licenseSectionHtml );

						return;
					}

					if (
						response.data.hasOwnProperty( 'errorMsg' ) &&
						response.data.errorMsg
					) {
						$noticeContainer.html( Give.notice.fn.getAdminNoticeHTML( response.data.errorMsg, 'error' ) );
					} else {
						Give.notice.fn.getAdminNoticeHTML( give_addon_var.notices.addon_activation_error, 'error' );
					}
				},
			} ).always( function() {
				$activateBtn.text( $activateBtn.attr( 'data-activate' ) );
				$activateBtnContainer.hide();
			} );
		} );
	} );
}( jQuery ) );
