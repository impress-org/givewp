/* globals process, __dirname, module  */

/**
 * External dependencies
 */
const path = require( 'path' );
const CopyWebpackPlugin = require( 'copy-webpack-plugin' );
const MiniCSSExtractPlugin = require( 'mini-css-extract-plugin' );
const BrowserSyncPlugin = require( 'browser-sync-webpack-plugin' );
const ImageminPlugin = require( 'imagemin-webpack-plugin' ).default;
const CleanWebpackPlugin = require( 'clean-webpack-plugin' );
const WebpackRTLPlugin = require( 'webpack-rtl-plugin' );

const inProduction = ( 'production' === process.env.NODE_ENV );
const mode = inProduction ? 'production' : 'development';

const config = {
	mode,

	entry: {
		give: [ './assets/src/css/frontend/give-frontend.scss', './assets/src/js/frontend/give.js' ],
		'give-stripe': [ './assets/src/js/frontend/give-stripe.js' ],
		'give-stripe-sepa': [ './assets/src/js/frontend/give-stripe-sepa.js' ],
		'give-stripe-becs': [ './assets/src/js/frontend/give-stripe-becs.js' ],
		admin: [ './assets/src/css/admin/give-admin.scss', './assets/src/js/admin/admin.js' ],
		'admin-global': [ './assets/src/css/admin/give-admin-global.scss' ],
		'admin-setup': [ './assets/src/css/admin/setup.scss', './assets/src/js/admin/admin-setup.js' ],
		'babel-polyfill': '@babel/polyfill',
		gutenberg: './blocks/load.js',
		'admin-shortcode-button': [ './assets/src/css/admin/shortcodes.scss' ],
		'admin-shortcodes': './includes/admin/shortcodes/admin-shortcodes.js',
		'plugin-deactivation-survey': [ './assets/src/css/admin/plugin-deactivation-survey.scss', './assets/src/js/admin/plugin-deactivation-survey.js' ],
		'admin-add-ons': [ './assets/src/js/admin/admin-add-ons.js' ],
		'give-sequoia-template': [ './src/Views/Form/Templates/Sequoia/assets/css/form.scss', './src/Views/Form/Templates/Sequoia/assets/js/form.js' ],
		'admin-reports': [ './assets/src/js/admin/reports/app.js' ],
		'admin-reports-widget': [ './assets/src/js/admin/reports/widget.js' ],
		'admin-widgets': [ './assets/src/js/admin/admin-widgets.js', './assets/src/css/admin/widgets.scss' ],
		'paypal-commerce': [ './assets/src/js/frontend/paypal-commerce/index.js' ],
		'admin-paypal-commerce': [ './assets/src/css/admin/paypal-commerce.scss' ],
		'admin-onboarding-wizard': [ './assets/src/js/admin/onboarding-wizard/index.js' ],
		'multi-form-goal-block': [ './src/MultiFormGoals/resources/css/common.scss' ],
		'donor-dashboards-app': [ './src/DonorDashboards/resources/js/app/index.js' ],
		'donor-dashboards-block': [ './src/DonorDashboards/resources/js/block/index.js' ],
		'give-log-list-table-app': [ './src/Log/Admin/index.js' ],
		'give-migrations-list-table-app': [ './src/MigrationLog/Admin/index.js' ],
		'give-date-field': [ './assets/src/js/plugins/give-date-field.js' ],
		'jquery-ui': [ './assets/src/css/plugins/jquery-ui-fresh.scss' ],
		'jquery-ui-timepicker': [ './assets/src/css/plugins/jquery-ui-timepicker.scss' ],
	},
	output: {
		path: path.join( __dirname, './assets/dist/' ),
		filename: 'js/[name].js',
	},

	// Ensure modules like magnific know jQuery is external (loaded via WP).
	externals: {
		$: 'jQuery',
		jquery: 'jQuery',
		lodash: 'lodash',
		'@wordpress/i18n' : 'wp.i18n',
	},
	devtool: ! inProduction ? 'source-map' : '',
	module: {
		rules: [

			// Use Babel to compile JS.
			{
				test: /\.js$/,
				exclude: /node_modules/,
				loader: 'babel-loader',
			},

			// Expose accounting.js for plugin usage.
			{
				test: require.resolve( 'accounting' ),
				use: [
					{
						loader: 'expose-loader',
						options: 'accounting',
					},
				],
			},

			// Create RTL styles.
			{
				test: /\.css$/,
				exclude: /\.module\.css$/,
				use: [
					//MiniCSSExtractPlugin.loader,
					'style-loader',
					'css-loader',
				],
			},

			{
				test: /\.module\.css$/,
				use: [
					//MiniCSSExtractPlugin.loader,
					'style-loader',
					{
						loader: 'css-loader',
						options: {
							sourceMap: true,
							modules: true,
							localIdentName: '[local]__[hash:base64:5]',
						},
					},
				],
			},

			// SASS to CSS.
			{
				test: /\.scss$/,
				exclude: /\.module\.scss$/,
				use: [
					MiniCSSExtractPlugin.loader,
					{
						loader: 'css-loader',
						options: {
							sourceMap: true,
						},
					},
					{
						loader: 'sass-loader',
						options: {
							sourceMap: true,
							outputStyle: ( inProduction ? 'compressed' : 'expanded' ),
						},
					} ],
			},

			// SASS to CSS.
			{
				test: /\.module\.scss$/,
				use: [
					'style-loader',
					{
						loader: 'css-loader',
						options: {
							sourceMap: true,
							modules: true,
							localIdentName: '[local]__[hash:base64:5]',
						},
					},
					{
						loader: 'sass-loader',
						options: {
							sourceMap: true,
							outputStyle: ( inProduction ? 'compressed' : 'expanded' ),
						},
					} ],
			},

			// Font files.
			{
				test: /\.(ttf|otf|eot|woff(2)?)(\?[a-z0-9]+)?$/,
				use: [
					{
						loader: 'file-loader',
						options: {
							name: 'fonts/[name].[ext]',
							publicPath: '../',
						},
					},
				],
			},

			// Image files.
			{
				test: /\.(png|jpe?g|gif|svg)$/,
				use: [
					{
						loader: 'file-loader',
						options: {
							name: 'images/[name].[ext]',
							publicPath: '../',
						},
					},
				],
			},
		],
	},

	// Plugins. Gotta have em'.
	plugins: [

		// Removes the "dist" folder before building.
		new CleanWebpackPlugin( [ 'assets/dist' ] ),

		new MiniCSSExtractPlugin( {
			filename: 'css/[name].css',
		} ),

		new CopyWebpackPlugin( [ { from: 'assets/src/images', to: 'images' } ] ),

		// Move supported tcpdf fonts to vendor folder.
		new CleanWebpackPlugin( [ 'vendor/tecnickcom/tcpdf/fonts/*' ] ),

		new CopyWebpackPlugin( [ { from: 'assets/src/tcpdf-fonts/', to: '../../vendor/tecnickcom/tcpdf/fonts/' } ] ),

		// Setup browser sync. Note: don't use ".local" TLD as it will be very slow. We recommending using ".test".
		new BrowserSyncPlugin( {
			files: [
				'**/*.php',
			],
			host: 'localhost',
			port: 3000,
			proxy: 'give.test',
		} ),
	],

	resolve: {
		alias: {
			'@givewp/components': path.resolve( __dirname, 'src/Views/Components/' ),
		},
	},
};

if ( inProduction ) {
	// Create RTL css.
	config.plugins.push( new WebpackRTLPlugin( {
		suffix: '-rtl',
		minify: true,
	} ) );

	// Minify images.
	// Must go after CopyWebpackPlugin above: https://github.com/Klathmon/imagemin-webpack-plugin#example-usage
	config.plugins.push( new ImageminPlugin( { test: /\.(jpe?g|png|gif|svg)$/i } ) );
}

module.exports = config;

