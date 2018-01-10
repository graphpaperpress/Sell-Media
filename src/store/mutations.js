export const CART_KEY = 'sell-media-cart'
export const USAGE_KEY = 'sell-media-usage'
export const LIGHTBOX_KEY = 'sell-media-lightbox'

export const state = {
  cart: JSON.parse(window.localStorage.getItem(CART_KEY) || '[]'),
  usage: JSON.parse(window.localStorage.getItem(USAGE_KEY) || '[]'),
  lightbox: JSON.parse(window.localStorage.getItem(LIGHTBOX_KEY) || '[]'),
  title: null,
  user: null
}

export const mutations = {
  addToCart (state, value) {
    state.cart.push(value)
  },

  // you cannot alter an object's properties directly, otherwise
  // the component will loose reactivity
  // https://vuejs.org/v2/guide/list.html#Caveats
  updateProduct (state, value) {
    state.cart.splice(state.cart.indexOf(value), 1, value)
  },

  removeFromCart (state, value) {
    state.cart.splice(state.cart.indexOf(value), 1)
  },

  deleteCart (state) {
    state.cart = JSON.parse('[]')
  },

  setUsage (state, value) {
    state.usage = [value]
  },

  deleteUsage (state) {
    state.usage = JSON.parse('[]')
  },

  addToLightbox (state, value) {
    state.lightbox.push(value)
  },

  removeFromLightbox (state, value) {
    state.lightbox.splice(state.lightbox.indexOf(value), 1)
  },

  deleteLightbox (state) {
    state.lightbox = JSON.parse('[]')
  },

  changeTitle( state, value ) {
    state.title = value;
    document.title = ( state.title ? state.title + ' - ' : '' ) + sell_media.site_name;
  },

   verifyProducts( state, value ) {
      state.cart = state.cart.filter((product, index)=>{
         let item = value.find( data => data.id === product.id );
         if( 'undefined' !== typeof item ){
            // If type is price-group then its downloads.
            if ( 'price-group' === product.type ) {
               let downloads = item.sell_media_pricing.downloads.find( download => download.id === product.price_id );
               if( 'undefined' !== typeof downloads ) {
                  // Update price based on api.
                  product.price = downloads.price;
                  return true;
               }
            } else if( 'reprints-price-group' == product.type ) {
               // [TODO] condition for reprint to be added.
            }
         }
         return false;
      });
   },

   setUser( state, user ) {
      state.user = user
    }
}
