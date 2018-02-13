<template>

	<div v-if="loaded">

		<h2 class="title">{{ attachment.title }}</h2>

		<div class="columns">

			<div :class="pageLayout.content">
				<template v-if="type === 'panorama' || type === 'dome'">
					<media-panorama :post="post"></media-panorama>
				</template>
				<template v-else-if="type === 'video'">
					<media-video :post="post"></media-video>
				</template>
				<template v-else-if="type === '360-video'">
					<media-video-360 :post="post"></media-video-360>
				</template>
				<template v-else>
					<img :src="attachment.media_details.sizes.large.source_url" :alt="attachment.alt"/>
				</template>
			</div>

			<div :class="pageLayout.sidebar">
				<cart-form :key="attachment.slug" :post="post" :attachment="attachment" :multiple="multiple"></cart-form>
			</div>

		</div>
		<div class="post-content content" v-if="attachment.caption" v-html="attachment.caption.rendered"></div>
	</div>

</template>

<script>
  import mixinGlobal from '../../mixins/global'

	export default {
    mixins: [mixinGlobal],

		data() {
			return {
				post: {},
				attachment: {},
				type: '',
				loaded: false,
				multiple: false,
				pageTitle: '',
				pageLayout: this.$store.getters.pageLayout
			};
		},

		mounted() {
			this.getAttachment()
		},

		methods: {

			getAttachment(){

				const vm = this;
				// console.log(vm.$route.params)

				vm.$http.get( '/wp-json/wp/v2/media', {
					params: {
						slug: vm.$route.params.slug
					}
				} )
				.then( ( res ) => {

					vm.attachment = res.data[0]
					// console.log(vm.attachment)
					vm.attachment.title = vm.attachment.title.rendered
					vm.pageTitle = vm.attachment.title.rendered
					vm.$store.dispatch( 'changeTitle', vm.pageTitle )

					this.getPost(vm.attachment.post)

				} )
				.catch( ( res ) => {

					//console.log( `Something went wrong : ${res}` );

				} );

			},

			getPost(id){

				const vm = this;

				vm.$http.get( '/wp-json/wp/v2/sell_media_item', {
					params: {
						include: id
					}
				} )
				.then( ( res ) => {
					vm.post = res.data[0]
					vm.type = vm.post.sell_media_meta.product_type[0].slug
					vm.loaded = true
				} )
				.catch( ( res ) => {
					console.log( `Something went wrong : ${res}` )
				} );
			},

		}

	};
</script>
