const wpTextdomain = require( 'wp-textdomain' );

wpTextdomain( process.argv[ 2 ], {
	domain: 'ADDON_TEXTDOMAIN',
	fix: true,
} );
