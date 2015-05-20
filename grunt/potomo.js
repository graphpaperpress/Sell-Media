// https://github.com/axisthemes/grunt-potomo
module.exports = {
	dist: {
		options: {
			poDel: false // Set to true if you want to erase the .po
		},
		files: [{
			expand: true,
			cwd: '<%= pkg.directories.languages %>',
			src: ['*.po'],
			dest: '<%=  pkg.directories.languages %>',
			ext: '.mo',
			nonull: true
		}]
	}
};
