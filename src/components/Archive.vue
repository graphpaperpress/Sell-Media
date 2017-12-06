<template>
	<div class="columns is-multiline">
		<item v-for="item in items" v-bind:i="item"></item>
	</div>
</template>

<script>

	import Item from './Item.vue';

	export default {

		mounted: function() {
			this.getItems();
		},

		data: function() {
			return {
				items: {},
				item: '',
				page: 1,
				per_page: 10,
			}
		},

		methods: {
			getItems: function() {
				const vm = this;
				vm.$http.get( '/wp-json/wp/v2/sell_media_item', {
					params: {
						per_page: vm.per_page,
						page: vm.page,
						_embed: true
					}
				} )
				.then(function(response){
					vm.items = response.data
					console.log(vm.items)
				})
				.catch(function(error){
					console.log(error)
				})
			},
			nextPage: function() {
				const vm = this
				vm.$set(vm, 'page', vm.page + 1)
			},
			prevPage: function() {
				const vm = this
				vm.$set(vm, 'page', vm.page - 1)
			}
		},

		components: {
			'Item': Item
		}
	}
</script>