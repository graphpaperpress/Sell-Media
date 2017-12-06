import Vue from 'vue'
import VueCookie from 'vue-cookie'
import VueRouter from 'vue-router'
import Vuex from 'vuex'
import VueAxios from 'vue-axios'
import axios from 'axios'

Vue.prototype.$http = axios;

// use vue plugins
Vue.use( VueCookie )
Vue.use( VueRouter )
Vue.use( Vuex )
Vue.use( VueAxios, axios )

// import and register components
import App from '../components/App.vue'
// import AppNav from './components/Nav.vue';
// Vue.component( 'app-nav', AppNav );
import Archive from '../components/Archive.vue'
Vue.component( 'sell-media-archive', Archive )
import Lightbox from '../components/Lightbox.vue'
Vue.component( 'sell-media-lightbox', Lightbox )
import Checkout from '../components/Checkout.vue'
Vue.component( 'sell-media-checkout', Checkout )

// define routes
const router = new VueRouter( {
	mode: 'history',
	routes: [

		{ path: '/archive', name: 'archive', component: Archive },
		{ path: sell_media.checkout_path, name: 'checkout', component: Checkout },
		{ path: sell_media.lightbox_path, name: 'lightbox', component: Lightbox },
		// { path: sell_media.thanks_url, name: 'thanks', component: Thanks },
		// { path: sell_media.dashboard_url, name: 'dashboard', component: Dashboard },
		// { path: sell_media.login_url, name: 'login', component: Login },
		// { path: sell_media.search_url, name: 'search', component: Search },

	]
} );

// init Vue
new Vue({
	el: '#sell-media-app',
	router,
	render: h => h(App)
})
