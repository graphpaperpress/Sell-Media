<template>
	<div id="modal-license" class="modal has-text-left is-active">
	  <div class="modal-background"></div>
	  <div class="modal-card">
		 <header class="modal-card-head">
			<p class="modal-card-title">Select Your Usage</p>
			<button class="delete" aria-label="close" @click="$emit('closeModal')"></button>
		 </header>
		 <section class="modal-card-body">
		 	<div v-if="taxonomy.terms" class="control-group" v-for="(taxonomy,index) in licenses" :key="index">

		 		<div class="columns">
					<div class="column is-one-third">
						<p>{{ taxonomy.name }}</p>
					</div>

					<div class="column is-two-thirds">
						<div class="select">
							<select v-model="values[index]">
								<option disabled :value="{}">Select</option>
								<option v-for="term in taxonomy.terms" :key="term.id" :value="{ term }">{{ term.name }}</option>
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
import mixinUser from '@/mixins/user'

export default {
  mixins: [mixinUser],

  data: function() {
    return {
      license_url: '/wp-json/sell-media/v2/licensing', // TODO grab this from store, store should get from Wordpress when initializing
      licenses: {},
      values: {},
      labels: sell_media.cart_labels
    }
  },

  mounted: function() {
    this.getLicenses()
  },

  methods: {
    getLicenses: function(){
      const params = {
        per_page: 100,
      }
      this.$http.get(this.license_url, { params })
        .then(res => this.licenses = res.data)
        .catch(res => console.log(res))
    },

    apply: function(values) {
      this.setUsage(values)
      this.$emit('closeModal')
    }
  }
}

</script>
