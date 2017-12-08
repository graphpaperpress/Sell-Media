import Vue from 'vue'
import VueCookie from 'vue-cookie'
import VueRouter from 'vue-router'
import Vuex from 'vuex'
import axios from 'axios'
import VueAxios from 'vue-axios'

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
Vue.component( 'sm-archive', Archive )
import Lightbox from '../components/Lightbox.vue'
Vue.component( 'sm-lightbox', Lightbox )
import Checkout from '../components/Checkout.vue'
Vue.component( 'sm-checkout', Checkout )
import Item from '../components/Item.vue'
Vue.component( 'sm-item', Item )
import Attachment from '../components/Attachment.vue'
Vue.component( 'sm-attachment', Attachment )
import Modal from '../components/Modal.vue'
Vue.component( 'sm-modal', Modal )
import GridItem from '../components/GridItem.vue'
Vue.component( 'sm-grid-item', GridItem )
import NotFound from '../components/NotFound.vue'
Vue.component( 'sm-not-found', NotFound )

// define routes
const router = new VueRouter( {
	mode: 'history',
	routes: [

		{ path: '/' + sell_media.archive_path + '/:page(\\d+)?', name: 'archive', component: Archive },
		{ path: '/' + sell_media.archive_path + '/:slug', name: 'item', component: Item },
		{ path: '/' + sell_media.archive_path + '/:prefix/:slug', name: 'attachment', component: Attachment },
		{ path: '/' + sell_media.checkout_path, name: 'checkout', component: Checkout },
		{ path: '/' + sell_media.lightbox_path, name: 'lightbox', component: Lightbox },
		{ path: '*', component: NotFound },
		// { path: sell_media.thanks_url, name: 'thanks', component: Thanks },
		// { path: sell_media.dashboard_url, name: 'dashboard', component: Dashboard },
		// { path: sell_media.login_url, name: 'login', component: Login },
		// { path: sell_media.search_url, name: 'search', component: Search },

	]
} );

// define vuex store
const store = new Vuex.Store( {
	state: {
		title: ''
	},
	mutations: {
		smChangeTitle( state, value ) {
			// mutate state
			state.title = value;
			document.title = ( state.title ? state.title + ' - ' : '' ) + sell_media.site_name;
		}
	}
} );

// init Vue
new Vue({
	el: '#sell-media-app',
	store,
	router,
	render: h => h(App)
})
