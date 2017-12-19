<template>

    <div>
        <span>Checkout ({{ cartItems }})</span>
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
            <tr v-for="cartItem in cartItems">
              <td>Three</td>
              <td>Four</td>
              <td>Three</td>
              <td>Four</td>
              <td>Four</td>
            </tr>
          </tbody>
        </table>
    </div>

</template>

<script>

    export default {

        data: function(){
            return {
                cartItems: this.$store.state.cart,
            }
        },

        computed: {
            itemsInCart: function () {
                this.cartItems = JSON.parse(localStorage.getItem('sell_media_cart'));
                return this.cartItems;
                console.log( this.cartItems );
            }
        },

        components: {}
    }

</script>
