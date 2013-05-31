/** */
jQuery( document ).ready(function( $ ){

    /**
     * Set-up our default Ajax options.
     * Please reference http://api.jquery.com/jQuery.ajaxSetup/
     */
    $.ajaxSetup({
        type: "POST",
        url: ajaxurl
    });


    /**
     * Our global user object
     */
    var _user = {
        "count": cart_count()
    };


    /**
     * Determine the price of an Item based on the below formula:
     *
     * amount = price + (( license_markup * .01 ) * price ))
     *
     */
    function calculate_total( license_markup, price ){

        if ( typeof( license_markup ) == "undefined" ) license_markup = 0;

        finalPrice = ( +price + ( +license_markup * .01 ) * price ).toFixed(2);

        if ( $('.subtotal-target').length ){
            $('.subtotal-target').html( finalPrice );
            $('.subtotal-target').val( finalPrice );
        }

        if ( $('.price-target').length ){
            $('.price-target').html( finalPrice );
            $('.price-target').val( finalPrice );
        }

        return finalPrice;
    }


    /**
     * Send an Ajax request to our function to update the users cart count
     */
    function cart_count(){
        $.ajax({
            data: "action=sell_media_count_cart",
            success: function( msg ){
                _user.count = msg;
                $('.count-target').html( msg );
            }
        });
    }


    /**
     * Calculate the total for each Item
     */
    function total_items(){
        var current = 0;
        var total = 0;

        if ( $('.item-price-target').length ){
            $( '.item-price-target' ).each(function( index ){
                current = ( +current ) + ( parseFloat( $(this).html() ) );
                total = ( +total ) + ( +current );
                final_total = current.toFixed(2);
            });
        } else {
            final_total = "0.00";
        }

        $( '.subtotal-target' ).html( final_total );
    }


    /**
     * Retrives the x, y coordinates of the viewport
     * getPageScroll() by quirksmode.com
     */
    function sell_media_get_page_scroll() {
        var xScroll, yScroll;
        if (self.pageYOffset) {
          yScroll = self.pageYOffset;
          xScroll = self.pageXOffset;
        } else if (document.documentElement && document.documentElement.scrollTop) {
          yScroll = document.documentElement.scrollTop;
          xScroll = document.documentElement.scrollLeft;
        } else if (document.body) {// all other Explorers
          yScroll = document.body.scrollTop;
          xScroll = document.body.scrollLeft;
        }
        return new Array(xScroll,yScroll)
    }


    /**
     * Calculate our total, round it to the nearst hundreds
     * and update the html our price target.
     */
    function sell_media_update_total(){
        var total = 0;
        $('.item-price-target').each(function(){
            total = +( $(this).text() ) + +total;
        });
        $('.subtotal-target').html( total.toFixed(2) );
        $('.price-target').html( total.toFixed(2) );
    }


    /**
     * Update our sub-total, if our sub-total is less than 0 we set
     * it to ''. Then update the html of our sub-total target.
     */
    function sell_media_update_sub_total(){
        $('.sell-media-quantity').each(function(){
            item_id = $(this).attr('data-id');

            if ( $(this).attr('data-markup') == null ){
                price = +$(this).attr('data-price');
            } else {
                price = calculate_total( $(this).attr('data-markup'), $(this).attr('data-price') );
            }
            qty = +$('#quantity-' + item_id ).val();

            sub_total = price * qty;

            if ( sub_total <= 0 )
                sub_total = 0;

            $( '#sub-total-target-' + item_id ).html( sub_total.toFixed(2) );
        });
    }

    /**
     * Add subtotal and shipping together
     */
    function sell_media_update_final_total(){
        total = +$('.subtotal-target').text() + +$('.shipping-target').text();
        $('.total-target').html( total.toFixed(2) );
    }


    /**
     * Run the following code below the DOM is ready update the cart count
     */
    sell_media_update_total();
    sell_media_update_final_total();

    /**
     * When the user clicks on our trigger we set-up the overlay,
     * launch our dialog, and send an Ajax request to load our cart form.
     */
    $( document ).on( 'click', '.sell-media-cart-trigger', function( event ){
        event.preventDefault();

        // Overlay set-up
        coordinates = sell_media_get_page_scroll();
        y = coordinates[1] + +100;
        x = ( $(window).width() / 2 ) - ( $( '.sell-media-cart-dialog' ).outerWidth() / 2 );
        $('.sell-media-cart-dialog').css({
            'top': y + 'px',
            'left': x + 'px'
        });


        // Show our dialog with a loading message
        $('.sell-media-cart-dialog').toggle();
        $( ".sell-media-cart-dialog-target" ).html( '<div class="sell-media-ajax-loader">Loading...</div>' );


        // Send Ajax request for Shopping Cart
        $.ajax({
            data: {
                "action": "sell_media_load_template",
                "template": "cart.php",
                "product_id": $( this ).attr( 'data-sell_media-product-id' ),
                "attachment_id": $( this ).attr( 'data-sell_media-thumb-id' )
            },
            success: function( msg ){
                $( ".sell-media-cart-dialog-target" ).fadeIn().html( msg ); // Give a smooth fade in effect
                cart_count();
            }
        });


        // Add our overlay to the html if #overlay is present.
        if($('#overlay').length > 0){
            $('#overlay').remove();
        } else {
            $('body').append('<div id="overlay"></div>');
            var doc_height = $(document).height();
            $('#overlay').height(doc_height);
            $('#overlay').click(function(){
                $('.sell-media-cart-dialog').toggle();
                $(this).remove();
            });
        }
    });

    $( document ).on( 'click', '.close', function(){
        $('.sell-media-cart-dialog').hide();
        $('#overlay').remove();
    });

    /**
     * On change run the calculate_total() function
     */
    $( document ).on('change', '#sell_media_license_select', function(){
        var price;
        var size = $('#sell_media_size_select :selected').attr('data-price');
        $("option:selected", this).each(function(){
            price = $(this).attr('data-price');
            calculate_total( price, size );
        });
        if ( price == 0 && size == 0 ){
            $('.sell-media-buy-button').attr('disabled', true);
        } else {
            $('.sell-media-buy-button').removeAttr('disabled');
        }
    });

    /**
     * On change run the calculate_total() function
     */
    $( document ).on('change', '#sell_media_size_select', function(){
        var size;

        if ( $('#sell_media_single_license_markup').length ){
            price = $('#sell_media_single_license_markup').val();
        } else {
            price = $('#sell_media_license_select :selected').attr('data-price');
        }

        $("option:selected", this).each(function(){
            size = $(this).attr('data-price');
            calculate_total( price, size );
        });
        if ( price == 0 && size == 0 ){
            $('.sell-media-buy-button').attr('disabled', true);
            $('#sell_media_license_select').attr('disabled', true);
        } else {
            $('.sell-media-buy-button').removeAttr('disabled');
            $('#sell_media_license_select').removeAttr('disabled');
        }
    });


    $( document ).on('submit', '.sell-media-dialog-form', function(){

        var _data = "action=sell_media_add_items&taxonomy=licenses&" + $( this ).serialize();

        if ( _user.count < 1 ) {
            text = '(<span class="count-container"><span class="count-target"></span></span>)';
            $('.empty').html( text );
            $('.cart-handle').show();
        }

        $.ajax({
            data: _data,
            success: function( msg ) {
                cart_count();
                $button = $('.sell-media-form').find('.sell-media-buy-button');
                $button.addClass('sell-media-purchased').val('Checkout');
                $( document ).on( 'click', '.sell-media-purchased', function(){
                    location.href = checkouturl;
                });
            }
        });
        return false;
    });


    $( document ).on('click', '.remove-item-handle', function(){

        $(this).closest('tr').remove();

        count = $(".sell_media-product-list li").size();

        if( count == 1 ) {
            $('.subtotal-target').html('0');
            $('.sell-media-buy-button-checkout').fadeOut();
        }

        data = {
            action: "sell_media_remove_item",
            item_id: $(this).attr('data-item_id')
        };

        $.ajax({
            data: data,
            success: function( msg ){
                // We have no items in the cart
                if ( msg ){
                    $('#sell-media-checkout').html( msg );
                }

                total_items();
                sell_media_update_final_total();
            }
        });
    });

    $( document ).on('click', '.cart-handle', function(){
        $.ajax({
            data: "action=sell_media_show_cart",
            success: function( msg ){
                $('.product-target-tmp').fadeOut();
                $('.cart-target-tmp').fadeIn().html( msg );
                total_items();
            }
        });
    });

    $( document ).on('click', '#checkout_handle', function(){
        $('.cart-container').hide();
        $('.payment-form-container').show();
    });

    $('.sell-media-advanced-search-fields').toggle();

    $('.sell-media-advanced-search').click(function(){
        $('.sell-media-advanced-search-fields').slideToggle();
    });

    $("#sell-media-checkout table tr:nth-child(odd)").addClass("odd-row");
    $("#sell-media-checkout table td:first-child, #sell-media-checkout table th:first-child").addClass("first");
    $("#sell-media-checkout table td:last-child, #sell-media-checkout table th:last-child").addClass("last");


    /**
     * If the user clicks inside of our input box and manually updates the quantiy
     * we run the sub-total and total functions.
     */
    $(document).on('change', '.sell-media-quantity', function(){
        sell_media_update_sub_total();
        sell_media_update_total();
        sell_media_update_final_total();
        if ( $(this).val() > 0 ){
            $('.sell-media-buy-button').removeAttr('disabled');
        } else {
            $('.sell-media-buy-button').attr('disabled', true);
        }
    });


    if ( $('#sell_media_checkout_form').length ){
        sell_media_update_sub_total();
        sell_media_update_total();
        sell_media_update_final_total();
    }
});