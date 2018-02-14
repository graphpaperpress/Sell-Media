// Used for generic state props that aren't model-specific

import * as types from "../mutation-types"

const state = {
  product: {},
  searchResultsLoaded: false,
  searchResults: {}
}

const getters = {
  product: state => state.product,
  searchResults: state => state.searchResults,
  searchResultsLoaded: state => state.searchResultsLoaded
}

const actions = {
  /**
   * Sets the currently viewed product
   * @param {product} the product object
   */
  setProduct ({ commit }, product ) {
    commit(types.SET_PRODUCT, product)
  },

  fetchProducts ({ commit }, pageNumber) {
    Axios.get("/wp-json/wp/v2/sell_media_item", {
      params: {
        per_page: sell_media.posts_per_page,
        page: pageNumber
      }
    })
    .then(res => {
      let searchResults = {
        results: res.data,
        totalPages: parseInt(res.headers["x-wp-totalpages"]),
        pageNumber: pageNumber
      }

      commit(types.SET_SEARCH_RESULTS, searchResults)
      commit(types.SET_SEARCH_RESULTS_LOADED, true)
    })
    .catch(res => {
      console.log(res);
    });
  }
}

const mutations = {
  [types.SET_PRODUCT](state, product) {
    state.product = product
  },

  [types.SET_SEARCH_RESULTS](state, results) {
    state.searchResults = results
  },

  [types.SET_SEARCH_RESULTS_LOADED](state, loaded) {
    state.searchResultsLoaded = loaded
  }
}

export default {
  state,
  getters,
  actions,
  mutations
}
