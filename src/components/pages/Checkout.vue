<template>

	<div class="checkout">

		<p v-if="!products.length">
			{{ labels.empty }}.
			<router-link :to="{ name: 'archive' }">{{ labels.visit }} &raquo;</router-link>
		</p>

		<div class="cart" v-else>
			
			<!-- <cart-steps></cart-steps> -->

			<!-- headings -->
			<div class="columns is-mobile headings is-uppercase has-text-weight-bold">
				<div class="column">{{ labels.product }}</div>
				<div class="column is-4 is-hidden-mobile">{{ labels.description }}</div>
				<div class="column">{{ labels.qty }}</div>
				<div class="column">{{ labels.price }}</div>
				<div class="column has-text-right">{{ labels.sub_total }}</div>
			</div>

			<!-- products -->
			<div class="columns is-mobile is-vcentered products" v-for="(product, index) in products" :key="index">
				<div class="column">
					<img :src="product.img" />
					<p class="is-hidden-mobile">{{ product.title }}</p>
				</div>
				<div class="column is-4 is-hidden-mobile">{{ product.price_name }} - {{ product.price_desc }}</div>
				<div class="column">
					<div class="field has-addons">
						<p class="control">
							<button :disabled="product.qty <= 0" class="button is-small" @click="product.qty -= 1">-</button>
						</p>
						<p class="control">
							<input
							v-model.number="product.qty"
							class="input is-small"
							type="number"
							step="1"
							min="1"
							max="99"
							@change="updateQuantity(product)">
						</p>
						<p class="control">
							<button class="button is-small" @click="product.qty += 1">+</button>
						</p>
					</div>
				</div>
				<div class="column">{{ currency_symbol }}{{ product.price }}</div>
				<div class="column has-text-right">
					{{ currency_symbol }}{{ subsubtotal(product) }}
					 <span class="dashicons dashicons-no-alt" @click="removeProduct(product)"></span>
				</div>
			</div>

			<!-- totals -->
			<div class="totals has-text-right is-uppercase">
				<div class="subtotal item">
					{{ labels.sub_total }}: <span class="value">{{ currency_symbol }}{{ subtotal }}</span>
				</div>
				<div class="tax item">
					{{ labels.tax }} ({{ tax_rate * 100 + '&#37;' }}): <span class="value">{{ currency_symbol }}{{ tax }}</span>
				</div>
				<div class="shipping item">
					{{ labels.shipping }}: <span class="value">{{ currency_symbol }}{{ shipping }}</span>
				</div>
				<div class="total item has-text-weight-bold">
					{{ labels.total }}: <span class="value">{{ currency_symbol }}{{ total }}</span>
				</div>
			</div>
		</div>
	</div>

</template>

<script>

	export default {

		data: function() {
			return {
				products: this.$store.state.cart,
				currency_symbol: sell_media.currency_symbol,
				labels: sell_media.cart_labels,
				tax_rate: sell_media.tax,
				shipping_settings: sell_media_reprints
			}
		},

		methods: {

			subsubtotal: function(product){
				return ( product.price * product.qty ).toFixed(2);
			},

			updateQuantity: function(product) {
				this.$store.commit( 'updateQuantity', product );
			},

			removeProduct: function(product) {
				this.$store.commit( 'removeFromCart', product );
			}

		},

		computed: {
			subtotal: function(){
				return this.products.reduce(function(subtotal, product){
					return Number(subtotal + product.price * product.qty)
				},0).toFixed(2);
			},
			tax: function(){
				return Number(this.subtotal * this.tax_rate ).toFixed(2)
			},
			shipping: function(){
				if ( this.shipping_settings.reprints_shipping === 'shippingFlatRate' ) {
					return Number(this.shipping_settings.reprints_shipping_flat_rate).toFixed(2);
				}
				if ( this.shipping_settings.reprints_shipping === 'shippingQuantityRate' ) {
					return Number(this.products.length * this.shipping_settings.reprints_shipping_flat_rate).toFixed(2);
				}
				if ( this.shipping_settings.reprints_shipping === 'shippingTotalRate' ) {
					return Number(this.subtotal * this.shipping_settings.reprints_shipping_flat_rate).toFixed(2);
				}
			},
			total: function(){
				return Number( Number(this.subtotal) + Number(this.tax) + Number(this.shipping) ).toFixed(2)
			}
		}


	}

</script>

<style lang="scss" scoped>

	.cart img {
		max-width: 75px;
		height: auto;
	}

	.headings {
		border-bottom: 2px solid #ddd;
		margin-bottom: 2rem;
		padding-bottom: .75rem;
	}

	.products {
		border-bottom: 1px solid #ddd;
		margin-bottom: 2rem;
		padding: 1rem 0;

		input[type=number] {
			text-align: center;
		}

		input[type=number]::-webkit-inner-spin-button,
		input[type=number]::-webkit-outer-spin-button {
			-webkit-appearance: none;
			margin: 0;
		}

		.dashicons-no-alt {
			color: #999;
			transition: transform .5s;
		}

		.dashicons-no-alt:hover {
			cursor: pointer;
			transform: scale(1.2);
		}
	}

	.totals .item {
		margin: 0 0 .75rem;
	}

	.totals .value {
		display: inline-block;
		width: 150px;
	}

</style>
