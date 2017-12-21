<template>
	<div class="item">
		<div class="item-link" @mouseover="quickViewVisible = true" @mouseleave="quickViewVisible = false">
			<router-link :to="{ name: 'item', params: { slug:post.slug }}">
				<img v-if="post.sell_media_featured_image" :src="post.sell_media_featured_image.sizes[thumbnailCrop][0]" :alt="post.alt">
			</router-link>
			<div class="quick-view" v-if="showQuickView && quickViewVisible" @click="toggleModal">{{ quick_view_label }}</div>
		</div>
		<h2 v-if="showTitles">{{ post.title.rendered }}</h2>
		<!-- <modal ref="preview" v-if="showModal" @closeModal="showModal = false" :post="post" :id="id"></modal> -->
		<expander ref="preview" v-if="showModal" @closeModal="showModal = false" :post="post" :id="id"></expander>
	</div>
</template>

<script>
	export default {

		props: ['post'],

		data: function () {
			return {
				showModal: false,
				quick_view_label: sell_media.quick_view_label,
				showTitles: sell_media.title == 1 ? true : false,
				showQuickView: sell_media.quick_view == 1 ? true : false,
				quickViewVisible: false,
				thumbnailCrop: sell_media.thumbnail_crop,
				id: null
			}
		},

		mounted () {
			this.id = 'preview-' + this._uid
		},

		methods: {

			toggleModal: function() {

				// let old_ele = document.querySelectorAll('.expander') || 0;
				// if ( old_ele.length > 0 ) {
				// 	console.log(old_ele)
				// 	old_ele.parentNode.removeChild( old_ele )
				// }

				this.showModal = true
				// modals added after mounted, so wait until nextTick
				this.$nextTick(function() {
			    	let ele = this.$refs.preview
			    	let box = document.getElementById(ele.$el.id)
					box.scrollIntoView({
						behavior: 'smooth'
					})
    			})
			}
		}
    }
</script>

<style lang="scss" scoped>

	$white-color: #fff;
	$black-color: #000;

	.item {
		padding: 5px;
	}

	.item-link {
		// float: left;
		position: relative;

		img {
			display: block;
		}
	}

	.quick-view {
		position: absolute;
		bottom: 0;
		padding: 10px;
		background: rgba($black-color,.5);
		left: 0;
		display: block;
		width: 100%;
		color: $white-color;
		z-index: 2;
		text-transform: uppercase;
		font-size: .8rem;
		letter-spacing: 1px;
		cursor: zoom-in;

		&:hover {
			background: rgba($black-color,.7);
		}
	}

</style>