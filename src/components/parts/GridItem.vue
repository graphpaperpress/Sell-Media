<template>
	<div :class="className" class="column is-mobile">
		<router-link :to="{ name: 'item', params: { slug:post.slug }}">
			<img v-if="post.sell_media_featured_image" :src="post.sell_media_featured_image.sizes.medium[0]" :alt="post.alt">
		</router-link>	
		<h2>{{ post.title.rendered }}</h2>
		<modal v-if="showModal" @closeModal="showModal = false" :post="post"></modal>
		<button class="button" @click="showModal = true">Quick View</button>
	</div>
</template>

<script>
	export default {

		props: ['post'],

		data: function () {
			return {
				showModal: false,
				quick_view_label: sell_media.quick_view_label,
				setting: sell_media.thumbnail_layout,
				className: ''
			}
		},

		mounted: function() {
			this.css();
		},

		methods: {

			css: function() {
				if ( 'sell-media-two-col' === this.setting ) {
					this.className = 'is-half';
				}
				if ( 'sell-media-three-col' === this.setting ) {
					this.className = 'is-one-third';
				}
				if ( 'sell-media-four-col' === this.setting ) {
					this.className = 'is-one-quarter';
				}
				if ( 'sell-media-five-col' === this.setting ) {
					this.className = 'is-one-fifth';
				}
				if ( 'sell-media-masonry' === this.setting ) {
					this.className = 'is-masonry';
				}
				if ( 'sell-media-horizontal-masonry' === this.setting ) {
					this.className = 'is-horizontal-masonry';
				}
			}

		}
    }
</script>