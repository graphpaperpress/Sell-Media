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
                if ($('#download #sell_media_size_select').length) {
                    $('#sell_media_license_select').attr('disabled', true);
                }
                if ($('#download #sell_media_size_select').length || $('#download #sell_media_license_select').length) {
                    $('.sell-media-button').attr('disabled', true);
                }
            }
        });

    });

    /**
     * When the user clicks on our trigger we set-up the overlay,
     * launch our dialogto load the terms of service.
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
     * Hide our current search option when the user clicks off the input field
     */
    $(document).on('blur', '#s', function(){
        $('.sell-media-search-options', this).hide();
    });

    $(document).on('click', '.sell-media-search-options-trigger', function(e){
        e.preventDefault();
        $(this).closest('.sell-media-search-form').find('.sell-media-search-options:first').toggle();
     });

    $(document).on('change', '.post_type_selector', function(){

        /**
         * Cache the objects for later use.
         */
        $collection = $('#collection_select');
        $keywords = $('#keywords_select');

        /**
         * We store the field name as an attribute since will toggle it later.
         * For our purposes its easier to just remove the name attribute so it
         * isn't sent to PHP in $_POST
         */
        if ( $('.sell-media-search-taxonomies').css('display') == 'block' ){
            $('.sell-media-search-taxonomies').hide();

            $collection.attr('name','');
            $keywords.attr('name','');
        } else {
            $('.sell-media-search-taxonomies').show();

            $collection.attr('name', $collection.attr('data-name'));
            $keywords.attr('name', $keywords.attr('data-name'));
        }
     });

    $('#sell_media_terms_cb').on('click', function(){
        $this = $(this);
        $this.val() == 'checked' ? $this.val('') : $this.val('checked');
    });

    // console.log(sell_media);

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
            method: "POST"
        },
        cartStyle: sell_media.cart_style,
        taxRate: parseFloat(sell_media.tax),
        currency: sell_media.currency_symbol,
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

    // Show cart if qty exists, otherwise, show empty message
    sellMediaCart.bind('ready', function(){

        $('#sell-media-checkout-cart').after('<div class="sell-media-load-checkout">Loading...</div>').fadeIn('fast');

        if ( sellMediaCart.quantity() ) {
            $('#sell-media-checkout-cart').show();
        } else {
            $('#sell-media-checkout-cart').hide();
            $('#sell-media-empty-cart-message').show();
        }

        $('.sell-media-load-checkout').delay(200).fadeOut('slow');
    });

    // Show added to cart message on dialog
    sellMediaCart.bind( 'afterAdd' , function( item ){
        $('.sell-media-added').remove();
        $('#sell-media-add-to-cart').after( '<p class="sell-media-added">' + sell_media.added_to_cart + '</p>' );
    });


    // Validate cart prices (price group, license markup, discount codes) on the server
    sellMediaCart.bind( 'beforeCheckout', function( data ){
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
                $.each( msg.cart, function( k, v ){
                    data[k] = v;
                });
            },
            error: function () {
                alert( sell_media.cart_error );
            }
        });
    });

    // set license description in tooltip to selected license
    $(document).on('change', '#sell_media_item_license', function(){

    });

    $(document).on('change', '#sell_media_item_size, #sell_media_item_license', function(){

        // disable add to cart button unless price selected
        if( $('#sell_media_item_size').val() ){
            $('#sell_media_item_license').prop('disabled', false);
        } else {
            $('.item_add').prop('disabled', true);
        }

        if( $('#sell_media_item_license').val() && $('#sell_media_item_size').val() ) {
            $('.item_add').prop('disabled', false);
        } else {
            $('.item_add').prop('disabled', true);
        }

        if ( $('#sell_media_item_license').length == 0 || $('div#sell_media_item_license').length == 1){
            $('.item_add').prop('disabled', false);
        }

        // calculate the price and license markup
        var price = $('#sell_media_item_size :selected').data('price');

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

        // set price_group id so it is passed to cart
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

});