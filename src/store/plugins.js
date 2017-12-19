import { STORAGE_KEY } from './mutations'

const localStoragePlugin = store => {
  store.subscribe((mutation, { cart }) => {
    window.localStorage.setItem(STORAGE_KEY, JSON.stringify(cart))
  })
}

export default [localStoragePlugin]
