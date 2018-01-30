<template>
	<div class="medias" v-if="loaded">

		<template v-if="type === 'panorama' || type === 'dome'">
			<media-panorama :post="post"></media-panorama>
		</template>
		<template v-else-if="type === 'video'">
			<template v-if="multiple">
				<media-video-gallery :post="post"></media-video-gallery>
			</template>
			<template v-else>
				<media-video :post="post"></media-video>
			</template>
		</template>
		
		<template v-else-if="type === '360-video'">
			<media-video-360 :post="post"></media-video-360>
		</template>
		<template v-else-if="multiple">
			<media-slideshow :post="post"></media-slideshow>
		</template>
		<template v-else>
			<featured-image :post="post" size="large"></featured-image>
		</template>
	</div>
</template>

<script>

	import MediaPanorama from './MediaPanorama.vue';
	import MediaVideoGallery from './MediaVideoGallery.vue';
	import MediaVideo from './MediaVideo.vue';
	import MediaVideo360 from './MediaVideo360.vue';
	import MediaSlideshow from './MediaSlideshow.vue';

	export default {
		props: ['post', 'type'],

		data: function() {
			return {
				loaded: false,
				multiple: 'undefined' !== typeof this.post.sell_media_attachments && Object.keys(this.post.sell_media_attachments).length > 1 ? true : false,
			}
		},

		mounted: function() {
			this.loaded = true
		},

		components: {
			'media-panorama': MediaPanorama,
			'media-video-gallery': MediaVideoGallery,
			'media-video': MediaVideo,
			'media-video-360': MediaVideo360,
			'media-slideshow': MediaSlideshow,
		}
    }
</script>
