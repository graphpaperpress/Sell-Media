import Vue from 'vue'
import VueRouter from 'vue-router'
import VBClass from 'vue-body-class'
import axios from 'axios'
import VueAxios from 'vue-axios'
import PortalVue from 'portal-vue'
import VideoJs from 'video.js'
import VueStripeCheckout from 'vue-stripe-checkout'
import Icon from 'vue-awesome/components/Icon'

import store from './store'
import icons from './icons'

import Main from 'components/Main.vue'
import Archive from 'components/pages/Archive.vue'
import Item from 'components/pages/Item.vue'
import Attachment from 'components/pages/Attachment.vue'
import Lightbox from 'components/pages/Lightbox.vue'
import Checkout from 'components/pages/Checkout.vue'
import Search from 'components/pages/Search.vue'
import Filters from 'components/pages/Filters.vue'
import NotFound from 'components/pages/NotFound.vue'
import Modal from 'components/parts/Modal.vue'
import Expander from 'components/parts/Expander.vue'
import ExpanderRelated from 'components/parts/ExpanderRelated.vue'
import Media from 'components/parts/Media.vue'
import FeaturedImage from 'components/parts/FeaturedImage.vue'
import Thumbnail from 'components/parts/Thumbnail.vue'
import CartForm from 'components/parts/CartForm.vue'
import CartModalLicense from 'components/parts/CartModalLicense.vue'
import Loader from 'components/parts/Loader.vue'

// this options will be disabled in production automatically by Vue
Vue.config.productionTip = false;
Vue.config.devtools = true;
Vue.config.performance = true;

const options = {
  key: sell_media.stripe_public_key,
  locale: 'auto',
  currency: sell_media.currency,
  billingAddress: true,
  panelLabel: "Pay" + " {{amount}}",
}

// use vue plugins
Vue.use(VueRouter)
Vue.use(VueAxios, axios)
Vue.use(PortalVue)
Vue.use(VueStripeCheckout, options)

Vue.component('archive', Archive)
Vue.component('item', Item)
Vue.component('attachment', Attachment)
Vue.component('lightbox', Lightbox)
Vue.component('checkout', Checkout)
Vue.component('search', Search)
Vue.component('filters', Filters)
Vue.component('not-found', NotFound)
Vue.component('modal', Modal)
Vue.component('expander', Expander)
Vue.component('expander-related', ExpanderRelated)
Vue.component('media', Media)
Vue.component('featured-image', FeaturedImage)
Vue.component('thumbnail', Thumbnail)
Vue.component('cart-form', CartForm)
Vue.component('cart-modal-license', CartModalLicense)
Vue.component('icon', Icon)
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
Vue.use(VBClass, router)

// init Vue
new Vue({
  el: '#sell-media-app',
  store,
  router,
  render: h => h(Main)
})
