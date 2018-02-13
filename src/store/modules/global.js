// Used for generic state props that aren't model-specific

import * as types from "../mutation-types"

const state = {
  title: null
}

const getters = {
  title: state => state.title
}

const actions = {
  /**
   * Sets the document <title> tag and appends the site_name
   * @param {value} the value to be used for the title
   */
  changeTitle({ commit }, value ) {
    commit(types.CHANGE_TITLE, value)
    document.title = ( state.title ? state.title + ' - ' : '' ) + sell_media.site_name;
  }
}

const mutations = {
  [types.CHANGE_TITLE](state, value) {
    state.title = value
  }
}

export default {
  state,
  getters,
  actions,
  mutations
}
