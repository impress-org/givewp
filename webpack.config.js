const webpack = require( 'webpack' );
const CopyWebpackPlugin = require( 'copy-webpack-plugin' );
const path = require( 'path' );
const ExtractTextPlugin = require( 'extract-text-webpack-plugin' );
const inProduction = ('production' === process.env.NODE_ENV);
const BrowserSyncPlugin = require( 'browser-sync-webpack-plugin' );
const ImageminPlugin = require( 'imagemin-webpack-plugin' ).default;
const CleanWebpackPlugin = require( 'clean-webpack-plugin' );

// Webpack config.
const config = {
	entry: {
		'admin': [ './assets/src/js/admin/admin.js', './assets/src/css/admin/give-admin.scss' ],
		'give': [ './assets/src/js/frontend/give.js', './assets/src/css/frontend/give-frontend.scss' ],
	},
	// Tell webpack where to output.
	output: {
		path: path.resolve( __dirname, './assets/dist/' ),
		filename: (inProduction ? 'js/[name].min.js' : 'js/[name].js')
	},
	// Ensure modules like magnific know jQuery is external (loaded via WP).
	externals: {
		$: 'jQuery',
		jquery: 'jQuery',
	},
	devtool: 'source-map',
	module: {
		rules: [
			// Use Babel to compile JS.
			{
				test: /\.js$/,
				exclude: /node_modules/,
				loaders: [
					'babel-loader',
				]
			},
			// Expose accounting.js for plugin usage.
			{
				test: require.resolve( 'accounting' ),
				loader: 'expose-loader?accounting'
			},
			// SASS to CSS.
			{
				test: /\.scss$/,
				use: ExtractTextPlugin.extract( {
					use: [ {
						loader: 'css-loader',
						options: {
							sourceMap: true
						}
					}, {
						loader: 'postcss-loader',
						options: {
							sourceMap: true
						},
					}, {
						loader: 'sass-loader',
						options: {
							sourceMap: true,
							outputStyle: 'production' === process.env.NODE_ENV ? 'compressed' : 'nested'
						}
					} ]
				} )
			},
			// Font files.
			{
				test: /\.(ttf|otf|eot|svg|woff(2)?)(\?[a-z0-9]+)?$/,
				use: [
					{
						loader: 'file-loader',
						options: {
							name: 'fonts/[name].[ext]',
							publicPath: '../'
						}
					}
				]
			},
			// Image files.
			{
				test: /\.(png|jpe?g|gif|svg)$/,
				use: [
					{
						loader: 'file-loader',
						options: {
							name: 'images/[name].[ext]',
							publicPath: '../'
						}
					}
				]
			}
		]
	},
	// Plugins. Gotta have em'.
	plugins: [

		new ExtractTextPlugin( (inProduction ? 'css/[name].min.css' : 'css/[name].css') ),

		// Copy admin images and SVGs
		new CopyWebpackPlugin( [
			{ from: 'assets/src/images/admin', to: 'images/admin' },
		] ),

		new CleanWebpackPlugin( [ 'assets/dist' ] ),

		// Minify images.
		new ImageminPlugin( { test: /\.(jpe?g|png|gif|svg)$/i } ),

		// Setup browser sync. Note: don't use ".local" TLD as it will be very slow. We recommending using ".test"
		new BrowserSyncPlugin( {
			files: [
				'**/*.php'
			],
			host: 'localhost',
			port: 3000,
			proxy: 'give.test' // This is the proxy you should be using. If not ... (specify... wp-config?)
		} )
	]
};

// inProd?
if ( inProduction ) {
	config.plugins.push( new webpack.optimize.UglifyJsPlugin( { sourceMap: true } ) ); // Uglify JS.
	config.plugins.push( new webpack.LoaderOptionsPlugin( { minimize: true } ) ); // Minify CSS.
}

module.exports = config;
