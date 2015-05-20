module.exports = {
	application: {
		dir: ['*.php', '!node_modules/**', '!build/**',]
		},
		options: {
			bin: 'phpcs',
			standard: 'WordPress',
		}
};