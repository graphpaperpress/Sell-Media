<template>

	<waterfall
	:line="line"
	:line-gap="200"
	:min-line-gap="100"
	:max-line-gap="220"
	:single-max-width="300"
	:fixed-height="200"
	:watch="posts"
	@reflowed="reflowed"
	ref="waterfall"
	>
		<!-- each component is wrapped by a waterfall slot -->
		<waterfall-slot
		v-for="(post, index) in posts"
		:width="post.sell_media_featured_image.sizes.medium[1]"
		:height="post.sell_media_featured_image.sizes.medium[2]"
		:order="index"
		:key="post.index"
		move-class="post-move"
		>
			<thumbnail :post="post" :index="post.index"></thumbnail>
		</waterfall-slot>
	</waterfall>

</template>

<script>

import Waterfall from 'vue-waterfall/lib/waterfall'
import WaterfallSlot from 'vue-waterfall/lib/waterfall-slot'

	export default {

		props: ['posts'],

		data: function() {
			return {

				line: '',
				isBusy: false

			}
		},

		created: function() {
			let layout = sell_media.thumbnail_layout;
			if ( layout === 'sell-media-horizontal-masonry' ) {
				this.line = 'h';
			} else {
				this.line = 'v';
			}
		},

		methods: {
			reflowed: function() {
				this.isBusy = false
			}

		},

		components: {
			Waterfall,
			WaterfallSlot
		}
	}

</script>

<style>

	.post-move {
		transition: all .5s cubic-bezier(.55,0,.1,1);
		-webkit-transition: all .5s cubic-bezier(.55,0,.1,1);
	}

	.item {
		position: absolute;
		top: 5px;
		left: 5px;
		right: 5px;
		bottom: 5px;
	}

</style>
