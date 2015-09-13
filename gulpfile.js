/**
 *  Give Gulp File
 *
 *  @description: Used for automating development tasks such as minifying files, compiling SASS and live-reload; using Gulp.js
 */

/* Modules (Can be installed with npm install command using package.json)
 ------------------------------------- */
var autoprefixer = require( 'gulp-autoprefixer' ),
	bower        = require( 'gulp-bower' ),
	bowerMain    = require( 'main-bower-files' ),
	concat       = require( 'gulp-concat' ),
	del          = require( 'del' ),
	filter       = require( 'gulp-filter' ),
	gulp         = require( 'gulp' ),
	gutil        = require( 'gulp-util' ),
	livereload   = require( 'gulp-livereload' ),
	minifyCSS    = require( 'gulp-minify-css' ),
	notify       = require( 'gulp-notify' ),
	plumber      = require( 'gulp-plumber' ),
	rename       = require( 'gulp-rename' ),
	sass         = require( 'gulp-sass' ),
	sourcemaps   = require( 'gulp-sourcemaps' ),
	uglify       = require( 'gulp-uglify' ),
	watch        = require( 'gulp-watch' );

/* Paths
 ------------------------------------- */
var source_paths = {
	admin_styles    : ['./assets/scss/**/give-admin.scss'],
	frontend_styles : ['./assets/scss/**/give-frontend.scss'],
	plugin_styles   : ['./assets/scss/**/*.scss'],
	scripts         : ['./assets/js/**/*.js', '!./assets/js/**/*.min.js'],
	frontend_scripts: [
		'./assets/js/plugins/accounting.min.js',
		'./assets/js/plugins/float-labels.min.js',
		'./assets/js/plugins/jquery.blockUI.min.js',
		'./assets/js/plugins/jquery.magnific-popup.min.js',
		'./assets/js/plugins/jquery.payment.min.js',
		'./assets/js/plugins/jquery.qtip.min.js',
		'./assets/js/frontend/*.min.js' //Frontend scripts need to be loaded last
	]
};

/* Admin SCSS Task
 ------------------------------------- */
gulp.task( 'admin_styles', function () {
	return gulp.src( source_paths.admin_styles )
		.pipe( sourcemaps.init() )
		.pipe( sass( {
			errLogToConsole: true
		} ) )
		.pipe( autoprefixer() ) //run this after sass!
		.pipe( rename( 'give-admin.css' ) )
		.pipe( sourcemaps.write( '.' ) )
		.pipe( gulp.dest( './assets/css' ) )
		.pipe( rename( 'give-admin.min.css' ) )
		.pipe( minifyCSS() )
		.pipe( sourcemaps.write() )
		.pipe( gulp.dest( './assets/css' ) )
		.pipe( livereload() )
		.pipe( notify( {
			message: 'Admin styles task complete!',
			onLast : true //only notify on completion of task
		} ) );
} );

/* Frontend SCSS Task
 ------------------------------------- */
gulp.task( 'frontend_styles', function () {
	return gulp.src( source_paths.frontend_styles )
		.pipe( sourcemaps.init() ) //start up sourcemapping
		.pipe( sass( {
			errLogToConsole: true
		} ) ) //compile SASS; ensure any errors don't stop gulp watch
		.pipe( autoprefixer() ) //add prefixes for older browsers, run this after sass!
		.pipe( rename( 'give.css' ) ) //rename for our main un-minfied file
		.pipe( sourcemaps.write( '.' ) ) //write SCSS source maps to the appropriate plugin dir
		.pipe( gulp.dest( './templates' ) ) //place compiled file in appropriate directory
		.pipe( rename( 'give.min.css' ) ) //rename for our minified version
		.pipe( minifyCSS() ) //actually minify the file
		.pipe( sourcemaps.write( '.' ) ) //write SCSS source maps to the appropriate plugin dir
		.pipe( gulp.dest( './templates' ) ) //place the minified compiled file
		.pipe( livereload() ) //reload browser
		.pipe( notify( {
			message: 'Frontend styles task complete!',
			onLast : true //notify developer: only notify on completion of task (prevents multiple notifications per file)
		} ) );
} );

