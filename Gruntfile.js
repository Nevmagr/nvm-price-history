module.exports = function (grunt) {
	var BUILD_DIR = "build/",
		PHP_DIR = "classes/",
		VENDOR_DIR = "vendor/";

	grunt.initConfig({
		pkg: grunt.file.readJSON("package.json"),
		clean: {
			all: BUILD_DIR,
		},
		watch: {
			options: {
				interval: 2000,
			},
			all: {
				files: [PHP_DIR + "**", "<%= pkg.name %>.php"],
				tasks: ["clean:all", "copy:all"],
				options: {
					spawn: false,
				},
			},
		},
		copy: {
			plugin: {
				src: "<%= pkg.name %>.php",
				dest: BUILD_DIR + "<%= pkg.name %>/",
			},
			php: {
				expand: true,
				cwd: PHP_DIR,
				src: "**",
				dest: BUILD_DIR + "<%= pkg.name %>/" + PHP_DIR,
			},
			psr: {
				expand: true,
				cwd: VENDOR_DIR + "psr",
				src: "**",
				dest: BUILD_DIR + "<%= pkg.name %>/" + VENDOR_DIR + "psr",
			},
			autoload: {
				src: VENDOR_DIR + "autoload.php",
				dest: BUILD_DIR + "<%= pkg.name %>/" + VENDOR_DIR + "autoload.php",
			},
			composer: {
				expand: true,
				cwd: VENDOR_DIR + "composer",
				src: "**",
				dest: BUILD_DIR + "<%= pkg.name %>/" + VENDOR_DIR + "composer",
			},
		},
		compress: {
			main: {
				options: {
					mode: "zip",
					archive: BUILD_DIR + "<%= pkg.name %>.zip",
				},
				expand: true,
				cwd: BUILD_DIR + "<%= pkg.name %>/",
				src: "**/*",
				dest: "<%= pkg.name %>",
			},
		},
	});

	grunt.loadNpmTasks("grunt-contrib-watch");
	grunt.loadNpmTasks("grunt-contrib-clean");
	grunt.loadNpmTasks("grunt-contrib-copy");
	grunt.loadNpmTasks("grunt-contrib-compress");

	grunt.registerTask("copy:all", [
		"clean:all",
		"copy:plugin",
		"copy:php",
		"copy:psr",
		"copy:autoload",
		"copy:composer",
	]);

	grunt.registerTask("build", ["copy:all", "compress"]);
};
