/* globals jQuery, ajaxurl, give_addon_var */

( function( $ ) {
	$( document ).ready( function() {
		const $container = $( '#give-license-activator-wrap' ),
			  $form = $( 'form', $container ),
			  $license = $('input[name="give_license_key"]', $container ),
			  $submitBtn = $( 'input[type="submit"]', $form ),
			  $noticeContainer = $( '.give-notices', $container );

		$license.on( 'change', function(){
			if( ! $(this).val().trim() ) {
				$submitBtn.prop( 'disabled', true );
				return;
			}

			$submitBtn.prop( 'disabled', false );
		}).change();

		$form.on( 'submit', function() {
			let license  = $license.val().trim(),
				action   = 'give_get_license_info',
				_wpnonce = $('input[name="give_license_activator_nonce"]', $(this)).val().trim();

			// Remove all errors.
			$noticeContainer.empty();

			if ( ! license ) {
				$noticeContainer.html( `<div class="give-notice notice notice-error"><p>${give_addon_var.notices.invalid_license}</p></div>` );
				return false;
			}

			$.ajax( {
				url: ajaxurl,
				method: 'POST',
				data: {
					action,
					license,
					_wpnonce
				},
				beforeSend: function(){
					$submitBtn.val( $submitBtn.data( 'activating' ) );
				},
				success: function( response ) {
					$submitBtn.val( $submitBtn.data( 'activate' ) );

					if( true === response.success ){
						if(
							response.data.hasOwnProperty( 'download_file' )
							&& response.data.download_file
						) {
							$noticeContainer.html( `<div class="give-notice notice notice-success"><p>${give_addon_var.notices.download_file.replace( '{link}', response.data.download_file )}</p></div>` );
						}else{
							$noticeContainer.html( `<div class="give-notice notice notice-error"><p>${give_addon_var.notices.invalid_license}</p></div>` );
						}

						return;
					}

					if(
						response.data.hasOwnProperty( 'errorMsg' )
						&& response.data.errorMsg
					) {
						$noticeContainer.html( `<div class="give-notice notice notice-error"><p>${response.data.errorMsg}</p></div>` );
					}else {
						$noticeContainer.html( `<div class="give-notice notice notice-error"><p>${give_addon_var.notices.invalid_license}</p></div>` );
					}
				},
			} ).always(function(){
				$noticeContainer.empty();
				$submitBtn.val( $submitBtn.data( 'activate' ) );
			});

			return false;
		} );
	} );

	$( document ).ready( function() {
		const $container = $( '#give-addon-uploader-wrap' ),
			$form = $( 'form', $container ),
			$file = $( 'input[type="file"]', $form ),
			$noticeContainer = $( '.give-notices', $container );

		// Drop
		// @todo: add validation to upload only zip files
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
						$noticeContainer.html( `<div class="give-notice notice notice-success"><p>${ give_addon_var.notices.uploaded }</p></div>` );
						return;
					}

					$noticeContainer.html( `<div class="give-notice notice notice-error"><p>${ response.data.errorMsg }</p></div>` );
				},
			} );
		}
	} );
}( jQuery ) );
