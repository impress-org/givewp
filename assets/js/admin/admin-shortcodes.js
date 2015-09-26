/*!
 * Give Admin Shortcodes JS
 *
 * @description: The Give Admin Shortcode script
 * @package:     Give
 * @subpackage:  Assets/JS
 * @copyright:   Copyright (c) 2015, WordImpress
 * @license:     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 */

/* global ajaxurl, tinymce */

var jq = jQuery.noConflict();

var giveForm = {

	open: function( editor_id )
	{
		var editor = tinymce.get( editor_id );

		if( editor ) {

			var data = {
				action: 'give_form_ajax'
			};

			jq.post( ajaxurl, data, function( response )
			{
				editor.windowManager.open({
					title    : response.title,
					body     : response.body,
					buttons  : [
						{
							text    : response.ok,
							classes : 'primary give-primary',
							onclick : function( e )
							{
								// Get the top most window object
								var win = editor.windowManager.getWindows()[0];

								// Do some validation voodoo
								if( win.find('#id')[0].state.data.value === '' ) {
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
					onsubmit : function( e )
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
					onclose : function( e )
					{
						var tmp = jq( '#tmp_textarea' );

						if( tmp.length ) {
							tinymce.get( 'tmp_textarea' ).remove();
							tmp.remove();
						}
					}
				});
			});
		}
	}
};

jq( function( $ )
{
	$( document ).on( 'click', 'button.give-shortcode-button', function( e )
	{
		e.preventDefault();

		if( !tinymce.get( window.wpActiveEditor ) ) {

			if( !$( '#tmp_textarea' ).length ) {

				$( 'body' ).append( '<textarea id="tmp_textarea" style="display: none;" />' );

				tinymce.init({
					mode     : "exact",
					elements : "tmp_textarea",
					plugins  : ['give_form','wplink']
				});
			}

			setTimeout( function() { tinymce.execCommand( 'Give_Form' ); }, 200 );
		}
		else {
			tinymce.execCommand( 'Give_Form' );
		}
	});
});
