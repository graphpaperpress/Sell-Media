<template>

	<div class="field" v-if="Object.keys(post.sell_media_pricing).length > 1">
		<label class="label">{{ labels.size }}</label>
		<div class="control">
			<div class="select">
				<select v-model="form.size" v-validate.initial="'required'">
					<option v-for="size in post.sell_media_pricing">{{ size.name }} ({{ size.width }} by {{ size.height }} px)</option>
				</select>
			</div>
		</div>
	</div>

	<button class="button is-primary">{{ labels.add }}</button>

</template>

<script>

	export default {

		props: ['post'],

		data: function() {
			return {
				form: {
					type: '',
					size: '',
				},
				labels: {
					size: sell_media.cart_labels.size,
					add: sell_media.cart_labels.add_to_cart,
					required: 'Please make a selection',
				}
			}
		}
	}
</script>