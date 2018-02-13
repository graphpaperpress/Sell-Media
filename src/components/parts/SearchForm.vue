<template>

	<div class="search-container" :class="{ active: showFilters }">

		<div class="columns is-clearfix">

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
						<input :value="search" class="input is-medium" type="text" :placeholder="labels.search" @keyup.enter="$emit('search', $event.target.value, search_type)">
					</p>
					<p class="control">
						<button class="button is-medium is-dark" @click="$emit('search', search, search_type)" :class="{ 'is-loading': loading }">
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

		<div class="filters box" v-if="showFilters || colors.hex !== '#2A94AE' || locations.length > 0 || sort">

			<div class="filters-active">

				<div class="field is-grouped is-grouped-multiline">

					<p class="control" v-if="colors.hex !== '#2A94AE' || locations.length > 0 || sort">Search Filters:</p>

					<div v-if="colors.hex !== '#2A94AE'" class="control is-lowercase">
						<div class="tags has-addons">
							<a class="tag" style="color: #fff" :style="{ 'background-color': colors.hex }">Color: {{ colors.hex }}</a>
							<a class="tag is-delete" @click="remove('colors')"></a>
						</div>
					</div>

					<div v-for="(location,index) in locations" :key="index" class="control is-lowercase">
						<div class="tags has-addons">
							<span class="tag is-success">Location: {{ location }}</span>
							<a class="tag is-delete" @click="remove(location, locations)"></a>
						</div>
					</div>

					<div v-if="sort" class="control is-lowercase">
						<div class="tags has-addons">
							<span class="tag is-info">{{ labels.sort }}: {{ sort }}</span>
							<a class="tag is-delete" @click="remove('sort')"></a>
						</div>
					</div>

				</div>

			</div>

			<div v-if="showFilters" class="filters-area">

				<div class="columns">

					<div class="column is-two-thirds">

						<div class="columns">

							<div class="column">
								<h6 class="has-text-weight-bold is-uppercase">{{ labels.colors }}</h6>
								<compact-picker v-model="colors"></compact-picker>
							</div>

							<div class="column">
								<h6 class="has-text-weight-bold is-uppercase">Locations</h6>
								<div class="control">
									<div v-for="(field,index) in locationFields" :key="index" :class="field.slug" class="field">
										<input class="is-checkradio is-success" type="checkbox" :id="field.slug" :value="field.slug" v-model="locations">
										<label class="checkbox" :for="field.slug">{{ field.name }}</label>
									</div>
								</div>
							</div>

							<div class="column">
								<h6 class="has-text-weight-bold is-uppercase">{{ labels.sort }}</h6>
								<div class="control">
									<div v-for="(field,index) in sortFields" :key="index" :class="field.slug" class="field">
										<input class="is-checkradio is-info" type="radio" :id="field.slug" :value="field.slug" v-model="sort">
										<label class="radio" :for="field.slug">{{ field.name }}</label>
									</div>
								</div>
							</div>

						</div>

					</div>

					<div class="column is-one-third">
						<div class="content">
							<h6 class="has-text-weight-bold is-uppercase">{{ labels.search_tips }}</h6>
							<ul>
								<li v-for="(tip,index) in labels.tips">{{ tip }}.</li>
							</ul>
						</div>
					</div>

				</div>

			</div>

		</div>

	</div>

</template>

<script>

	import { Compact } from 'vue-color'

	let defaultColors = {
		hex: '#2A94AE',
		hsl: {
			h: 192,
			s: 0.61,
			l: 0.42,
			a: 1
		},
		hsv: {
			h: 150,
			s: 0.66,
			v: 0.30,
			a: 1
		},
		rgba: {
			r: 42,
			g: 148,
			b: 174,
			a: 1
		},
		a: 1
	}

	export default {

		props: ['loading', 'search'],

		data(){
			return {
				types: {},
				labels: sell_media.search_labels,
				search_type: sell_media.default_search_type ? sell_media.default_search_type : '',
				showFilters: false,
				colors: defaultColors,
				locations: [],
				locationFields: {
					0: {
						name: 'Urban',
						slug: 'urban'
					},
					1: {
						name: 'Rural',
						slug: 'rural'
					},
					2: {
						name: 'Mountains',
						slug: 'mountains'
					},
					3: {
						name: 'Coastal',
						slug: 'coastal'
					},
					4: {
						name: 'Desert',
						slug: 'desert'
					},
					5: {
						name: 'Forest',
						slug: 'forest'
					}
				},
				sort: '',
				sortFields: {
					0: {
						name: sell_media.search_labels.date,
						slug: 'date'
					},
					1: {
						name: sell_media.search_labels.name,
						slug: 'name'
					},
					2: {
						name: sell_media.search_labels.popular,
						slug: 'popular'
					}
				},

			}
		},

		mounted() {
			this.getProductTypes();
		},

		methods: {

			getProductTypes(){
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

			remove(item, array){
				if ( array ) {
					const index = array.indexOf(item);
					array.splice(index, 1);
				}
				if ( item === 'colors' ) {
					this.colors = defaultColors
				}
				if ( item === 'sort' ) {
					this.sort = ''
				}
			}
		},

		components: {
			'compact-picker': Compact
		}
	}

</script>

<style lang="scss" scoped>

.search-container {
	margin: 2rem 0;

	&.active {}
}

.search-area {
	.select {
		width: inherit;

		@media (max-width: 468px) {
			width: 8rem;
		}
	}
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

	ul {
		margin: 0;
	}
}

input[type=text] {
	border: 1px solid #dbdbdb;
	box-shadow: inset 0 1px 2px hsla(0,0%,4%,.1);
}

</style>
