<template>

	<div v-if="loaded">

		<h2 class="title">{{ post.title.rendered }}</h2>

		<img
			:src="post.media_details.sizes.large.source_url" 
			:data-srcset="post.media_details.sizes.full.source_url"
			:alt="post.alt_text"
		/>

		<div class="post-content content" v-if="post.caption" v-html="post.caption.rendered" ></div>
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

				vm.$http.get( '/wp-json/wp/v2/media', {
					params: {
						slug: vm.$route.params.slug
					}
				} )
				.then( ( res ) => {

					vm.post = res.data[0];
					vm.loaded = true;
					vm.pageTitle = vm.post.title.rendered;
					vm.$store.commit( 'smChangeTitle', vm.pageTitle );

					console.log(vm.post);

				} )
				.catch( ( res ) => {

					//console.log( `Something went wrong : ${res}` );

				} );

			}

		}

	};
</script>
