import { mapGetters, mapActions } from "vuex"

export default {
  computed: {
    ...mapGetters([
      "user",
      "cart",
      "lightbox"
    ])
  },

  methods: {
    ...mapActions([
      "setUser",
      "addToLightbox",
      "removeFromLightbox",
      "deleteLightbox"
    ])
  }
}