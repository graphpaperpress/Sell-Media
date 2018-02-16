<template>

	<div v-if="loaded">

		<searchform @search="goToSearchResults"></searchform>

		<h2 class="title">{{ attachment.title }}</h2>

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
				<template v-else>
					<img :src="attachment.media_details.sizes.large.source_url" :alt="attachment.alt"/>
				</template>
			</div>

			<div :class="pageLayout.sidebar">
				<cart-form :key="attachment.slug" :post="product" :attachment="attachment" :multiple="multiple"></cart-form>
			</div>

		</div>
		<div class="post-content content" v-if="attachment.caption" v-html="attachment.caption.rendered"></div>
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
				attachment: {},
				type: '',
				loaded: false,
				multiple: false,
				pageTitle: '',
				pageLayout: this.$store.getters.pageLayout
			}
		},

		mounted() {
			this.getAttachment()
		},

		methods: {
			getAttachment(){
				const vm = this

				vm.$http.get( '/wp-json/wp/v2/media', {
					params: {
						slug: vm.$route.params.slug
					}
				} )
				.then(( res ) => {
					vm.attachment = res.data[0]
					vm.attachment.title = vm.attachment.title.rendered
					vm.pageTitle = vm.attachment.title.rendered
					vm.$store.dispatch('changeTitle', vm.pageTitle)
					vm.$store.dispatch('fetchPost', { include: vm.attachment.post })
				})
				.catch(( res ) => {
					console.log(`Something went wrong : ${res}`)
				})
			},

			goToSearchResults(search, search_type){
				const vm = this

				if ( search ) {
					vm.$router.push( { name: 'archive', query: { search: search, type: search_type } } )
				}
			}

		},

		components: {
			'searchform': SearchForm,
		}

	}
</script>
