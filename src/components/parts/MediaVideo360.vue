<template>
	<div class="videocontent" v-if="post.sell_media_attachments[0]" :id="'videocontent-' + post.id" :key="post.id">
		<video :id="'video-' + post.sell_media_attachments[0].id" class="video-js vjs-default-skin vjs-big-play-centered vjs-16-9" controls preload="auto" width="640" height="264" :poster="post.sell_media_featured_image.sizes.large[0]" ref="videoPlayer">
			<source :src="post.sell_media_attachments[0].file" :type="post.sell_media_attachments[0].type">
			<p class="vjs-no-js">
				To view this video please enable JavaScript, and consider upgrading to a web browser that
				<a href="http://videojs.com/html5-video-support/" target="_blank">supports HTML5 video</a>
			</p>
		</video>
	</div>
</template>

<script>
import { mapActions } from "vuex"
require('!style-loader!css-loader!video.js/dist/video-js.css')
require('!style-loader!css-loader!videojs-panorama/dist/videojs-panorama.min.css')
import VideoJs from 'video.js'
import panorama from 'videojs-panorama'
window.HELP_IMPROVE_VIDEOJS = false

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
      let videos = VideoJs.players
      if (!videos.hasOwnProperty(this.$refs.videoPlayer)) {
        let player = VideoJs( this.$refs.videoPlayer, {}, function(){})
        player.panorama({
          clickAndDrag: true
        })
        return player
      }
    }
  },

  beforeDestroy() {
    this.player.dispose()
  }
}
</script>

<style lang="scss" scoped>
	.videocontent {
		width: 100%;
		position: relative;
		padding-top: 56.25%;

		.video-js {
			height: 100% !important;
			width: 100% !important;
			position: absolute;
			top: 0;
			left: 0;
		}
	}
</style>