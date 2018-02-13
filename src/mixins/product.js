import { mapGetters, mapActions } from "vuex"

export default {
  computed: {
    ...mapGetters([
      "product"
    ])
  },

  methods: {
    ...mapActions([
      "setProduct"
    ])
  }
}