import Vue from 'vue'
import Vuex from 'vuex'
import * as getters from './getters'
import plugins from './plugins'

// Modules
import Global from './modules/global'
import User from './modules/user'
import Product from './modules/product'

Vue.use(Vuex)

export default new Vuex.Store({
  strict: process.env.NODE_ENV !== 'production',
  modules: {
    Global,
    User,
    Product
  },
  getters,
  plugins
})
