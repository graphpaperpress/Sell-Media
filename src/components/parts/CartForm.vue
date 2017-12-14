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

				<button class="button is-black" @click="addToCart(post.id)" :disabled="disabled">{{ add }}</button>

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
				fields: [],
				active: '',
				total: 0,
				size: '',
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
				this.$cookie.set(
					'sell_media_cart', 'Random value', {
						expires: 30,
						domain: this.domain
					}
				);
				this.disabled = true; // disable add to cart button
				this.added = true;
			},

			validateForm() {

			},

			activateButton: function() {
				this.disabled = false;
			}
		},

		components: {
			'cart-field-select': CartFieldSelect
		}

	}
</script>