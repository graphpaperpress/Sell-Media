<template>

	<div class="field" v-if="Object.keys(p.sell_media_pricing).length > 1">
		<label class="label">{{ size_label }}</label>
		<div class="control">
			<div class="select">
				<select>
					<option v-for="size in p.sell_media_pricing">{{ size.name }} ({{ size.width }} by {{ size.height }} px)</option>
				</select>
			</div>
		</div>
	</div>

</template>

<script>
	
	export default {

		props: ['p'],

		data: function() {
			return {

				size_label: sell_media.cart_labels.size,

			}
		}
	}

</script>