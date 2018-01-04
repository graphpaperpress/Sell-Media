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

				<cart-field-select v-for="field in fields" v-if="active == field || post.sell_media_meta" :key="field" :post="post" :field="field" :active="active" @selected="setCart"></cart-field-select>

				<button class="button is-black" @click="addToCart" :disabled="disabled">{{ add }}</button>

				<button class="button is-text" @click="addToLightbox" :disabled="disabled">{{ save_to_lightbox }}</button>

			</div>

			<div v-if="added" class="content">
				{{ added_to_cart }}
				<router-link :to="{ name: 'checkout' }">{{ view_cart }} &raquo;</router-link>
			</div>

			<div v-if="saved" class="content">
				{{ saved_to_lightbox }}
				<router-link :to="{ name: 'lightbox' }">{{ view_lightbox }} &raquo;</router-link>
			</div>

			</div>

		</form>

	</section>

</template>

<script>

	import CartFieldSelect from './CartFieldSelect.vue'

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
				this.cart = {
					'id': Number(this.post.id),
					'title': this.attachment.title,
					'attachment_id': Number(this.attachment.id),
					'img': this.multiple ? this.attachment.sizes.medium[0] : this.post.sell_media_featured_image.sizes.medium[0],
					'price_id': value.id,
					'price': Number(value.price).toFixed(2),
					'price_name': value.name,
					'price_desc': value.description,
					'type': value.type,
					'qty': 1
				}
				this.disabled = false
			},

			addToCart: function() {
				this.$store.commit( 'addToCart', this.cart )
				this.disabled = true
				this.added = true
			},

			addToLightbox: function() {
				let item = {
					'post_id': Number(this.post.id),
					'attachment_id': Number(this.attachment.id),
					'price_id': Number(this.price_id),
				}
				this.$store.commit( 'addToLightbox', item );
				this.saved = true;
			},

			validateForm() {

			}
		},

		components: {
			'cart-field-select': CartFieldSelect
		}

	}
</script>
