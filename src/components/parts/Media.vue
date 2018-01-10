<template>
	<div class="media" v-if="loaded">

		<template v-if="type && type.slug === 'panorama'">
			<media-panorama :post="post"></media-panorama>
		</template>
		<template v-else-if="type && type.slug === 'video'">
			<media-video :post="post"></media-video>
		</template>
		<template v-else-if="type && type.slug === '360-video'">
			<media-video-360 :post="post"></media-video-360>
		</template>
		<template v-else-if="multiple">
			<media-slideshow :attachments="attachments"></media-slideshow>
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
		props: ['post'],

		data: function() {
			return {
				attachments: {},
				type: {},
				multiple: false,
				loaded: false,
			}
		},

		created: function() {
			this.attachments = this.post.sell_media_attachments
			let count = Object.keys(this.attachments)
			this.multiple = count.length > 1 ? true : false
		},

		mounted: function() {
			this.getProductType()
		},

		methods: {

			getProductType: function() {
				const vm = this;
				vm.loaded = false;
				vm.$http.get( '/wp-json/wp/v2/product_type', {
					params: {
						post: vm.post.id
					}
				} )
				.then( ( res ) => {
					vm.type = res.data[0];
					vm.loaded = true;
				} )
				.catch( ( res ) => {
					console.log( res )
				} )
			}
		},

		components: {
			'media-panorama': MediaPanorama,
			'media-video': MediaVideo,
			'media-video-360': MediaVideo360,
			'media-slideshow': MediaSlideshow,
		}
    }
</script>
