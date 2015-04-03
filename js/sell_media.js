jQuery(document).ready(function($){

    // Sell Media popup dialog
    function popup(message){

        // get the screen height and width
        var maskHeight = $(document).height();
        var maskWidth = $(window).width();

        // calculate the values for center alignment and position from top
        var dialogLeft = (maskWidth/2) - ($('.sell-media-dialog-box').width()/2);
        var dialogTop = $(document).scrollTop() + 100;

        // assign values to the overlay and dialog box and show overlay and dialog
        $('.sell-media-dialog-overlay').css({height:maskHeight, width:maskWidth}).show();
        $('.sell-media-dialog-box').css({left:dialogLeft, top:dialogTop}).show();
        $('.sell-media-dialog-overlay, .sell-media-dialog-box').show();

    }

    /**
     * When the user clicks on our trigger we set-up the overlay,
     * launch our dialog, and send an Ajax request to load our cart form.
     */
    $(document).on('click','.sell-media-cart-trigger',function(){
        popup();
        // show a loading message while doing ajax request
        $('#sell-media-dialog-box-target').html('<div class="sell-media-ajax-loader">Loading...</div>');
        // send ajax request for product in shopping cart
        $.ajax({
            type: "POST",
            url: sell_media.ajaxurl,
            data: {
                "action": "sell_media_load_template",
                "template": "cart.php",
                "product_id": $(this).attr('data-sell_media-product-id'),
                "attachment_id": $(this).attr('data-sell_media-thumb-id')
            },
            success: function(msg){
                $('#sell-media-dialog-box-target').fadeIn().html(msg);
                required_fields();

            }
        });

    });

    /**
     * Check the required fields and change state of add to cart button
     */
    function required_fields(){
        // if size, license, or type (print/download) fields exists, disable add button
        if ( $('#sell_media_download_size_fieldset').length || $('#sell_media_download_license_fieldset').length || $('#sell_media_product_type').length ) {
            $('.item_add').prop('disabled', true);
        } else {
            $('.item_add').prop('disabled', false);
        }

        var required = $('#sell-media-dialog-box [required]');
        // bind change for all your just click and keyup for all text fields
        required.bind('change keyup', function() {
            var flag = 0;
            // check every el in collection
            required.each(function() {
                if ( $(this).val() != '' ) flag++;
            });
            // number of nonempty (nonchecked) fields == nubmer of required fields
            if ( flag == required.length )
                $('.item_add').prop('disabled',false);
            else
                $('.item_add').prop('disabled', true);
        });
    }

    /**
     * When the user clicks on our trigger we set-up the overlay,
     * launch our dialog to load the terms of service.
     */
    $(document).on('click','.sell-media-empty-dialog-trigger',function(){
        popup();
    });

    $(document).on('click','.sell-media-dialog-overlay, .sell-media-dialog-box .close',function(){
        // close the dialog if the overlay layer or the close button are clicked
        $('.sell-media-dialog-overlay, .sell-media-dialog-box').hide();
        return false;
    });

    // if user resizes the window, call the same function again
    // to make sure the overlay fills the screen and dialog box is aligned to center
    $(window).resize(function(){
        //only do it if the dialog box is not hidden
        if (!$('.sell-media-dialog-box').is(':hidden')) popup();
    });

    $(document).on('click', '.sellMediaCart_checkout', function(){
        $(this).prop('disabled', true).css({"cursor": "progress"}).text(sell_media.checkout_wait_text);
    });

    /**
     * Show search options when user clicks inside the search field
     */
    $('.sell-media-search-query').on('click', function(){
        $('.sell-media-search-hidden, .sell-media-search-close').show();
        $('.sell-media-search-form').addClass('active');
    });

    /**
     * Hide search options when user clicks close
     */
    $('.sell-media-search-close').on('click', function(){
        $('.sell-media-search-hidden, .sell-media-search-close').hide();
        $('.sell-media-search-form').removeClass('active');
    });

    $('#sell_media_terms_cb').on('click', function(){
        $this = $(this);
        $this.val() == 'checked' ? $this.val('') : $this.val('checked');
    });

    // Cart config
    sellMediaCart({
        checkout: {
            type: "PayPal",
            sandbox: sell_media.sandbox,
            email: sell_media.paypal_email,
            success: sell_media.thanks_page,
            cancel: sell_media.checkout_page,
            notify: sell_media.listener_url,
            shipping: sell_media.shipping, // 0 prompt & optional, 1 no prompt, 2 prompt & required
            method: "POST",
            site: sell_media.site_name
        },
        cartStyle: sell_media.cart_style,
        taxRate: parseFloat(sell_media.tax),
        currency: sell_media.currency_symbol,
        shippingCustom: function(){
            var items = JSON.parse(localStorage.getItem("sellMediaCart_items"));
            var shipping_cost = false;
            sellMediaCart.each( items, function (item) {
                if( "print" == item.type ) {
                    shipping_cost = true;
                }
            });
            return shipping_cost;
        },
        cartColumns: [{
            view: "image",
            attr: "image",
            label: false
        },
            {
                view: function(item, column){

                    var name = item.get( "name" );
                    var sep = ', ';

                    var license = item.get( "usage" );
                    if ( license == undefined ) {
                        license = '';
                        sep = '';
                    }

                    var size = item.get( "size" );
                    if ( size == undefined ) {
                        size = '';
                        sep = '';
                    }

                    return name + "<span class='size-license'>" + size + sep + license + "</span>";
                },
                attr: "custom",
                label: sell_media.cart_labels.name
            },
            {
                attr: "price",
                label: sell_media.cart_labels.price,
                view: "currency"
            },
            {
                view: "decrement",
                label: false,
                text: "-"
            },
            {
                attr: "quantity",
                label: sell_media.cart_labels.qty
            },
            {
                view: "increment",
                label: false,
                text: "+"
            },
            {
                attr: "total",
                label: sell_media.cart_labels.sub_total,
                view: "currency"
            }]
    });

    // Hide cart if no items, otherwise, show the cart
    sellMediaCart.bind('update', function(){

        if ( sellMediaCart.quantity() === 0 ){
            // hide the cart
            $('#sell-media-checkout-cart').hide();
            $('#sell-media-empty-cart-message').show();
        } else {
            // show the cart
            $('#sell-media-checkout-cart').show();
        }

    });

    // Show added to cart message on dialog
    sellMediaCart.bind( 'afterAdd' , function( item ){
        $('.sell-media-added').remove();
        $('#sell-media-add-to-cart').after( '<p class="sell-media-added">' + sell_media.added_to_cart + '</p>' );
    });

    // Validate cart prices (price group, license markup, discount codes) on the server
    sellMediaCart.bind( 'beforeCheckout', function( data ){

        // pass discount codes into cart data
        if ( $('#discount-id').length ) {
            data.custom = $('#discount-id').val();
        }

        // ajax callback to vertify prices
        $.ajax({
            type: "POST",
            url: sell_media.ajaxurl,
            async: false,
            data: {
                security: $('#cart_nonce_security').val(),
                action: 'sell_media_verify_callback',
                cart: data
            },
            success: function( msg ){
                console.log(data);
                $.each( msg.cart, function( k, v ){
                    data[k] = v;
                });
            },
            error: function () {
                alert( sell_media.cart_error );
            }
        });
    });

    $(document).on('change', '#sell_media_item_size, #sell_media_item_license', function(){

        // get the price from the selected option
        var price = $('#sell_media_item_size :selected').data('price');
        // if the price doesn't exist, set the price to the total shown
        // either the custom price of the item or the default price from settings
        if ( price == undefined || price == 0 )
            price = $('#sell_media_item_base_price').val();

        // check for selected license or single license
        if ( $('#sell_media_item_license :selected').data('name') ){
            var markup = $('#sell_media_item_license :selected').data('price');
            var license_name = $('#sell_media_item_license :selected').data('name');
            var license_id = $('#sell_media_item_license :selected').val();
        } else {
            var markup = $('#sell_media_item_license').data('price');
            var license_name = $('#sell_media_item_license').data('name');
            var license_id = $('#sell_media_item_license').data('id');
        }

        // selected license doesn't have markup
        if ( markup == undefined || markup == 0 )
            sum = price;
        // selected license has markup
        else
            sum = ( + price + ( markup / 100 ) * price ).toFixed(2);

        $('#total').text(sum);

        // set price_group id so it is passed to cart
        var price_group = $('#sell_media_item_size :selected').data('id');
        if ( price_group != null )
            $('.item_pgroup').attr('value', price_group);

        // set item_size so it is passed to cart
        var size = $('#sell_media_item_size :selected').data('size');
        if ( size != null )
            $('.item_size').attr('value', size);

        // set license name for display on cart
        if ( license_name != null )
            $('.item_usage').attr('value', license_name);

        // set license id
        if ( license_id != null )
            $('.item_license').attr('value', license_id);

        // set the license description
        var license_desc = $('#sell_media_item_license :selected').attr('title');
        // must use .attr since .data types are cached by jQuery
        if(license_desc){
            $('#license_desc').attr('data-tooltip', license_desc).show();
        } else {
            $('#license_desc').hide();
        }

    });

    // Add to lightbox on click
    $( '.add-to-lightbox' ).on( 'click', function( e ) {

        var id = $(this).data('id'),
            selector = $('#sell-media-lightbox-content #sell-media-' + id);

        var data = {
            action: 'sell_media_update_lightbox',
            id: id
        };

        $.ajax({
            type: 'POST',
            url: sell_media.ajaxurl,
            data: data,
            success: function(msg){
                $('.lightbox-counter').text(msg.count);
                $('#lightbox-' + id).text(msg.text);
                $(selector).hide();
            }
        });
    });

    // Count lightbox
    function count_lightbox() {
        var cookie = $.cookie('sell_media_lightbox');
        if ( cookie == undefined ) {
            return 0;
        } else {
            var data = $.parseJSON( cookie ),
                keys = [];
            $.each(data, function(key, value) {
                keys.push(key);
            });
            return keys.length;
        }
    }

    // Lightbox menu
    $('<span class="lightbox-counter">' + count_lightbox() + '</span>').appendTo('.lightbox-menu a');

    // Checkout total menu
    $('(<span class="sellMediaCart_total checkout-price">0</span>)').appendTo('.checkout-total a');

    // Checkout qty menu
    $('(<span class="sellMediaCart_quantity checkout-counter">0</span>)').appendTo('.checkout-qty a');

});
