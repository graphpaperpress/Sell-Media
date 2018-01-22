<template>
	<div class="media" v-if="loaded">

		<template v-if="type === 'panorama' || type === 'dome'">
			<media-panorama :post="post"></media-panorama>
		</template>
		<template v-else-if="type === 'video'">
			<media-video :post="post"></media-video>
		</template>
		<template v-else-if="type === '360-video'">
			<media-video-360 :post="post"></media-video-360>
		</template>
		<template v-else-if="'undefined' !== typeof post.sell_media_attachments && Object.keys(post.sell_media_attachments).length > 1">
			<media-slideshow :post="post"></media-slideshow>
		</template>
		<template v-else>
			<featured-image :post="post" size="large"></featured-image>
		</template>
	</div>
</template>

<script>

	import MediaPanorama from './MediaPanorama.vue';
	import MediaVideo from './MediaVideo.vue';
	import MediaVideo360 from './MediaVideo360.vue';
	import MediaSlideshow from './MediaSlideshow.vue';

	export default {
		props: ['post', 'type'],

		data: function() {
			return {
				loaded: false
			}
		},

		mounted: function() {
			this.loaded = true
		},

		components: {
			'media-panorama': MediaPanorama,
			'media-video': MediaVideo,
			'media-video-360': MediaVideo360,
			'media-slideshow': MediaSlideshow,
		}
    }
</script>
