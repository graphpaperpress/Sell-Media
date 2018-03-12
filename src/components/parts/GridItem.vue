<template>
	<div :class="className" class="column is-mobile">
		<div class="item-link" @mouseover="quickViewVisible = true" @mouseleave="quickViewVisible = false">
			<router-link :to="{ name: 'item', params: { slug:post.slug }}">
				<img v-if="post.sell_media_featured_image" :src="post.sell_media_featured_image.sizes.medium[0]" :alt="post.alt">
			</router-link>
			<div class="quick-view" v-if="showQuickView && quickViewVisible" @click="showModal = true">{{ quick_view_label }}</div>
		</div>
		<h2 v-if="showTitles">{{ post.title.rendered }}</h2>
		<modal v-if="showModal" @closeModal="showModal = false" :post="post"></modal>
	</div>
</template>

<script>
export default {

  props: ['post'],

  data: function () {
    return {
      showModal: false,
      quick_view_label: sell_media.quick_view_label,
      layout: sell_media.thumbnail_layout,
      className: '',
      showTitles: sell_media.title == 1 ? true : false,
      showQuickView: sell_media.quick_view == 1 ? true : false,
      quickViewVisible: false,
    }
  },

  mounted: function() {
    this.css();
  },

  methods: {

    css: function() {
      if ( 'sell-media-two-col' === this.layout ) {
        this.className = 'is-half';
      }
      if ( 'sell-media-three-col' === this.layout ) {
        this.className = 'is-one-third';
      }
      if ( 'sell-media-four-col' === this.layout ) {
        this.className = 'is-one-quarter';
      }
      if ( 'sell-media-five-col' === this.layout ) {
        this.className = 'is-one-fifth';
      }
      if ( 'sell-media-masonry' === this.layout ) {
        this.className = 'is-masonry';
      }
      if ( 'sell-media-horizontal-masonry' === this.layout ) {
        this.className = 'is-horizontal-masonry';
      }
    }

  }
}
</script>

<style lang="scss" scoped>

	$white-color: #fff;
	$black-color: #000;

	.item-link {
		float: left;
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