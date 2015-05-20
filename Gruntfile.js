/* global require, process */
module.exports = function( grunt ) {
	// Load Grunt plugin configurations
	require('load-grunt-config')(grunt, {
		data: {
			pkg: grunt.file.readJSON('package.json')
		}
	});
};