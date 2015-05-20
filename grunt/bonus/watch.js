// https://github.com/gruntjs/grunt-contrib-watch
module.exports = {
	js: {
		options: {
			livereload: true
		},
		files: [
			'<%= pkg.directories.js %>'
		],
		tasks: [
			'build:js'
		]
	},
	scss: {
		options: {
			livereload: true
		},
		files: [
			'<%= pkg.directories.sass %>'
		],
		tasks: [
			'build:css'
		]
	}
};
