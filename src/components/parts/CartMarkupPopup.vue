<template>
	<div class="modal" id="modal-ter" v-bind:class="{ 'is-active': showLicensePopup }">
	  <div class="modal-background"></div>
	  <div class="modal-card">
		 <header class="modal-card-head">
			<p class="modal-card-title">Additional Options</p>
			<button class="delete" aria-label="close" v-on:click="this.$parent.toggleLicensePopup"></button>
		 </header>
		 <section class="modal-card-body">
			<select v-model="selected" @change="change(selected)">
				<option disabled :value="{}">Select</option>
				<!-- <option v-for="size in getTerms(taxonomy)" :value="{ term_obj }">{{ term_obj.name }} ({{ term_obj.description }})</option> -->
			</select>
		 </section>
		 <footer class="modal-card-foot">
			<button class="button is-success">Save changes</button>
			<button class="button" v-on:click="this.$parent.toggleLicensePopup">Cancel</button>
		 </footer>
	  </div>
	</div>
</template>

<script>
export default {
	props: ['showLicensePopup'],
	data() {
		return {
		}
	},
	change: function(selected) {
		this.$emit('selected', selected.size);
	}
}
</script>
