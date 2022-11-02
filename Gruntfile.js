module.exports = function (grunt) {
  config = process.env;
  grunt.initConfig({
    pkg: grunt.file.readJSON("package.json"),

    uglify: {
      //Run The Uglify Task For Javascript_Files
      uglify_js_files: {
        files: {
          "dist/js/script.js": "js/build/*.js",
        },
      },
      // The Global Options
      options: {
        mangle: {},
        compress: {},
        beautify: false,
        expression: false,
        report: "max",
        sourceMap: false,
        sourceMapName: undefined,
        sourceMapIn: undefined,
        sourceMapIncludeSources: false,
        enclose: undefined,
        wrap: undefined,
        exportAll: false,
        preserveComments: undefined,
        banner: "/*Uglify*/",
        footer: "",
      },
    }, //end Uglify Task;

    //the Sass Task
    sass: {
      sass_my_files: {
        files: {
          "dist/css/main.css": "scss/main.scss",
        },
        options: {
          style: "compressed",
          precision: "7",
        },
      },
    },

    postcss: {
      options: {
        processors: [
          require("pixrem")(), // add fallbacks for rem units
          require("autoprefixer")(), // add vendor prefixes
          require("cssnano")(), // minify the result
        ],
      },
      dist: {
        src: "dist/css/*.css",
      },
    },

    imagemin: {
      dynamic: {
        // Another target
        files: [
          {
            expand: true, // Enable dynamic expansion
            cwd: "img/", // Src matches are relative to this path
            src: ["**/*.{png,jpg,gif,svg}"], // Actual patterns to match
            dest: "dist/img", // Destination path prefix
          },
        ],
      },
    },

    //The Watch Task
    watch: {
      // Watching For Changes
      watch_sass_files: {
        files: ["scss/**/*.scss", "*.html"],
        tasks: ["sass", "postcss", "buildHtml"],
      },
      options: {
        spawn: true,
        interrupt: false,
        debounceDelay: 500,
        interval: 100,
        event: "all",
        reload: false,
        forever: true,
        dateFormat: null,
        atBegin: false,
        livereload: 9000,
        cwd: process.cwd(),
        livereloadOnError: true,
      },
    },

    sftp: {
      deploy: {
        files: {
          "./": ["dist/img/*", "dist/css/*.css", "*.php", "cron.sh", "*.ico"],
        },
        options: {
          path: "<%= config.REMOTE_PATH %>",
          host: "<%= config.REMOTE_HOST %>",
          username: "<%= config.REMOTE_USERNAME %>",
          privateKey: grunt.file.read(process.env.HOME + "/.ssh/id_rsa"),
          createDirectories: true,
          showProgress: true,
        },
      },
    },

    sshexec: {
      cache: {
        command: "cd <%= config.REMOTE_PATH %> && sh cron.sh",
        options: {
          host: "<%= config.REMOTE_HOST %>",
          username: "<%= config.REMOTE_USERNAME %>",
          privateKey: grunt.file.read(process.env.HOME + "/.ssh/id_rsa"),
        },
      },
    },
  });

  grunt.loadNpmTasks("grunt-contrib-watch");
  grunt.loadNpmTasks("grunt-contrib-sass");
  grunt.loadNpmTasks("grunt-postcss");
  grunt.loadNpmTasks("grunt-contrib-imagemin");
  grunt.loadNpmTasks("grunt-ssh");

  grunt.registerTask("buildHtml", "", function () {
    const exec = require("child_process").execSync;
    const result = exec("sh cron.sh", { encoding: "utf8" });
    grunt.log.writeln(result);
  });
  grunt.registerTask("default", ["watch"]);
  grunt.registerTask("build", ["imagemin", "sass", "postcss"]);
  grunt.registerTask("deploy", ["build", "sftp:deploy", "sshexec:cache"]);
};
