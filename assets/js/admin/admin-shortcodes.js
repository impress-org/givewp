/* global ajaxurl, jQuery, scShortcodes, tinymce */

var jq = jQuery.noConflict();

var scShortcode;

var scForm = {

	open: function( editor_id )
	{
		var editor = tinymce.get( editor_id );

		if( !editor ) {
			return;
		}

		var i, len, data, required, valid, win;

		data = {
			action    : 'give_shortcode',
			shortcode : scShortcode
		};

		jq.post( ajaxurl, data, function( response )
		{
			if( response.body.length === 0 ) {
				window.send_to_editor( '[' + response.shortcode + ']' );

				scForm.destroy();

				return;
			}

			editor.windowManager.open({
				title   : response.title,
				body    : response.body,
				minWidth: 320,
				buttons : [
					{
						text    : response.ok,
						classes : 'primary sc-primary',
						onclick : function()
						{
							// Get the top most window object
							win = editor.windowManager.getWindows()[0];

							// Get the shortcode required field IDs
							required = scShortcodes[ scShortcode ];

							valid = true;

							// Do some validation voodoo
							for( len = required.length, i = 0; i < len; i++ ) {
								if( win.find( '#' + required[ i ] )[0].state.data.value === '' ) {
									valid = false;
								}
							}

							if( !valid ) {
								alert( response.alert );
							}
							else {
								win.submit();
							}
						}
					},
					{
						text    : response.close,
						onclick : 'close'
					},
				],
				onsubmit: function( e )
				{
					var attributes = '';

					for( var key in e.data ) {
						if( e.data.hasOwnProperty( key ) && e.data[ key ] !== '' ) {
							attributes += ' ' + key + '="' + e.data[ key ] + '"';
						}
					}

					// Insert shortcode into the WP_Editor
					window.send_to_editor( '[' + response.shortcode + attributes + ']' );
				},
				onclose: function()
				{
					scForm.destroy();
				}
			});
		});
	},

	destroy: function()
	{
		var tmp = jq( '#scTemp' );

		if( tmp.length ) {
			tinymce.get( 'scTemp' ).remove();
			tmp.remove();
		}
	}
};

jq( function( $ )
{
	var scOpen = function()
	{
		$( '#sc-button' ).addClass( 'active' );
		$( '#sc-menu' ).show();
	};

	var scClose = function()
	{
		$( '#sc-button' ).removeClass( 'active' );
		$( '#sc-menu' ).hide();
	};

	$( document ).on( 'click', function( e )
	{
		if( !$( e.target ).closest( '.sc-wrap' ).length ) {
			scClose();
		}
	});

	$( document ).on( 'click', '#sc-button', function( e )
	{
		e.preventDefault();

		if( $( this ).hasClass( 'active' ) ) {
			scClose();
		}
		else {
			scOpen();
		}
	});

	$( document ).on( 'click', '.sc-shortcode', function( e )
	{
		e.preventDefault();

		scShortcode = $( this ).attr( 'data-shortcode' );

		if( scShortcode ) {
			if( !tinymce.get( window.wpActiveEditor ) ) {

				if( !$( '#scTemp' ).length ) {

					$( 'body' ).append( '<textarea id="scTemp" style="display: none;" />' );

					tinymce.init({
						mode     : "exact",
						elements : "scTemp",
						plugins  : ['give_shortcode', 'wplink']
					});
				}

				setTimeout( function() { tinymce.execCommand( 'Give_Shortcode' ); }, 200 );
			}
			else {
				tinymce.execCommand( 'Give_Shortcode' );
			}

			setTimeout( function() { scClose(); }, 100 );
		}
		else {
			alert( 'No custom shortcodes have been registered!' );
		}
	});
});
