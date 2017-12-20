<template>

    <div>
        <table class="table is-fullwidth">
          <thead>
            <tr>
              <th></th>
              <th>NAME</th>
              <th>SIZE</th>
              <th>QTY</th>
              <th>PRICE</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="cart in carts">
              <td><a href=""><img v-bind:src="cart.featured_image"/></a></td>
              <td>{{cart.title}}</td>
              <td>{{cart.size}}</td>
              <td>{{cart.qty}}</td>
              <td>{{cart.currency_symbol}}{{cart.price }}</td>
            </tr>
          </tbody>
        </table>
    </div>

</template>

<script>

    export default {

        data: function(){
            console.log(this.$store.state.cart);
            return {
                carts: this.$store.state.cart,
            }
        }
    }

</script>
