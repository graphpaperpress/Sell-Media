jQuery(document).ready(function($){

    /**
     * Set-up our default Ajax options.
     * Please reference http://api.jquery.com/jQuery.ajaxSetup/
     */
    $.ajaxSetup({
        type: "POST",
        url: sell_media.ajaxurl
    });

    // Sell Media popup dialog
    function popup(message){

        // get the screen height and width
        var maskHeight = $(document).height();
        var maskWidth = $(window).width();

        // calculate the values for center alignment
        var dialogLeft = (maskWidth/2) - ($('#sell-media-dialog-box').width()/2);

        // assign values to the overlay and dialog box
        $('#sell-media-dialog-overlay').css({height:maskHeight, width:maskWidth}).show();
        $('#sell-media-dialog-box').css({left:dialogLeft}).show();
            
    }

    /**
     * When the user clicks on our trigger we set-up the overlay,
     * launch our dialog, and send an Ajax request to load our cart form.
     */
    $(document).on('click','.sell-media-cart-trigger',function(){
        // calculate document size to center cart
        popup();
        // show the overlay and cart dialog
        $('#sell-media-dialog-overlay, #sell-media-dialog-box').show();
        // show a loading message while doing ajax request
        $('.sell-media-cart-dialog-target').html('<div class="sell-media-ajax-loader">Loading...</div>');
        // send ajax request for product in shopping cart
        $.ajax({
            data: {
                "action": "sell_media_load_template",
                "template": "cart.php",
                "product_id": $(this).attr('data-sell_media-product-id'),
                "attachment_id": $(this).attr('data-sell_media-thumb-id')
            },
            success: function(msg){
                $('.sell-media-cart-dialog-target').fadeIn().html(msg); // Give a smooth fade in effect
                if ($('#download #sell_media_size_select').length) {
                    $('#sell_media_license_select').attr('disabled', true);
                };
                if ($('#download #sell_media_size_select').length || $('#download #sell_media_license_select').length) {
                    $('.sell-media-button').attr('disabled', true);
                };
            }
        });

    });

    $(document).on('click','#sell-media-dialog-overlay, #sell-media-dialog-box .close',function(){
        // close the dialog if the overlay layer or the close button are clicked
        $('#sell-media-dialog-overlay, #sell-media-dialog-box').hide();
        return false;
    });

    // if user resizes the window, call the same function again
    // to make sure the overlay fills the screen and dialog box is aligned to center
    $(window).resize(function(){
        //only do it if the dialog box is not hidden
        if (!$('#sell-media-dialog-box').is(':hidden')) popup();
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

    // Cart config
    simpleCart({
        cartStyle: "table",
        checkout: {
            sandbox: sell_media.sandbox,
            type: "PayPal",
            email: sell_media.paypal_email,
            currency: sell_media.currency_symbol,
            success: sell_media.thanks_page,
            cancel: sell_media.checkout_page,
            notify: sell_media.listener_url,
            shipping: 0, // 0 prompt & optional, 1 no prompt, 2 prompt & required
            method: "POST"
        },
        cartColumns: [{
                view: "image",
                attr: "image",
                label: false
            },
            {
                view: function(item, column){

                    var name = item.get( "name" );
                    var license = item.get( "usage" );
                    var sep = ', ';
                    if ( license == undefined ) {
                        license = '';
                        sep = '';
                    }
                    var size = item.get( "size" );

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
            },
            {
                view: "remove",
                label: false,
                text: "Remove"
            }]
    });
    
    // Show cart if qty exists, otherwise, show empty message
    simpleCart.bind('load', function(){
        if ( simpleCart.quantity() ) {
            $('#sell-media-checkout-cart').show();
        } else {
            $('#sell-media-empty-cart-message').show();
        }
    });

    // Show added to cart message on dialog
    simpleCart.bind( 'afterAdd' , function( item ){
        $('.sell-media-added').remove();
        $('#sell-media-add-to-cart').after( '<p class="sell-media-added">' + sell_media.added_to_cart + '</p>' );
    });


    // Validate cart contents on the server
    // simpleCart.bind( 'beforeCheckout', function( data ){
    //     $.ajax({
    //         async: false,
    //         data: {
    //             security: $('#cart_nonce_security').val(),
    //             action: 'sell_media_verify_callback',
    //             cart: data
    //         },
    //         success: function( msg ){
    //             //if ( msg != undefined && msg.post != undefined ){
    //                 $.each( msg.cart, function( k, v ){
    //                     data[k] = v;
    //                 });
    //             //}
    //         },
    //         error: function () {
    //           alert('There was an error loading the cart data. Please contact the site owner.');
    //         }
    //     });
    // });

    // set license description in tooltip to selected license
    $(document).on('change', '#sell_media_item_license', function(){
        var license_desc = $('#sell_media_item_license :selected').attr('title');
        // must use .attr since .data types are cached by jQuery
        if(license_desc){
            $('#license_desc').attr('data-tooltip', license_desc).show();
        } else {
            $('#license_desc').hide();
        }
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

        if ( $('#sell_media_item_license :selected').data('name') ){
            var markup = $('#sell_media_item_license :selected').data('price');
            var license_name = $('#sell_media_item_license :selected').data('name');
        } else {
            var markup = $('#sell_media_item_license').data('price');
            var license_name = $('#sell_media_item_license').data('name');
        }

        // selected license doesn't have markup
        if ( markup == undefined || markup == 0 )
            sum = price;
        // selected license has markup
        else
            sum = ( + price + ( markup / 100 ) * price ).toFixed(2);

        $('#total').text(sum);

        // set license name for display on cart
        if ( license_name != null )
            $('.item_usage').attr('value', license_name);

        // set price_group id so it is passed to cart
        var price_group = $('#sell_media_item_size :selected').data('id');
        if ( price_group != null )
            $('.item_pgroup').attr('value', price_group);

        // set price_group id so it is passed to cart
        var size = $('#sell_media_item_size :selected').data('size');
        if ( size != null )
            $('.item_size').attr('value', size);
    });

});