<template>
	<div id="modal-license" class="modal has-text-left is-active">
	  <div class="modal-background"></div>
	  <div class="modal-card">
		 <header class="modal-card-head">
			<p class="modal-card-title">Select Your Usage</p>
			<button class="delete" aria-label="close" @click="$emit('closeModal')"></button>
		 </header>
		 <section class="modal-card-body">
		 	<div class="control-group" v-for="(taxonomy,index) in licenses" :key="taxonomy" v-if="taxonomy.terms">

		 		<div class="columns">
					<div class="column is-one-third">
						<p>{{ taxonomy.name }}</p>
					</div>

					<div class="column is-two-thirds">
						<div class="select">
							<select v-model="selectedUsage[index]">
								<option disabled :value="{}">Select</option>
								<option v-for="term in taxonomy.terms" :key="term" :value="{ term }">{{ term.name }}</option>
							</select>
						</div>
						<!-- <p>{{ selectedUsage[index].description }}</p> -->
					</div>
				</div>

			</div>

		 </section>
		 <footer class="modal-card-foot">
			<button class="button is-success" @click="setUsage(selectedUsage)">Apply</button>
		 </footer>
	  </div>
	</div>
</template>

<script>

	export default {

		data: function() {
			return {
				licenses: {},
				selectedUsage: {},
			}
		},

		mounted: function() {
			const vm = this
			vm.getLicenses()
		},

		methods: {
			getLicenses: function(){
				const vm = this
				vm.$http.get( '/wp-json/sell-media/v2/licensing', {
					params: {
						per_page: 100
					}
				} )
				.then( ( res ) => {
					vm.licenses = res.data
					console.log(vm.licenses)
				} )
				.catch( ( res ) => {
					console.log( res )
				} )
			},

			setUsage: function(value) {
				this.$emit('setUsage', value)
				this.$emit('closeModal')
			}
		}
	}

</script>
