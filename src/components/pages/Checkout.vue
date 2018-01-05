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
							class="input is-small has-min-width"
							type="number"
							step="1"
							min="1"
							max="99"
							@change="updateProduct(product)">
						</p>
						<p class="control">
							<button class="button is-small" @click="increaseQuantity(product)">+</button>
						</p>
					</div>
				</div>
				<div class="column">{{ currency_symbol }}{{ product.price }}</div>
				<div class="column has-text-right">
					{{ currency_symbol }}{{ subsubtotal(product) }}
					<span class="icon-x" @click="removeProduct(product)">&#10005;</span>
				</div>
			</div>

			<!-- totals -->
			<div class="totals has-text-right is-uppercase">
				<div class="subtotal item">
					{{ labels.sub_total }}: <span class="value">{{ currency_symbol }}{{ subtotal }}</span>
				</div>
				<div class="usage item" v-if="usageFee > 0">
					{{ labels.usage_fee }}: <span class="value">{{ currency_symbol }}{{ usageFee }} <span class="icon-x" @click="deleteUsage">&#10005;</span></span>
				</div>
				<div class="usage item" v-if="discount_code.active && !discount_code_added">
					{{discount_code.labels.discount_code}}: <span class="value"><input v-model="discount_code_value"/> <button @click="applyDiscountCode" :disabled="discount_code_validating">Apply</button>
						<em>{{discount_code_error}}</em>
					</span>
				</div>
				<div class="usage item" v-if="discount_code.active && discount_code_added">
					{{discount_code.labels.discount}}: <span class="value">{{ currency_symbol }}{{discount_code_amount}} <span class="dashicons dashicons-no-alt" @click="deleteDiscountCode"></span></span>
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
<!-- 				<div class="usage-description is-capitalized is-small" v-if="usageFee > 0"><span class="usage-term" v-for="item in usage" :key="item">
					{{ item.term.taxonomy }} ({{ item.term.name }})</span>
				</div> -->
			</div>

			<div class="useage-button-wrap has-text-right" v-if="usageNotSet">
				<button class="button is-primary is-large modal-button" @click="showModal = true">{{ labels.next }}</button>
				<cart-modal-license v-if="showModal" @closeModal="showModal = false"></cart-modal-license>
			</div>
			<div class="checkout-button-wrap has-text-right" v-else>
				<button class="button is-primary is-large" @click="checkout" :disabled="notValid">{{ labels.next }}</button>
			</div>
			<div class="continue-shopping has-text-right">
				<router-link :to="{ name: 'archive' }">{{ labels.continue_shopping }} &raquo;</router-link>
		</div>
	</div>

</template>

