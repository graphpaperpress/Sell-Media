<template>

	<div :id="'panorama-' + post.id">
		<div id="panorama" ref="panoPlayer"></div>
	</div>

</template>

<script>
import { mapActions } from "vuex"

import 'pannellum/js/libpannellum.js'
import 'pannellum/js/RequestAnimationFrame.js'
import 'pannellum/js/pannellum.js'
import 'pannellum/css/pannellum.css'

export default {

  props: ['post'],

  mounted: function() {
    this.player
  },

  updated: function() {
    this.player
  },

  methods: {
    ...mapActions(["setProduct"])
  },

  computed: {
    player: function() {
      this.$store.dispatch( 'setProduct', { post_id: this.post.sell_media_attachments[0].parent, attachment_id: this.post.sell_media_attachments[0].id } )

      pannellum.viewer(this.$refs.panoPlayer.id, {
        "type": "equirectangular",
        "panorama": "" + this.post.sell_media_attachments[0].sizes.full[0] + "",
        "preview": "" + this.post.sell_media_attachments[0].sizes.medium_large[0] + "",
        "autoLoad": true
      })
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
