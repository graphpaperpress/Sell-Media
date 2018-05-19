// Used for user-specific state properties
import axios from 'axios'

import api from "../api"
import {
  addToArrayImmutable,
  updateArrayImmutable,
  removeFromArrayImmutable
} from '../../utils';

import {
  SET_USER,
  SET_USER_DOWNLOAD_ACCESS,
  SET_LIGHTBOX,
  ADD_TO_LIGHTBOX,
  RESET_LIGHTBOX,
  REMOVE_FROM_LIGHTBOX,
  SET_USAGE,
  DELETE_USAGE,
  ADD_TO_CART,
  REMOVE_FROM_CART,
  UPDATE_CART_PRODUCT,
  DELETE_CART,
} from "../mutation-types"

export const CART_KEY = 'sell-media-cart'
export const LIGHTBOX_KEY = 'sell-media-lightbox'
export const USAGE_KEY = 'sell-media-usage'

// State: the state properties for the module
const state = {
  user: null,
  user_download_access: null,
  cart: JSON.parse(window.localStorage.getItem(CART_KEY) || '[]'),
  lightbox: JSON.parse(window.localStorage.getItem(LIGHTBOX_KEY) || '[]'),
  usage: JSON.parse(window.localStorage.getItem(USAGE_KEY) || '[]')
}

// Getters: Used to retrieve state properties
const getters = {
  user: state => state.user,
  user_download_access: state => state.user_download_access,
  lightbox: state => state.lightbox,
  cart: state => state.cart,
  usage: state => state.usage
}

// Actions: Used to trigger asyncronous tasks and commit mutations
const actions = {
  getUser({ commit }) {
    axios.get( '/wp-json/sell-media/v2/api', {
      params: {
        action: 'get_user',
        _wpnonce: sell_media.nonce
      }
    } )
      .then(( res ) => {
        commit(SET_USER, res.data.ID)
      })
      .catch(( res ) => {
        console.log( res )
      })
  },

  /**
   * Sets the user to the WP user object
   * @param { user } the user object
   */
  setUser({ commit }, user) {
    commit(SET_USER, user)
  },

  /**
   * Gets the logged in user download meta from user meta
   * @param { user } the user object
   */
  getUserDownloadAccess({ commit }) {
    axios.get( '/wp-json/sell-media/v2/api', {
      params: {
        action: 'get_user_download_access',
        _wpnonce: sell_media.nonce
      }
    } )
      .then(( res ) => {
        commit(SET_USER_DOWNLOAD_ACCESS, res.data)
      })
      .catch(( res ) => {
        console.log( res )
      })
  },

  /**
   * Sets the logged in user download meta from user meta
   * @param { user } the user object
   */
  setUser({ commit }, user_download_access) {
    commit(SET_USER_DOWNLOAD_ACCESS, user_download_access)
  },

  /**
   * Adds a given product to the users lightbox.
   * @param { product } the product to be added
   */
  addToLightbox ({ commit }, product) {
    commit(ADD_TO_LIGHTBOX, product)
  },

  /**
   * Removes a given product from the users lightbox.
   * @param { product } the product to be removed
   */
  removeFromLightbox ({ commit }, product) {
    commit(REMOVE_FROM_LIGHTBOX, product)
  },

  /**
   * Resets the lightbox
   */
  deleteLightbox ({ commit }) {
    commit(RESET_LIGHTBOX)
  },

  /**
   * Adds a product object to the cart
   * @param { product } the product to be added
   */
  addToCart ({ commit }, product) {
    commit(ADD_TO_CART, product)
  },

  /**
   * Removes a product object to the cart
   * @param { product } the product to be removed
   */
  removeFromCart ({ commit }, product) {
    commit(REMOVE_FROM_CART, product)
  },

  /**
   * Replaces an existing product object with an updated version
   * @param { product } the product to be updated
   */
  updateCartProduct ({ commit, state }, product) {
    state.cart.indexOf(product) !== -1 ? commit(UPDATE_CART_PRODUCT, { index, product }) : void 0;
  },

  /**
   * Resets the cart state
   */
  deleteCart ({ commit }) {
    commit(DELETE_CART)
  },

  /**
   * Sets the usage/license for the user
   * @param { value } the usage value
   */
  setUsage ({ commit }, value) {
    commit(SET_USAGE, value)
  },

  /**
   * Resets the usage for the user
   */
  deleteUsage ({ commit }) {
    commit(DELETE_USAGE);
  }
}

// Mutations: Used to modify the state's properties
const mutations = {
  [SET_USER](state, user) {
    state.user = user
  },
  [SET_USER_DOWNLOAD_ACCESS](state, user_download_access) {
    state.user_download_access = user_download_access
  },
  [ADD_TO_LIGHTBOX](state, item) {
    state.lightbox = addToArrayImmutable(state.lightbox, item)
  },
  [REMOVE_FROM_LIGHTBOX](state, item) {
    state.lightbox = removeFromArrayImmutable(state.lightbox, item)
  },
  [RESET_LIGHTBOX](state) {
    state.lightbox = []
  },
  [ADD_TO_CART](state, product) {
    state.cart = addToArrayImmutable(state.cart, product)
  },
  [REMOVE_FROM_CART](state, product) {
    state.cart = removeFromArrayImmutable(state.cart, product)
  },
  [UPDATE_CART_PRODUCT](state, { index, product }) {
    state.cart = updateArrayImmutable(state.cart, index, product);
  },
  [DELETE_CART](state) {
    state.cart = []
  },
  [SET_USAGE](state, value) {
    state.usage = [value]
  },
  [DELETE_USAGE](state) {
    state.usage = []
  },
}

export default {
  state,
  getters,
  actions,
  mutations
}
