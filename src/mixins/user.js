import { mapGetters, mapActions } from "vuex"

export default {
  computed: {
    ...mapGetters([
      "user",
      "cart",
      "lightbox",
      "usage"
    ])
  },

  methods: {
    ...mapActions([
      "getUser",
      "setUser",
      "addToLightbox",
      "removeFromLightbox",
      "deleteLightbox",
      "addToCart",
      "removeFromCart",
      "updateCartProduct",
      "deleteCart",
      "setUsage",
      "deleteUsage"
    ])
  }
}
