<template>

	<div v-if="loaded">

		<h2 class="post-title">{{ post.title.rendered }}</h2>

		<div v-if="attachments.length > 1">
			This gallery contains {{ attachments.length }} images
			<div class="columns is-multiline">
				<grid-item v-for="attachment in attachments" v-bind:key="attachment.slug" v-bind:p="attachment"></grid-item>
			</div>
		</div>
		<div v-else>
			<img
				:src="post.sell_media_featured_image.sizes.large[0]" 
				:data-srcset="post.sell_media_featured_image.sizes.srcset[0]"
			/>
		</div>

		<div class="post-content content" v-if="post.content" v-html="post.content.rendered" ></div>

	</div>

</template>

<script>

	import GridItem from './GridItem.vue';

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
			'grid-item': GridItem
		}

	};
</script>
