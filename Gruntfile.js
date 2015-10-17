module.exports = function ( grunt ) {

// Load multiple grunt tasks using globbing patterns
	require( 'load-grunt-tasks' )( grunt );

// Project configuration.
	grunt.initConfig( {
		pkg: grunt.file.readJSON( 'package.json' ),

		checktextdomain: {
			options: {
				text_domain       : 'give',
				create_report_file: true,
				keywords          : [
					'__:1,2d',
					'_e:1,2d',
					'_x:1,2c,3d',
					'esc_html__:1,2d',
					'esc_html_e:1,2d',
					'esc_html_x:1,2c,3d',
					'esc_attr__:1,2d',
					'esc_attr_e:1,2d',
					'esc_attr_x:1,2c,3d',
					'_ex:1,2c,3d',
					'_n:1,2,3,4d',
					'_nx:1,2,4c,5d',
					'_n_noop:1,2,3d',
					'_nx_noop:1,2,3c,4d',
					' __ngettext:1,2,3d',
					'__ngettext_noop:1,2,3d',
					'_c:1,2d',
					'_nc:1,2,4c,5d'
				]
			},
			files  : {
				src   : [
					'**/*.php', // Include all files
					'!node_modules/**', // Exclude node_modules/
					'!build/.*'// Exclude build/
				],
				expand: true
			}
		},

		makepot: {
			target: {
				options: {
					domainPath     : '/languages/',    // Where to save the POT file.
					exclude        : ['includes/libraries/.*', '.js'],
					mainFile       : 'give.php',    // Main project file.
					potFilename    : 'give.pot',    // Name of the POT file.
					potHeaders     : {
						poedit                 : true,                 // Includes common Poedit headers.
						'x-poedit-keywordslist': true // Include a list of all possible gettext functions.
					},
					type           : 'wp-plugin',    // Type of project (wp-plugin or wp-theme).
					updateTimestamp: true,    // Whether the POT-Creation-Date should be updated without other changes.
					processPot     : function ( pot, options ) {
						pot.headers['report-msgid-bugs-to'] = 'https://givewp.com/';
						pot.headers['last-translator'] = 'WP-Translations (http://wp-translations.org/)';
						pot.headers['language-team'] = 'WP-Translations <wpt@wp-translations.org>';
						pot.headers['language'] = 'en_US';
						var translation, // Exclude meta data from pot.
							excluded_meta = [
								'Plugin Name of the plugin/theme',
								'Plugin URI of the plugin/theme',
								'Author of the plugin/theme',
								'Author URI of the plugin/theme'
							];
						for ( translation in pot.translations[''] ) {
							if ( 'undefined' !== typeof pot.translations[''][translation].comments.extracted ) {
								if ( excluded_meta.indexOf( pot.translations[''][translation].comments.extracted ) >= 0 ) {
									console.log( 'Excluded meta: ' + pot.translations[''][translation].comments.extracted );
									delete pot.translations[''][translation];
								}
							}
						}
						return pot;
					}
				}
			}
		},

		dirs: {
			lang: 'languages'
		},

		potomo: {
			dist: {
				options: {
					poDel: true
				},
				files  : [{
					expand: true,
					cwd   : '<%= dirs.lang %>',
					src   : ['*.po'],
					dest  : '<%= dirs.lang %>',
					ext   : '.mo',
					nonull: true
				}]
			}
		}


	} );

// Default task. - grunt makepot
	grunt.registerTask( 'default', 'makepot' );

// Build task(s).
//	grunt.registerTask( 'build', ['clean', 'copy', 'compress'] );

};
