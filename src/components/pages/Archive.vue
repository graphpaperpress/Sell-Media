<template>
	<div :id="name" :class="name">

		<searchform @search="getSearchResults" :loading="loading"></searchform>

		<div v-if="searchResults" class="search-results-total content" >
			<p>{{ search_labels.we_found }} {{ searchResults }} {{ search_labels.results_for }} "{{ search }}." <span class="reset-search" @click="resetSearch">Reset</span></p>
		</div>

		<div v-if="searchResults === 0" class="search-results-total content" >
			<p>{{ search_labels.no_results }} "{{ search }}." <span class="reset-search" @click="resetSearch">Reset</span></p>
		</div>

		<div v-if="loaded" :class="gridContainer" class="is-multiline has-text-centered">
			<thumbnail v-for="post in posts" :key="post.slug" :post="post"></thumbnail>
		</div>

		<div v-else class="loading">
			<div class="is-size-7">loading media...</div>
		</div>

		<nav v-if="totalPages > 1" class="pagination">
			<button class="button" v-if="showPrev" @click.prevent="showPrevPage()" :class="{ 'is-loading': loading }">Previous</button>
			<span> {{ currentPage }} / {{ totalPages }} </span>
			<button class="button" v-if="showNext" @click.prevent="showNextPage()" :class="{ 'is-loading': loading }">Next</button>
		</nav>

	</div>
</template>

<script>

import SearchForm from '../parts/SearchForm.vue';

	export default {

		mounted: function() {
			const vm = this
			const search = vm.$route.query.search
			const type = vm.$route.query.type
			const page = vm.$route.params.page ? vm.$route.params.page : '1'

			if ( search ) {
				vm.getSearchResults( search, type, page )
			} else {
				vm.getPosts( page )
			}

			vm.getUser()
		},

		data: function() {
			return {
				user: {},
				posts: {},
				currentPage: '',
				prevPage: '',
				nextPage: '',
				showNext: true,
				showPrev: true,
				postCollection: '',
				postPerPage: sell_media.posts_per_page,
				totalPages: '',
				loaded: false,
				loading: false,
				pageTitle: '',
				name: this.$options.name,
				search: '',
				search_type: '',
				search_labels: sell_media.search_labels,
				searchResults: false,
				gridContainer: this.$store.getters.gridLayoutContainer
			}
		},

		methods: {

			getPosts: function( pageNumber = 1 ) {
				const vm = this
				vm.loading = true
				vm.$http.get( '/wp-json/wp/v2/sell_media_item', {
					params: {
						per_page: vm.postPerPage,
						page: pageNumber
					}
				} )
				.then( ( res ) => {
					vm.posts = res.data
					vm.totalPages = res.headers[ 'x-wp-totalpages' ]

					if ( pageNumber <= parseInt( vm.totalPages ) ) {
						vm.currentPage = parseInt( pageNumber )
					} else {
						vm.$router.push( { 'name': 'archive' } )
						vm.currentPage = 1
					}

					vm.loading = false
					vm.loaded = true
					vm.pageTitle = 'Archive'
					vm.$store.commit( 'changeTitle', vm.pageTitle )

				} )
				.catch( ( res ) => {
					console.log( res )
				} )
			},

			getSearchResults: function( search, search_type, pageNumber = 1 ) {
				
				const vm = this
				vm.loading = true
				vm.search = search
				vm.search_type = search_type

				vm.$http.get( '/wp-json/sell-media/v2/search', {
					params: {
						s: search,
						type: search_type,
						per_page: vm.postPerPage,
						page: pageNumber
					}
				} )
				.then( ( res ) => {
					vm.posts = res.data
					vm.searchResults = res.headers[ 'x-wp-total' ]? res.headers[ 'x-wp-total' ] : 0 //res.data ? res.data.length : 0
					vm.totalPages = res.headers[ 'x-wp-totalpages' ]

					if ( pageNumber <= parseInt( vm.totalPages ) ) {
						vm.currentPage = parseInt( pageNumber );
					} else {
						vm.$router.push( { name: 'archive', query: { search: search, type: search_type } } );
						vm.currentPage = 1;
					}

					vm.loading = false
					vm.loaded = true
					vm.pageTitle = 'Search results for: ' + search
					vm.$store.commit( 'changeTitle', vm.pageTitle )

				} )
				.catch( ( res ) => {
					console.log( res )
				} )
			},

			resetSearch: function() {
				this.searchResults = false
				this.search = ''
				this.search_type = ''
				this.getPosts()
			},

			showNextPage: function( event ) {
				const vm = this

				if ( vm.currentPage < vm.totalPages ) {
					showNext: true
					vm.currentPage = vm.currentPage + 1
					vm.$router.push( { 'name': 'archive', params: { 'page': vm.currentPage } } )
				}
			},

			showPrevPage: function( event ) {
				const vm = this

				if ( vm.currentPage != 1 ) {
					showPrev: true
					vm.currentPage = vm.currentPage - 1
					vm.$router.push( { 'name': 'archive', params: { 'page': vm.currentPage } } )
				}
			},

			getUser: function() {
				const vm = this
				vm.$http.get( '/wp-json/sell-media/v2/api', {
					params: {
						action: 'get_user',
						_wpnonce: sell_media.nonce
					}
				} )
				.then( ( res ) => {
					vm.user = res.data.ID
					vm.$store.commit( 'setUser', vm.user )
				} )
				.catch( ( res ) => {
					console.log( res )
				} )
			}
		},

		watch: {

			'$route'( to, from ) {
				this.getPosts( this.$route.params.page )
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

	.loading {
		min-height: 600px;
	}

	.reset-search {
		color: #ff2b56;
		cursor: pointer;
	}
</style>
