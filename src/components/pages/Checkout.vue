<template>

	<div class="checkout">

		<p v-if="!products.length">
			{{ labels.empty }}.
			<router-link :to="{ name: 'archive' }">{{ labels.visit }} &raquo;</router-link>
		</p>

		<div class="cart" v-else>

			<!-- <cart-steps></cart-steps> -->

			<!-- headings -->
			<div class="columns is-mobile headings is-uppercase is-size-7-mobile has-text-weight-bold">
				<div class="column">{{ labels.product }}</div>
				<div class="column is-hidden-mobile">{{ labels.description }}</div>
				<div class="column">{{ labels.qty }}</div>
				<div class="column">{{ labels.price }}</div>
				<div class="column has-text-right">{{ labels.sub_total }}</div>
			</div>

			<!-- products -->
			<div class="columns is-mobile is-size-7-mobile is-vcentered products" v-for="(product, index) in products" :key="index">
				<div class="column">
					<img :src="product.img" />
					<p class="is-hidden-mobile is-size-7">{{ product.title }}</p>
				</div>
				<div class="column is-hidden-mobile">{{ product.price_name }} <span v-if="product.price_desc">-</span> {{ product.price_desc }}</div>
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
					<span class="icon-x" @click="removeFromCart(product)">&#10005;</span>
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

				<div class="tax item">
					{{ labels.tax }} ({{ tax_rate * 100 + '&#37;' }}): <span class="value">{{ currency_symbol }}{{ tax }}</span>
				</div>

				<div class="shipping item">
					{{ labels.shipping }}: <span class="value">{{ currency_symbol }}{{ shipping }}</span>
				</div>

				<div class="discount item" v-if="discountTotal > 0">
					{{ discount_code_labels.discount_code }}:
					<span class="value">
						- {{ currency_symbol }}{{ discountTotal }}
					</span>
				</div>

				<div class="total item has-text-weight-bold">
					{{ labels.total }}: <span class="value">{{ currency_symbol }}{{ total }}</span>
				</div>

				<div class="discount-control item" v-if="discount_code_labels">

					<div class="field has-addons has-addons-right">
						<div class="control has-icons-right">
							<input v-model="discount_code_value" class="input is-small" :class="{ 'is-invalid': discount && !discount.status }" type="text" :placeholder="discount_code_labels.discount_code" />
							<span v-if="discountTotal > 0" class="icon is-small is-right">
								<icon name="check" class="is-success"></icon>
							</span>
							<span v-if="discount && !discount.status" class="icon is-small is-right">
								<icon name="ban" class="is-danger"></icon>
							</span>
						</div>
						<div class="control">
							<button class="button is-small is-dark" @click="applyDiscountCode">{{ labels.apply }}</button>
						</div>
					</div>

					<p v-if="discount && !discount.status" class="help is-danger">{{ discount.message }}</p>

				</div>

			</div>

			<div v-if="usageNotSet" class="useage-button-wrap has-text-right">
				<button class="button is-info is-large modal-button" @click="showModal = true">{{ labels.next }}</button>
				<cart-modal-license v-if="showModal" @closeModal="showModal = false"></cart-modal-license>
			</div>
			<div v-else class="checkout-button-wrap has-text-right">
				<button class="button is-info is-large" @click="checkout" :disabled="notValid">{{ labels.next }}</button>
			</div>
			<div class="continue-shopping has-text-right">
				<router-link :to="{ name: 'archive' }">{{ labels.continue_shopping }} &raquo;</router-link>
			</div>
			<div v-if="processing">
				<button class="button is-loading is-large">Processing your payment</button>
			</div>
		</div>
	</div>

</template>

<script>
import mixinGlobal from '@/mixins/global'
import mixinUser from '@/mixins/user'

