<template>

	<div v-if="postLoaded">

		<searchform @search="goToSearchResults"></searchform>

		<h2 class="title">{{ post.title.rendered }}</h2>

		<div class="columns">
			<div :class="!multiple ? pageLayout.content : 'column'">
				<media :post="post" :type="type" @attachment="setAttachment"></media>
				<div class="post-content content" v-if="post.content" v-html="post.content.rendered"></div>
			</div>
			<div v-if="!multiple" :class="pageLayout.sidebar">
				<cart-form :key="post.slug" :post="post" :attachment="attachment" :multiple="multiple"></cart-form>
			</div>
		</div>

	</div>
  <div v-else>
    Loading...
  </div>

</template>

<script>
import SearchForm from '../parts/SearchForm.vue'
import mixinGlobal from '../../mixins/global'
import mixinProduct from '../../mixins/product'

	export default {
    mixins: [mixinGlobal, mixinProduct],

		data: function() {
			return {
				base_path: sell_media.site_url,
				attachment: {},
				attachments: {},
				multiple: false,
				type: '',
				image_size: 'large',
				pageTitle: '',
				pageLayout: this.$store.getters.pageLayout
			}
		},

		beforeMount: function() {
      this.$store.dispatch('fetchPost', { slug: this.$route.params.slug })
		},

		methods: {

			setAttachment(data){
				this.attachment = data
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
    },

    watch: {
      post(val) {
        if ( !val.id ) { return false }
        this.attachments = this.post.sell_media_attachments
        this.multiple = (this.attachments != null && this.attachments.length > 1) ? true : false
        this.type = this.post.sell_media_meta != null ? this.post.sell_media_meta.product_type[0] : ''
        this.$store.dispatch( 'changeTitle', this.post.title.rendered )
      }
    }

	}
</script>
