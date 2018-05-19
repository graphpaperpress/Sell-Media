<template>
	<div :id="name" :class="name">

		<searchform @search="getSearchResults" :loading="!searchResultsLoaded" :search="search"></searchform>
		<div class="search-results-wrapper" v-if="searchResultsLoaded">

			<div class="content">
				<p v-if="search && hasSearchResults">{{ search_labels.we_found }} {{ searchResults }} {{ search_labels.results_for }} "{{ search }}." <span class="reset-search" @click="resetSearch">Reset</span></p>

				<p v-if="search && !hasSearchResults">{{ search_labels.no_results }} {{ search_labels.results_for }} "{{ search }}." <span class="reset-search" @click="resetSearch">Reset</span></p>
			</div>

			<div :class="gridContainer" class="is-multiline has-text-centered">
				<thumbnail v-for="post in searchResults.results" :key="post.slug" :post="post"></thumbnail>
			</div>

			<nav v-if="searchResults.totalPages > 1" class="pagination">
				<button class="button" v-if="showPrev" @click.prevent="showPrevPage()" :class="{ 'is-loading': !searchResultsLoaded }">Previous</button>
				<span> {{ currentPage }} / {{ searchResults.totalPages }} </span>
				<button class="button" v-if="showNext" @click.prevent="showNextPage()" :class="{ 'is-loading': !searchResultsLoaded }">Next</button>
			</nav>

		</div>

	</div>
</template>

<script>
import mixinGlobal from '@/mixins/global'
import mixinProduct from '@/mixins/product'
import mixinUser from '@/mixins/user'

import SearchForm from 'components/parts/SearchForm.vue'

export default {
  mixins: [mixinGlobal, mixinProduct, mixinUser],

  data(){
    return {
      currentPage: '',
      prevPage: '',
      nextPage: '',
      showNext: true,
      showPrev: true,
      postCollection: '',
      postPerPage: sell_media.posts_per_page,
      name: this.$options.name,
      search: '',
      search_type: sell_media.default_search_type ? sell_media.default_search_type : '',
      search_labels: sell_media.search_labels,
      hasSearchResults: false,
      gridContainer: this.$store.getters.gridLayoutContainer
    }
  },

  mounted(){
    const search = this.$route.query.search || this.search
    const type = this.$route.query.type || this.search_type
    const page = this.$route.params.page || 1

    this.changeTitle('Archive')
    this.getUser()
    this.getUserDownloadAccess()

    if (search || type) {
      this.getSearchResults({ search, type, page })
    } else {
      this.fetchProducts(1)
    }
  },

  methods: {
    getSearchResults({ search, type, page = 1 }) {
      this.search = search
      this.search_type = type || ''

      if (search) {
        this.$router.push({
          name: 'archive',
          params: { page },
          query: { search, type }
        });
      }

      this.searchProducts({ search, type, page })
      this.changeTitle(`Search results for: ${search}`)
    },

    resetSearch(){
      this.hasSearchResults = false
      this.search = ''
      this.search_type = sell_media.default_search_type ? sell_media.default_search_type : ''
      this.$router.push( { name: 'archive', query: {} } )
      this.fetchProducts(1)
    },

    showNextPage(event){
      if ( this.currentPage < this.searchResults.totalPages ) {
        this.showNext = true
        this.currentPage += 1
        this.$router.push( { 'name': 'archive', params: { 'page': this.currentPage } } )
      }
    },

    showPrevPage(event){
      if ( this.currentPage != 1 ) {
        this.showPrev = true
        this.currentPage -= 1
        this.$router.push( { 'name': 'archive', params: { 'page': this.currentPage } } )
      }
    }
  },

  watch: {
    '$route'( to, from ) {
      const page_number = this.$route.params.page;

      if ( this.search || this.$route.query.search ) {
        const search = this.$route.query.search || this.search
        this.getSearchResults( { search, search_type: this.search_type, page_number } )
      } else if ( ! this.search && this.search_type ) {
        this.getSearchResults( { search: '', search_type: this.search_type, page_number } )
      } else {
        this.fetchProducts(page_number )
      }
    },

    searchResults(val) {
      this.hasSearchResults = val.hasSearchResults
      if (val.pageNumber <= parseInt(val.totalPages)) {
        this.currentPage = parseInt(val.pageNumber)
      } else {
        this.currentPage = 1
      }
    }

  },

  components: {
    'searchform': SearchForm,
  }
}
</script>

<style lang="scss" scoped>

	.search-wrapper {
		margin: 2rem auto;
	}

	.reset-search {
		color: #ff2b56;
		cursor: pointer;
	}

</style>
