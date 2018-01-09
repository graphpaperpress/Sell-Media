<template>

	<div id="panorama"></div>

</template>

<script>

	var libpannellumjs = require('../../../node_modules/pannellum/src/js/libpannellum.js')
	var RequestAnimationFrame = require('../../../node_modules/pannellum/src/js/RequestAnimationFrame.js')
	var pannellumjs = require('../../../node_modules/pannellum/src/js/pannellum.js')
	var pannellumcss = require('../../../node_modules/pannellum/src/css/pannellum.css')

	export default {
		props: ['post'],

		data: function() {
			return {
				attachment: {},
			}
		},

		mounted: function() {
			this.attachment = this.post.sell_media_attachments[0]
			this.$emit('attachment', this.attachment)
			
			pannellum.viewer("panorama", {
				"type": "equirectangular",
				"panorama": "" + this.attachment.sizes.full[0] + "",
				"preview": "" + this.attachment.sizes.medium_large[0] + "",
				"autoLoad": true
			});
		},
	}
</script>

<style lang="sass" scoped>
	#panorama {
		width: 600px;
    	height: 400px;
	}
</style>