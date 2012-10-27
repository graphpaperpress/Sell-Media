jQuery( document ).ready(function( $ ){

    $.ajaxSetup({
        type: "POST",
        url: ajaxurl
    });

    var _user = {
        "count": cart_count()
    };

    /**
     * Calculate the price using the formular: price + (( percent * .01 ) * price ))
     */
    function calculate_price(){

        markUp = $('#sell_media_license_select option:selected').attr('data-price');
        price = $('#sell_media_size_select').attr('value');

        if ( markUp != undefined && price != undefined ) {
            finalPrice = +price + ( ( +markUp * .01 ) * price );
            finalPrice = finalPrice.toFixed(2);
            $('#price_target').html( finalPrice );
            $('.price-target').val( finalPrice );
        } else {
            $('#price_target').html( $('#sell_media_single_price').attr('data-price') );
            $('.price-target').val( $('#sell_media_single_price').attr('data-price') );
        }
    }

    function _disable_buttons(){
        $( 'input[type="submit"], input[type="button"]').animate({ opacity: 0.5 }).attr('disabled','disabled');
    }

    function _enable_buttons(){
        $( 'input[type="submit"], input[type="button"]' ).animate({ opacity: 1 }).removeAttr('disabled');
    }

    function cart_count(){
        $.ajax({
            data: "action=sell_media_count_cart",
            success: function( msg ){
                _user.count = msg;
                $('.count-target').html( msg );
            }
        });
    }

    // calculate the total for each item
    function total_items(){
        var current = 0;
        var total = 0;

        jQuery( '.item-price-target' ).each(function( index ){

            current = ( +current ) + ( parseFloat( jQuery(this).html() ) );
            total = ( +total ) + ( +current );
            final_total = current.toFixed(2);

            jQuery( this ).next('.item-total-target').html( final_total );

            jQuery( '.price-target' ).html( final_total );

        });
    }

    cart_count();

    $('.price-target').html(total_items());

    // getPageScroll() by quirksmode.com
    // Retrives the x, y coordinates of the viewport
    function get_page_scroll() {
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

    // the cart
    $('.sell-media-cart-trigger').click(function( event ){

        coordinates = get_page_scroll();
        y = coordinates[1] + +100;
        x = ( $(window).width() / 2 ) - ( $( '.sell-media-cart-dialog' ).outerWidth() / 2 );

        $('.sell-media-cart-dialog').css({
            'top': y + 'px',
            'left': x + 'px'
        });

        event.preventDefault();

        $('.sell-media-cart-dialog').toggle();
        $( ".sell-media-cart-dialog-target" ).html( "<h2>Loading...</h2>" );
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
            beforeSend: function(){
                _disable_buttons();
            },
            success: function( msg ) {
                cart_count();
            },
            complete: function(){
                _enable_buttons();
            }
        });
        return false;
    });

    $('.remove-item-handle').live('click', function(){

        $(this).closest('tr').fadeOut("fast", function() {
                $(this).remove();
        });
        $(this).closest('li').fadeOut("fast", function() {
                $(this).remove();
        });

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
                if ( msg == "0" ){
                    // location.reload();
                    $('#sell-media-checkout').fadeOut();
                }

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