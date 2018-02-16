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
import mixinGlobal from '../../mixins/global'
import mixinProduct from '../../mixins/product'
import SearchForm from '../parts/SearchForm.vue';

	export default {
    mixins: [mixinGlobal, mixinProduct],

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
			const vm = this
			const search = vm.$route.query.search ? vm.$route.query.search : vm.search
			const type = vm.$route.query.type ? vm.$route.query.type : vm.search_type
			const page = vm.$route.params.page ? vm.$route.params.page : '1'

      vm.$store.dispatch('changeTitle', 'Archive')
      vm.$store.dispatch('getUser')

			if ( search || type ) {
				vm.getSearchResults({ search: search, search_type: type, page_number: page })
			} else {
        this.$store.dispatch('fetchProducts', 1)
			}
		},

		methods: {
			getSearchResults({ search, search_type, pageNumber = 1 }){
				const vm = this
				vm.search = search
        vm.search_type = search_type ? search_type : ''

				if ( search ) {
					vm.$router.push({
            name: 'archive',
            params: { page: pageNumber },
            query: { search: search, type: search_type }
          });
        }

        vm.$store.dispatch( 'searchProducts', {
          search: search,
          search_type: search_type,
          page_number: pageNumber
        })

        vm.$store.dispatch( 'changeTitle', 'Search results for: ' + search )
			},

			resetSearch(){
				this.hasSearchResults = false
				this.search = ''
				this.search_type = sell_media.default_search_type ? sell_media.default_search_type : ''
				this.$router.push( { name: 'archive', query: {} } );
        this.$store.dispatch('fetchProducts', 1)
			},

			showNextPage(event){
				const vm = this

				if ( vm.currentPage < vm.searchResults.totalPages ) {
					showNext: true
					vm.currentPage = vm.currentPage + 1
					vm.$router.push( { 'name': 'archive', params: { 'page': vm.currentPage } } )
				}
			},

			showPrevPage(event){
				const vm = this

				if ( vm.currentPage != 1 ) {
					showPrev: true
					vm.currentPage = vm.currentPage - 1
					vm.$router.push( { 'name': 'archive', params: { 'page': vm.currentPage } } )
				}
			}
		},

		watch: {

			'$route'( to, from ) {
				const vm = this
				if ( vm.search || vm.$route.query.search ) {
					let search = vm.$route.query.search ? vm.$route.query.search : vm.search
					vm.getSearchResults( { search: search, search_type: vm.search_type, page_number: vm.$route.params.page } )
				} else if ( ! vm.search && vm.search_type ) {
					vm.getSearchResults( { search: '', search_type: vm.search_type, page_number: vm.$route.params.page } )
			 	} else {
          this.$store.dispatch('fetchProducts', vm.$route.params.page)
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
