function sm_calculate_shipping(){
    // Define vars.
    var total_shipping = 0,
        subtotal = 0,
        items = jQuery( "#sell-media-checkout-cart tr.itemRow" ),
        total_print_qty = 0;

    // Check if sell media reprints is active.
    if( 'undefined' === typeof sell_media_reprints ){
        return total_shipping;
    }

    // Get price of all items.
    items.each( function(){
        var price = jQuery(this).find('.item-price').attr('data-price');
        var type = jQuery(this).attr('data-type');
        var current_qty = jQuery(this).find( '.item-quantity' ).text();

        // Check if product is printable.
        if( 'print' === type ){
            total_print_qty += parseInt(current_qty);
            subtotal+= parseFloat( price ) * parseFloat( current_qty );
        }
    });

    // Check if shipping is on total rate.
    if( 'shippingTotalRate' == sell_media_reprints.reprints_shipping ){
        var total_shipping = parseFloat( subtotal ) * parseFloat( sell_media_reprints.reprints_shipping_flat_rate );
    }

    // Check if shipping is on flate rate.
    if( 'shippingFlatRate' == sell_media_reprints.reprints_shipping ){
        var total_shipping = parseFloat(sell_media_reprints.reprints_shipping_flat_rate);
    }

    // Check if shipping is on quantity rate.
    if( 'shippingQuantityRate' == sell_media_reprints.reprints_shipping ){
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
    var items = jQuery( "#sell-media-checkout-cart tr.itemRow" ),
        currency_symbol = sell_media.currencies[sell_media.currency_symbol].symbol,
        subtotal = 0,
        tax = 0,
        total_shipping = 0,
        total_qty = 0;

    // Get price of all items.
    items.each( function(){
        var price = jQuery(this).find('.item-price').attr('data-price');
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
    var parent = el.parents( 'tr' ),
        id = parent.attr('id'),
        price = parent.find('.item-price').attr('data-price'),
        current_qty = parent.find( '.item-quantity' ).text(),
        currency_symbol = sell_media.currencies[sell_media.currency_symbol].symbol,
        updated_qty = parseInt(current_qty) - 1;

    // Add qty if type is 'plus'.
    if( 'plus' === type )
        updated_qty = parseInt(current_qty) + 1;

    // Update price.
    var updated_price = parseInt( updated_qty ) * parseFloat( price );

    // Update qty.
    parent.find( '.item-quantity #count' ).text( updated_qty );

    // Update item total.
    parent.find( '.item-total' ).html( currency_symbol + updated_price.toFixed( 2 ) );

    // Hide if qty is less than 1.
    if( updated_qty < 1 ){
        parent.fadeOut( 'slow' );
        jQuery("#sell-media-checkout-cart").fadeOut( 'slow' );
        jQuery("#sell-media-empty-cart-message").fadeIn( 'slow' );
    }

    // Update cart total.
    sm_update_cart_totals();

    // Update cart item in session.
    jQuery.post( sell_media.ajaxurl, { action: 'sm_update_cart', cart_item_id: id, qty:updated_qty });
}

jQuery( function( $ ){

    // Update cart totals on load.
    sm_update_cart_totals();

    // Add to cart click event.
    $(document).on( 'click', 'button.item_add', function(){
        var $button = $(this);
        var data = $( "form#sell-media-cart-items" ).serializeArray();
        var price = $("#sell-media-dialog-box span#total").text();
        var ajaxurl = sell_media.ajaxurl + '?action=sm_add_to_cart&price=' + price;

        // Add cart item in session.
        $.post( ajaxurl, data, function( response ){
            $('.sell-media-added').remove();
            $('#sell-media-add-to-cart').after( '<p class="sell-media-added">' + sell_media.added_to_cart + '</p>' );
        });

        // Disable add to cart button.
        $button.attr("disabled","disabled");
    });

    // Decrease item qty.
    $(document).on( 'click', '.sell-media-cart-decrement', function(){
        sm_update_cart_item( $(this), 'minus' );
    });

    // Increase item qty.
    $(document).on( 'click', '.sell-media-cart-increment', function(){
        sm_update_cart_item( $(this), 'plus' );
    });

    $(document).on('click', '.sell-media-cart-checkout', function(){
        var selected_payment = $( '#sell_media_payment_gateway' ).find( 'input:checked' );
        if( 'paypal' == selected_payment.val() )
            $("#sell_media_payment_gateway").submit();
    });

});