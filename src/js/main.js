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
// import FilteredItems from './components/FilteredItems.vue'
// Vue.component( 'FilteredItems', FilteredItems )
import Lightbox from '../components/Lightbox.vue'

//Create main vue component
const App = Vue.extend( {
	template: '<main><router-view></router-view></main>',
	computed: {
	}
} );

//Define route for vue app
//ref : http://router.vuejs.org/en/
const router = new VueRouter( {
	mode: 'history',
	routes: [

		{ path: '/sell-media-lightbox', name: 'lightbox', component: Lightbox },
		// { path: '/blog/:name', name: 'post', component: post },
		// { path: '/', redirect: '/blog' },

	]
} );

// window.onload = function () {
// 	// init vue compotent
// 	new Vue ( {
// 		el: '#app',
// 		router
// 		//render: h => h(Lightbox)
// 	} )
// }

window.onload = function () {
	new App( {
		router
	} ).$mount( '#app' );
}