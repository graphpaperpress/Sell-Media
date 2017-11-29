import Vue from 'vue'
import VueCookie from 'vue-cookie'
import VueRouter from 'vue-router'
import VueResource from 'vue-resource'
import Vuex from 'vuex'

// use vue plugins
Vue.use( Vuex )
Vue.use( VueRouter )
Vue.use( VueResource )
Vue.use( VueCookie )

Vue.config.debug = true;
Vue.config.devTools = true;

// import all vue components
import FilteredItems from '../components/FilteredItems.vue'
Vue.component( 'FilteredItems', FilteredItems )
import EmptyLightbox from '../components/EmptyLightbox.vue'

// define routes for vue app
// ref : http://router.vuejs.org/en/
const router = new VueRouter( {
	mode: 'history',
	routes: [

		{ path: '/blog/:page(\\d+)?', name: 'Filtered Items', component: FilteredItems },
		{ path: '/', redirect: '/blog' },

	]
} )

// init vue compotent
var app = new Vue ( {
	el: '#content',
	template: '<section><router-view></router-view></section>',
	router
} )