/* Concatenate & Minify JS
 ------------------------------------- */
gulp.task( 'scripts', function () {
	return gulp.src( source_paths.scripts )
		.pipe( uglify( {
			preserveComments: 'all'
		} ) )
		.pipe( rename( {suffix: ".min"} ) )
		.pipe( gulp.dest( 'assets/js' ) )
		.pipe( notify( {
			message: 'Scripts task complete!',
			onLast : true //only notify on completion of task (prevents multiple notifications per file)
		} ) )
		.pipe( livereload() );
} );

gulp.task( 'concat_scripts', function ( cb ) {
	del( [
		'assets/js/frontend/give.all.min.js'
	]).then( function () {
		return gulp.src( source_paths.frontend_scripts )
			.pipe( concat( 'give.all.min.js' ) ) //Add all compressed frontend JS scripts into one minified file for production
			.pipe( gulp.dest( 'assets/js/frontend' ) )
			.pipe( notify( {
				message: 'Concat scripts task complete!',
				onLast : true //only notify on completion of task (prevents multiple notifications per file)
			} ) )
	} );
} );

/* Watch Files For Changes
 ------------------------------------- */
gulp.task( 'watch', function () {

	//Start up livereload on this biz
	livereload.listen();

	//Add watching on Admin SCSS-files
	gulp.watch( 'assets/scss/admin/*.scss', ['admin_styles'] );

	//Add watching on Frontend SCSS-files
	gulp.watch( 'assets/scss/frontend/*.scss', ['frontend_styles'] );

	//Add watching on JS files
	gulp.watch( source_paths.scripts, ['scripts', 'concat_scripts'] );

	//Add watching on template-files
	gulp.watch( 'templates/*.php', function () {
		livereload(); //and reload when changed
	} );
} );

/* Handle errors elegantly with gulp-notify
 ------------------------------------- */
var onError = function ( err ) {
	gutil.log( '======= ERROR. ========\n' );
	notify.onError( "ERROR: " + err.plugin )( err ); // for growl
	gutil.beep();
	this.end();
};

/* Default Gulp task
 ------------------------------------- */
gulp.task( 'default', function () {

	var overrides = {
		"chosen"         : { main: ['chosen.jquery.js'] },
		"float-labels.js": { main: ['dist/float-labels.js', 'src/scss/float-labels.scss'] },
		"Flot"           : { main: ['jquery.flot.js', 'jquery.flot.time.js'] },
		"flot.orderbars" : { main: ['js/jquery.flot.orderBars.js'] },
		"jquery"         : { ignore: true },
		"magnific-popup" : { main: ['dist/jquery.magnific-popup.js', 'src/css/*.scss'] },
		"qtip2"          : { main: ['jquery.qtip.js', 'jquery.qtip.css'], dependencies: null }
	};

	// run bower install
	bower()
		.pipe( gulp.dest( 'bower' ) )
		.on( 'end', function () {

			// copy bower plugin scripts to assets
			gulp.src( bowerMain( { overrides: overrides } ) )
				.pipe( filter( ['*.js'] ) )
				.pipe( gulp.dest( 'assets/js/plugins/' ) )
				.on( 'end', function () {

					// copy bower plugin (s)css to assets
					gulp.src( bowerMain( { overrides: overrides } ) )
						.pipe( filter( ['*.css', '*.scss'] ) )
						.pipe( rename( function ( path ) {
							path.extname  = '.scss';
							if ( path.basename === 'main' ) {
								path.basename = 'magnific-popup';
							} else if ( path.basename === 'jquery.qtip' ) {
								path.basename = 'qtip';
							}
						} ) )
						.pipe( gulp.dest( 'assets/scss/plugins/' ) )
						.on( 'end', function () {

							// ... and now we run all the tasks!
							gulp.start( 'watch', 'admin_styles', 'frontend_styles', 'scripts', 'concat_scripts' );
						} );
				} );
		} );
} );
