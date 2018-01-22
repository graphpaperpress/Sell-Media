<template>

	<div :id="'panorama-' + post.id">
		<div id="panorama"></div>
	</div>

</template>

<script>

	var libpannellumjs = require('../../../node_modules/pannellum/src/js/libpannellum.js')
	var RequestAnimationFrame = require('../../../node_modules/pannellum/src/js/RequestAnimationFrame.js')
	var pannellumjs = require('../../../node_modules/pannellum/src/js/pannellum.js')
	var pannellumcss = require('../../../node_modules/pannellum/src/css/pannellum.css')

	export default {
		props: ['post'],

		mounted: function() {
			this.$store.commit( 'setProduct', { post_id: this.post.sell_media_attachments[0].parent, attachment_id: this.post.sell_media_attachments[0].id } )
			
			this.render()
		},

		updated: function() {
			this.$store.commit( 'setProduct', { post_id: this.post.sell_media_attachments[0].parent, attachment_id: this.post.sell_media_attachments[0].id } )

			this.render()
		},

		methods: {
			render: function() {
				pannellum.viewer("panorama", {
					"type": "equirectangular",
					"panorama": "" + this.post.sell_media_attachments[0].sizes.full[0] + "",
					"preview": "" + this.post.sell_media_attachments[0].sizes.medium_large[0] + "",
					"autoLoad": true
				});
			}
		}
	}
</script>

<style lang="scss" scoped>
	#panorama {
		width: 600px;
    	height: 300px;
	}
</style>