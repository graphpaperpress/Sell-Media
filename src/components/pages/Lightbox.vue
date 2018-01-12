<template>
	<div id="lightbox">

		<div v-if="products.length > 0" class="content">
			<nav class="buttons has-addons">
				<button class="button" @click="addAll" :title="lightbox_labels.add_all">
					<span class="icon">
						<icon name="plus"></icon>
	      			</span>
	      			<span>{{ lightbox_labels.add_all }}</span>
	      		</button>
				<button class="button" @click="removeAll" :title="lightbox_labels.remove_all">
					<span class="icon">
						<icon name="minus"></icon>
	      			</span>
	      			<span>{{ lightbox_labels.remove_all }}</span>
	      		</button>
				<router-link :to="{ name: 'archive' }" class="button">
	      			<span>{{ search_labels.back }} &raquo;</span>
	      		</router-link>
	      	</nav>

	  		<p v-if="addedAllToCart">
				{{ cart_labels.added_to_cart }} <router-link :to="{ name: 'checkout' }">{{ cart_labels.view_cart }} &raquo;</router-link>
			</p>

		</div>

		<div v-else class="content">
			{{ lightbox_labels.empty }}. <router-link :to="{ name: 'archive' }">{{ search_labels.back }} &raquo;</router-link>
		</div>

		<div v-if="loaded" :class="gridContainer" class="columns is-multiline has-text-centered">
			<div v-for="(product, index) in products" :key="index" class="column" :class="gridLayout">
				<img :src="product.img" />
				<p class="is-hidden-mobile">{{ product.title }}</p>
				<p><span @click="remove(product)" :title="lightbox_labels.remove" class="remove">{{ lightbox_labels.remove }}</span></p>
			</div>
		</div>

		<div v-else class="loading">
			<button class="button is-white is-loading">Loading...</button>
			<div class="is-size-7 is-lowercase">loading {{ lightbox_labels.lightbox }} media</div>
		</div>
	</div>
</template>

<script>

	export default {

		data: function() {
			return {
				products: this.$store.state.lightbox,
				loaded: false,
				addedAllToCart: false,
				lightbox_labels: sell_media.lightbox_labels,
				cart_labels: sell_media.cart_labels,
				search_labels: sell_media.search_labels,
				gridContainer: this.$store.getters.gridLayout + '-container',
				gridLayout: this.$store.getters.gridLayout,
			}
		},

		mounted: function() {
			this.loaded = true
		},

		methods: {
			remove: function(product) {
				this.$store.commit( 'removeFromLightbox', product );
			},
			removeAll: function() {
				this.$store.commit( 'deleteLightbox' );
				this.products = {}
			},
			addAll: function() {
				for ( let product of this.products ) {
					this.$store.commit( 'addToCart', product )
				}
				this.$store.commit( 'deleteLightbox' )
				this.addedAllToCart = true
			}
		}
	}
</script>

<style lang="scss">
	.remove {
		color: #ff2b56;
		font-size: .75rem;
		cursor: pointer;
	}
</style>
