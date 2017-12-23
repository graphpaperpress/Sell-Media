<template>
	<div id="modal-license" class="modal has-text-left is-active">
	  <div class="modal-background"></div>
	  <div class="modal-card">
		 <header class="modal-card-head">
			<p class="modal-card-title">Select Your Usage</p>
			<button class="delete" aria-label="close" @click="$emit('closeModal')"></button>
		 </header>
		 <section class="modal-card-body">
		 	<div class="control-group" v-for="(taxonomy,index) in licenses" v-if="taxonomy.length > 0" :key="taxonomy">
		 		
		 		<div class="columns">

					<div class="column is-one-third">
						<p>This is the title area</p>
					</div>

					<div class="column is-two-thirds">
						<div class="select">
							<select v-model="selectedUsage[index]" @change="change(selectedUsage[index])">
								<option disabled :value="{ term: { id: '', description: '', sell_media_meta: { markup: '' } } }">Select</option>
								<option v-for="term in taxonomy" :key="term" :value="{ term }">{{ term.name }}</option>
							</select>
						</div>
						<p>{{ selectedUsage[index].term.description }}</p>
					</div>
				</div>

			</div>

		 </section>
		 <footer class="modal-card-foot">
			<button class="button is-success" @click="$emit('selectedUsage', selectedUsage)">Apply</button>
		 </footer>
	  </div>
	</div>
</template>

<script>

	export default {

		data: function() {
			return {
				taxonomies: sell_media.licensing_markup_taxonomies,
				licenses: {},
				selectedUsage: {
					0: {
						term: {
							id: '',
							description: '',
							sell_media_meta: {
								markup: ''
							}
						}
					},
					1: {
						term: {
							id: '',
							description: '',
							sell_media_meta: {
								markup: ''
							}
						}
					},
					2: {
						term: {
							id: '',
							description: '',
							sell_media_meta: {
								markup: ''
							}
						}
					}
				}
			}
		},

		mounted: function() {
			const vm = this
			vm.getLicenses()
		},

		methods: {
			getLicenses: function(){
				const vm = this
				let obj = []
				vm.taxonomies.forEach(function(taxonomy) {
					vm.$http.get( '/wp-json/wp/v2/' + taxonomy, {
						params: {
							per_page: 100
						}
					} )
					.then( ( res ) => {
						obj.push(res.data)
						
					} )
					.catch( ( res ) => {
						console.log( res )
					} )
				} )
				vm.licenses = obj
			},

			change: function(usage) {
				//console.log(usage)
			}
		}
	}

</script>
