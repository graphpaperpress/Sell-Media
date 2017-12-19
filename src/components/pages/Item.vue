<template>

	<div v-if="loaded">

		<h2 class="title">{{ post.title.rendered }}</h2>

		<template v-if="attachments && attachments.length > 1">
			<p class="subtitle">This gallery contains <strong>{{ attachments.length }}</strong> images</p>
			<gallery v-bind:attachments="attachments" v-bind:prefix="post.slug" v-bind:key="post.slug"></gallery>
		</template>
		<template v-else>
			<div class="columns">
				<div :class="contentContainer">
					<img
						:src="post.sell_media_featured_image.sizes.large[0]" 
						:data-srcset="post.sell_media_featured_image.sizes.srcset[0]"
						:alt="post.sell_media_featured_image.alt" 
					/>
				</div>
				<div :class="formContainer">
					<cart-form :key="post.slug" :post="post"></cart-form>
				</div>
			</div>
		</template>

		<div class="post-content content" v-if="post.content" v-html="post.content.rendered" ></div>

	</div>

</template>

<script>

	import Gallery from '../parts/Gallery.vue';

	export default {

		mounted: function() {
			this.getPost();
		},

		data: function() {
			return {
				base_path: sell_media.site_url,
				post: {},
				attachments: {},
				loaded: false,
				pageTitle: '',
				layout: sell_media.layout,
				contentContainer: '',
				formContainer: '',
			}
		},

		created: function() {
			if ( this.layout === 'sell-media-single-two-col' ) {
				this.contentContainer = 'column is-two-thirds';
				this.formContainer = 'column is-one-third';
			}
		},

		methods: {

			getPost: function() {

				const vm = this;

				vm.$http.get( '/wp-json/wp/v2/sell_media_item', {
					params: {
						slug: vm.$route.params.slug
					}
				} )
				.then( ( res ) => {

					vm.post = res.data[0];
					vm.attachments = vm.post.sell_media_attachments;
					vm.loaded = true;
					vm.pageTitle = vm.post.title.rendered;
					vm.$store.commit( 'smChangeTitle', vm.pageTitle );

					console.log(vm.post);

				} )
				.catch( ( res ) => {

					//console.log( `Something went wrong : ${res}` );

				} );
			}
		},

		components: {
			'gallery': Gallery
		}

	};
</script>
