<template>

	<div v-if="loaded">

		<h2 class="title">{{ post.title.rendered }}</h2>

		<template v-if="multiple">
			<p class="subtitle">This gallery contains <strong>{{ attachments.length }}</strong> images</p>
			<gallery v-bind:attachments="attachments" v-bind:prefix="post.slug" v-bind:key="post.slug"></gallery>
		</template>
		<template v-else>
			<div class="columns">
				<div :class="pageLayout.content">
					<figure>
						<featured-image :post="post" @attachment="setAttachment" :size="image_size" ></featured-image>
					</figure>
				</div>
				<div :class="pageLayout.sidebar">
					<cart-form :key="post.slug" :post="post" :attachment="attachment" :multiple="multiple"></cart-form>
				</div>
			</div>
		</template>

		<div class="post-content content" v-if="post.content" v-html="post.content.rendered"></div>
	</div>

</template>

<script>

	import Gallery from '../parts/Gallery.vue';

	export default {

		data: function() {
			return {
				base_path: sell_media.site_url,
				post: {},
				attachment: {},
				attachments: {},
				multiple: false,
				loaded: false,
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
					vm.loaded = true;
					vm.pageTitle = vm.post.title.rendered;
					vm.$store.commit( 'changeTitle', vm.pageTitle );

					console.log(vm.post);

				} )
				.catch( ( res ) => {

					//console.log( `Something went wrong : ${res}` );

				} );
			},

			setAttachment: function(data) {
				this.attachment = data
			}
		},

		components: {
			'gallery': Gallery
		}

	};
</script>
