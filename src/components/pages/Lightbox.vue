<template>
	<div id="lightbox">

		<div v-if="lightbox.length > 0" class="content">
			<nav class="buttons">
				<button class="button is-small" @click="addAll" :title="lightbox_labels.add_all">
	      			<span>{{ lightbox_labels.add_all }}</span>
	      		</button>
				<button class="button is-small" @click="deleteLightbox" :title="lightbox_labels.remove_all">
	      			<span>{{ lightbox_labels.remove_all }}</span>
	      		</button>
	      	</nav>
		</div>

		<div v-else class="content">
			{{ lightbox_labels.empty }}. <router-link :to="{ name: 'archive' }">{{ search_labels.back }} &raquo;</router-link>
		</div>

		<div v-if="lightbox" class="columns is-multiline is-size-7">
			<div v-for="(product, index) in lightbox" :key="index" class="column is-one-quarter lightbox-item">
				<button class="delete" @click="removeFromLightbox(product)"></button>
				<img :src="product.img" />
				<p class="is-hidden-mobile">{{ product.title }}</p>
			</div>
		</div>

		<div v-else class="loading">
			<button class="button is-white is-loading">Loading...</button>
			<div class="is-size-7 is-lowercase">loading {{ lightbox_labels.lightbox }} media</div>
		</div>
	</div>
</template>

<script>
import mixinUser from '@/mixins/user'

export default {
  mixins: [mixinUser],

  data() {
    return {
      addedAllToCart: false,
      lightbox_labels: sell_media.lightbox_labels,
      cart_labels: sell_media.cart_labels,
      search_labels: sell_media.search_labels,
      gridContainer: this.$store.getters.gridLayout + '-container',
      gridLayout: this.$store.getters.gridLayout,
    }
  },

  methods: {
    addAll: function() {
      for ( let product of this.lightbox ) {
				this.addToCart(product)
			}
			this.deleteLightbox()
      this.$router.push( { 'name': 'checkout' } )
      this.pageTitle = this.$route.name
    }
  }
}
</script>

<style lang="scss" scoped>
	.lightbox-item {
		position: relative;

		.delete {
			position: absolute;
			top: 0;
			left: 0;
		}
	}
	.remove {
		color: #ff2b56;
		cursor: pointer;
	}
</style>
