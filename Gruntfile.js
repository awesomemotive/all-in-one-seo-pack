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
					// severity: 1
				},
				src: [
				'<%= files_php %>'
				]
			},
			// https://www.npmjs.com/package/grunt-phpcbf#the-phpcbf-task
			phpcbf: {
				options: {
					standard: 'phpcs.xml',
					// noPatch: true,
					extensions: 'php',
					// severity: 0,
					warningSeverity: 0

				},
				src: [
					'<%= files_php %>'
				]
			},
			phplint: {
				options: {
					standard: 'phpcs.xml'
				},
				src: [
				'<%= files_php %>'
				]
			},
			jshint: {
				options: {
					jshintrc:true,
					reporterOutput:'logs/jslogs.log'
				},
				all: [
				'<%= files_js %>'
				]
			},
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
			eslint: {
				options: {
					outputFile:'logs/eslint.log'
				},
				target: [
					'<%= files_js %>'
				]
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

	// Default task(s).
	grunt.registerTask( 'default', ['mkdir', 'phpcs', 'phpcbf', 'phplint', 'jshint', 'eslint', 'uglify'] );
	grunt.registerTask( 'dev', ['mkdir', 'phpcbf', 'phpcs', 'phplint', 'jshint', 'eslint', 'uglify'] );

};
