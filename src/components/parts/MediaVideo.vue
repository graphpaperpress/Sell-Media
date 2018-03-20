<template>

	<div class="videos">

		<div class="videocontent">
			<video
			id="video"
			ref="videoPlayer"
			class="video-js vjs-default-skin vjs-big-play-centered vjs-16-9"
			controls
			preload="auto"
			width="640"
			height="264"
			:poster="poster">
				<source :src="file" :type="type">
			</video>
		</div>

		<portal to="video-thumbnails">
			<div id="video-thumbnails" class="video-thumbnails">
				<div
				:class="gridContainer"
				class="is-multiline has-text-centered">
					<div
					v-for="(video, index) in post.sell_media_attachments"
					:key="index"
					@click="getVideo(index)"
					:class="[{ active: currentVideo === index}, gridLayout]">
						<div class="video-thumbnail">
							<img :src="video.file.slice(0, -4) + '.jpg'" :alt="video.title" />
						</div>
					</div>
				</div>
			</div>
		</portal>

	</div>

</template>

<script>
window.HELP_IMPROVE_VIDEOJS = false // npm releases of video.js do not include Google Analytics tracking at all

import VideoJs from 'video.js'
require('!style-loader!css-loader!video.js/dist/video-js.css')

import { mapActions } from "vuex"

export default {
  props: ['post'],

  data(){
    return {
      player: '',
      currentVideo: 0,
      file: '',
      type: '',
      poster: '',
      gridLayout: this.$store.getters.gridLayout,
      gridContainer: this.$store.getters.gridLayoutContainer,
    }
  },

  mounted(){
    this.getVideo()
  },

  methods: {
    ...mapActions(["setProduct"]),

    getVideo(index = 0){
      this.currentVideo = index
      this.file = this.post.sell_media_attachments[index].file
      this.type = this.post.sell_media_attachments[index].type
      this.poster = this.file.slice(0, -4) + '.jpg'

      let attachment = this.post.sell_media_attachments[index]
      this.$store.dispatch( 'setProduct', { post_id: attachment.parent, attachment_id: attachment.id } )

      let player = VideoJs(this.$refs.videoPlayer, {}, function(){})
      player.src({src: this.file, type: this.type})
      this.player = player
    }
  },

  beforeDestroy(){
    this.player.dispose()
  }
}
</script>

<style lang="scss">

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

	.video-thumbnails {
		margin: 1rem auto;
		text-align: center;

		.column,
		.is-horizontal-masonry {

			&:hover {
				cursor: pointer;
			}

			&.active {
				height: auto; // override parent height: 600 applied to expander grid
			}

			.video-thumbnail {
				border: 1px solid transparent;
				padding: 5px;
				position: relative;
				display: inline-block;
				cursor: pointer;

				&:before {
					position: absolute;
					top: calc(50% - 10px);
					left: 50%;
					opacity: .8;
					text-shadow: 0px 0px 30px rgba(0, 0, 0, 0.5);

					border-width: 10px 10px 10px 18px;
					border-color: transparent transparent transparent rgba(255,255,255,0.5);
					border-style: solid;
					content: '';
				}

				&:hover:before {
					border-color: transparent transparent transparent rgba(255,255,255,1);
				}
			}

			&.active .video-thumbnail {
				border: 1px solid #000;
				background: #444;

				&:before {
					border-color: transparent transparent transparent rgba(255,255,255,1);
				}
			}
		}
	}

</style>
