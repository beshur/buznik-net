module.exports = function(grunt) {
    grunt.initConfig({
        pkg: grunt.file.readJSON('package.json'),
        uglify: {
            //Run The Uglify Task For Javascript_Files
            uglify_js_files: {
                files: {
                    "dist/js/script.js":"js/build/*.js",
                }
            },
            // The Global Options
            options: {
                'mangle': {},
                'compress': {},
                'beautify': false,
                'expression': false,
                'report': 'max',
                'sourceMap': false,
                'sourceMapName': undefined,
                'sourceMapIn': undefined,
                'sourceMapIncludeSources': false,
                'enclose': undefined,
                'wrap': undefined,
                'exportAll': false,
                'preserveComments': undefined,
                'banner': '/*Uglify*/',
                'footer': ''
            }
        },//end Uglify Task;

        //the Sass Task 
        sass: {
            sass_my_files : {

                files : {
                    //"the Destination " : " the source files"
                    "dist/css/main.css" : "scss/main.scss",
                },
                options : {
                    "style":"compressed",
                    "precision":"7"
                },
            },
        },

        postcss: {
            options: {
                map: true, // inline sourcemaps
                processors: [
                    require('pixrem')(), // add fallbacks for rem units
                    require('autoprefixer')({browsers: 'last 2 versions'}), // add vendor prefixes
                    require('cssnano')() // minify the result
                ]
            },
            dist: {
                src: 'css/*.css'
            }
        },

        svgmin: {
            options: {
                plugins: [
                    {
                        removeViewBox: false
                    }, {
                        removeUselessStrokeAndFill: false
                    }
                ]
            },
            dist: {
                files: {
                    'img/shu.svg': 'svg/shu.svg'
                }
            }
        },

        imagemin: {
            dynamic: {                         // Another target
                files: [{
                    expand: true,                  // Enable dynamic expansion
                    cwd: 'img/',                   // Src matches are relative to this path
                    src: ['**/*.{png,jpg,gif,svg}'],   // Actual patterns to match
                    dest: 'dist/img'                  // Destination path prefix
                }]
            }
        },


        grunticon: {
            myIcons: {
                    files: [{
                        expand: true,
                        cwd: 'img/',
                        src: ['*.svg', '*.png'],
                        dest: "dist/img64"
                    }],
                options: {
                    loadersnippet: "grunticon.loader.js",
                }
            }
        },

        //The Watch Task
        watch: {
            // Watching For html Changes
            watch_html_files: {
                files : ['*.html']
            },
            // Watching For Sass Changes
            watch_sass_files: {
                files : ['scss/**/*.scss'],
                tasks : ['sass', 'postcss']
            },
            options: {
                'spawn': true,
                'interrupt': false,
                'debounceDelay': 500,
                'interval': 100,
                'event': 'all',
                'reload': false,
                'forever': true,
                'dateFormat': null,
                'atBegin': false,
                'livereload': 9000,
                'cwd': process.cwd(),
                'livereloadOnError': true
            }
        },
    });

    grunt.loadNpmTasks('grunt-contrib-watch');
    grunt.loadNpmTasks('grunt-contrib-sass');
    grunt.loadNpmTasks('grunt-postcss');
    grunt.loadNpmTasks('grunt-contrib-imagemin');
    grunt.loadNpmTasks('grunt-grunticon');

    grunt.registerTask('default', ['watch']);
    grunt.registerTask('build', ['imagemin']);

};