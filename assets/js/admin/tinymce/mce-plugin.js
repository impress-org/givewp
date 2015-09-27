/* global scForm */

( function( tinymce ) {

	tinymce.PluginManager.add( 'give_shortcode', function( editor )
	{
		editor.addCommand( 'Give_Shortcode', function()
		{
			window.scForm && window.scForm.open( editor.id );
		});
	});

})( window.tinymce );
