module.exports = function(grunt) {

  // Project configuration.
  grunt.initConfig({
    pkg: grunt.file.readJSON('package.json'),
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
            '!node_modules/**',
            '!logs/**'
    ],
    mkdir: {
        logs: {
            options: {
                create: ['logs']
            }
        }
    },
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
    phpcbf: {
        options: {
            standard: 'phpcs.xml',
        },
        src: [
            '<%= files_php %>'
        ]
    },
    phplint: {
        options: {
            standard: 'phpcs.xml',
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
                return dst + '/' + src.replace('.js', '.min.js');
              }
          }]
        }
     }
  });

  // Load the plugins
  grunt.loadNpmTasks('grunt-mkdir');
  grunt.loadNpmTasks('grunt-phpcbf');
  grunt.loadNpmTasks('grunt-phpcs');
  grunt.loadNpmTasks('grunt-phplint');
  grunt.loadNpmTasks('grunt-contrib-jshint');
  grunt.loadNpmTasks('grunt-contrib-uglify');

  // Default task(s).
  grunt.registerTask('default', ['mkdir', 'phpcbf', 'phpcs', 'phplint', 'jshint', 'uglify']);

};


