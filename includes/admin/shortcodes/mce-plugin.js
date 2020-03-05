( function( tinymce ) {
	tinymce.PluginManager.add(
		'give_shortcode',
		function( editor ) {
			editor.addCommand(
				'Give_Shortcode',
				function() {
					if ( window.scForm ) {
						window.scForm.open( editor.id );
					}
				}
			);
		}
	);
}( window.tinymce ) );
