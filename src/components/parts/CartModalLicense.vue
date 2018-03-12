<template>
	<div id="modal-license" class="modal has-text-left is-active">
	  <div class="modal-background"></div>
	  <div class="modal-card">
		 <header class="modal-card-head">
			<p class="modal-card-title">Select Your Usage</p>
			<button class="delete" aria-label="close" @click="$emit('closeModal')"></button>
		 </header>
		 <section class="modal-card-body">
		 	<div v-if="taxonomy.terms" class="control-group" v-for="(taxonomy,index) in licenses" :key="taxonomy">

		 		<div class="columns">
					<div class="column is-one-third">
						<p>{{ taxonomy.name }}</p>
					</div>

					<div class="column is-two-thirds">
						<div class="select">
							<select v-model="values[index]">
								<option disabled :value="{}">Select</option>
								<option v-for="term in taxonomy.terms" :key="term" :value="{ term }">{{ term.name }}</option>
							</select>
						</div>
						<!-- <p>{{ values[index].description }}</p> -->
					</div>
				</div>

			</div>

		 </section>
		 <footer class="modal-card-foot">
			<button class="button is-info" @click="apply(values)">{{ labels.apply }}</button>
		 </footer>
	  </div>
	</div>
</template>

<script>
import mixinUser from '../../mixins/user'

export default {
  mixins: [mixinUser],

  data: function() {
    return {
      licenses: {},
      values: {},
      labels: sell_media.cart_labels
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
        } )
        .catch( ( res ) => {
          console.log( res )
        } )
    },

    apply: function(values) {
      this.$emit('closeModal')
      this.$store.dispatch( 'setUsage', values )
    }
  }
}

</script>
