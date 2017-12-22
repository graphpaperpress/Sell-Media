<template>
	<div id="modal-license" class="modal is-active">
	  <div class="modal-background"></div>
	  <div class="modal-card">
		 <header class="modal-card-head">
			<p class="modal-card-title">Additional Options</p>
			<button class="delete" aria-label="close" @click="$emit('closeModal')"></button>
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
	data() {
		return {
		}
	},
	change: function(selected) {
		this.$emit('selected', selected.size);
	}
}
</script>
