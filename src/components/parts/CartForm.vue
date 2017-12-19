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

				<button class="button is-black" @click="addToCart(post.id,attachment_id)" :disabled="disabled">{{ add }}</button>

			</div>

			<div v-if="added" class="content">
				{{ added_to_cart }}
				<router-link :to="{ name: 'checkout' }">{{ view_cart }} &raquo;</router-link>
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
				post_id: '',
				attachment_id: '',
				price_id: '',
				fields: [],
				active: '',
				total: 0,
				currency_symbol: sell_media.currency_symbol,
				add: sell_media.cart_labels.add_to_cart,
				added: false,
				added_to_cart: sell_media.cart_labels.added_to_cart,
				view_cart: sell_media.cart_labels.view_cart,
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

			// set post id
			vm.post_id = vm.post.id;
			// set attachment id
			vm.attachment_id = vm.post.sell_media_attachments[0].id; // this is wrong. don't assume first attachment.
		},

		methods: {

			showTabs: function(post) {
				if ( post.sell_media_meta.sell.length > 1 ) {
					return true;
				}
			},

			selectedTab: function(field) {
				this.active = field;
				this.disabled = true; // disable add to cart button
			},

			addToCart: function(data) {
				// get existing cart data
				let cart = JSON.parse(this.$cookie.get('sell_media_cart')) || [];
				// add new cart data
				cart.push(
					{
						'post_id': this.post_id,
						'attachment_id': this.attachment_id,
						'price_id': this.price_id,
					}
				);
				// cookies only allow strings, so convert object into string
				this.$cookie.set(
					'sell_media_cart',
					JSON.stringify( cart ),
					{
						expires: 30,
						domain: this.domain
					}
				);
				this.disabled = true; // disable add to cart button
				this.added = true;
			},

			validateForm() {

			},

			activateButton: function(value) {
				this.disabled = false;
				this.price_id = value;
			}
		},

		components: {
			'cart-field-select': CartFieldSelect
		}

	}
</script>