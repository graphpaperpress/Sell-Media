<template>
	<div :id="name" :class="name">

		<div class="search-wrapper">
			<label>{{ search_label }}</label>
			<input type="text" v-model="search" :placeholder="search_label" @keyup.enter="getSearchResults(search)">
		</div>
		
		<template v-if="layout === 'sell-media-masonry' || layout === 'sell-media-horizontal-masonry'">
			<masonry :posts="posts" class="has-text-centered"></masonry>
		</template>
		<template v-else>
			<div class="columns is-gapless is-multiline has-text-centered" v-if="loaded === true">
				<div v-for="post in posts" :class="gridLayout" class="column is-mobile">
					<thumbnail :key="post.slug" :post="post"></thumbnail>
				</div>
			</div>
		</template>
		<nav class="pagination">
			<button class="button" v-if="showPrev" @click.prevent="showPrevPage()">Previous</button>
			<span> {{ currentPage }} / {{ totalPages }} </span>
			<button class="button" v-if="showNext" @click.prevent="showNextPage()">Next</button>
		</nav>

<!-- 		<div id="child">
			<child :message="name"></child>
		</div> -->

	</div>
</template>

<script>

//import SearchForm from '../parts/SearchForm.vue';
import Masonry from '../parts/Masonry.vue';

	export default {

		mounted: function() {
			const vm = this;

			if ( vm.$route.params.page ) {
				vm.getPosts( vm.$route.params.page );
			} else {
				vm.getPosts();
			}
		},

		data: function() {
			return {
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
				name: this.$options.name, // component name
				gridLayout: this.$store.getters.gridLayout,
				search: '',
				search_label: sell_media.search_labels.search,
			}
		},

		methods: {

			getPosts: function( pageNumber = 1 ) {
				const vm = this;
				vm.loaded = false;
				vm.$http.get( '/wp-json/wp/v2/sell_media_item', {
					params: {
						per_page: vm.postPerPage,
						page: pageNumber
					}
				} )
				.then( ( res ) => {
					vm.posts = res.data;
					console.log(vm.posts)
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

			getSearchResults: function( search, pageNumber = 1 ) {
				const vm = this;
				vm.loaded = false;
				vm.$http.get( '/wp-json/sell-media/v2/search', {
					params: {
						s: search,
						per_page: vm.postPerPage,
						page: pageNumber
					}
				} )
				.then( ( res ) => {
					vm.posts = res.data;
					console.log(vm.posts);
					vm.totalPages = res.headers[ 'x-wp-totalpages' ];

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
			}
		},

		watch: {

			'$route'( to, from ) {
				this.getPosts( this.$route.params.page );
			}

		},

		components: {
			'masonry': Masonry,
			//'searchform': SearchForm,
		}
	}
</script>

<style lang="scss" scoped>

	.search-wrapper {
		margin: 2rem auto;
	}
</style>