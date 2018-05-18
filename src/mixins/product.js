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
      "productTypesLoaded",
      "attachment",
      "attachmentLoaded"
    ])
  },

  methods: {
    ...mapActions([
      "fetchPost",
      "setProduct",
      "searchProducts",
      "setAttachment",
      "fetchProducts",
      "fetchProductTypes",
      "fetchAttachment"
    ])
  }
}
