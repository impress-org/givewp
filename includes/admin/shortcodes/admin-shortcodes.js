/*!
 * Give Admin Shortcodes JS
 *
 * @description: The Give Admin Shortcode scripts. Only enqueued on the admin widgets screen; used to show shortcode dialogs, show/hide, and other functions
 * @package:     Give
 * @subpackage:  Assets/JS
 * @author:      Paul Ryley
 * @copyright:   Copyright (c) 2016, GiveWP
 * @license:     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since:       1.3.0
 */

/* global Give, ajaxurl, jQuery, scShortcodes, tinymce */

var scShortcode, scButton;


jQuery( function( $ ) {

	let doc = $( this );

	/**
	 * Show continue button title setting field only if display style is not All Fields.
	 */
	window.render_continue_button_title_field = function() {
		var selected_display_style = $('.mce-txt', '.mce-give-display-style').text(),
			expected_display_styles = [ '- Select -', 'All Fields' ];

		if( -1 !== $.inArray( selected_display_style, expected_display_styles ) ) {
			$('.mce-give-continue-button-title').closest('.mce-container' ).hide()
		} else {
			$('.mce-give-continue-button-title').closest('.mce-container' ).show()
		}
	};

	window.scForm = {

		open: function( editor_id ) {
			var editor = tinymce.get( editor_id );

			if ( ! editor ) {
				return;
			}

			var data, field, required, valid, win;

			data = {
				action    : 'give_shortcode',
				shortcode : scShortcode
			};

			$.post( ajaxurl, data, function( response ) {

				// what happens if response === false?
				if ( ! response.body ) {
					console.error( 'Bad AJAX response!' );
					return;
				}

				if ( response.body.length === 0 ) {
					window.send_to_editor( '[' + response.shortcode + ']' );

					scForm.destroy();

					return;
				}

				/**
				 * Render continue button title setting field on basis of display style value.
				 */
				$.each( response.body, function( index, item ){

					if( 'display_style' === item.name ) {
						response.body[index].onselect = function(){
							render_continue_button_title_field();
						};
					}
				});

				var popup = {
					title   : response.title,
					body    : response.body,
					classes: 'sc-popup',
					minWidth: 320,
					buttons : [ {
						text    : response.ok,
						classes : 'primary sc-primary',
						onclick : function() {
							// Get the top most window object
							win = editor.windowManager.getWindows()[0];

							// Get the shortcode required attributes
							required = scShortcodes[ scShortcode ];

							valid = true;

							// Do some validation voodoo
							for ( var id in required ) {
								if ( required.hasOwnProperty( id ) ) {

									field = win.find( '#' + id )[0];

									if ( typeof field !== 'undefined' && field.state.data.value === '' ) {

										valid = false;

										new Give.modal.GiveErrorAlert({
											modalContent:{
												desc: required[ id ],
												cancelBtnTitle: Give.fn.getGlobalVar('ok'),
											}
										}).render();

										break;
									}
								}
							}

							if ( valid ) {
								win.submit();
							}
						}
					},
						{
							text    : response.close,
							onclick : 'close'
					}, ],
					onsubmit: function( e ) {
						var attributes = '';

						for ( var key in e.data ) {
							if ( e.data.hasOwnProperty( key ) && e.data[ key ] !== '' ) {
								attributes += ' ' + key + '="' + e.data[ key ] + '"';
							}
						}

						// Insert shortcode into the WP_Editor
						window.send_to_editor( '[' + response.shortcode + attributes + ']' );
					},
					onclose: function() {
						scForm.destroy();
					},
					onopen: function() {

						// Hacky way to remove scrollbars when not necessary.
						let popup = $('.mce-sc-popup');
						popup.css({
							width: popup.width(),
							height: popup.height(),
							overflow: 'auto'
						});

						// Conditional fields.
						render_continue_button_title_field();
					}
				};

				// Change the buttons if server-side validation failed
				if ( response.ok.constructor === Array ) {
					popup.buttons[0].text    = response.ok[0];
					popup.buttons[0].onclick = 'close';
					delete popup.buttons[1];
				}

				editor.windowManager.open( popup );
			});
		},

		destroy: function() {
			var tmp = $( '#scTemp' );

			if ( tmp.length ) {
				tinymce.get( 'scTemp' ).remove();
				tmp.remove();
			}
		}
	};

	var scOpen = function() {
		scButton.addClass( 'active' ).parent().find( '.sc-menu' ).show();
	};

	var scClose = function() {
		if ( typeof scButton !== 'undefined' ) {
			scButton.removeClass( 'active' ).parent().find( '.sc-menu' ).hide();
		}
	};

	doc.on( 'click', function( e ) {
		if ( ! $( e.target ).closest( '.sc-wrap' ).length ) {
			scClose();
		}
	});

	doc.on( 'click', '.sc-button', function( e ) {
		e.preventDefault();

		scButton = $( this );

		if ( scButton.hasClass( 'active' ) ) {
			scClose();
		} else {
			scOpen();
		}
	});

	doc.on( 'click', '.sc-shortcode', function( e ) {
		e.preventDefault();

		// scShortcode is used by scForm to trigger the correct popup
		scShortcode = $( this ).attr( 'data-shortcode' );

		if ( scShortcode ) {
			if ( ! tinymce.get( window.wpActiveEditor ) ) {

				if ( ! $( '#scTemp' ).length ) {

					$( 'body' ).append( '<textarea id="scTemp" style="display: none;" />' );

					tinymce.init({
						mode     : "exact",
						elements : "scTemp",
						plugins  : ['give_shortcode', 'wplink']
					});
				}

				setTimeout( function() { tinymce.execCommand( 'Give_Shortcode' ); }, 200 );
			} else {
				tinymce.execCommand( 'Give_Shortcode' );
			}

			setTimeout( function() { scClose(); }, 100 );
		} else {
			console.warn( 'That is not a valid shortcode link.' );
		}
	});
});
