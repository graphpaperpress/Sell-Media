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
						<input v-model="search" class="input is-medium" type="text" :placeholder="labels.search" @keyup.enter="$emit('search', search, search_type)">
					</p>
					<p class="control">
						<button class="button is-medium is-dark" @click="$emit('search', search, search_type)">
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
					<chrome-picker v-model="colors" />
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

	import { Chrome } from 'vue-color'

	let defaultProps = {
	  hex: '#194d33',
	  hsl: {
	    h: 150,
	    s: 0.5,
	    l: 0.2,
	    a: 1
	  },
	  hsv: {
	    h: 150,
	    s: 0.66,
	    v: 0.30,
	    a: 1
	  },
	  rgba: {
	    r: 25,
	    g: 77,
	    b: 51,
	    a: 1
	  },
	  a: 1
	}

	export default {

		props: [],

		data: function() {
			return {
				types: {},
				labels: sell_media.search_labels,
				search_type: '',
				search: '',
				showFilters: false,
				colors: defaultProps
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
		},

		components: {
			'chrome-picker': Chrome
		}
	}

</script>

<style lang="scss" scoped>

.search-container {
	margin: 2rem 0;

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

input[type=text] {
	border: 1px solid #dbdbdb;
	box-shadow: inset 0 1px 2px hsla(0,0%,4%,.1);
}

</style>
