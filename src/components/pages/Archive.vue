<template>
	<div :id="name" :class="name">

		<searchform @search="getSearchResults" :loading="loading" :search="search"></searchform>
		<div class="search-results-wrapper" v-if="!loading">

			<div class="content">
				<p v-if="search && searchResults">{{ search_labels.we_found }} {{ searchResults }} {{ search_labels.results_for }} "{{ search }}." <span class="reset-search" @click="resetSearch">Reset</span></p>

				<p v-if="search && !searchResults">{{ search_labels.no_results }} {{ search_labels.results_for }} "{{ search }}." <span class="reset-search" @click="resetSearch">Reset</span></p>
			</div>

			<div :class="gridContainer" class="is-multiline has-text-centered">
				<thumbnail v-for="post in posts" :key="post.slug" :post="post"></thumbnail>
			</div>

			<nav v-if="totalPages > 1" class="pagination">
				<button class="button" v-if="showPrev" @click.prevent="showPrevPage()" :class="{ 'is-loading': loading }">Previous</button>
				<span> {{ currentPage }} / {{ totalPages }} </span>
				<button class="button" v-if="showNext" @click.prevent="showNextPage()" :class="{ 'is-loading': loading }">Next</button>
			</nav>

		</div>

	</div>
</template>

<script>
import mixinGlobal from '../../mixins/global'
import SearchForm from '../parts/SearchForm.vue';

	export default {
    mixins: [mixinGlobal],
		data(){
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
				loading: false,
				pageTitle: '',
				name: this.$options.name,
				search: '',
				search_type: sell_media.default_search_type ? sell_media.default_search_type : '',
				search_labels: sell_media.search_labels,
				searchResults: false,
				gridContainer: this.$store.getters.gridLayoutContainer
			}
		},

		mounted(){
			const vm = this
			const search = vm.$route.query.search ? vm.$route.query.search : vm.search
			const type = vm.$route.query.type ? vm.$route.query.type : vm.search_type
			const page = vm.$route.params.page ? vm.$route.params.page : '1'

			if ( search || type ) {
				vm.getSearchResults( search, type, page )
			} else {
				vm.getPosts()
			}
			vm.getUser()
		},

		methods: {

			getPosts(pageNumber = 1){
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

					vm.pageTitle = 'Archive'
					vm.$store.dispatch( 'changeTitle', vm.pageTitle )
					vm.loading = false

				} )
				.catch( ( res ) => {
					console.log( res )
				} )
			},

			getSearchResults(search, search_type, pageNumber = 1){

				const vm = this
				vm.loading = true
				vm.search = search
				vm.search_type = search_type

				if ( search ) {
					vm.$router.push( { name: 'archive', params: { page: pageNumber }, query: { search: search, type: search_type } } );
				}

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
					vm.searchResults = res.headers[ 'x-wp-total' ] ? res.headers[ 'x-wp-total' ] : 0
					vm.totalPages = res.headers[ 'x-wp-totalpages' ]

					if ( pageNumber <= parseInt( vm.totalPages ) ) {
						vm.currentPage = parseInt( pageNumber )
					} else {
						vm.currentPage = 1
					}

					vm.pageTitle = 'Search results for: ' + search
					vm.$store.dispatch( 'changeTitle', vm.pageTitle )
					vm.loading = false

				} )
				.catch( ( res ) => {
					console.log( res )
				} )
			},

			resetSearch(){
				this.searchResults = false
				this.search = ''
				this.search_type = sell_media.default_search_type ? sell_media.default_search_type : ''
				this.$router.push( { name: 'archive', query: {} } );
				this.getPosts()
			},

			showNextPage(event){
				const vm = this

				if ( vm.currentPage < vm.totalPages ) {
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
			},

			getUser(){
				const vm = this
				vm.$http.get( '/wp-json/sell-media/v2/api', {
					params: {
						action: 'get_user',
						_wpnonce: sell_media.nonce
					}
				} )
				.then( ( res ) => {
					vm.user = res.data.ID
					vm.$store.dispatch( 'setUser', vm.user )
				} )
				.catch( ( res ) => {
					console.log( res )
				} )
			}
		},

		watch: {

			'$route'( to, from ) {
				const vm = this
				if ( vm.search || vm.$route.query.search ) {
					let search = vm.$route.query.search ? vm.$route.query.search : vm.search
					vm.getSearchResults( search, vm.search_type, vm.$route.params.page )
				} else if ( ! vm.search && vm.search_type ) {
					vm.getSearchResults( '', vm.search_type, vm.$route.params.page )
			 	} else {
					vm.getPosts( vm.$route.params.page )
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
