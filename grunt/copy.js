module.exports = {
	main: {
		src: [
			'**',
			'!node_modules/**',
			'!build/**',
			'!grunt/**',
			'!.git/**',
			'!Gruntfile.js',
			'!package.json',
			'!.gitignore',
			'!.gitmodules',
			'!.tx/**',
			'!**/Gruntfile.js',
			'!**/package.json',
			'!**/README.md',
			'!**/*~'
			],
		dest: 'build/<%= pkg.name %>/'
	}
};