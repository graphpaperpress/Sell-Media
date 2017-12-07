<template>

	<div v-if="loaded">

		<h2 class="post-title">{{ post.title.rendered }}</h2>

		<img
			:src="post.sell_media_featured_image.sizes.large[0]" 
			:data-srcset="post.sell_media_featured_image.sizes.srcset[0]"
		/>

		<div class="post-content content" v-if="post.content" v-html="post.content.rendered" ></div>

	</div>

</template>

<script>
export default {

	mounted: function() {
		this.getPost();
	},

	data: function() {
		return {

			base_path: sell_media.site_url,
			post: {},
			loaded: false,
			pageTitle: ''
		};
	},

	methods: {

		getPost: function() {

			const vm = this;

			vm.$http.get( '/wp-json/wp/v2/sell_media_item', {
				params: { slug: vm.$route.params.slug }
			} )
			.then( ( res ) => {

				vm.post = res.data[0];
				vm.loaded = true;
				vm.pageTitle = vm.post.title.rendered;
				vm.$store.commit( 'smChangeTitle', vm.pageTitle );

				// console.log(vm.post);

			} )
			.catch( ( res ) => {

				//console.log( `Something went wrong : ${res}` );

			} );

		}

	}
};
</script>
