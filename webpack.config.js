const webpack = require( 'webpack' );
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
		// window: 'Window',
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
				loader: 'file-loader?name=fonts/[name].[ext]'
			},
			// Image files.
			{
				test: /\.(png|jpg|gif)$/,
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
	plugins: [
		new ExtractTextPlugin( (inProduction ? 'css/[name].min.css' : 'css/[name].css') ),
		new CleanWebpackPlugin( [ 'assets/dist' ] ),
		new ImageminPlugin( { test: /\.(jpe?g|png|gif|svg)$/i } ),
		new BrowserSyncPlugin( {
			files: [
				'**/*.php'
			],
			host: 'localhost',
			port: 3000,
			proxy: 'givetest.local'
		} ),
	]
};

if ( inProduction ) {
	config.plugins.push( new webpack.optimize.UglifyJsPlugin( { sourceMap: true } ) ); // Uglify JS.
	config.plugins.push( new webpack.LoaderOptionsPlugin( { minimize: true } ) ); // Minify CSS.
}

module.exports = config;
