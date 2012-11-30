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
     * Determine the price of an Item based on License Type. Once
     * determined, update the needed dom elements with the new price
     *
     * Formula: price + (( percent * .01 ) * price ))
     */
    function calculate_price(){

        /**
         * If this item has NO license selected we default
         * markup to 0.
         */
        if ( $('#sell_media_license_select option:selected').length ){
            markUp = $('#sell_media_license_select option:selected').attr('data-price');
        } else {
            markUp = 0;
        }

        price = $('#sell_media_size_select').attr('value');
        data_price = $('#sell_media_single_price').attr('data-price');

        if ( markUp != undefined && price != undefined ) {
            finalPrice = +price + ( ( +markUp * .01 ) * price );
            finalPrice = finalPrice.toFixed(2);
            $('#price_target').html( finalPrice );
            $('.price-target').val( finalPrice );
        } else {
            $('#price_target').html( data_price );
            $('.price-target').val( data_price );
        }
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

                $( this ).next('.item-total-target').html( final_total );

                $( '.price-target' ).html( final_total );
            });
        } else {
            $( '.price-target' ).html( "0.00" );
        }
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
     * Run the following code below the DOM is ready update the cart count
     */
    cart_count();
    $('.price-target').html(total_items());


    /**
     * When the user clicks on our trigger we set-up the overlay,
     * launch our dialog, and send an Ajax request to load our cart form.
     */
    $('.sell-media-cart-trigger').click(function( event ){

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
                calculate_price();
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


    $('.close').live('click', function(){
        $('.sell-media-cart-dialog').hide();
        $('#overlay').remove();
    });


    /**
     * On change run the calculate_price() function
     */
    $('#sell_media_license_select, #sell_media_size_select').live('change', function(){
        $("option:selected", this).each(function(){
            calculate_price();
        });
    });

    $('#sell_media_cart_form').live('submit', function(){

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
            }
        });
        return false;
    });

    $('.remove-item-handle').live('click', function(){

        $(this).closest('tr').hide();

        count = $(".sell_media-product-list li").size();

        if( count == 1 ) {
            $('.price-target').html('0');
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

                total_items()
            }
        });
    });

    $('.cart-handle').live('click', function(){
        $.ajax({
            data: "action=sell_media_show_cart",
            success: function( msg ){
                $('.product-target-tmp').fadeOut();
                $('.cart-target-tmp').fadeIn().html( msg );
                total_items();
            }
        });
    });

    $('#checkout_handle').live('click', function(){
        $('.cart-container').hide();
        $('.payment-form-container').show();
    });

    $('.sell-media-advanced-search-fields').toggle();

    $('.sell-media-advanced-search').click(function(){
        $('.sell-media-advanced-search-fields').slideToggle();
    });

    $('#sell_media_checkout_form').on('submit', function( event ){
        if ($('#sell_media_first_name_field').val().length==0){
            $('#firstname-error').show();
            event.preventDefault();
        }

        if ($('#sell_media_last_name_field').val().length==0){
            $('#lastname-error').show();
            event.preventDefault();
        }

        var email = $('#sell_media_email_field').val();

        if ( /(.+)@(.+){2,}\.(.+){2,}/.test(email) ){
          // valid email
        } else {
          $('#email-error').show();
          event.preventDefault();
        }

        $('.sell-media-buy-button-checkout').val( 'Loading...' );
        //event.preventDefault();

        // Validation
    });
});