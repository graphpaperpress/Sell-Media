// https://github.com/gruntjs/grunt-contrib-sass
module.exports = {
	options: {
		sourcemap: 'none',
		force: true,
		style: 'expanded',
		trace: true,
		lineNumbers: true
	},
		files: [
			{
				expand: true,
				cwd: '<%= pkg.directories.sass %>',
				src: 'style.scss',
				dest: '',
				ext: '.css'
			}
		]
	}
};