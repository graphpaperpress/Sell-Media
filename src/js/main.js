Vue.config.devtools = true
import Vue from 'vue'
import VueCookie from 'vue-cookie'
import VueRouter from 'vue-router'
import Vuex from 'vuex'
import axios from 'axios'
import VueAxios from 'vue-axios'
import VeeValidate from 'vee-validate'

// use vue plugins
Vue.use( VueCookie )
Vue.use( VueRouter )
Vue.use( Vuex )
Vue.use( VueAxios, axios )
Vue.use( VeeValidate )

// import and register components
import Main from '../components/Main.vue'
import Archive from '../components/pages/Archive.vue'
Vue.component( 'archive', Archive )
import Item from '../components/pages/Item.vue'
Vue.component( 'item', Item )
import Attachment from '../components/pages/Attachment.vue'
Vue.component( 'attachment', Attachment )
import Lightbox from '../components/pages/Lightbox.vue'
Vue.component( 'lightbox', Lightbox )
import Checkout from '../components/pages/Checkout.vue'
Vue.component( 'checkout', Checkout )
import NotFound from '../components/pages/NotFound.vue'
Vue.component( 'not-found', NotFound )

import Modal from '../components/parts/Modal.vue'
Vue.component( 'modal', Modal )
import GridItem from '../components/parts/GridItem.vue'
Vue.component( 'grid-item', GridItem )
import CartForm from '../components/parts/CartForm.vue'
Vue.component( 'cart-form', CartForm )
// import Tabs from '../components/parts/Tabs.vue'
// Vue.component( 'tabs', Tabs )
// import Tab from '../components/parts/Tab.vue'
// Vue.component( 'tab', Tab )

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
	render: h => h(Main)
})