<script>

	export default {

		data: function() {
			return {
				products: this.$store.state.cart,
				pageTitle: 'Checkout',
				currency_symbol: sell_media.currency_symbol,
				labels: sell_media.cart_labels,
				tax_rate: sell_media.tax,
				shipping_settings: ( typeof sell_media_reprints != 'undefined' ) ? sell_media_reprints : null,
				showModal: false,
				notValid: false,
				discount_code: sell_media.discount_code,
				discount_code_added: false,
				discount_code_value: '',
				discount_code_amount: 0,
				discount_code_error: '',
				discount_code_validating: false
			}
		},

		methods: {

			subsubtotal: function(product){
				return ( product.price * product.qty ).toFixed(2);
			},

			updateProduct: function(product) {
				this.$store.commit( 'updateProduct', product );
			},

			decreaseQuantity: function(product) {
				product.qty -= 1
				this.$store.commit( 'updateProduct', product );
			},

			increaseQuantity: function(product) {
				product.qty += 1
				this.$store.commit( 'updateProduct', product );
			},

			removeProduct: function(product) {
				this.$store.commit( 'removeFromCart', product );
			},

			deleteUsage: function() {
				this.$store.commit( 'deleteUsage' );
			},

			checkout: function() {
				const vm = this;
				let item_ids = [];
				for (var i = 0; i < vm.products.length; i++) {
					item_ids.push(vm.products[i].id);
				}
				item_ids = item_ids.join();
				this.notValid = true;
            // Get items data.
				vm.$http.get( '/wp-json/wp/v2/sell_media_item', {
					params: {
						include: item_ids
					}
				} )
				.then( ( res ) => {
					var dataLength = res.data.length;
					if( dataLength > 0 ){
						for (var i = 0; i < vm.products.length; i++) {
							var item = res.data.find(function(data){
								return data.id === vm.products[i].id;
							});
							if( 'undefined' !== typeof item ) {
								var downloadsCounts = Object.keys( item.sell_media_pricing.downloads ).length;
								// If type is price-group then its downloads.
								if ( 'price-group' === vm.products[i].type &&  downloadsCounts > 0 ) {
									var downloads = item.sell_media_pricing.downloads.find(function(download){
										return download.id === vm.products[i].price_id;
									});
									if( 'undefined' !== typeof downloads ) {
										// Update price based on api.
										vm.products[i].price = downloads.price;
										vm.$store.commit( 'updateProduct', vm.products[i] );
									} else{
                              // if no download tax is found remove item from cart.
										vm.$store.commit( 'removeFromCart', vm.products[i] );
									}
								} else if( 'reprints-price-group' == vm.products[i].type ) {
                           // [TODO] condition for reprint to be added.
								} else {
									// if no type is found remove item from cart.
									vm.$store.commit( 'removeFromCart', vm.products[i] );
								}
							} else{
								// if no item is found remove item from cart.
								vm.$store.commit( 'removeFromCart', vm.products[i] );
							}
						}
					}
					this.notValid = false;
				} )
				.catch( ( res ) => {
					console.log( `Something went wrong : ${res}` );
					this.notValid = false;
				} );
			},

			applyDiscountCode: function() {
				this.discount_code_validating = true;

            // Let server values be.
				var discountCodeStatus = true;
            var discountAmount = 10;

				if('' === this.discount_code_value) {
					this.deleteDiscountCode();
					this.discount_code_error = this.discount_code.labels.error_no_code;
					return false;
				}

				if( false === discountCodeStatus ){
					this.deleteDiscountCode();
					this.discount_code_error = this.discount_code.labels.error_invalid_code;
					return false;
				}

				this.discount_code_amount = Number( discountAmount ).toFixed(2);
				this.discount_code_added = true;
				this.discount_code_validating = false;
			},

			deleteDiscountCode: function() {
				this.discount_code_added = false;
				this.discount_code_value = '';
				this.discount_code_amount = 0;
				this.discount_code_validating = false;
				this.discount_code_error = ''
			}

		},

		computed: {
			subtotal: function(){
				return this.products.reduce(function(subtotal, product){
					return Number(subtotal + product.price * product.qty)
				},0).toFixed(2);
			},
			downloadsSubtotal: function(){
				let subtotal = 0
				let products = this.products

				for ( let product in products ) {
					if ( products[product].type == 'price-group' ) {
						subtotal += Number(products[product].price * products[product].qty)
					}
				}

				return subtotal.toFixed(2)
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
				return Number( Number(this.subtotal) + Number(this.usageFee) + Number(this.tax) + Number(this.shipping)  - Number( this.discount_code_amount ) ).toFixed(2)
			},
			usage: function(){
				return this.$store.state.usage[0]
			},
			usageFee: function(){
				let usage = this.usage
				let sum = 0
				for ( let item in usage ) {
					let meta = usage[item].term.markup
					let val = 0
					if ( meta !== '' ) {
						val = meta.replace('%', '')
					}
					//let val = meta.replace('%', '')
					// change this.downloadSubtotal to this.subtotal to add markup to all product types
					sum += +Number(this.downloadsSubtotal * val / 100)
				}
				return sum.toFixed(2)
			},
			usageNotSet: function(){
				let status = false

				// licensing is enabled, but buyer hasn't selected usage
				if ( sell_media.licensing_enabled && typeof this.usage === 'undefined' ) {
					let products = this.products
					for ( let product in products ) {
						if ( products[product].type === 'price-group' ) {
							status = true
						}
					}
				}

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

		.has-min-width {
			min-width: 20px;
		}
	}

	.icon-x {
		color: #999;
		transition: all .25s;
		transition-timing: ease-in-out;

		&:hover {
			color: red;
			cursor: pointer;
			transform: rotate(90deg);
		}
	}

	.totals .item {
		margin: 0 0 .75rem;
	}

	.totals .value {
		display: inline-block;
		width: 150px;
	}

	.continue-shopping {
		margin: 2rem 0;
	}

</style>
