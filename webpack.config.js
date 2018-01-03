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
	output: {
		path: path.resolve( __dirname, './assets/dist/' ),
		filename: (inProduction ? 'js/[name].min.js' : 'js/[name].js')
	},
	externals: {
		$: 'jQuery',
		jquery: 'jQuery',
	},
	resolve: {
		modules: [
			__dirname,
			'node_modules',
		],
	},
	devtool: 'source-map',
	module: {
		rules: [
			{
				test: /\.js$/,
				exclude: /node_modules/,
				loaders: [
					'babel-loader',
				]
			},
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
			{
				test: /\.(ttf|otf|eot|svg|woff(2)?)(\?[a-z0-9]+)?$/,
				loader: 'file-loader?name=fonts/[name].[ext]'
			},
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
		} )
	]
};

if ( inProduction ) {
	config.plugins.push( new webpack.optimize.UglifyJsPlugin( { sourceMap: true } ) ); // Uglify JS.
	config.plugins.push( new webpack.LoaderOptionsPlugin( { minimize: true } ) ); // Minify CSS.
}

module.exports = config;
