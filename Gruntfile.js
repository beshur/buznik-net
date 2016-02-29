module.exports = function(grunt) {
    grunt.initConfig({
        pkg: grunt.file.readJSON('package.json'),
        uglify: {
            //Run The Uglify Task For Javascript_Files
            uglify_js_files: {
                files: {
                    "js/dist/script.js":"js/build/*.js",
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
                    "css/main.css" : "scss/main.scss",
                },
                options : {
                    "style":"compressed",
                    "precision":"7"
                },
            },
        },

        //The Watch Task
        watch: {
            //watching The Javascript files for changes and run the uglify function on theme
            watch_js_files: {
                files : ['js/build/*.js'],
                tasks : ['uglify']
            },
            // Watching For html Changes
            watch_html_files: {
                files : ['*.html']
            },
            // Watching For Sass Changes
            watch_sass_files: {
                files : ['scss/**/*.scss'],
                tasks : ['sass']
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
    grunt.loadNpmTasks('grunt-contrib-uglify');
    grunt.loadNpmTasks('grunt-contrib-sass');

    grunt.registerTask('default', ['watch']);

};