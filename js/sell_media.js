jQuery(document).ready(function($){

    /** 
     * Update cart totals on load
     */
    sm_update_cart_totals();

    /**
     * Check required fields on load
     */
    required_fields();

    /**
     * Update menu cart qty and subtotal on load
     */
    sm_update_cart_menu();

    /** 
     * Dialog popup
     */
    function popup(message){
        // assign values to the overlay and dialog box and show overlay and dialog
        var width = $(window).width();
        var height = $(document).height();
        var dialogTop = $(document).scrollTop() + 25;
        $('.sell-media-dialog-box').width(width).height(height).addClass('is-visible');
        $('.sell-media-dialog-box #sell-media-dialog-box-target').css({top:dialogTop});
    }

    /**
     * When the user clicks on our trigger we set-up the overlay,
     * launch our dialog, and send an Ajax request to load our cart form.
     */
    $(document).on('click','.quick-view',function( event ){

        event.preventDefault();

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
                "product_id": $(this).data('product-id'),
                "attachment_id": $(this).data('attachment-id')
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

        var required = $('.sell-media-add-to-cart-fields [required]');
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

    /**
     * Close dialog
     */
    $(document).on('click', '.sell-media-dialog-box', function(event){
        if( $(event.target).is('.close') || $(event.target).is('.sell-media-dialog-box') ) {
            event.preventDefault();
            $(this).removeClass('is-visible');
        }
    });
    /**
     * Resize dialog
     * 
     * if user resizes the window, call the same function again
     * to make sure the overlay fills the screen and dialog box is aligned to center
     */
    $(window).resize(function(){
        //only do it if the dialog box is not hidden
        if (!$('.sell-media-dialog-box').is(':hidden')) popup();
    });

    /**
    * Checkout click
    */
    $(document).on('click', '.sell-media-cart-checkout', function(){
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

    /**
     * Terms of service checkbox
     */
    $('#sell_media_terms_cb').on('click', function(){
        $this = $(this);
        $this.val() == 'checked' ? $this.val('') : $this.val('checked');
    });

    /**
     * Size/License selections
     */
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
        $('#total').attr( 'data-price', sum );

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
        if ( license_desc ) {
            $('#license_desc').attr('data-tooltip', license_desc).show();
        } else {
            $('#license_desc').hide();
        }

    });

    /**
     * Lightbox
     */
    $( document ).on( 'click', '.add-to-lightbox', function( event ) {

        event.preventDefault();

        var post_id = $(this).data('id'),
            attachment_id = $(this).data('attachment-id'),
            selector = $('#sell-media-lightbox-content #sell-media-' + attachment_id);

        var data = {
            action: 'sell_media_update_lightbox',
            post_id: post_id,
            attachment_id: attachment_id
        };

        $.ajax({
            type: 'POST',
            url: sell_media.ajaxurl,
            data: data,
            success: function(msg){
                $('.lightbox-counter').text(msg.count);
                $('#lightbox-' + post_id).text(msg.text);
                $('#lightbox-' + post_id).attr("title", msg.text);
                $(selector).hide();
                if ( msg.text == 'Remove' ) {
                    $('.lightbox-notice').fadeIn('fast');
                } else {
                    $('.lightbox-notice').fadeOut('fast');
                }
            }
        });
    });

    // Empty the lightbox
    $('.empty-lightbox').on( 'click', function( event ){
        event.preventDefault();

        var emptied = $.removeCookie('sell_media_lightbox', { path: '/' });

        if ( emptied ) {
            $('.sell-media-grid-container').remove();
            $(this).text($(this).data('empty-text'));
            $(this).removeClass('empty-lightbox');
            $('.lightbox-counter').text(0);
        }
    });

    // Count lightbox
    function count_lightbox() {
        var cookie = $.cookie('sell_media_lightbox');
        if ( cookie === undefined ) {
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
    $('(<span class="sell-media-cart-total checkout-counter-wrap">' + sell_media.currency_symbol + '<span class="checkout-price">0</span></span>)').appendTo('.checkout-total a');

    // Checkout qty menu
    $('(<span class="sell-media-cart-quantity checkout-counter">0</span>)').appendTo('.checkout-qty a');

    // Reload current location
    $('.reload').click(function() {
        location.reload();
    });


    /**
     * Add to cart
     */
    $(document).on( 'click', 'button.item_add', function(){
        var $button = $(this);
        var data = $( "form#sell-media-cart-items" ).serializeArray();
        var price = $("span#total").text();
        var qty = $(".checkout-qty").text();
        var ajaxurl = sell_media.ajaxurl + '?action=sm_add_to_cart&price=' + price;

        // Add cart item in session.
        $.post( ajaxurl, data, function( response ){
            $('.sell-media-added').remove();
            $('#sell-media-add-to-cart').after( '<p class="sell-media-added">' + sell_media.added_to_cart + '</p>' );
            sm_update_cart_menu();
        });

        // Disable add to cart button.
        $button.attr("disabled","disabled");

    });

    // Decrease item qty.
    $(document).on( 'click', '.sell-media-cart-decrement', function(){
        sm_update_cart_item( $(this), 'minus' );
        var el = $('.checkout-counter');
        var value = parseInt($(el).text());
        if (!isNaN(value)) {
            $(el).text(value - 1);
        }
    });

    // Increase item qty.
    $(document).on( 'click', '.sell-media-cart-increment', function(){
        sm_update_cart_item( $(this), 'plus' );
        var el = $('.checkout-counter');
        var value = parseInt($(el).text());
        if (!isNaN(value)) {
            $(el).text(value + 1);
        }
    });

    // Submit to payment gateway
    $(document).on('click', '.sell-media-cart-checkout', function(){
        var selected_payment = $( '#sell_media_payment_gateway' ).find( 'input:checked' );
        if( 'paypal' == selected_payment.val() )
            $("#sell_media_payment_gateway").submit();
    });
});

/**
 * Update the menu cart with Qty and Subtotal
 */
function sm_update_cart_menu(){

    var data = {
        action: 'sell_media_cart_menu'
    };

    jQuery.ajax({
        type: 'POST',
        url: sell_media.ajaxurl,
        data: data,
        success: function(msg){
            jQuery('.checkout-price').text(msg.subtotal);
            jQuery('.checkout-counter').text(msg.qty);
        }
    });
}

/**
 * Calculate shipping costs
 */
function sm_calculate_shipping(){
    // Define vars.
    var total_shipping = 0,
        subtotal = 0,
        items = jQuery( "#sell-media-checkout-cart .item" ),
        total_print_qty = 0;

    // Check if sell media reprints is active.
    if( 'undefined' === typeof sell_media_reprints ){
        return total_shipping;
    }

    // Get price of all items.
    items.each( function(){
        var price = jQuery(this).attr('data-price');
        var type = jQuery(this).attr('data-type');
        var current_qty = jQuery(this).find( '.item-quantity' ).text();

        // Check if product is printable.
        if( 'print' === type ){
            total_print_qty += parseInt(current_qty);
            subtotal+= parseFloat( price ) * parseFloat( current_qty );
        }
    });

    // Check if shipping is on total rate.
    if( 'shippingTotalRate' == sell_media_reprints.reprints_shipping && '' !== sell_media_reprints.reprints_shipping_flat_rate ){
        var total_shipping = parseFloat( subtotal ) * parseFloat( sell_media_reprints.reprints_shipping_flat_rate );
    }

    // Check if shipping is on flate rate.
    if( 'shippingFlatRate' == sell_media_reprints.reprints_shipping && '' !== sell_media_reprints.reprints_shipping_flat_rate ){
        var total_shipping = parseFloat(sell_media_reprints.reprints_shipping_flat_rate);
    }

    // Check if shipping is on quantity rate.
    if( 'shippingQuantityRate' == sell_media_reprints.reprints_shipping && '' !== sell_media_reprints.reprints_shipping_flat_rate ){
        var total_shipping = parseInt( total_print_qty ) * parseFloat(sell_media_reprints.reprints_shipping_flat_rate);
    }

    // Return total shipping cost.
    return total_shipping;
}

/**
 * Update cart total.
 */
function sm_update_cart_totals(){
    // Define vars.
    var items = jQuery( "#sell-media-checkout-cart .item" ),
        currency_symbol = sell_media.currencies[sell_media.currency_symbol].symbol,
        subtotal = 0,
        tax = 0,
        total_shipping = 0,
        total_qty = 0;

    // Get price of all items.
    items.each( function(){
        var price = jQuery(this).attr('data-price');
        var current_qty = jQuery(this).find( '.item-quantity' ).text();

        total_qty += parseInt(current_qty);
        subtotal+= parseFloat( price ) * parseFloat( current_qty );
    });

    // Set grand total.
    var grand_total = subtotal;

    // Add tax if tax is set.
    if( sell_media.tax > 0 ){
        tax = parseFloat( subtotal ) * parseFloat( sell_media.tax );
        grand_total = subtotal  + tax ;
    }

    // Add shipping cost.
    if( '2' === sell_media.shipping  ){
        var total_shipping = sm_calculate_shipping();
        var grand_total = parseFloat( grand_total )  + parseFloat( total_shipping );
    }
    
    // Show sub total.
    jQuery( '.sell-media-totals .sell-media-cart-total' ).html( currency_symbol + subtotal.toFixed( 2 ) );

    // Show tax.
    jQuery( '.sell-media-totals .sell-media-cart-tax' ).html( currency_symbol + tax.toFixed( 2 ) );

    // Show shipping.
    jQuery( '.sell-media-totals .sell-media-cart-shipping' ).html( currency_symbol + total_shipping.toFixed( 2 ) );

    // Show Grand total.
    jQuery( '.sell-media-totals .sell-media-cart-grand-total' ).html( currency_symbol + grand_total.toFixed( 2 ) );

}

/**
 * Update cart item
 * @param  {object} el   Element of item
 * @param  {string} type Update type
 */
function sm_update_cart_item( el, type ){
    var parent = el.parents( 'li' ),
        id = parent.attr('id'),
        price = parent.attr('data-price'),
        current_qty = parent.find( '.item-quantity' ).text(),
        currency_symbol = sell_media.currencies[sell_media.currency_symbol].symbol,
        updated_qty = parseInt(current_qty) - 1;

    // Add qty if type is 'plus'.
    if( 'plus' === type )
        updated_qty = parseInt(current_qty) + 1;

    // Update price.
    var updated_price = parseInt( updated_qty ) * parseFloat( price );

    // Update qty.
    parent.find( '.item-quantity .count' ).text( updated_qty );

    // Update item total.
    parent.find( '.item-total' ).html( currency_symbol + updated_price.toFixed( 2 ) );

    // Hide if qty is less than 1.
    if( updated_qty < 1 ){
        parent.fadeOut( 'slow' ).remove();
        if( jQuery("#sell-media-checkout-cart .sell-media-cart-items li").length < 1 ){
            jQuery("#sell-media-checkout-cart").fadeOut( 'slow' );
            jQuery("#sell-media-empty-cart-message").fadeIn( 'slow' );
        }
    }

    // Update cart total.
    sm_update_cart_totals();

    // Update cart item in session.
    jQuery.post( sell_media.ajaxurl, { action: 'sm_update_cart', cart_item_id: id, qty:updated_qty });
}