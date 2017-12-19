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
        methods: {},
        data: function(){
            return {
                cartItems: {},
            }
        },

        created: function() {
            let cart = this.$cookie.get('sell_media_cart');
            this.cartItems = JSON.parse( cart );
            console.log( this.cartItems );
        },

        components: {}
    }

</script>
