module.exports = function (grunt) {
  config = process.env;
  grunt.initConfig({
    pkg: grunt.file.readJSON("package.json"),

    copy: {
      main: {
        files: [
          // includes files within path
          { expand: true, src: ["css/*"], dest: "dist/" },
        ],
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
      watch_files: {
        files: ["css/*.css", "*.html"],
        tasks: ["copy:main", "postcss", "buildHtml"],
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

  grunt.loadNpmTasks("grunt-contrib-copy");
  grunt.loadNpmTasks("grunt-contrib-watch");
  grunt.loadNpmTasks("grunt-postcss");
  grunt.loadNpmTasks("grunt-contrib-imagemin");
  grunt.loadNpmTasks("grunt-ssh");

  grunt.registerTask("buildHtml", "", function () {
    const exec = require("child_process").execSync;
    const result = exec("sh cron.sh", { encoding: "utf8" });
    grunt.log.writeln(result);
  });
  grunt.registerTask("default", ["watch"]);
  grunt.registerTask("build", ["imagemin", "copy:main", "postcss"]);
  grunt.registerTask("deploy", ["build", "sftp:deploy", "sshexec:cache"]);
};
