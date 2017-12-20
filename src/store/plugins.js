import { CART_KEY, LIGHTBOX_KEY } from './mutations'

const localStoragePlugin = store => {
  store.subscribe((mutation, { cart, lightbox }) => {
    window.localStorage.setItem(CART_KEY, JSON.stringify(cart))
    window.localStorage.setItem(LIGHTBOX_KEY, JSON.stringify(lightbox))
  })
}

export default [localStoragePlugin]
