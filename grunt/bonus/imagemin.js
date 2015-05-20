// https://github.com/gruntjs/grunt-contrib-imagemin
module.exports = {
	images: {
		files: [
			{
				expand: true,
				src: [
					'**/*.{png,jpg}',
					'!node_modules/**',
					'!build/**',
				]
			}
		]
	}
};
