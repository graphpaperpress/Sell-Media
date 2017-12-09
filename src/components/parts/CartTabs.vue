<template>
    
    <tabs>
        <tab name="Prints" :selected="true">
            <cart-field-print-size :key="post.slug" :post="post"></cart-field-print-size>
            <button class="button is-primary">{{ labels.add }}</button>
        </tab>

        <tab name="Downloads">
            <cart-field-download-size :key="post.slug" :post="post"></cart-field-download-size>
            <button class="button is-primary">{{ labels.add }}</button>
        </tab>
    </tabs>

</template>

<script>

    import CartFieldPrintSize from './CartFieldPrintSize.vue'
    import CartFieldDownloadSize from './CartFieldDownloadSize.vue'

    export default {

        props: ['post'],

        data: function() {
            return {

                labels: {
                    add: sell_media.cart_labels.add_to_cart,
                }

            }
        },

        components: {
            'cart-field-print-size': CartFieldPrintSize,
            'cart-field-download-size': CartFieldDownloadSize
        }
    }

</script>