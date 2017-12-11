<template>

	<section class="form">

		<form @submit.prevent="validateForm">

			<!-- Add check if subscription -->

			<template v-if="showFields(post)">

				<div class="tabs">
					<ul>
						<li v-for="field in fields" :class="{ 'is-active': (active == field) }">
							<a href="javascript:void(0);" @click="activeField(field)">{{ field }}</a>
						</li>
					</ul>
				</div>

			</template>

			<div class="content">

				<cart-field-select v-for="field in fields" v-if="active == field" :key="field" :post="post" :field="field"></cart-field-select>

				<button class="button is-black">{{ add }}</button>

			</div>

		</form>
	
	</section>

</template>

<script>

	import CartFieldSelect from './CartFieldSelect.vue'

	export default {

		props: ['post'],

		data: function() {
			
			return {
				fields: ['Prints', 'Downloads'],
				active: 'Prints',
				total: 0,
				size: '',
				currency_symbol: sell_media.currency_symbol,
				add: sell_media.cart_labels.add_to_cart,
			}
		},

		methods: {
			showFields: function(post) {
				if ( post.sell_media_meta.reprints_sell === 'both' ) {
					return true;
				}
			},

			activeField(field) {
				this.active = field
			},

			validateForm() {

			}
		},

		components: {
			'cart-field-select': CartFieldSelect
		}

	}
</script>