<template>
	<div id="lightbox">
		<p v-on:click="emptyLightbox" class="empty-lightbox" v-bind:title="title">{{ title }}</p>
		<div>
			<div class="columns is-multiline">
				<media v-for="media in medias" v-bind:m="media"></media>
			</div>
		</div>
	</div>
</template>

<script>

	import Media from '../parts/Media.vue'

	export default {

		mounted: function() {
			this.getMedias()
		},

		data: function() {
			return {
				medias: {},
				media: '',
				title: sell_media.lightbox_labels.remove_all,
				title_empty: sell_media.lightbox_labels.empty,
			}
		},

		methods: {
			getMedias: function() {
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
						params: {
							per_page: 100,
							page: 1,
							include: attachment_ids
						}
					} )
					.then(function(response){
						vm.medias = response.data
					})
					.catch(function(error){
						console.log(error)
					})
				}
			},
			emptyLightbox: function() {
				const vm = this;
				vm.$cookie.delete('sell_media_lightbox')
				vm.title = vm.title_empty
				vm.$set(vm, 'medias', {})
			}
		},

		components: {
            'media': Media
        }
	}
</script>