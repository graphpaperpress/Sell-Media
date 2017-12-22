<template>
	<div id="modal-license" class="modal is-active">
	  <div class="modal-background"></div>
	  <div class="modal-card">
		 <header class="modal-card-head">
			<p class="modal-card-title">Select your usage</p>
			<button class="delete" aria-label="close" @click="$emit('closeModal')"></button>
		 </header>
		 <section class="modal-card-body">
		 	<div class="control-group" v-for="(taxonomy,index) in licenses" :key="index">
		 		{{ name }}
				<div class="control" v-for="(taxonomy,index) in licenses" :key="index">
					<div class="select">
						<select v-model="selected" @change="change(selected)">
							<option disabled :value="{}">Select</option>
							<!-- <option v-for="size in getTerms(taxonomy)" :value="{ term_obj }">{{ term_obj.name }} ({{ term_obj.description }})</option> -->
						</select>
					</div>
				</div>
			</div>
		 </section>
		 <footer class="modal-card-foot">
			<button class="button is-success" @click="$emit('selected')">Save changes</button>
			<button class="button" @click="$emit('closeModal')">Cancel</button>
		 </footer>
	  </div>
	</div>
</template>

<script>
export default {

	props: ['licenses'],

	data() {
		return {
		}
	},

	methods: {

		change: function(selected) {
			this.$emit('selected', selected.size);
		}
	}
}
</script>
