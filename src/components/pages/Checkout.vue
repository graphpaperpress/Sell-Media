<template>

	<div>
		<table class="table is-fullwidth">
			<thead>
				<tr>
					<th class="has-text-left">PRODUCT</th>
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
					<td>{{ product.price_id }}</td>
					<td>
						<input
						v-model.number="qty"
						class="input"
						type="number"
						:value="product.qty">
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
				counter: 1,
				currency_symbol: sell_media.currency_symbol
			}
		}
	}

</script>
