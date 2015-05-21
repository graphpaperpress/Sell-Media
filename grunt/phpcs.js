module.exports = {
    application: {
        src: ['*.php', '!node_modules/**', '!build/**',]
        },
        options: {
            bin: 'phpcs',
            standard: 'WordPress',
        }
};