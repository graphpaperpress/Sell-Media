// Used for generic state props that aren't model-specific

import * as types from "../mutation-types"

const state = {
  product: {}
}

const getters = {
  product: state => state.product
}

const actions = {
  /**
   * Sets the currently viewed product
   * @param {product} the product object
   */
  setProduct ({ commit }, product ) {
    commit(types.SET_PRODUCT, product)
  }
}

const mutations = {
  [types.SET_PRODUCT](state, product) {
    state.product = product
  }
}

export default {
  state,
  getters,
  actions,
  mutations
}
