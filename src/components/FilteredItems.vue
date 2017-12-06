<template>
	<div class="container">
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

			<div class="filtered-tab" v-for="category in categories">
				<input
				type="radio"
				v-bind:id="category.slug"
				v-bind:value="category.id"
				v-model="categoryFilter">
				<label v-bind:for="category.slug">{{ category.name }}</label>
			</div>
		</div>

		<div class="filtered-content masonry">
			<article v-for="post in filteredPosts" class="post masonry-brick">
				<a v-bind:href="post.link" v-bind:title="post.title.rendered">
					<img v-if="hasImage" v-bind:src="post._embedded['wp:featuredmedia'][0].media_details.sizes.large.source_url">
					<div class="post-content">
						<h2>{{ post.title.rendered }}</h2>
						<small v-for="keyword in post.product_keywords">
							{{ keyword }}
						</small>
					</div>
				</a>
			</article>
		</div>

		<div class="pagination">
			<button class="btn" v-on:click="getProducts(prev_page)" :disabled="!prev_page">
			Previous
			</button>
			<span class="current-page">Page {{currentPage}} of {{allPages}}</span>
			<button class="btn" v-on:click="getProducts(next_page)" :disabled="!next_page">
			Next
			</button>
		</div>

	</div>

</template>

<script>
export default {
	data: function() {
		return {
			posts: [],
			post: '',
			search: '',
			categoryFilter: '',
			categories: [],
			allPages: '',
			prev_page: '',
			next_page: '',
			currentPage: '',
			postPerPage: '20',
			hasImage: true,
			// string translations uses wp_localize_script in scripts.php
			label_search: sell_media.search_labels.search,
			label_search_no_results: sell_media.search_labels.no_results,
		}
	},
	mounted: function() {
		const vm = this;
		vm.getProducts(1)
		vm.getCategories()
	},
	methods: {
		getProducts: function(pageNumber){
			const vm = this;

            vm.currentPage = pageNumber;

			vm.$http.get( '/wp-json/wp/v2/sell_media_item', {
				params: {
					per_page: vm.postPerPage,
					page: pageNumber,
					_embed: null
				}
			})
			.then(function(response){
				vm.$set(vm, 'posts', response.data);
				vm.makePagination(response);
			}
			.catch(function(error){
				console.log(error)
			})
		},
		getCategories: function(){
			const vm = this;

			vm.$http.get('/wp-json/wp/v2/collection')
			.then(function(response){
				vm.$set(vm, 'categories', response.data);
			}
			.catch(function(error){
				console.log(error);
			}
		},
		makePagination: function(data){
			const vm = this;

			vm.$set(vm, 'allPages', data.headers.get('x-wp-totalpages'));
			
			//Setup prev page
			if(vm.currentPage > 1){
				vm.$set(vm, 'prev_page', vm.currentPage - 1);
			} else {
				vm.$set(vm, 'prev_page', null);
			}

			// Setup next page
			if(vm.currentPage == vm.allPages){
				vm.$set(vm, 'next_page', null);
			} else {
				vm.$set(vm, 'next_page', vm.currentPage + 1);
			}
		}
	},
	computed: {
		filteredPosts: function(){
			const vm = this;

			return vm.posts.filter((post) => {
				return post.title.rendered.match(vm.search)
			})
			return vm.posts.filter((post) => {
				if ( vm.categoryFilter ) {
					return post.product_type.indexOf(vm.categoryFilter) > -1
				} else {
					return post
				}
				
			})
		}
	}
}
</script>

<style{{#sass}} lang="scss"{{/sass}}>
</style>