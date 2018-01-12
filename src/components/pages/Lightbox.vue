<template>
	<div id="lightbox">
		<p @click="deleteLightbox" class="empty-lightbox" v-bind:title="title">{{ title }}</p>
		<div>
			<div class="columns is-multiline">
				<media v-for="media in medias" v-bind:m="media" :post="media"></media>
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
				lightbox: this.$store.state.lightbox,
				medias: {},
				media: '',
				added: false,
				title: sell_media.lightbox_labels.remove_all,
				title_empty: sell_media.lightbox_labels.empty,
			}
		},

		methods: {
			getMedias: function() {

				const vm = this;

				if ( Object.keys(vm.lightbox).length === 0 ) {
					vm.$set(vm, 'title', vm.title_empty)
				} else {
					let attachment_ids = []
					for ( let value of vm.lightbox ) {
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

			deleteLightbox: function() {
				const vm = this;
				vm.$store.commit( 'deleteLightbox' );
				vm.title = vm.title_empty
				vm.$set(vm, 'medias', {})
			}
		},

		components: {
            'media': Media
        }
	}
</script>
