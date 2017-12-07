<template>
	<div v-bind:id="name" v-bind:class="name">
		<div class="columns is-multiline" v-if="loaded === true">
			<sm-grid-item v-for="post in posts" v-bind:key="post.slug" v-bind:p="post"></sm-grid-item>
		</div>
		<nav class="pagination">
			<button class="button" v-if="showPrev" v-on:click.prevent="showPrevPage()">Previous</button>
			<span> {{ currentPage }} / {{ totalPages }} </span>
			<button class="button" v-if="showNext" v-on:click.prevent="showNextPage()">Next</button>
		</nav>
	</div>
</template>

<script>

	import GridItem from './GridItem.vue';

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
				name: this.$options.name // component name
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
					console.log(vm.posts);
					vm.totalPages = res.headers[ 'x-wp-totalpages' ];

					if ( pageNumber <= parseInt( vm.totalPages ) ) {
						vm.currentPage = parseInt( pageNumber );
					} else {
						vm.$router.push( { 'name': 'archive' } );
						vm.currentPage = 1;
					}

					vm.loaded = true;
					vm.pageTitle = 'Archive';
					vm.$store.commit( 'smChangeTitle', vm.pageTitle );

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
		},

		watch: {

			'$route'( to, from ) {
				this.getPosts( this.$route.params.page );
			}

		},

		components: {
			'sm-grid-item': GridItem // grid-item = component html tag, GridItem = component object
		}
	}
</script>