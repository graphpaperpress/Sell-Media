<template>

	<div v-if="loaded">

		<h2 class="title">{{ attachment.title.rendered }}</h2>

		<div class="columns">

			<div :class="pageLayout.content">
				<img
					:src="attachment.media_details.sizes.large.source_url" 
					:data-srcset="attachment.media_details.sizes.large.source_url"
					:alt="attachment.alt_text"
				/>
			</div>

			<div :class="pageLayout.sidebar">
				<cart-form :key="attachment.slug" :post="post" :attachment="attachment" :multiple="multiple"></cart-form>
			</div>

		</div>
		<div class="post-content content" v-if="attachment.caption" v-html="attachment.caption.rendered"></div>
	</div>

</template>

<script>

	export default {

		mounted: function() {
			this.getAttachment();
		},

		data: function() {
			return {
				base_path: sell_media.site_url,
				post: {},
				attachment: {},
				loaded: false,
				pageTitle: '',
				pageLayout: this.$store.getters.pageLayout
			};
		},

		methods: {

			getAttachment: function() {

				const vm = this;

				vm.$http.get( '/wp-json/wp/v2/media', {
					params: {
						slug: vm.$route.params.slug
					}
				} )
				.then( ( res ) => {

					vm.attachment = res.data[0];
					vm.loaded = true;
					vm.pageTitle = vm.attachment.title.rendered;
					vm.$store.commit( 'changeTitle', vm.pageTitle );

					console.log(vm.attachment);

				} )
				.catch( ( res ) => {

					//console.log( `Something went wrong : ${res}` );

				} );

			}

		}

	};
</script>
