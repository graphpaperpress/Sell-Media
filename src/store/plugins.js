import { CART_KEY, USAGE_KEY, LIGHTBOX_KEY } from './modules/user'
import { SEARCH_RESULTS_KEY } from './modules/product'

const localStoragePlugin = store => {
  store.subscribe((mutation, { cart, usage, lightbox, searchResults }) => {
    window.localStorage.setItem(CART_KEY, JSON.stringify(store.state.User.cart))
    window.localStorage.setItem(USAGE_KEY, JSON.stringify(store.state.User.usage))
    window.localStorage.setItem(LIGHTBOX_KEY, JSON.stringify(store.state.User.lightbox))
    window.localStorage.setItem(SEARCH_RESULTS_KEY, JSON.stringify(store.state.Product.searchResults))
  })
}

export default [localStoragePlugin]
