import { mapGetters, mapActions } from "vuex"

export default {
  computed: {
    ...mapGetters([
      "product",
      "searchResults",
      "searchResultsLoaded"
    ])
  },

  methods: {
    ...mapActions([
      "setProduct",
      "fetchProducts"
    ])
  }
}