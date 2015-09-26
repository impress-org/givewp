( function( tinymce ) {

	tinymce.PluginManager.add( 'give_form', function( editor )
	{
		editor.addCommand( 'Give_Form', function()
		{
			window.giveForm && window.giveForm.open( editor.id );
		});
	});

})( window.tinymce );
