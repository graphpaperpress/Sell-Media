<template>
	<div class="videocontent" v-if="post.sell_media_attachments[0]">
		<video :id="'video-' + post.sell_media_attachments[0].id" class="video-js vjs-default-skin vjs-big-play-centered vjs-16-9" controls preload="auto" width="640" height="264" :poster="post.sell_media_featured_image.sizes.large[0]" data-setup="{}">
			<source :src="post.sell_media_attachments[0].file" :type="post.sell_media_attachments[0].type">
			<p class="vjs-no-js">
				To view this video please enable JavaScript, and consider upgrading to a web browser that
				<a href="http://videojs.com/html5-video-support/" target="_blank">supports HTML5 video</a>
			</p>
		</video>
	</div>
</template>

<script>
	import VideoJs from 'video.js'
	require('!style-loader!css-loader!video.js/dist/video-js.css')
	export default {
		props: ['post'],

		mounted: function() {
			window.HELP_IMPROVE_VIDEOJS = false;
			let attachment = this.post.sell_media_attachments[0]
			this.$store.commit( 'setProduct', { post_id: attachment.parent, attachment_id: attachment.id } )
			VideoJs('video-' + this.post.sell_media_attachments[0].id, {width: 200, height: 150});
		}
	}
</script>

<style lang="scss" scoped>
.videocontent{
	width: 640px;
	height: 264px;
}
</style>
