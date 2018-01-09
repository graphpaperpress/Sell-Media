<template>
	<div class="videocontent" v-if="attachment">
		<video :id="'video-'+attachment.id" class="video-js vjs-default-skin vjs-big-play-centered vjs-16-9" controls preload="auto" width="640" height="264" :poster="attachment.featured_image" data-setup="{}">
			<source :src="attachment.file" :type="attachment.type">
			<p class="vjs-no-js">
				To view this video please enable JavaScript, and consider upgrading to a web browser that
				<a href="http://videojs.com/html5-video-support/" target="_blank">supports HTML5 video</a>
			</p>
		</video>
	</div>
</template>

<script>
	import videojs from 'video.js'
	export default {
		props: ['post'],

		data: function() {
			return {
				attachment: {},
			}
		},
		beforeMount: function() {
			this.attachment = this.post.sell_media_attachments[0]
			this.attachment.featured_image = 'undefined' !== typeof this.post.sell_media_featured_image.sizes.large[0] ? this.post.sell_media_featured_image.sizes.large[0] : ''
			this.$emit('attachment', this.attachment)
		},
		mounted: function() {
			console.log('video-'+this.attachment.id);
			videojs('video-'+this.attachment.id)
		},
	}
</script>
