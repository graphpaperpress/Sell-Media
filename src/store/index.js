Vue.config.devtools = true
import Vue from 'vue'
import Vuex from 'vuex'
import { state, mutations } from './mutations'
import * as getters from './getters'
import plugins from './plugins'

// Modules
import User from './modules/user'

Vue.use(Vuex)

export default new Vuex.Store({
  state,
  modules: {
    User
  },
  mutations,
  getters,
  plugins
})