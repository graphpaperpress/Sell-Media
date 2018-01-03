<template>
	<div :class="{ active: visible }" class="item" ref="itemContainer">
		<div class="item-link" @mouseover="quickViewVisible = true" @mouseleave="quickViewVisible = false">
			<router-link :to="{ name: 'item', params: { slug:post.slug }}">
				<img v-if="post.sell_media_featured_image" :src="post.sell_media_featured_image.sizes[thumbnailCrop][0]" :alt="post.alt">
			</router-link>
			<div class="quick-view" v-if="showQuickView && quickViewVisible" @click="visible = !visible">{{ quick_view_label }}</div>
		</div>
		<h2 v-if="showTitles">{{ post.title.rendered }}</h2>
		<modal ref="preview" v-if="visible" @closeModal="visible = false" :post="post"></modal>
<!-- 		<expander ref="preview" v-if="visible" @closeModal="visible = false" :post="post"></expander> -->
	</div>
</template>

<script>
	export default {

		props: ['post'],

		data: function () {
			return {
				visible: false,
				quick_view_label: sell_media.quick_view_label,
				showTitles: sell_media.title == 1 ? true : false,
				showQuickView: sell_media.quick_view == 1 ? true : false,
				quickViewVisible: false,
				thumbnailCrop: sell_media.thumbnail_crop
			}
		},

		methods: {

			documentClick(e){
				let el = this.$refs.itemContainer
				let target = e.target
				if ( el !== target && !el.contains(target) ) {
					this.visible = false
				}
				// this.$nextTick(function() {
				// 	el.scrollIntoView({
				// 		behavior: 'smooth'
				// 	})
				// })
			}
		},

		created () {
			document.addEventListener('click', this.documentClick)
		},
		destroyed () {
			// important to clean up!!
			document.removeEventListener('click', this.documentClick)
		}
    }
</script>

<style lang="scss" scoped>

	$white-color: #fff;
	$black-color: #000;

	.item {
		padding: 5px;
	
		&.active {

			.item-link {
				position: relative;

				&:after {
					content: " ";
					position: absolute;
					bottom: -12px;
					left: 50%;
					right: 50%;
					width: 0;
					height: 0;
					border-left: 12px solid transparent;
					border-right: 12px solid transparent;
					border-bottom: 12px solid #333;
				}
			}
		}
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