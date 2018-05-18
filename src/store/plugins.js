import { CART_KEY, USAGE_KEY, LIGHTBOX_KEY } from './modules/user'
import { SEARCH_RESULTS_KEY } from './modules/product'

const saveToStorage = (key, value) => {
  window.localStorage.setItem(key, JSON.stringify(value))
}

const localStoragePlugin = store => {
  store.subscribe((mutation, { user, product }) => {
    saveToStorage(CART_KEY, user.cart);
    saveToStorage(USAGE_KEY, user.usage);
    saveToStorage(LIGHTBOX_KEY, user.lightbox);
    saveToStorage(SEARCH_RESULTS_KEY, product.searchResults);
  })
}

export default [localStoragePlugin]
