<template>

	<div class="search-wrapper">
		<div class="field has-addons">
			<p class="control">
				<span class="select">
					<select v-model="search_type">
						<option value=""></option>
						<option v-for="type in types" v-if="type.count > 0" :value="type.slug">{{ type.name }}</option>
					</select>
				</span>
			</p>
			<p class="control">
				<input v-model="search" class="input" type="text" :placeholder="labels.search" @keyup.enter="$emit('search', search)">
			</p>
			<p class="control">
				<button class="button is-info" @click="$emit('search', search)">
					{{ labels.search }}
				</button>
			</p>
		</div>
	</div>

</template>

<script>
	
	export default {

		props: [],

		data: function() {
			return {
				types: {},
				labels: sell_media.search_labels,
				search_type: '',
				search: ''
			}
		},

		mounted: function() {
			this.getProductTypes();
		},

		methods: {

			getProductTypes: function() {
				const vm = this;
				vm.loaded = false;
				vm.$http.get( '/wp-json/wp/v2/product_type' )
				.then( ( res ) => {
					vm.types = res.data;
				} )
				.catch( ( res ) => {
					console.log( res )
				} )
			},
		}
	}

</script>