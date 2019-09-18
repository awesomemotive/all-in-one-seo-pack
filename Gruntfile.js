module.exports = function(grunt) {

	// Project configuration.
	grunt.initConfig(
		{
			pkg: grunt.file.readJSON( 'package.json' ),
			files_php: [
			'*.php',
			'**/*.php',
			'!.git/**',
			'!vendor/**',
			'!node_modules/**',
			'!logs/**'
			],
			files_js: [
			'*.js',
			'**/*.js',
			'!*.min.js',
			'!**/*.min.js',
			'!.git/**',
			'!vendor/**',
			'!js/vendor/*.js',
			'!public/js/vendor/*.js',
			'!node_modules/**',
			'!logs/**',
			'!Gruntfile.js'
			],
			// https://www.npmjs.com/package/grunt-mkdir#the-mkdir-task
			mkdir: {
				logs: {
					options: {
						create: ['logs']
					}
				}
			},
			// https://www.npmjs.com/package/grunt-phpcs#php-code-sniffer-task
			phpcs: {
				options: {
					standard: 'phpcs.xml',
					reportFile: 'logs/phpcs.log',
					extensions: 'php'
				},
				src: [
				'<%= files_php %>'
				]
			},
			// https://www.npmjs.com/package/grunt-phpcbf#the-phpcbf-task
			phpcbf: {
				options: {
					standard: 'phpcs.xml',
					noPatch:false,
					extensions: 'php'
				},
				src: [
				'<%= files_php %>'
				]
			},
			// https://www.npmjs.com/package/phplint#grunt
			phplint: {
				options: {
					standard: 'phpcs.xml'
				},
				src: [
				'<%= files_php %>'
				]
			},
			// https://www.npmjs.com/package/jshint
			jshint: {
				options: {
					jshintrc:true,
					reporterOutput:'logs/jslogs.log'
				},
				all: [
				'<%= files_js %>'
				]
			},
			// https://www.npmjs.com/package/uglify
			uglify: {
				dev: {
					files: [{
						expand: true,
						src: ['js/*.js', '!js/*.min.js', 'js/**/*.js', '!js/**/*.min.js'],
						dest: ['.'],
						cwd: '.',
						rename: function (dst, src) {
							// To keep the source js files and make new files as `*.min.js`:
							return dst + '/' + src.replace( '.js', '.min.js' );
						}
					}]
				}
			},
			// https://www.npmjs.com/package/eslint
			eslint: {
				options: {
					outputFile:'logs/eslint.log'
				},
				target: [
					'<%= files_js %>'
				]
			},
			cssmin: {
				dev: {
					files: [{
						expand: true,
						src: ['css/*.css', 'css/!*.min.css', 'css/**/*.css', '!css/**/*.min.css'],
						dest: '.',
						cwd: '.',
						ext: '.min.css'
					}]
				}
			},
			csslint: {
				options : {
					"order-alphabetical" : false,
					"ids" : false,
					"important" : false,
					"adjoining-classes" : false,
					"duplicate-properties" : false,
					"universal-selector" : false,
					"floats" : false,
					"unique-headings" : false,
					"font-sizes" : false,
					"overqualified-elements" : false,
					"box-model" : false,
					"qualified-headings" : false,
					"zero-units" : false,
					"fallback-colors" : false,
					"duplicate-background-images" : false,
					"display-property-grouping" : false,
					"regex-selectors" : false,
					"unqualified-attributes" : false,
					"outline-none" : false
				},
				src: ['css/*.css', 'css/*.min.css', 'css/**/*.css', 'css/**/*.min.css']
			}
		}
	);

	// Load the plugins
	grunt.loadNpmTasks( 'grunt-mkdir' );
	grunt.loadNpmTasks( 'grunt-phpcbf' );
	grunt.loadNpmTasks( 'grunt-phpcs' );
	grunt.loadNpmTasks( 'grunt-phplint' );
	grunt.loadNpmTasks( 'grunt-contrib-jshint' );
	grunt.loadNpmTasks( 'grunt-contrib-uglify' );
	grunt.loadNpmTasks( 'grunt-eslint' );
	grunt.loadNpmTasks( 'grunt-contrib-cssmin' );
	grunt.loadNpmTasks( 'grunt-contrib-csslint' );

	// Default task(s).
	grunt.registerTask( 'default', ['mkdir', 'phpcbf', 'phpcs', 'phplint', 
	'jshint', 'eslint', 'uglify', 'csslint', 'cssmin'] );

};
