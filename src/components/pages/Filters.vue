<template>
	<div class="filters">
		<h3>{{label_search}}</h3>
		<input type="text" v-model="search" placeholder="Search title.."/>

		<h3 class="section-title">Newest Additions</h3>
		<div class="filtered-tabs clear">
			<div class="filtered-tab">
				<input
				type="radio"
				id="all"
				value=""
				v-model="categoryFilter">
				<label for="all">All</label>
			</div>

			<div class="filtered-tab" v-for="category in categories" :key="category.id">
				<input
				type="radio"
				v-bind:id="category.slug"
				v-bind:value="category.id"
				v-model="categoryFilter">
				<label v-bind:for="category.slug">{{ category.name }}</label>
			</div>
		</div>

		<div class="filtered-content columns is-gapless is-multiline has-text-centered">
			<div v-for="(post, index) in filteredPosts" :key="index" :class="gridLayout" class="column is-mobile">
					<thumbnail :key="post.slug" :post="post"></thumbnail>
				</div>
		</div>

		<div class="pagination">
			<button class="btn" v-on:click="fetchProducts(prev_page)" :disabled="!prev_page">
			Previous
			</button>
			<span class="current-page">Page {{currentPage}} of {{allPages}}</span>
			<button class="btn" v-on:click="fetchProducts(next_page)" :disabled="!next_page">
			Next
			</button>
		</div>

	</div>

</template>

<script>

export default {

  data: function() {

    return {
      post: '',
      search: '',
      categoryFilter: '',
      categories: [],
      allPages: '',
      prev_page: '',
      next_page: '',
      currentPage: '',
      postPerPage: '20',
      gridLayout: this.$store.getters.gridLayout,
      hasImage: true,
      label_search: sell_media.search_labels.search,
      label_search_no_results: sell_media.search_labels.no_results,
    }
  },

  mounted: function() {
    this.fetchProducts(1)
    this.getCategories()
  },

  methods: {
    getProducts: function(pageNumber){
      this.currentPage = pageNumber
      this.fetchProducts(pageNumber)
    },

    getCategories: function(){
      this.$http.get('/wp-json/wp/v2/collection')
        .then(function(response){
          this.$set(this, 'categories', response.data)
        })
        .catch(function(error){
          console.log(error)
        })
    },

    makePagination: function(data){
      this.$set(this, 'allPages', data.headers.get('x-wp-totalpages'))

      //Setup prev page
      if(this.currentPage > 1){
        this.$set(this, 'prev_page', this.currentPage - 1)
      } else {
        this.$set(this, 'prev_page', null)
      }

      // Setup next page
      if(this.currentPage == this.allPages){
        this.$set(this, 'next_page', null)
      } else {
        this.$set(this, 'next_page', this.currentPage + 1)
      }
    }
  },
  computed: {
    filteredPosts: function(){
      return this.searchResults.results.filter((post) => {
        return post.title.rendered.match(this.search)
      })
      return this.searchResults.results.filter((post) => {
        if ( this.categoryFilter ) {
          return post.product_type.indexOf(this.categoryFilter) > -1
        } else {
          return post
        }

      })
    }
  }
}
</script>

<style lang="scss" scoped>
</style>
