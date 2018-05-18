import Vue from 'vue'
import Vuex from 'vuex'
import * as getters from './getters'
import plugins from './plugins'

// Modules
import global from './modules/global'
import user from './modules/user'
import product from './modules/product'

Vue.use(Vuex)

export default new Vuex.Store({
  strict: process.env.NODE_ENV !== 'production',
  modules: {
    global,
    user,
    product
  },
  getters,
  plugins
})
