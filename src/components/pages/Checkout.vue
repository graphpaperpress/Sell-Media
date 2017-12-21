<template>

	<div class="checkout">

		<p v-if="!products.length">
			{{ labels.empty }}.
			<router-link :to="{ name: 'archive' }">{{ labels.visit }} &raquo;</router-link>
		</p>
		<table v-else class="table is-fullwidth is-mobile">
			<thead>
				<tr>
					<th>{{ labels.product }}</th>
					<th>{{ labels.description }}</th>
					<th>{{ labels.qty }}</th>
					<th>{{ labels.price }}</th>
					<th>{{ labels.sub_total }}</th>
				</tr>
			</thead>
			<tbody>
				<tr v-for="(product, index) in products" :key="index">
					<td>
						<img :src="product.img" />
						<p>{{ product.title }}</p>
					</td>
					<td>{{ product.price_name }} - {{ product.price_desc }}</td>
					<td>
						<input
						v-model.number="product.qty"
						class="input"
						type="number"
						step="1"
						min="1"
						@change="updateQuantity(product)">
					</td>
					<td>{{ currency_symbol }}{{ product.price }}</td>
					<td>
						{{ currency_symbol }}{{ product.price * product.qty }}
						<button class="delete is-small" @click="removeProduct(product)"></button>
					</td>
				</tr>
			</tbody>
			<tfoot>
				<tr>
					<td colspan="5">{{ labels.sub_total }}: {{ currency_symbol }}{{ subtotal }}</td>
				</tr>
				<tr>
					<td colspan="5">{{ labels.tax }}: {{ currency_symbol }}{{}}</td>
				</tr>
				<tr>
					<td colspan="5">{{ labels.shipping }}: {{ currency_symbol }}{{}}</td>
				</tr>
				<tr>
					<td colspan="5"><strong>{{ labels.total }}: {{ currency_symbol }}{{}}</strong></td>
				</tr>
			</tfoot>
		</table>
	</div>

</template>

<script>

	export default {

		data: function() {
			return {
				products: this.$store.state.cart,
				currency_symbol: sell_media.currency_symbol,
				labels: sell_media.cart_labels,
				// subtotal: null,
			}
		},

		methods: {

			updateQuantity: function(product) {
				this.$store.commit( 'updateQuantity', product );
			},

			removeProduct: function(product) {
				this.$store.commit( 'removeFromCart', product );
			}

		},

		computed: {
			subtotal: function(){
				return this.products.reduce(function(subtotal, product){
					return subtotal + Number(product.price) * product.qty
				},0);
			}
		}


	}

</script>

<style lang="scss" scoped>
	
	table thead,
	table td {
		font-size: .8rem;
	}

	table td img {
		max-width: 75px;
		height: auto;
	}

	table th:last-child,
	table td:last-child {
		text-align: right;
	}

	table th,
	table td {
		width: 10%;

		&:nth-child(1),
		&:nth-child(2) {
			width: 35%;
		}
	}

</style>
