<template>

	<div>

		<searchform @search="goToSearchResults"></searchform>
    <template v-if="attachmentLoaded">
		<h2 class="title">{{ attachment.title.rendered }}</h2>

		<div class="columns">

			<div :class="pageLayout.content">
				<template v-if="type === 'panorama' || type === 'dome'">
					<media-panorama :post="product"></media-panorama>
				</template>
				<template v-else-if="type === 'video'">
					<media-video :post="product"></media-video>
				</template>
				<template v-else-if="type === '360-video'">
					<media-video-360 :post="product"></media-video-360>
				</template>
				<template v->
					<img :src="attachment.media_details.sizes.large.source_url" :alt="attachment.alt"/>
				</template>
			</div>

			<div :class="pageLayout.sidebar">
				<cart-form :key="attachment.slug" :post="product" :attachment="attachment" :multiple="multiple"></cart-form>
			</div>

		</div>
		<div class="post-content content" v-if="attachment.caption" v-html="attachment.caption.rendered"></div>
    </template>
    <template v-else>
      <loader></loader>
    </template>
	</div>

</template>

<script>
import mixinGlobal from '../../mixins/global'
import mixinProduct from '../../mixins/product'
import SearchForm from '../parts/SearchForm.vue'

	export default {
    mixins: [mixinGlobal, mixinProduct],

		data() {
			return {
				type: '',
				multiple: false,
				pageLayout: this.$store.getters.pageLayout
			}
		},

		beforeMount() {
      this.$store.dispatch('fetchAttachment', { slug: this.$route.params.slug })
		},

		methods: {
			goToSearchResults(search, search_type){
				const vm = this

				if ( search ) {
					vm.$router.push( { name: 'archive', query: { search: search, type: search_type } } )
				}
			}

    	},

		watch: {
			attachment(val) {
				//console.log(val)
				this.$store.dispatch('changeTitle', val.title.rendered)
			}
		},

		components: {
			'searchform': SearchForm,
		}

	}
</script>
