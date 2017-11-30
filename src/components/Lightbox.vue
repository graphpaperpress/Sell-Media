<template>
	<div id="sell-media-lightbox">
		<p v-on:click="emptyLightbox" class="empty-lightbox" v-bind:title="title">{{ title }}</p>
		<section>
			<figure v-if="posts" v-for="post in posts">
				<a v-bind:href="post.link" v-bind:title="post.title.rendered">
					<img v-bind:src="post.media_details.sizes.medium_large.source_url">
					<figcaption>
						<h2>{{ post.title.rendered }}</h2>
					</figcaption>
				</a>
			</figure>
		</section>
	</div>
</template>

<script>

	export default {

		mounted: function() {
			this.getPosts();
		},

		data: function() {
			return {
				posts: {},
				post: '',
				title: sell_media.lightbox_labels.remove_all,
				title_empty: sell_media.lightbox_labels.empty,
			}
		},

		methods: {
			getPosts: function() {
				const vm = this;
				let json = vm.$cookie.get('sell_media_lightbox')
				if ( ! json ) {
					vm.$set(vm, 'title', vm.title_empty)
				} else {
					let obj = JSON.parse(json)
					let attachment_ids = []
					for ( let value of obj ) {
						attachment_ids.push(value.attachment_id)
					}
					vm.$http.get( '/wp-json/wp/v2/media', {
						params: { per_page: 100, page: 1, include: attachment_ids }
					} )
					.then(function(response){
						vm.posts = response.data
					}, function(error){
						console.log(error.statusText);
					});
				}
			},
			emptyLightbox: function() {
				const vm = this;
				vm.$cookie.delete('sell_media_lightbox')
				vm.title = vm.title_empty
				vm.$set(vm, 'posts', {})
			}
		},
	}
</script>