// Used for generic state props that aren't model-specific

import * as types from "../mutation-types"

const state = {
  product: {},
  productLoaded: false,
  post: {},
  postLoaded: false,
  searchResultsLoaded: false,
  searchResults: {},
  productTypes: [],
  productTypesLoaded: false
}

const getters = {
  product: state => state.product,
  productLoaded: state => state.productLoaded,
  searchResults: state => state.searchResults,
  searchResultsLoaded: state => state.searchResultsLoaded,
  post: state => state.post,
  postLoaded: state => state.postLoaded,
  productTypes: state => state.productTypes,
  productTypesLoaded: state => state.productTypesLoaded
}

const actions = {
  /**
   * Sets the currently viewed product
   * @param {product} the product object
   */
  setProduct ({ commit }, product ) {
    commit(types.SET_PRODUCT, product)
  },

  /**
   * Queries for a set of posts (products) starting at a given page number.
   * @param {pageNumber} The page you want to start at
   */
  fetchProducts ({ commit }, pageNumber = 1) {
    commit(types.SET_SEARCH_RESULTS_LOADED, false)

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
      console.log(res)
    })
  },

  searchProducts ({ commit }, { search, search_type, page_number = 1}) {
    commit(types.SET_SEARCH_RESULTS_LOADED, false)
    Axios.get( '/wp-json/sell-media/v2/search', {
      params: {
        s: search,
        type: search_type,
        per_page: sell_media.posts_per_page,
        page: page_number
      }
    } )
    .then(( res ) => {
      let searchResults = {
        results: res.data,
        hasSearchResults: res.headers[ 'x-wp-total' ] ? res.headers[ 'x-wp-total' ] : 0,
        totalPages: parseInt(res.headers["x-wp-totalpages"]),
        pageNumber: page_number
      }

      commit(types.SET_SEARCH_RESULTS, searchResults)
      commit(types.SET_SEARCH_RESULTS_LOADED, true)
    })
    .catch( ( res ) => {
      console.log( res )
    })
  },

  fetchPost({ commit }, params) {
    commit(types.SET_POST_LOADED, false)
    Axios.get( '/wp-json/wp/v2/sell_media_item', {
      params: params
    })
    .then(( res ) => {
      commit(types.SET_POST, res.data[0])
      commit(types.SET_POST_LOADED, true)
    })
    .catch(( res ) => {
      console.log( `Something went wrong : ${res}` )
    })
  },

  fetchProductTypes({ commit }) {
    commit(types.SET_PRODUCT_TYPES_LOADED, false)
    Axios.get( '/wp-json/wp/v2/product_type' )
    .then(( res ) => {
      console.log(res)
      commit(types.SET_PRODUCT_TYPES, res.data)
      commit(types.SET_PRODUCT_TYPES_LOADED, true)
    })
    .catch(( res ) => {
      console.log( res )
    })
  }
}

const mutations = {
  [types.SET_PRODUCT](state, product) {
    state.product = product
  },

  [types.SET_PRODUCT_LOADED](state, loaded) {
    state.productLoaded = loaded
  },

  [types.SET_SEARCH_RESULTS](state, results) {
    state.searchResults = results
  },

  [types.SET_SEARCH_RESULTS_LOADED](state, loaded) {
    state.searchResultsLoaded = loaded
  },

  [types.SET_POST](state, post) {
    state.post = post
  },

  [types.SET_POST_LOADED](state, loaded) {
    state.postLoaded = loaded
  },

  [types.SET_PRODUCT_TYPES](state, productTypes) {
    state.productTypes = productTypes
  },

  [types.SET_PRODUCT_TYPES_LOADED](state, loaded) {
    state.productTypesLoaded = loaded
  },
}

export default {
  state,
  getters,
  actions,
  mutations
}
