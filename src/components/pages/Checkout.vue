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
							<button :disabled="product.qty <= 0" class="button is-small" @click="decreaseQuantity(product)">-</button>
						</p>
						<p class="control">
							<input
							disabled
							v-model.number="product.qty"
							class="input is-small"
							type="number"
							step="1"
							min="1"
							max="99"
							@change="updateQuantity(product)">
						</p>
						<p class="control">
							<button class="button is-small" @click="increaseQuantity(product)">+</button>
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

			<template v-if="licensing_enabled && hasDownloads">
				<button class="button is-primary is-large modal-button" @click="showModal = true">{{ labels.next }}</button>
				<cart-modal-license v-if="showModal" @selectedUsage="selectedUsage"></cart-modal-license>
			</template>
			<template v-else>
				<button class="button is-primary is-large">{{ labels.next }}</button>
			</template>
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
				shipping_settings: ( typeof sell_media_reprints != 'undefined' ) ? sell_media_reprints : null,
				showModal: false,
				licensing_enabled: sell_media.licensing_enabled,
			}
		},

		methods: {

			subsubtotal: function(product){
				return ( product.price * product.qty ).toFixed(2);
			},

			updateQuantity: function(product) {
				this.$store.commit( 'updateQuantity', product );
			},

			decreaseQuantity: function(product) {
				product.qty -= 1
				this.$store.commit( 'updateQuantity', product );
			},

			increaseQuantity: function(product) {
				product.qty += 1
				this.$store.commit( 'updateQuantity', product );
			},

			removeProduct: function(product) {
				this.$store.commit( 'removeFromCart', product );
			},

			selectedUsage: function(usage) {
				this.showModal = false
				console.log(usage)
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
				if ( ! this.shipping_settings ) {
					return 0;
				}
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
			},
			hasDownloads: function(){
				let status = false
				let products = this.products
				products.forEach(function(product) {
					if (product.type === 'price-group') {
						status = true
					}
				});
				return status
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

			&[disabled] {
				opacity: 1;
				border-color: #dbdbdb;
			}
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
