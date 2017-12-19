<template>

	<div v-if="field == active" :class="className(field)">

		<div class="field" v-if="getPricelist(post,field)">
			<label class="label">{{ field }} {{ labels.size }}</label>
			<div class="control">
				<div class="select">
					<select v-model="value" v-validate.initial="'required'" @change="change(value)">
						<option disabled value="">{{ labels.choose }}</option>
						<option v-for="price in getPricelist(post,field)" :value="price.id">{{ price.name }} ({{ price.description }})</option>
					</select>
				</div>
			</div>
		</div>
	
		<p class="total">{{ labels.price }}: {{ labels.currency_symbol }}{{ value || 0 }}</p>

	</div>

</template>

<script>

	export default {

		props: ['post', 'field', 'active'],

		data: function() {
			return {
				value: '',
				labels: {
					price: sell_media.cart_labels.price,
					choose: sell_media.cart_labels.choose,
					size: sell_media.cart_labels.size,
					required: 'Please make a selection',
					currency_symbol: sell_media.currency_symbol,
				}
			}
		},

		methods: {

			className: function(field) {
				return field.toLowerCase().replace(/ /g, '-') + '-field';
			},

			getPricelist: function(post,field) {
				let list = field.toLowerCase();
				if ( post['sell_media_pricing'][list] ) {
					return post['sell_media_pricing'][list];
				} else {
					this.$emit('selected', true);
				}
			},

			change: function(value) {
				this.$emit('selected', value);
				//console.log(value);
			}
		}
	}
</script>

<style lang="scss" scoped>

	.total {
		margin-bottom: 1rem;
	}

</style>