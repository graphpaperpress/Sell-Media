Vue.config.devtools = true
import Vue from 'vue'
import axios from 'axios'
import VueAxios from 'vue-axios'
import VueRouter from 'vue-router'
import PortalVue from 'portal-vue'
import store from './store'
import icons from './icons'
import VideoJs from 'video.js'
import VueStripeCheckout from 'vue-stripe-checkout'
import isNil from 'lodash/isnil'

const options = {
	key: sell_media.stripe_public_key,
	locale: 'auto',
	currency: sell_media.currency,
	billingAddress: true,
	panelLabel: "Pay" + " {{amount}}",
}

// use vue plugins
Vue.use( VueAxios, axios )
Vue.use( VueRouter )
Vue.use( PortalVue )
Vue.use( VueStripeCheckout, options )

// import components
import Search from './components/pages/Search.vue'
import Checkout from './components/pages/Checkout.vue'
import Lightbox from './components/pages/Lightbox.vue'
import Filters from './components/pages/Filters.vue'
import Modal from './components/parts/Modal.vue'
Vue.component( 'modal', Modal )
import Form from './components/parts/CartForm.vue'
import Icon from 'vue-awesome/components/Icon'
Vue.component( 'icon', Icon )

// define routes
const router = new VueRouter({
	mode: 'history',
	routes: [

		{ path: '/' + sell_media.search_path + '/:page(\\d+)?', name: 'search', component: Search }

	]
})


window.Axios = axios
window.isNil = isNil

// Search
new Vue({
	el: '#sell-media-search',
	store,
	router,
	render: h => h(Search)
})

// Checkout
new Vue({
	el: '#sell-media-checkout',
	store,
	render: h => h(Checkout)
})

// Lightbox
new Vue({
	el: '#sell-media-lightbox',
	store,
	render: h => h(Lightbox)
})

// Modal
new Vue({
	el: '#sell-media-modal',
	store,
	render: h => h(Lightbox)
})

// Form
new Vue({
	el: '#sell-media-cart-form',
	store,
	render: h => h(Form)
})
