<template>

	<div class="search-container" :class="{ active: showFilters }">

		<div class="columns">

			<div class="search-area column is-two-thirds">
				<div class="field has-addons">
					<p class="control">
						<span class="select is-medium">
							<select v-model="search_type">
								<option value="">{{ labels.all }}</option>
								<option v-for="type in types" v-if="type.count > 0" :value="type.slug">{{ type.name }}</option>
							</select>
						</span>
					</p>
					<p class="control is-expanded">
						<input v-model="search" class="input is-medium" type="text" :placeholder="labels.search" @keyup.enter="$emit('search', search)">
					</p>
					<p class="control">
						<button class="button is-medium is-dark" @click="$emit('search', search)">
							{{ labels.search }}
						</button>
					</p>
				</div>
			</div>
			<div class="filters-button column is-one-third">
				<button class="button is-medium is-pulled-right" @click="showFilters = !showFilters">
					<template v-if="!showFilters">
						<span class="icon is-large">
							<icon name="angle-down"></icon>
						</span>
					 	{{ labels.open_filters }}
					 </template>
					<template v-else>
						<span class="icon is-large">
							<icon name="angle-up"></icon>
						</span>
					 	{{ labels.close_filters }}
					</template>
				</button>
			</div>

		</div>

		<div v-if="showFilters" class="filters-area">
			<div class="columns">
				<div class="column">
					<h6 class="has-text-weight-bold is-uppercase">{{ labels.colors }}</h6>
				</div>
				<div class="column">
					<h6 class="has-text-weight-bold is-uppercase">{{ labels.orientation }}</h6>
					<p class="horizontal">
						<label class="checkbox">
							<input type="checkbox">
							{{ labels.horizontal }}
						</label>
					</p>
					<p class="vertical">
						<label class="checkbox">
							<input type="checkbox">
							{{ labels.vertical }}
						</label>
					</p>
					<p class="panoramic">
						<label class="checkbox">
							<input type="checkbox">
							{{ labels.panoramic }}
						</label>
					</p>
				</div>
				<div class="column">
					<h6 class="has-text-weight-bold is-uppercase">{{ labels.collections }}</h6>
				</div>
			</div>
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
				search: '',
				showFilters: false,
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
			}
		}
	}

</script>

<style lang="scss" scoped>

.search-container {
	margin-bottom: 2rem;

	&.active {}
}

.filters-button .fa-icon {
	margin-right: 1rem;
	width: auto;
	height: 1em;
}

.filters-area {
	h6 {
		padding-bottom: 1rem;
		margin-bottom: 1rem;
	}
}

</style>