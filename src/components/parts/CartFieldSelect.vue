<template>

	<div v-if="show(post)" :class="className(field)">

		<div class="field" v-if="Object.keys(post.sell_media_pricing).length > 1">
			<label class="label">{{ field }} {{ labels.size }}</label>
			<div class="control">
				<div class="select">
					<select v-model="value" v-validate.initial="'required'" @change="activateButton">
						<option disabled value="">Choose</option>
						<option v-for="size in post.sell_media_pricing" :value="size.price">{{ size.name }} ({{ size.width }} by {{ size.height }} px)</option>
					</select>
				</div>
			</div>
		</div>
	
		<p class="total">{{ labels.price }}: {{ labels.currency_symbol }}{{ value || 0 }}</p>

	</div>

</template>

<script>

	export default {

		props: ['post', 'field'],

		data: function() {
			return {
				value: '',
				labels: {
					price: 'Price',
					size: sell_media.cart_labels.size,
					required: 'Please make a selection',
					currency_symbol: sell_media.currency_symbol,
				}
			}
		},

		methods: {
			show: function(post) {
				if ( post.sell_media_meta.reprints_sell === 'both' ||  post.sell_media_meta.reprints_sell === 'prints' ) {
					return true;
				}
			},

			className: function(field) {
				return field.toLowerCase().replace(/ /g, '-') + '-field';
			},

			activateButton: function() {
				this.$emit('selected', 1);
			}
		}
	}
</script>

<style lang="scss" scoped>

	.total {
		margin-bottom: 1rem;
	}

</style>