jQuery(document).ready(function($){

    function createPayment( data ){
        console.log( data );
        $.ajax({
            url: sell_media.ajaxurl,
            data: {
                'action': 'sell_media_ajax_create_payment',
                'cart_data' : data
            },
            success:function( data ) {
                // This outputs the result of the ajax request
                console.log( data );
            },
            error: function( errorThrown ){
                console.log( errorThrown );
            }
        });
    }

    simpleCart({
        cartStyle: "table",
        checkout: {
            sandbox: sell_media.sandbox,
            type: "PayPal",
            email: sell_media.paypal_email
        },
        cartColumns: [
            { view: "image", attr: "image", label: false },
            { attr: "name", label: "Name" },
            { attr: "size", label: "Size" },
            { attr: "usage", label: "Usage License" },
            { attr: "price", label: "Price", view: "currency" },
            { view: "decrement", label: false, text: "-" },
            { attr: "quantity", label: "Qty" },
            { view: "increment", label: false, text: "+" },
            { attr: "total", label: "SubTotal", view: "currency" },
            { view: "remove", text: "Remove", label: false }
        ],
        currency: sell_media.cart.currency_symbol,
        success: sell_media.thanks_page,
        cancel: sell_media.checkout_page,
        notify: sell_media.listener_url,
        shipping: 0 // 0 prompt & optional, 1 no prompt, 2 prompt & required

    });

    // callback beforeCheckout
    simpleCart.bind( 'beforeCheckout' , function( data ){
        // validate items and price sent to cart
        // optionally create new draft post (getting rid of this)
        // createPayment(data);
        console.log(data);
        //exit();
    });

    simpleCart.bind( 'afterAdd' , function( item ){
        $('.sell-media-added').remove();
        $('#sell-media-add-to-cart').after( '<p class="sell-media-added small">' + item.get('name') + sell_media.added_to_cart + '</p>' );
    });

    $(document).on('change', '#sell_media_item_size, #sell_media_item_license', function(){
        console.log('change');
        // disable add to cart button unless price selected
        if( $('#sell_media_item_size').val() )
            $('.item_add, #sell_media_item_license').prop('disabled', false);
        else
            $('.item_add, #sell_media_item_license').prop('disabled', true);

        // calculate the price and license markup
        var price = $('#sell_media_item_size :selected').data('price');
        var markup = $('#sell_media_item_license :selected').data('price');
        var markup_single = $('#sell_media_item_license').data('price');
        // selected license doesn't have markup
        if ( markup == undefined || markup == 0 )
            sum = price;
        // selected license has markup
        else
            sum = ( price * ( markup / 100 ) ).toFixed(2);

        $('#total').text(sum);

        // set license name for display on cart
        var license_name = $('#sell_media_item_license :selected').data('name');
        if ( license_name != null )
            $('.item_usage').text(license_name);

        // set price_group id so it is passed to cart
        var price_group = $('#sell_media_item_size :selected').data('id');
        if ( price_group != null )
            $('.item_pgroup').text(price_group);

    });


});