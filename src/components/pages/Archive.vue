<template>
	<div :id="name" :class="name">

		<searchform @search="getSearchResults"></searchform>

		<div v-if="searchResults" class="search-results-total" >
			{{ search_labels.we_found }} {{ searchResults }} {{ search_labels.results_for }} "{{ search }}."
		</div>
		<div v-if="searchResults === 0" class="search-results-total" >
			{{ search_labels.no_results }} "{{ search }}."
		</div>

		<div :class="gridContainer" class="columns is-multiline has-text-centered" v-if="loaded === true">
			<thumbnail v-for="post in posts" :key="post.slug" :post="post"></thumbnail>
		</div>

		<nav v-if="totalPages > 1" class="pagination">
			<button class="button" v-if="showPrev" @click.prevent="showPrevPage()">Previous</button>
			<span> {{ currentPage }} / {{ totalPages }} </span>
			<button class="button" v-if="showNext" @click.prevent="showNextPage()">Next</button>
		</nav>

	</div>
</template>

<script>

import SearchForm from '../parts/SearchForm.vue';
import Masonry from '../parts/Masonry.vue';

	export default {

		mounted: function() {
			const vm = this;

			if ( vm.$route.params.page ) {
				vm.getPosts( vm.$route.params.page );
			} else {
				vm.getPosts();
			}

			vm.getUser();
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
				pageTitle: '',
				name: this.$options.name,
				search: '',
				search_labels: sell_media.search_labels,
				searchResults: false,
				search_type: '',
				gridContainer: this.$store.getters.gridLayout + '-container'
			}
		},

		methods: {

			getPosts: function( pageNumber = 1 ) {
				const vm = this;
				vm.loaded = false;
				let path = '/wp-json/wp/v2/sell_media_item';
				let params = {
					per_page: vm.postPerPage,
					page: pageNumber
				}
				if ( false !== this.searchResults ) {
					path = '/wp-json/sell-media/v2/search';
					params = {
						s: this.search,
						per_page: vm.postPerPage,
						page: pageNumber,
						type: this.search_type
					}
				}

				vm.$http.get( path, {
					params
				} )
				.then( ( res ) => {
					vm.posts = res.data;
					vm.totalPages = res.headers[ 'x-wp-totalpages' ];

					if ( pageNumber <= parseInt( vm.totalPages ) ) {
						vm.currentPage = parseInt( pageNumber );
					} else {
						vm.$router.push( { 'name': 'archive' } );
						vm.currentPage = 1;
					}

					vm.loaded = true;
					vm.pageTitle = 'Archive';
					vm.$store.commit( 'changeTitle', vm.pageTitle );

				} )
				.catch( ( res ) => {
					console.log( res )
				} )
			},

			getSearchResults: function( search, search_type, pageNumber = 1 ) {
				const vm = this;
				vm.loaded = false;
				this.search_type = search_type;
				vm.$http.get( '/wp-json/sell-media/v2/search', {
					params: {
						s: search,
						per_page: vm.postPerPage,
						page: pageNumber,
						type: search_type
					}
				} )
				.then( ( res ) => {
					vm.posts = res.data ? res.data : this.posts
					vm.searchResults = res.data ? res.data.length : 0
					vm.search = search
					vm.totalPages = res.headers[ 'x-wp-totalpages' ]

					if ( pageNumber <= parseInt( vm.totalPages ) ) {
						vm.currentPage = parseInt( pageNumber );
					} else {
						vm.$router.push( { 'name': 'archive' } );
						vm.currentPage = 1;
					}

					vm.loaded = true;
					vm.pageTitle = 'Search results for:' + search;
					vm.$store.commit( 'changeTitle', vm.pageTitle );

				} )
				.catch( ( res ) => {
					console.log( res )
				} )
			},

			showNextPage: function( event ) {
				const vm = this;

				if ( vm.currentPage < vm.totalPages ) {
					showNext: true;
					vm.currentPage = vm.currentPage + 1;
					vm.$router.push( { 'name': 'archive', params: { 'page': vm.currentPage } } );
				}
			},

			showPrevPage: function( event ) {
				const vm = this;

				if ( vm.currentPage != 1 ) {
					showPrev: true;
					vm.currentPage = vm.currentPage - 1;
					vm.$router.push( { 'name': 'archive', params: { 'page': vm.currentPage } } );
				}
			},

			getUser: function() {
				const vm = this;
				vm.$http.get( '/wp-json/sell-media/v2/api', {
					params: {
						action: 'get_user',
						_wpnonce: sell_media.nonce
					}
				} )
				.then( ( res ) => {
					vm.user = res.data.ID
					vm.$store.commit( 'setUser', vm.user );
				} )
				.catch( ( res ) => {
					console.log( res )
				} )
			}
		},

		watch: {

			'$route'( to, from ) {
				this.getPosts( this.$route.params.page );
			}

		},

		components: {
			'masonry': Masonry,
			'searchform': SearchForm,
		}
	}
</script>

<style lang="scss" scoped>

	.search-wrapper {
		margin: 2rem auto;
	}
</style>
