const mix = require( 'laravel-mix' );
const wpPot = require( 'wp-pot' );

mix
	.setPublicPath( 'public' )
	.sourceMaps( false )

	// admin assets
	.js( 'src/NextGen/resources/js/admin/give-next-gen-admin.js', 'public/js/' )
	.sass( 'src/NextGen/resources/css/admin/give-next-gen-admin.scss', 'public/css' )

	// public assets
	.js( 'src/NextGen/resources/js/frontend/give-next-gen.js', 'public/js/' )
	.sass( 'src/NextGen/resources/css/frontend/give-next-gen-frontend.scss', 'public/css' )

	// images
	.copy( 'src/NextGen/resources/images/*.{jpg,jpeg,png,gif}', 'public/images' );

mix.webpackConfig( {
	externals: {
		$: 'jQuery',
		jquery: 'jQuery',
	},
} );

if ( mix.inProduction() ) {
	wpPot( {
		package: 'Give - Next Gen',
		domain: 'give',
		destFile: 'languages/give.pot',
		relativeTo: './',
		bugReport: 'https://github.com/impress-org/give-next-gen/issues/new',
		team: 'GiveWP <info@givewp.com>',
	} );
}
