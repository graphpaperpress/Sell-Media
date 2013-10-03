/** */
jQuery( document ).ready(function( $ ){

    /**
     * Set-up our default Ajax options.
     * Please reference http://api.jquery.com/jQuery.ajaxSetup/
     */
    $.ajaxSetup({
        type: "POST",
        url: sell_media.ajaxurl
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

        if ( $('.sell-media-item-price').length ){
            $('.sell-media-item-price').html( finalPrice );
            $('.sell-media-item-price').val( finalPrice );
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
                $('.menu-cart-items').html( msg );
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

        sell_media.cart.subtotal = final_total;

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
        if ( $('.item-price-target').length ){
            $('.item-price-target').each(function(){
                total = +( $(this).text() ) + +total;
            });
        } else {
            total = +sell_media.cart.subtotal;
        }

        $('.subtotal-target').html( total.toFixed(2) );
        $('.menu-cart-total').html( total.toFixed(2) );
        $('.sell-media-item-price').html( total.toFixed(2) );
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
     * Updates a div with the class name called 'menu-cart-items' to have
     * the total number of items.
     */
    function sell_media_quantity_total(){
        var total = 0;
        if ( $('.sell-media-quantity').length ){
            $('.sell-media-quantity').each(function(){
                item_id = $(this).attr('data-id');
                qty = +$('#quantity-' + item_id ).val();
                total = total + qty;
            });
        } else {
            total = sell_media.cart.quantity;
        }

        if ( $('.menu-cart-items').length )
            $('.menu-cart-items').html( total );
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
    sell_media_quantity_total();


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
                if ($('#download #sell_media_size_select').length) {
                    $('#sell_media_license_select').attr('disabled', true);
                };
                if ($('#download #sell_media_size_select').length || $('#download #sell_media_license_select').length) {
                    $('.sell-media-buy-button').attr('disabled', true);
                };
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

        if ( typeof( size ) === "undefined" )
            size = $('input[name="CalculatedPrice"]').val();

        var license_desc = $('#sell_media_license_select :selected').attr('title');

        $("option:selected", this).each(function(){
            price = $(this).attr('data-price');
            calculate_total( price, size );
        });

        $(this).parent().find(".license_desc").attr('data-tooltip', license_desc);
        if ( license_desc == '' ){
            $(this).parent().find(".license_desc").hide();
        } else {
            $(this).parent().find(".license_desc").show();
        }

        // If we have a value enable the buy button, if we don't disable it
        if ( $("option:selected", this).val() ){
            $('.sell-media-buy-button').removeAttr('disabled');
        } else {
            $('.sell-media-buy-button').attr('disabled', true);
        }
    });


    /**
     * On change make sure the licnese has a value
     */
    $( document ).on('change', '#sell_media_size_select', function(){

        /**
         * Derive the license from the select
         * or from the single item
         */
        if ( $('#sell_media_single_license_markup').length ){
            license = $('#sell_media_single_license_markup').val();
        } else if( $('#sell_media_license_select').length ){
            license = $('#sell_media_license_select :selected').val();
        } else {
            license = null;
        }

        $("option:selected", this).each(function(){
            size = $(this).attr('data-price');
            calculate_total( license, size );
        });

        size = $('#sell_media_size_select :selected').attr('data-price');

        // if no size disable the add to cart button
        // and the license select
        if ( size == 0 && license != null ){
            $('.sell-media-buy-button').attr('disabled', true);
            $('#sell_media_license_select').attr('disabled', true);
        }

        // Check if multiple licenses are in use, else we enable the
        // buy button
        if ( $('#sell_media_license_select').length ) {
            if ( size != 0 && license >= 0 ) {
                $('#sell_media_license_select').removeAttr('disabled');
            }
        } else {
            console.log( 'enable button' );
            if ( size != 0 && license >= 0 ) {
                $('.sell-media-buy-button').removeAttr('disabled');
            }
        }

        // user selected a size, but there's no license to select
        if ( size != 0 && license == null ){
            $('.sell-media-buy-button').removeAttr('disabled');
        }

    });


    $( document ).on('submit', '.sell-media-dialog-form', function(){

        $('.sell-media-buy-button').attr('disabled',true);

        if ( $('.sell-media-buy-button').hasClass('sell-media-purchased') ){
            $('.sell-media-buy-button').removeAttr('disabled');
            location.href = sell_media.checkouturl;
        } else {

            var _data = "action=add_items&taxonomy=licenses&" + $( this ).serialize();

            if ( _user.count < 1 ) {
                text = '(<span class="count-container"><span class="count-target"></span></span>)';
                $('.empty').html( text );
                $('.cart-handle').show();
            }

            $.ajax({
                data: _data,
                success: function( msg ) {
                    cart_count();
                    // sell_media_update_total();

                    total = ( +( $('.menu-cart-total').html() ) + +( $('.sell-media-item-price').html() ) );
                    $('.menu-cart-total').html( total.toFixed(2) );

                    $button = $('.sell-media-form').find('.sell-media-buy-button');
                    $button.addClass('sell-media-purchased').val('Checkout');
                    $('.sell-media-buy-button').removeAttr('disabled');
                }
            });
        }
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
            action: "remove_item",
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
                sell_media_quantity_total();
                sell_media_update_total();
            }
        });
    });

    $( document ).on('click', '.remove-all-handle', function( e ){
        e.preventDefault();

        var item_ids = [];
        $('.remove-item-handle').each(function(){
            console.log( $( this ).attr('data-item_id') );
            item_ids.push( $( this ).attr('data-item_id') );
        });

        $.ajax({
            data: {
                action: "remove_item",
                item_id: item_ids
            },
            success: function( msg ){
                // We have no items in the cart
                if ( msg ){
                    $('#sell-media-checkout').html( msg );
                }
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


    $("#sell-media-checkout table tr:nth-child(odd)").addClass("odd-row");
    $("#sell-media-checkout table td:first-child, #sell-media-checkout table th:first-child").addClass("first");
    $("#sell-media-checkout table td:last-child, #sell-media-checkout table th:last-child").addClass("last");


    /**
     * If the user clicks inside of our input box and manually updates the quantiy
     * we run the sub-total and total functions.
     */
    $(document).on('keyup', '.sell-media-quantity', function(){
        sell_media_update_sub_total();
        sell_media_update_total();
        sell_media_update_final_total();
        sell_media_quantity_total();
        if ( $(this).val() > 0 ){
            $('.sell-media-buy-button').removeAttr('disabled');
        } else {
            $('.sell-media-buy-button').attr('disabled', true);
        }
    });

    $( document ).on( 'submit', '#sell_media_checkout_form', function() {
        var faults = $( 'input' ).filter( function() {
            return $( this ).data( 'required' ) && $( this ).val() === '';
        }).css( 'background-color', 'red');
        if ( faults.length ) return false;
    });

     /**
     * When the user clicks on our trigger we set-up the overlay,
     * launch our dialog, and send an Ajax request to load our cart form.
     */
    $( document ).on( 'click', '#agree_terms_and_conditions', function( event ){
        event.preventDefault();

        // Overlay set-up
        coordinates = sell_media_get_page_scroll();
        y = coordinates[1] + +100;
        x = ( $(window).width() / 2 ) - ( $( '#terms-and-conditions-dialog' ).outerWidth() / 2 );
        $('#terms-and-conditions-dialog').css({
            'top': y + 'px',
            'left': x + 'px'
        });

        // Show our dialog with a loading message
        $('#terms-and-conditions-dialog').toggle();

        // Add our overlay to the html if #overlay is present.
        if($('#overlay').length > 0){
            $('#overlay').remove();
        } else {
            $('body').append('<div id="overlay"></div>');
            var doc_height = $(document).height();
            $('#overlay').height(doc_height);
            $('#overlay').click(function(){
                $('#terms-and-conditions-dialog').toggle();
                $(this).remove();
            });
        }
    });

    $( document ).on( 'click', '.close', function(){
        $('#terms-and-conditions-dialog').hide();
        $('#overlay').remove();
    });


    /**
     * Hide our current seach option when the user clicks off the input field
     */
    $( document ).on('blur', '#s', function(){
        $(".sell-media-search-options", this).hide();
    });


    $( document ).on('click', '.sell-media-search-options-trigger', function(e){
        e.preventDefault();
        $(this).closest('.sell-media-search-form').find('.sell-media-search-options:first').show();
     });


    $( document ).on('change', '.post_type_selector', function(){

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

            $collection.attr('name', $collection.attr( 'data-name') );
            $keywords.attr('name', $keywords.attr( 'data-name' ) );
        }
     });


    /**
     * Check if the email exists, if it does we display an error message
     * if not we submit the form
     */
    $('.sell-media-buy-button-checkout').on('click', function( e ){
        e.preventDefault();
        $.ajax({
            url: sell_media.ajax_url,
            data: {
                email: $('#sell_media_email_field').val(),
                action: 'sell_media_check_email',
                security: $('#sell_media_cart_nonce').val()
            },
            success: function( msg ){
                if ( msg.success ){
                    $('#sell_media_checkout_form').submit();
                } else {
                    if(!$(".sell-media-error").length) {
                        $('#sell_media_email_field').after( '<span class="sell-media-error">' + sell_media.error.email_exists + '</span>' );
                    }
                }
            }
        });
    });


    $('#sell_media_terms_cb').on('click', function(){
        $this = $(this);
        $this.val() == 'checked' ? $this.val('') : $this.val('checked');
    });
});