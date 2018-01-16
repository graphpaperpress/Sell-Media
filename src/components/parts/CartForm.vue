<template>

	<section class="form">

		<form @submit.prevent="validateForm">

			<!-- Add check if subscription -->

			<template v-if="showTabs(post)">

				<div class="tabs">
					<ul>
						<li v-for="field in fields" :class="{ 'is-active': (active == field) }">
							<a href="javascript:void(0);" @click="selectedTab(field)">{{ field }}</a>
						</li>
					</ul>
				</div>

			</template>

			<div class="content">
				
				<template v-if="quickViewStyle === 'expander-related'">
					<cart-field-radio v-for="field in fields" v-if="active == field || post.sell_media_meta" :key="field" :post="post" :field="field" :active="active" @selected="setCart"></cart-field-radio>
				</template>
				<template v-else>
					<cart-field-select v-for="field in fields" v-if="active == field || post.sell_media_meta" :key="field" :post="post" :field="field" :active="active" @selected="setCart"></cart-field-select>
				</template>
				
				<div class="buttons">
					<button class="button is-info" @click="addTo('cart')" :disabled="disabled">{{ add }}</button>

					<button class="button is-info" @click="addTo('lightbox')" :disabled="disabled" :title="save_to_lightbox">
						<span class="icon">
							<icon name="heart"></icon>
						</span>
					</button>

				</div>

			</div>

			<div v-if="added" class="content is-size-7">
				{{ added_to_cart }}
				<router-link :to="{ name: 'checkout' }" class="view">{{ view_cart }} &raquo;</router-link>
			</div>

			<div v-if="saved" class="content">
				<router-link :to="{ name: 'lightbox' }" class="view is-size-7">{{ view_lightbox }} &raquo;</router-link>
			</div>

		</form>

	</section>

</template>

<script>

	import CartFieldSelect from './CartFieldSelect.vue'
	import CartFieldRadio from './CartFieldRadio.vue'

	export default {

		props: ['post', 'attachment', 'multiple'],

		data: function() {

			return {
				cart: {},
				fields: [],
				active: '',
				total: 0,
				currency_symbol: sell_media.currency_symbol,

				added: false,
				add: sell_media.cart_labels.add_to_cart,
				added_to_cart: sell_media.cart_labels.added_to_cart,
				view_cart: sell_media.cart_labels.view_cart,

				saved: false,
				save_to_lightbox: sell_media.lightbox_labels.save,
				saved_to_lightbox: sell_media.lightbox_labels.saved,
				view_lightbox: sell_media.lightbox_labels.view,

				disabled: true,
			}
		},

		created: function() {
			// set fields object, make prints first tab
			this.fields = this.post.sell_media_meta.sell.reverse()
			// set active tab to first field and show corresponding price group
			this.active = this.fields[0]
		},

		methods: {

			showTabs: function(post) {
				if ( post.sell_media_meta.sell.length > 1 ) {
					return true
				}
			},

			selectedTab: function(field) {
				this.active = field
				this.disabled = true
			},

			setCart: function(value) {
				// this feels wrong
				// set product price, type and qty
				this.cart = {
					'price_id': value.id,
					'price': Number(value.price).toFixed(2),
					'price_name': value.name,
					'price_desc': value.description,
					'type': value.type,
					'qty': 1
				}
				this.disabled = false
			},

			addTo: function($where) {
				// this feels wrong
				// add currently visible post and attachment
				this.cart = {
					'id': Number(this.post.id),
					'title': this.attachment.title,
					'attachment_id': Number(this.attachment.id),
					'img': this.multiple ? this.attachment.sizes.medium[0] : this.post.sell_media_featured_image.sizes.medium[0],
					'price_id': this.cart.price_id,
					'price': Number(this.cart.price).toFixed(2),
					'price_name': this.cart.price_name,
					'price_desc': this.cart.price_desc,
					'type': this.cart.type,
					'qty': this.cart.qty
				}

				if ( 'cart' === $where ) {
					this.$store.commit( 'addToCart', this.cart )
					this.added = true
				}

				if ( 'lightbox' === $where ) {
					this.$store.commit( 'addToLightbox', this.cart )
					this.saved = true
				}

				this.disabled = true
			},

			validateForm() {

			}
		},

		components: {
			'cart-field-select': CartFieldSelect,
			'cart-field-radio': CartFieldRadio
		}

	}
</script>

<style lang="scss">
	
	.view {
		color: #fff;
	}

</style>
