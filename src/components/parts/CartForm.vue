<template>

	<section class="form">

		<form @submit.prevent="validateForm">

			<!-- Add check if subscription -->

			<template v-if="showTabs(post)">

				<div class="tabs">
					<ul>
						<li v-for="(field, index) in fields" :key="index" :class="{ 'is-active': (active == field) }">
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

				<div class="button-wrap">
					<div class="buttons">
						<button :class="buttonSize" class="button is-info" @click="addTo('cart')" :title="add">{{ add }}</button>

						<button :class="buttonSize" class="button is-info" @click="addTo('lightbox')" :title="save_to_lightbox">
							<span class="icon">
								<icon name="heart"></icon>
							</span>
						</button>
					</div>
					<div class="notifications">
						<div v-if="added" class="content" :class="textSize">
							{{ added_to_cart }}
							<router-link :to="{ name: 'checkout' }" class="view">{{ view_cart }} &raquo;</router-link>
						</div>

						<div v-if="saved" class="content" :class="textSize">
							<router-link :to="{ name: 'lightbox' }" class="view">{{ view_lightbox }} &raquo;</router-link>
						</div>
					</div>
				</div>
			</div>

		</form>

	</section>

</template>

<script>
import mixinUser from '@/mixins/user'
import CartFieldSelect from './CartFieldSelect.vue'
import CartFieldRadio from './CartFieldRadio.vue'

export default {
  mixins: [mixinUser],

  props: ['post', 'attachment', 'multiple'],

  data(){

    return {
      product: {},
      fields: [],
      active: '',
      total: 0,
      currency_symbol: sell_media.currency_symbol,
      quickViewStyle: sell_media.quick_view_style ? sell_media.quick_view_style : 'modal',

      added: false,
      add: sell_media.cart_labels.add_to_cart,
      added_to_cart: sell_media.cart_labels.added_to_cart,
      view_cart: sell_media.cart_labels.view_cart,

      saved: false,
      save_to_lightbox: sell_media.lightbox_labels.save,
      saved_to_lightbox: sell_media.lightbox_labels.saved,
      view_lightbox: sell_media.lightbox_labels.view,

      disabled: true,
      buttonSize: ('attachment' === this.$route.name || 'item' === this.$route.name) ? 'is-large' : '',
      textSize: ('attachment' === this.$route.name || 'item' === this.$route.name) ? 'is-normal' : 'is-size-7',
    }
  },

  created(){
    // set fields object, make prints first tab
    this.fields = [...this.post.sell_media_meta.sell].reverse();
    // set active tab to first field and show corresponding price group
    this.active = this.fields[0]
  },

  methods: {

    showTabs(post){
      if ( post.sell_media_meta.sell.length > 1 ) {
        return true
      }
    },

    selectedTab(field){
      this.active = field
      this.disabled = true
    },

    setCart(value){
      // this feels wrong
      // set product price, type and qty
      this.product = {
        'price_id': value.id,
        'price': Number(value.price).toFixed(2),
        'price_name': value.name,
        'price_desc': value.description,
        'type': value.type,
        'qty': 1
      }
      this.disabled = false
    },

    addTo($where){
      if (this.disabled) {
        return alert('Please select a size first');
      }
      // console.log(this.attachment)
      // attachment object data differs depending on which json api endpoint is requested.
      // so, we'll check for all the various locations in a logical order
      let img = ''
      if ( this.attachment.sizes && this.attachment.sizes.medium ) {
        img = this.attachment.sizes.medium[0]
      } else if ( this.attachment.source_url ) {
        img = this.attachment.source_url
      } else {
        img = this.post.sell_media_featured_image.sizes.medium[0]
      }


      // this feels wrong
      // add currently visible post and attachment
      this.product = {
        'id': Number(this.post.id),
        'title': this.attachment.title,
        'attachment_id': Number(this.attachment.id),
        'img': img,
        'price_id': this.product.price_id,
        'price': Number(this.product.price).toFixed(2),
        'price_name': this.product.price_name,
        'price_desc': this.product.price_desc,
        'type': this.product.type,
        'qty': this.product.qty
      }

      if ( 'cart' === $where ) {
        this.$store.dispatch( 'addToCart', this.product )
        this.added = true
      }

      if ( 'lightbox' === $where ) {
        this.$store.dispatch( 'addToLightbox', this.product )
        this.saved = true
      }

      this.disabled = true
    },

    validateForm(){

    }
  },

  components: {
    'cart-field-select': CartFieldSelect,
    'cart-field-radio': CartFieldRadio
  }

}
</script>

<style lang="scss">

	.form {
		display: inline-block;
	}

	.expander {
		.view {
			color: #fff;
		}
	}

	.button-wrap {
		position: relative;
		float: left;

		.buttons {
			margin-bottom: 2rem;
		}

		.notifications {
			position: absolute;
			bottom: 0;
			left: 0;
		}
	}

</style>
