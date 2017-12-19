export const STORAGE_KEY = 'sell-media-cart'

export const state = {
  cart: JSON.parse(window.localStorage.getItem(STORAGE_KEY) || '[]'),
  title: ''
}

export const mutations = {
  addToCart (state, value) {
    state.cart.push(value)
  },

  deleteCart (state, value) {
    state.cart.splice(state.cart.indexOf(value), 1)
  },

  changeTitle( state, value ) {
    state.title = value;
    document.title = ( state.title ? state.title + ' - ' : '' ) + sell_media.site_name;
  }
}