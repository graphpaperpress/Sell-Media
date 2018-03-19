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

const PANORAMA_TYPE = {
  EQUIRECTANGULAR: 'equirectangular',
  CUBEMAP: 'cubemap',
  MULTIRES: 'multires',
}

export default {
  props: ['post'],

  mounted: function() {
    this.initPlayer()
  },

  updated: function() {
    this.initPlayer()
  },

  methods: {
    ...mapActions([
      'setProduct'
    ]),
    initPlayer() {
      const { sizes, parent: post_id, id: attachment_id } = this.post.sell_media_attachments[0];
      this.setProduct({
        post_id,
        attachment_id,
      })
      pannellum.viewer(this.$refs.panoPlayer.id, {
        type: PANORAMA_TYPE.EQUIRECTANGULAR,
        panorama: `${sizes.full[0]}`,
        preview: `${sizes.medium_large[0]}`,
        autoLoad: true,
      })
    }
  },
}
</script>

<style lang="scss" scoped>
	#panorama {
		width: 600px;
    	height: 300px;
	}
</style>
