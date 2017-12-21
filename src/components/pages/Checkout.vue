<template>

	<div>
		<table class="table is-fullwidth">
			<thead>
				<tr>
					<th>PRODUCT</th>
					<th>SIZE</th>
					<th>QTY</th>
					<th>PRICE</th>
					<th>SUBTOTAL</th>
				</tr>
			</thead>
			<tbody>
				<tr v-for="product in cart">
					<td>
						<img :src="product.img" />
						<p>{{ product.title }}</p>
					</td>
					<td>{{ product.price_name }} - {{ product.price_desc }}</td>
					<td>
						<input
						v-model.number="qty"
						class="input"
						type="number">
					</td>
					<td>{{ currency_symbol }}{{ product.price }}</td>
					<td>{{ currency_symbol }}{{ product.price * qty }}</td>
				</tr>
			</tbody>
		</table>
	</div>

</template>

<script>

	export default {

		data: function() {
			return {
				cart: this.$store.state.cart,
				qty: 1,
				currency_symbol: sell_media.currency_symbol
			}
		}
	}

</script>

<style lang="scss" scoped>
	
	table thead {
		font-size: .8rem;
	}

	table th,
	table td {
		width: 15%;

		&:nth-child(1) {
			width: 40%;
		}

		img {
			max-width: 75px;
			height: auto;
		}
	}

</style>
