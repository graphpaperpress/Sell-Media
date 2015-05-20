// https://github.com/yoavf/grunt-cssjanus
module.exports = {
	theme: {
		options: {
			swapLtrRtlInUrl: false
		},
		files: [
			{ // Must be done on dev, otherwise /* @noflip */ is removed
				src: 'style.css',
				dest: 'style-rtl.css'
			}
		]
	}
};