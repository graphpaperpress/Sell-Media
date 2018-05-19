<template>
	<div
	:class="[{ active: visible }, gridLayout, 'has-' + quickViewStyle]"
	ref="itemContainer">
		<div class="item-link" @mouseover="quickViewVisible = true" @mouseleave="quickViewVisible = false">
			<router-link :to="{ name: 'item', params: { slug:post.slug }}">
				<img v-if="post.sell_media_featured_image" :src="post.sell_media_featured_image.sizes[thumbnailCrop][0]" :alt="post.alt">
			</router-link>
			<div class="quick-view" @click="handle($event, post.slug)">{{ quick_view_label }}</div>
		</div>
		
		<h2 v-if="showTitles">{{ post.title.rendered }}</h2>

		<template v-if="quickViewStyle === 'expander-related'">
			<expander-related ref="preview" v-if="visible" @closeModal="visible = false" :post="post"></expander-related>
		</template>
		<template v-else-if="quickViewStyle === 'expander'">
			<expander ref="preview" v-if="visible" @closeModal="visible = false" :post="post"></expander>
		</template>
		<template v-else>
			<modal ref="preview" v-if="visible" @closeModal="visible = false" :post="post"></modal>
		</template>

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
      quickViewStyle: sell_media.quick_view_style ? sell_media.quick_view_style : 'modal',
      quickViewVisible: false,
      thumbnailCrop: sell_media.thumbnail_crop,
      gridLayout: this.$store.getters.gridLayout
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
    },

    handle(event, slug){
      if (event.metaKey){
        this.openInNewWindow(slug)
      } else {
        this.visible = !this.visible
      }
    },

    openInNewWindow(slug){
      let routeData = this.$router.resolve({name: 'item', params: { slug: slug }})
      window.open(routeData.href, '_blank');
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

<style lang="scss">

	$white-color: #fff;
	$black-color: #000;

	.is-horizontal-masonry {
		
		&.active {
			height: 750px;

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
					border-bottom: 12px solid $black-color;
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
