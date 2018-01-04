<template>

	<div v-if="field == active" :class="className(field)">

		<div class="field" v-if="getSizes(post,field)">
			<label class="label">{{ field }} {{ labels.size }}</label>
			<div class="control">
				<div class="select">
					<select id="sizes" v-model="selected" @change="$emit('selected', selected)">
						<option disabled :value="{}">{{ labels.choose }}</option>
						<option v-for="size in getSizes(post,field)" :value="size">{{ size.name }} ({{ size.description }})</option>
					</select>
				</div>
			</div>
		</div>
	
		<p class="total">{{ labels.price }}: {{ labels.currency_symbol }}
			<span v-if="selected.price">{{ selected.price }}</span>
			<span v-else>0</span>
		</p>

	</div>

</template>

<script>

	export default {

		props: ['post', 'field', 'active'],

		data: function() {
			return {
				selected: {},
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

			getSizes: function(post,field) {
				let taxonomy = field.toLowerCase()
				if ( post['sell_media_pricing'][taxonomy] ) {
					return post['sell_media_pricing'][taxonomy]
				} else {
					this.$emit('selected', true)
				}
			}
		}
	}
</script>

<style lang="scss" scoped>

	.total {
		margin-bottom: 1rem;
	}

</style>