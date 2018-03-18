Vue.config.devtools = true
import Vue from 'vue'
import VueRouter from 'vue-router'
import VBClass from 'vue-body-class'
import axios from 'axios'
import VueAxios from 'vue-axios'
import PortalVue from 'portal-vue'
import store from './store'
import icons from './icons'
import VideoJs from 'video.js'
import VueStripeCheckout from 'vue-stripe-checkout'

const options = {
  key: sell_media.stripe_public_key,
  locale: 'auto',
  currency: sell_media.currency,
  billingAddress: true,
  panelLabel: "Pay" + " {{amount}}",
}

// use vue plugins
Vue.use( VueRouter )
Vue.use( VueAxios, axios )
Vue.use( PortalVue )
Vue.use( VueStripeCheckout, options )

// import and register components
import Main from './components/Main.vue'
import Archive from './components/pages/Archive.vue'
Vue.component( 'archive', Archive )
import Item from './components/pages/Item.vue'
Vue.component( 'item', Item )
import Attachment from './components/pages/Attachment.vue'
Vue.component( 'attachment', Attachment )
import Lightbox from './components/pages/Lightbox.vue'
Vue.component( 'lightbox', Lightbox )
import Checkout from './components/pages/Checkout.vue'
Vue.component( 'checkout', Checkout )
import Search from './components/pages/Search.vue'
Vue.component( 'search', Search )
import Filters from './components/pages/Filters.vue'
Vue.component( 'filters', Filters )
import NotFound from './components/pages/NotFound.vue'
Vue.component( 'not-found', NotFound )

import Modal from './components/parts/Modal.vue'
Vue.component( 'modal', Modal )
import Expander from './components/parts/Expander.vue'
Vue.component( 'expander', Expander )
import ExpanderRelated from './components/parts/ExpanderRelated.vue'
Vue.component( 'expander-related', ExpanderRelated )
import Media from './components/parts/Media.vue'
Vue.component( 'media', Media )
import FeaturedImage from './components/parts/FeaturedImage.vue'
Vue.component( 'featured-image', FeaturedImage )
import Thumbnail from './components/parts/Thumbnail.vue'
Vue.component( 'thumbnail', Thumbnail )
import CartForm from './components/parts/CartForm.vue'
Vue.component( 'cart-form', CartForm )
import CartModalLicense from './components/parts/CartModalLicense.vue'
Vue.component( 'cart-modal-license', CartModalLicense )
import Icon from 'vue-awesome/components/Icon'
Vue.component('icon', Icon)
import Loader from './components/parts/Loader.vue'
Vue.component('loader', Loader)

// define routes
const router = new VueRouter({
  mode: 'history',
  routes: [

    { path: '/' + sell_media.archive_path + '/:page(\\d+)?', name: 'archive', component: Archive, meta: { bodyClass: 'product-archive' } },
    { path: '/' + sell_media.search_path + '/:page(\\d+)?', name: 'search', component: Search, meta: { bodyClass: 'product-search' } },
    { path: '/' + sell_media.archive_path + '/:slug', name: 'item', component: Item, meta: { bodyClass: 'product-item' } },
    { path: '/' + sell_media.archive_path + '/:prefix/:slug', name: 'attachment', component: Attachment, meta: { bodyClass: 'product-attachment' } },
    { path: '/' + sell_media.checkout_path, name: 'checkout', component: Checkout, meta: { bodyClass: 'product-checkout' } },
    { path: '/' + sell_media.lightbox_path, name: 'lightbox', component: Lightbox, meta: { bodyClass: 'product-lightbox' } },
    { path: '/filters', name: 'filters', component: Filters },
    { path: '*', component: NotFound, meta: { bodyClass: 'product-not-found' } },
    // { path: sell_media.thanks_url, name: 'thanks', component: Thanks },
    // { path: sell_media.dashboard_url, name: 'dashboard', component: Dashboard },
    // { path: sell_media.login_url, name: 'login', component: Login },
    // { path: sell_media.search_url, name: 'search', component: Search },

  ]
})

// Add router body classes
Vue.use( VBClass, router )

window.Axios = axios

// init Vue
new Vue({
  el: '#sell-media-app',
  store,
  router,
  render: h => h(Main)
})
