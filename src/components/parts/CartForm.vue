<template>

	<section class="form">

		<form @submit.prevent="validateForm">

			<!-- Add check if subscription -->

			<template v-if="showFields(post)">

				<div class="tabs">
					<ul>
						<li v-for="field in fields" :class="{ 'is-active': (active == field) }">
							<a href="javascript:void(0);" @click="activeField(field)">{{ field }}</a>
						</li>
					</ul>
				</div>

			</template>

			<div class="content">

				<cart-field-select v-for="field in fields" v-if="active == field" :key="field" :post="post" :field="field" v-on:selected="activateButton"></cart-field-select>

				<button class="button is-black" @click="addToCart(post.id)" v-bind:disabled="disabled">{{ add }}</button>

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
				fields: ['Prints', 'Downloads'],
				active: 'Prints',
				total: 0,
				size: '',
				currency_symbol: sell_media.currency_symbol,
				add: sell_media.cart_labels.add_to_cart,
				disabled: true,
			}
		},

		methods: {
			showFields: function(post) {
				if ( post.sell_media_meta.reprints_sell === 'both' ) {
					return true;
				}
			},

			activeField: function(field) {
				this.active = field;
				this.disabled = true; // disable add to cart button
			},

			addToCart: function(data) {
				let cart = JSON.parse(localStorage.getItem('sell_media_cart')) || [];
				cart.push(data);
				localStorage.setItem('sell_media_cart', JSON.stringify(cart));
			},

			validateForm() {

			},

			activateButton: function() {
				this.disabled = false;
				console.log('parent message:' + this.disabled );
			}
		},

		components: {
			'cart-field-select': CartFieldSelect
		}

	}
</script>