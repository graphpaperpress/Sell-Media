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

				<cart-field-select v-for="field in fields" v-if="active == field || post.sell_media_meta" :key="field" :post="post" :field="field" :active="active" @selected="activateButton"></cart-field-select>

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

		props: ['post'],

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
				domain: window.location.hostname,
			}
		},

		created: function() {
			let vm = this;

			// set fields object, make prints first tab
			vm.fields = vm.post.sell_media_meta.sell.reverse();
			// set active tab to first field and show corresponding price group
			vm.active = vm.fields[0];

			vm.cart.post_id = vm.post.id;
			// WRONG! Don't assume first attachment. Get visible attachment id.
			vm.cart.title = vm.post.sell_media_attachments[0].title;
			vm.cart.attachment_id = vm.post.sell_media_attachments[0].id;
			vm.cart.img = vm.post.sell_media_featured_image.sizes.medium[0];
		},

		methods: {

			showTabs: function(post) {
				if ( post.sell_media_meta.sell.length > 1 ) {
					return true;
				}
			},

			selectedTab: function(field) {
				this.active = field;
				this.disabled = true;
			},

			addToCart: function() {
				this.$store.commit( 'addToCart', this.cart );
				this.disabled = true;
				this.added = true;
			},

			addToLightbox: function() {
				let item = {
					'post_id': this.post_id,
					'attachment_id': this.attachment_id,
					'price_id': this.price_id,
				}
				this.$store.commit( 'addToLightbox', item );
				this.saved = true;
			},

			validateForm() {

			},

			activateButton: function(value) {
				this.disabled = false;
				this.cart.price_id = value.id;
				this.cart.price = value.price.toFixed(2);
				this.cart.price_name = value.name;
				this.cart.price_desc = value.description;
				this.cart.type = value.type;
				this.cart.qty = 1;
			}
		},

		components: {
			'cart-field-select': CartFieldSelect
		}

	}
</script>