export default {
  mixins: [mixinGlobal, mixinUser],

  data() {
    return {
      currency_symbol: sell_media.currency_symbol,
      labels: sell_media.cart_labels,
      tax_rate: sell_media.tax,
      shipping_settings: ( typeof sell_media_reprints != 'undefined' ) ? sell_media_reprints : null,
      showModal: false,
      notValid: false,
      discount: false,
      discount_code_labels: sell_media.discount_code_labels,
      token: null,
      processing: false,
      discount_code_value: ''
    }
  },

  mounted(){
    this.$store.dispatch( 'changeTitle', sell_media.checkout_path )
  },

  methods: {

    subsubtotal(product){
      return ( product.price * product.qty ).toFixed(2)
    },

    updateProduct(product){
      this.$store.dispatch( 'updateCartProduct', product )
    },

    decreaseQuantity(product){
      product.qty -= 1
      this.$store.dispatch( 'updateCartProduct', product )
    },

    increaseQuantity(product){
      product.qty += 1
      this.$store.dispatch( 'updateCartProduct', product )
    },

    deleteUsage(){
      this.$store.dispatch( 'deleteUsage' )
    },

    checkout(){
      const vm = this
      // this.$checkout.close()
      // is also available.
      vm.$checkout.open({
        currency: sell_media.currency,
        amount: vm.total * 100,
        token: (token, args) => {
          // vm.submit(token)
          vm.token = JSON.stringify(token, null, 2)
          vm.processing = true

          vm.$http.post( sell_media.ajaxurl + '?action=charge', {
            token: token,
            args: args,
            _wpnonce: sell_media.nonce,
            type: 'stripe',
            discount: vm.discount_code_value,
            discount_id: ( false !== vm.discount ) ? vm.discount.id : null,
            // encode these?
            cart: localStorage.getItem('sell-media-cart'),
            usage: localStorage.getItem('sell-media-usage')
          } )
            .then( ( res ) => {
              // console.dir(res.data)
              vm.processing = false
              this.$store.dispatch( 'deleteCart' )
              this.$store.dispatch( 'deleteUsage' )
              return window.location = res.data.url
            } )
            .catch( ( res ) => {
              console.log( `Something went wrong : ${res}` )
            } )
        }
      })
    },

    applyDiscountCode(){
      const vm = this

      if ('' === vm.discount_code_value) {
        return vm.discount = false
      }

      vm.$http.get( '/wp-json/sell-media/v2/api', {
        params: {
          action: 'validate_discount_code',
          discount_code: vm.discount_code_value
        }
      } )
        .then( ( res ) => {
          return vm.discount = res.data
        } )
        .catch( ( res ) => {
          console.log( `Something went wrong : ${res}` )
        } )

    }

  },

  computed: {
    products() {
      return this.cart
    },
    subtotal(){
      return this.products.reduce(function(subtotal, product){
        return Number(subtotal + product.price * product.qty)
      },0).toFixed(2)
    },
    downloadsSubtotal(){
      let subtotal = 0
      let products = this.products

      for ( let product in products ) {
        if ( products[product].type == 'price-group' ) {
          subtotal += Number(products[product].price * products[product].qty)
        }
      }

      return subtotal.toFixed(2)
    },
    tax(){
      return Number(this.subtotal * this.tax_rate ).toFixed(2)
    },
    shipping(){
      if ( ! this.shipping_settings ) {
        return 0
      }
      if ( this.shipping_settings.reprints_shipping === 'shippingFlatRate' ) {
        return Number(this.shipping_settings.reprints_shipping_flat_rate).toFixed(2)
      }
      if ( this.shipping_settings.reprints_shipping === 'shippingQuantityRate' ) {
        return Number(this.products.length * this.shipping_settings.reprints_shipping_flat_rate).toFixed(2)
      }
      if ( this.shipping_settings.reprints_shipping === 'shippingTotalRate' ) {
        return Number(this.subtotal * this.shipping_settings.reprints_shipping_flat_rate).toFixed(2)
      }
    },
    total(){
      return Number( Number(this.subtotal) + Number(this.usageFee) + Number(this.tax) + Number(this.shipping) - Number( this.discountTotal ) ).toFixed(2)
    },
    usageFee(){
      let usage = this.usage[0]
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
    usageNotSet(){
      let status = false

      // licensing is enabled, but buyer hasn't selected usage
      if ( sell_media.licensing_enabled && (typeof this.usage === 'undefined' || this.usage.length === 0) ) {
        let products = this.products
        for ( let product in products ) {
          if ( products[product].type === 'price-group' ) {
            status = true
          }
        }
      }

      return status
    },
    discountTotal(){

      if ( !this.discount || false === this.discount.status ) {
        return 0
      }

      let amount = Number(this.discount.amount).toFixed(2)
      let type = this.discount.type
      let discountAmount = 'flat' === type ? amount : amount * 0.01 * this.subtotal

      return Number( discountAmount ).toFixed(2)
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

	.is-danger.fa-icon {
		color: #ff3860;
	}

	.is-success.fa-icon {
		color: #23d160;
	}

	.is-invalid {
		animation: shake 0.82s cubic-bezier(.36,.07,.19,.97) both;
		transform: translate3d(0, 0, 0);
		backface-visibility: hidden;
		perspective: 1000px;
	}

	@keyframes shake {
		10%, 90% {
			transform: translate3d(-1px, 0, 0);
		}

		20%, 80% {
			transform: translate3d(2px, 0, 0);
		}

		30%, 50%, 70% {
			transform: translate3d(-4px, 0, 0);
		}

		40%, 60% {
			transform: translate3d(4px, 0, 0);
		}
	}

</style>
