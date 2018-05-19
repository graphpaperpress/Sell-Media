import { mapGetters, mapActions } from "vuex"

export default {
  computed: {
    ...mapGetters([
      "user",
      "user_download_access",
      "cart",
      "lightbox",
      "usage"
    ])
  },

  methods: {
    ...mapActions([
      "getUser",
      "setUser",
      "getUserDownloadAccess",
      "setUserDownloadAccess",
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
