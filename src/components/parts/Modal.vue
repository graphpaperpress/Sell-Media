<template>
	<div v-bind:id="name" v-bind:class="name" class="modal is-active">
		<div class="modal-background" @click="$emit('closeModal')"></div>
		<div class="modal-content">
			<div class="columns">
				<div class="column is-two-thirds">
					<figure class="image">
						<img
							:src="p.sell_media_featured_image.sizes.large[0]" 
							:data-srcset="p.sell_media_featured_image.sizes.srcset[0]"
							:alt="p.sell_media_featured_image.alt" 
						/>
					</figure>
				</div>
				<div class="column">
					<div class="box is-shadowless">
						<p class="title is-5">{{p.title.rendered}}</p>

						<div class="field">
							<div class="control">
								<label class="radio">
									<input type="radio" name="question">
									Yes
								</label>
								<label class="radio">
									<input type="radio" name="question">
									No
								</label>
							</div>
						</div>

						<sm-select-size v-bind:key="p.slug" v-bind:p="p"></sm-select-size>

						<button class="button is-primary">Add to cart</button>

					</div>
				</div>
			</div>
		</div>
		<button class="modal-close is-large" aria-label="close" @click="$emit('closeModal')"></button>
	</div>
</template>

<script>

import SelectSize from './SelectSize.vue'

	export default {

		props: ['p'],

		data: function () {
			return {
			}
		},

		components: {
			'sm-select-size': SelectSize
		}
	}
</script>

<style lang="sass-loader" scoped>

.modal-content {
	background: #fff;
	@media print, screen and (min-width: 769px) {
		width: 940px;
	}
}

</style>