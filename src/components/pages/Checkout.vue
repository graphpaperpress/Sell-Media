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
              <td>Image</td>
              <td>{{ cart.post_id }}</td>
              <td>{{ cart.attachment_id }}</td>
              <td>1</td>
              <td>{{ cart.price_id }}</td>
            </tr>
          </tbody>
        </table>
    </div>

</template>

<script>

    export default {

        data: function(){
            return {
                carts: this.$store.state.cart,
            }
        }
    }

</script>
