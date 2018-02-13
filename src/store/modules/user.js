// Used for user-specific state properties

import api from "../api"
import * as types from "../mutation-types"

export const CART_KEY = 'sell-media-cart'
export const LIGHTBOX_KEY = 'sell-media-lightbox'
export const USAGE_KEY = 'sell-media-usage'

// State: the state properties for the module
const state = {
  user: null,
  cart: JSON.parse(window.localStorage.getItem(CART_KEY) || '[]'),
  lightbox: JSON.parse(window.localStorage.getItem(LIGHTBOX_KEY) || '[]'),
  usage: JSON.parse(window.localStorage.getItem(USAGE_KEY) || '[]')
}

// Getters: Used to retrieve state properties
const getters = {
  user: state => state.user,
  lightbox: state => state.lightbox,
  cart: state => state.cart,
  usage: state => state.usage
}

// Actions: Used to trigger asyncronous tasks and commit mutations
const actions = {
  /**
   * Sets the user to the WP user object
   * @param { user } the user object
   */
  setUser({ commit, state }, user) {
    commit(types.SET_USER, user)
  },

  /**
   * Adds a given product to the users lightbox.
   * @param { product } the product to be added
   */
  addToLightbox ({ commit, state, getters }, product) {
    let newLightbox = state.lightbox
    newLightbox.push(product)
    commit(types.SET_LIGHTBOX, newLightbox)
  },

  /**
   * Removes a given product from the users lightbox.
   * @param { product } the product to be removed
   */
  removeFromLightbox ({ commit, state, getters }, product) {
    let newLightbox = state.lightbox
    newLightbox.splice(newLightbox.indexOf(product), 1)
    commit(types.SET_LIGHTBOX, newLightbox)
  },

  /**
   * Resets the lightbox
   */
  deleteLightbox ({ commit }) {
    commit(types.SET_LIGHTBOX, JSON.parse('[]'))
  },

  /**
   * Adds a product object to the cart
   * @param { product } the product to be added
   */
  addToCart ({ commit }, product) {
    commit(types.ADD_TO_CART, product)
  },

  /**
   * Removes a product object to the cart
   * @param { product } the product to be removed
   */
  removeFromCart ({ commit }, product) {
    commit(types.REMOVE_FROM_CART, product)
  },

  /**
   * Replaces an existing product object with an updated version
   * @param { product } the product to be updated
   */
  updateCartProduct ({ commit }, product) {
    commit(types.UPDATE_CART_PRODUCT, product)
  },

  /**
   * Resets the cart state
   */
  deleteCart ({ commit }) {
    commit(types.DELETE_CART)
  },

  /**
   * Sets the usage/license for the user
   * @param { value } the usage value
   */
  setUsage ({ commit }, value) {
    commit(types.SET_USAGE, value)
  },

  /**
   * Resets the usage for the user
   */
  deleteUsage ({ commit }) {
    commit(types.SET_USAGE, JSON.parse('[]'))
  },

  // TODO: This mutation was called from app.js but wasn't defined. @EvanAgee
  initCart ({ state }) {
    return true
  },
}

// Mutations: Used to modify the state's properties
const mutations = {
  [types.SET_USER](state, user) {
    state.user = user
  },

  [types.SET_LIGHTBOX](state, lightbox) {
    state.lightbox = lightbox
  },

  [types.ADD_TO_CART](state, product) {
    state.cart.push(product)
  },

  // you cannot alter an object's properties directly, otherwise
  // the component will loose reactivity
  // https://vuejs.org/v2/guide/list.html#Caveats
  [types.REMOVE_FROM_CART](state, product) {
    state.cart.splice(state.cart.indexOf(product), 1)
  },

  // you cannot alter an object's properties directly, otherwise
  // the component will loose reactivity
  // https://vuejs.org/v2/guide/list.html#Caveats
  [types.UPDATE_CART_PRODUCT](state, product) {
    state.cart.splice(state.cart.indexOf(product), 1, product)
  },

  [types.DELETE_CART](state) {
    state.cart = JSON.parse('[]')
  },

  [types.SET_USAGE](state, value) {
    state.usage = [value]
  }
}

export default {
  state,
  getters,
  actions,
  mutations
}
