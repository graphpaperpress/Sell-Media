import api from "../api"
import * as types from "../mutation-types"

export const CART_KEY = 'sell-media-cart'
export const LIGHTBOX_KEY = 'sell-media-lightbox'

// initial state
const state = {
  user: null,
  cart: JSON.parse(window.localStorage.getItem(CART_KEY) || '[]'),
  lightbox: JSON.parse(window.localStorage.getItem(LIGHTBOX_KEY) || '[]')
}

// getters
const getters = {
  user: state => state.user,
  lightbox: state => state.lightbox,
  cart: state => state.cart
}

// actions
const actions = {
  setUser({commit, state}, user) {
    commit(types.SET_USER, user)
  },

  /**
   * Adds a given product to the users lightbox.
   * @param { product } the product to be added
   */
  addToLightbox ({commit, state, getters}, product) {
    let newLightbox = state.lightbox
    newLightbox.push(product)
    commit(types.SET_LIGHTBOX, newLightbox)
  },

  /**
   * Removes a given product from the users lightbox.
   * @param { product } the product to be removed
   */
  removeFromLightbox ({commit, state, getters}, product) {
    let newLightbox = state.lightbox
    newLightbox.splice(newLightbox.indexOf(product), 1)
    commit(types.SET_LIGHTBOX, newLightbox)
  },

  deleteLightbox ({commit}) {
    commit(types.SET_LIGHTBOX, JSON.parse('[]'))
  },
}

// mutations
const mutations = {
  [types.SET_USER](state, user) {
    state.user = user
  },

  [types.SET_LIGHTBOX](state, lightbox) {
    state.lightbox = lightbox
  },
}

export default {
  state,
  getters,
  actions,
  mutations
}
