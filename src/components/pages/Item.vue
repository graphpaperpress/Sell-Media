<template>

	<div v-if="loaded">

		<h2 class="title">{{ post.title.rendered }}</h2>

		<div class="columns">
			<div :class="pageLayout.content">
				<media :post="post" :type="type" @attachment="setAttachment"></media>
				<div class="post-content content" v-if="post.content" v-html="post.content.rendered"></div>
			</div>
			<div :class="pageLayout.sidebar">
				<cart-form :key="post.slug" :post="post" :attachment="attachment" :multiple="multiple"></cart-form>
			</div>
		</div>

	</div>

</template>

<script>
  import mixinGlobal from '../../mixins/global'
	export default {
    mixins: [mixinGlobal],

		data: function() {
			return {
				base_path: sell_media.site_url,
				post: {},
				attachment: {},
				attachments: {},
				multiple: false,
				loaded: false,
				type: '',
				image_size: 'large',
				pageTitle: '',
				pageLayout: this.$store.getters.pageLayout
			}
		},

		mounted: function() {
			this.getPost();
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
					vm.multiple = vm.attachments.length > 1 ? true : false;
					vm.type = vm.post.sell_media_meta.product_type[0];
					vm.loaded = true;
					vm.pageTitle = vm.post.title.rendered;
					vm.$store.dispatch( 'changeTitle', vm.pageTitle );

					// console.log(vm.post);
				} )
				.catch( ( res ) => {

					//console.log( `Something went wrong : ${res}` );

				} );
			},

			setAttachment: function(data) {
				this.attachment = data
			}
		}

	};
</script>
