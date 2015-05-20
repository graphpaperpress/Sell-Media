// Compress build directory into <name>.zip and <name>-<version>.zip
module.exports = {
	main: {
		options: {
			mode: 'zip',
			archive: './build/<%= pkg.name %>-<%= pkg.version %>.zip'
			},
		expand: true,
		cwd: 'build/<%= pkg.name %>/',
		src: ['**/*'],
		dest: '<%= pkg.name %>/'
	}
};