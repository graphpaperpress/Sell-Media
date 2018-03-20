<template>
    <div :id="name" :class="name">

        <div class="search-wrapper">
            <input type="text" v-model="keyword" placeholder="Search Title…"/>
            <label>Search Title:</label>
        </div>

        <template v-if="searchResultsLoaded">
          <div class="columns is-multiline has-text-centered">
              <thumbnail v-for="post in searchResults.results" :key="post.slug" :post="post"></thumbnail>
          </div>
          <nav class="pagination">
              <button class="button" v-if="showPrev" @click.prevent="showPrevPage()">Previous</button>
              <span> {{ currentPage }} / {{ searchResults.totalPages }} </span>
              <button class="button" v-if="showNext" @click.prevent="showNextPage()">Next</button>
          </nav>
        </template>
        <template v-else>
          <loader></loader>
        </template>

    </div>
</template>

<script>
import mixinGlobal from "@/mixins/global"
import mixinProduct from "@/mixins/product"

export default {
  mixins: [mixinGlobal, mixinProduct],

  beforeMount: function() {
    if (this.$route.params.page) {
      this.$store.dispatch('fetchProducts', this.$route.params.page)
    } else {
      this.$store.dispatch('fetchProducts', 1)
    }
  },

  mounted: function() {
    this.$store.dispatch("changeTitle", "Search")
  },

  data: function() {
    return {
      prevPage: "",
      nextPage: "",
      showNext: true,
      showPrev: true,
      postCollection: "",
      currentPage: 1,
      keyword: "",
      name: this.$options.name // component name
    }
  },

  methods: {
    showNextPage: function(event) {
      if (this.currentPage < this.searchResults.totalPages) {
        showNext: true
        this.currentPage = this.currentPage + 1
        this.$router.push({ name: "search", params: { page: this.currentPage } })
      }
    },
    showPrevPage: function(event) {
      if (this.currentPage != 1) {
        showPrev: true
        this.currentPage = this.currentPage - 1
        this.$router.push({ name: "search", params: { page: this.currentPage } })
      }
    }
  },

  watch: {
    $route(to, from) {
      this.$store.dispatch('fetchProducts', this.$route.params.page)
    },

    searchResults(val) {
      if (val.pageNumber <= parseInt(val.totalPages)) {
        this.currentPage = parseInt(val.pageNumber)
      } else {
        this.$router.push({ name: "search" })
        this.currentPage = 1
      }
    }
  }
}
</script>
