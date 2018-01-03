import Vue from 'vue'
import Vuex from 'vuex'
import { state, mutations } from './mutations'
import * as getters from './getters'
import plugins from './plugins'

Vue.use(Vuex)

export default new Vuex.Store({
  state,
  mutations,
  getters,
  plugins
})