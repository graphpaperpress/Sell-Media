import { CART_KEY, USAGE_KEY, LIGHTBOX_KEY } from './modules/user'

const localStoragePlugin = store => {
  store.subscribe((mutation, { cart, usage, lightbox }) => {
    window.localStorage.setItem(CART_KEY, JSON.stringify(store.state.User.cart))
    window.localStorage.setItem(USAGE_KEY, JSON.stringify(store.state.User.usage))
    window.localStorage.setItem(LIGHTBOX_KEY, JSON.stringify(store.state.User.lightbox))
  })
}

export default [localStoragePlugin]
