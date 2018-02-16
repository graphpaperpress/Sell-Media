import { mapGetters, mapActions } from "vuex"

export default {
  computed: {
    ...mapGetters([
      "product",
      "productLoaded",
      "searchResults",
      "searchResultsLoaded",
      "post",
      "postLoaded",
      "productTypes",
      "productTypesLoaded"
    ])
  },

  methods: {
    ...mapActions([
      "fetchPost",
      "setProduct",
      "fetchProducts",
      "fetchProductTypes"
    ])
  }
}