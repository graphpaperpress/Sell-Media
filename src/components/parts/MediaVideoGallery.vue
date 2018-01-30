<template>

	<div class="video-gallery">

		<div class="videocontent" v-for="(video, index) in videos" v-if="index === currentVideo" :id="'videocontent-' + video.id" :key="video.id">
			<video
			:id="'video-' + index"
			class="video-js vjs-default-skin vjs-big-play-centered vjs-16-9"
			controls
			preload="auto"
			width="640"
			height="264"
			:poster="video.file.slice(0, -4) + '.jpg'">
				<source :src="video.file" :type="video.type">
			</video>
		</div>

		<portal to="video-thumbnails">
			<div id="video-thumbnails" class="video-thumbnails">
				<div
				:class="gridContainer"
				class="is-multiline has-text-centered">
					<div
					v-for="(video, index) in videos"
					@click="goToVideo(index)"
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

	import VideoJs from 'video.js'
	require('!style-loader!css-loader!video.js/dist/video-js.css')
	window.HELP_IMPROVE_VIDEOJS = false

	export default {

		props: ['post'],

		data: function() {
			return {
				currentVideo: 0,
				videos: this.post.sell_media_attachments,
				gridLayout: this.$store.getters.gridLayout,
				gridContainer: this.$store.getters.gridLayoutContainer,
			}
		},

		mounted: function() {
			this.player
		},

		updated: function() {
			this.player
		},

		computed: {
			player: function() {
				let attachment = this.post.sell_media_attachments[this.currentVideo]
				this.$store.commit( 'setProduct', { post_id: attachment.parent, attachment_id: attachment.id } )

				let videos = VideoJs.players
				console.log(videos)
				if (!videos.hasOwnProperty('video-' + this.currentVideo)) {
					console.log('video-' + this.currentVideo)
					return VideoJs( 'video-' + this.currentVideo, {}, function(){})
				}
				
			}
		},

		methods: {
			goToVideo: function(index) {
				this.currentVideo = index
				let attachment = this.post.sell_media_attachments[this.currentVideo]
				this.$store.commit( 'setProduct', { post_id: attachment.parent, attachment_id: attachment.id } )
			}
		},

		beforeDestroy() { 
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

			.video-thumbnail {
				border: 1px solid transparent;
				padding: 5px;
			}

			&.active .video-thumbnail {
				border: 1px solid #000;
				background: #444;
			}
		}
	}

</style>