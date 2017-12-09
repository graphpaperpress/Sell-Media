<template>

	<section class="form">

		<form @submit.prevent="validateForm">

			<!-- Add check if subscription -->

			<template v-if="post.sell_media_meta.reprints_sell === 'both'">

				<cart-tabs :key="post.slug" :post="post"></cart-tabs>

			</template>

			<template v-else>

				<cart-field-download-size :key="post.slug" :post="post"></cart-field-download-size>

			</template>

		</form>
	
	</section>

</template>

<script>

	import CartTabs from './CartTabs.vue'
	import CartFieldDownloadSize from './CartFieldDownloadSize.vue'

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
					type: 'Select a product type',
				}

			}
		},

		methods: {
			validateForm() {

			}
		},

		components: {
			'cart-tabs': CartTabs,
			'cart-field-download-size': CartFieldDownloadSize
		}

	}
</script>