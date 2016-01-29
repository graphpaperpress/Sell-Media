function sm_calculate_shipping(){
    var total_shipping = 0;
    if( 'undefined' === typeof sell_media_reprints ){
        return total_shipping;
    }

    var subtotal = 0;
    var items = jQuery( "#sell-media-checkout-cart tr.itemRow" );

    // Get price of all items
    var total_qty = 0;
    items.each( function(){
        var price = jQuery(this).find('.item-price').attr('data-price');
        var type = jQuery(this).attr('data-type');
        var current_qty = jQuery(this).find( '.item-quantity' ).text();

        if( 'print' === type ){
            total_qty += parseInt(current_qty);
            subtotal+= parseFloat( price ) * parseFloat( current_qty );
        }
    });

    if( 'shippingTotalRate' == sell_media_reprints.reprints_shipping ){
        var total_shipping = parseFloat( subtotal ) * parseFloat( sell_media_reprints.reprints_shipping_flat_rate );
    }

    if( 'shippingFlatRate' == sell_media_reprints.reprints_shipping ){
        var total_shipping = parseFloat(sell_media_reprints.reprints_shipping_flat_rate);
    }

    if( 'shippingQuantityRate' == sell_media_reprints.reprints_shipping ){
        var total_shipping = parseInt( total_qty ) * parseFloat(sell_media_reprints.reprints_shipping_flat_rate);
    }

    return total_shipping;
}

/**
 * Update cart total.
 */
function sm_update_cart_totals(){
    var items = jQuery( "#sell-media-checkout-cart tr.itemRow" );
    var currency_symbol = sell_media.currencies[sell_media.currency_symbol].symbol;
    var subtotal = 0;
    var grand_total = 0;

    // Get price of all items
    var total_qty = 0;
    items.each( function(){
        var price = jQuery(this).find('.item-price').attr('data-price');
        var current_qty = jQuery(this).find( '.item-quantity' ).text();

        total_qty += parseInt(current_qty);
        subtotal+= parseFloat( price ) * parseFloat( current_qty );
    });

    // Show sub total
    jQuery( '.sell-media-totals .sell-media-cart-total' ).html( currency_symbol + subtotal );
    
    // Add tax
    jQuery( '.sell-media-totals .sell-media-cart-tax' ).html( currency_symbol + '0' );

    // Add tax if tax is set.
    if( sell_media.tax > 0 ){
        var tax = parseFloat( subtotal ) * parseFloat( sell_media.tax );
        var grand_total = ( subtotal  + tax ).toFixed(2);

        tax = tax.toFixed(2);
        jQuery( '.sell-media-totals .sell-media-cart-tax' ).html( currency_symbol + tax );
    }


    
    var total_shipping = 0;

    // Add shipping
    if( '2' === sell_media.shipping  ){
        var total_shipping = sm_calculate_shipping();

        var grand_total = ( parseFloat( grand_total )  + parseFloat(total_shipping) );
    }

    // Show shipping.
    jQuery( '.sell-media-totals .sell-media-cart-shipping' ).html( currency_symbol + total_shipping.toFixed( 2 ) );

    // Grand total.
    jQuery( '.sell-media-totals .sell-media-cart-grandTotal' ).html( currency_symbol + grand_total );

}

/**
 * Update cart item
 * @param  {object} el   Element of item
 * @param  {string} type Update type
 */
function sm_update_cart_item( el, type ){
    var parent = el.parents( 'tr' );
    var id = parent.attr('id');
    var price = parent.find('.item-price').attr('data-price');
    var current_qty = parent.find( '.item-quantity' ).text();
    var currency_symbol = sell_media.currencies[sell_media.currency_symbol].symbol;
    var updated_qty = parseInt(current_qty) - 1;

    // Add qty if type is 'plus'.
    if( 'plus' === type )
        updated_qty = parseInt(current_qty) + 1;

    // Update price.
    var updated_price = parseInt( updated_qty ) * parseFloat( price );

    // Update qty.
    parent.find( '.item-quantity' ).text( updated_qty );

    // Update item total.
    parent.find( '.item-total' ).html( currency_symbol + updated_price.toFixed( 2 ) );

    // Hide if qty is less than 1.
    if( updated_qty < 1 ){
        parent.fadeOut( 'slow' );
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