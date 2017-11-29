<template>

	<div class="container">
		<h3>Search</h3>
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
					<img v-bind:src="post._embedded['wp:featuredmedia'][0].media_details.sizes.large.source_url">
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

	mounted: function() {
		this.getProducts(1)
		this.getCategories()
	},
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
		}
	},
	methods: {
		getProducts: function(pageNumber){

            this.currentPage = pageNumber;

			this.$http.get( '/wp-json/wp/v2/sell_media_item', {
				params: { per_page: this.postPerPage, page: pageNumber, _embed: '' }
			} )
			.then(function(response){
				this.$set(this, 'posts', response.data);
				this.makePagination(response);
			}, function(error){
				console.log(error.statusText);
			});
		},
		getCategories: function(){

			this.$http.get('/wp-json/wp/v2/product_type').then(function(response){
				this.$set(this, 'categories', response.data);
			}, function(error){
				console.log(error.statusText);
			});
		},
		makePagination: function(data){
			this.$set(this, 'allPages', data.headers.get('x-wp-totalpages'));
			
			//Setup prev page
			if(this.currentPage > 1){
				this.$set(this, 'prev_page', this.currentPage - 1);
			} else {
				this.$set(this, 'prev_page', null);
			}

			// Setup next page
			if(this.currentPage == this.allPages){
				this.$set(this, 'next_page', null);
			} else {
				this.$set(this, 'next_page', this.currentPage + 1);
			}
		}
	},
	computed: {
		filteredPosts: function(){
			return this.posts.filter((post) => {
				return post.title.rendered.match(this.search)
			})
			return this.posts.filter((post) => {
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

<style{{#sass}} lang="scss"{{/sass}}>
</style>