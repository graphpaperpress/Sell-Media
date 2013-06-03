<?php

/**
 * Print attached image
 *
 * @access      public
 * @since       0.1
 * @return      html
 */
function sell_media_image( $post_id=null ) {

    $thumb_id = get_post_thumbnail_id( $post_id );
    $attachment = get_post( $thumb_id );
    $title = $attachment->post_title;
    $size = 'large';
    $image = wp_get_attachment_image_src( $thumb_id, $size );
    $image = '<img src="' . $image[0] . '" alt="' .  $title . '" title="' .  $title . '" class="wp-post-image" />';

    print $image;

}


/**
 * Print attached image caption
 *
 * @access      public
 * @since       0.1
 * @return      string
 */
function sell_media_image_caption( $post_id=null ) {

    $thumb_id = get_post_thumbnail_id( $post_id );
    $attachment = get_post( $thumb_id );
    $title = $attachment->post_title;

    return $title;

}


/**
 * Print attached image size
 *
 * @access      public
 * @since       0.1
 * @return      html
 */
function sell_media_get_image_size( $post_id=null ) {

    $thumb_id = get_post_thumbnail_id( $post_id );
    $meta = get_post_meta( intval( $thumb_id ), '_wp_attachment_metadata' , true );

    if ( $meta['width'] && $meta['height'] )
        $size = $meta['width'] . 'x' . $meta['height'] . ' pixels';
    else
        $size = false;

    return $size;
}


/**
 * Print attached image filename
 *
 * @access      public
 * @since       0.1
 * @return      html
 */
function sell_media_image_filename( $post_id=null, $echo=true ) {

    $filename = basename( get_post_meta( $post_id, '_sell_media_attached_file', true ) );

    if ( $echo )
        print $filename;
    else
        return $filename;
}


/**
 * Print attached image keywords
 *
 * @access      public
 * @since       0.1
 * @return      html
 */
function sell_media_image_keywords( $post_id=null ) {

    $product_terms = wp_get_object_terms( $post_id, 'keywords' );
    if ( !empty( $product_terms ) ) {
        if ( !is_wp_error( $product_terms ) ) {
            foreach ( $product_terms as $term ) {
                echo '<a href="' . get_term_link( $term->slug, 'keywords' ) . '">' . $term->name . '</a> ';
            }
        }
    }
}


/**
 * Print the buy button
 *
 * @access      public
 * @since       0.1
 * @return      html
 */
function sell_media_item_buy_button( $post_id=null, $button=null, $text=null, $echo=true ) {

    $thumb_id = get_post_thumbnail_id( $post_id );
    $text = apply_filters('sell_media_purchase_text', __( $text,'sell_media' ), $post_id );
    $html = '<a href="javascript:void(0)" data-sell_media-product-id="' . esc_attr( $post_id ) . '" data-sell_media-thumb-id="' . esc_attr( $thumb_id ) . '" class="sell-media-cart-trigger sell-media-buy-' . $button . '">' . $text . '</a>';

    if ( $echo ) print $html; else return $html;
}


/**
 * Check if item is part of a taxonomy
 *
 * @access      public
 * @since       0.1
 * @return      boolean
 */
function sell_media_item_has_taxonomy_terms( $post_id=null, $taxonomy=null ) {

    $terms = wp_get_post_terms( $post_id, $taxonomy );

    if ( empty ( $terms ) )
        return false;
    else
        return true;

}


/**
 * Returns item size
 *
 * @since 0.1
 * @return string
 */
function sell_media_item_size( $post_id=null ){

    $mime_type = get_post_mime_type( get_post_thumbnail_id( $post_id ) );
    $size = false;

    switch( $mime_type ){
        case 'image/jpeg':
        case 'image/png':
        case 'image/gif':
            $size = sell_media_get_image_size( $post_id );
            break;
        case 'video/mpeg':
        case 'video/mp4':
        case 'video/quicktime':
        case 'application/octet-stream':
            return;
        case 'text/csv':
        case 'text/plain':
        case 'text/xml':
        case 'text/document':
        case 'application/pdf':
            return;
    }

    return $size;
}


/**
 * Retrives and prints the price of an item
 *
 * @since 0.1
 * @param $post_id The Item ID
 * @param $currency (bool) Display the currency symbold or not
 * @param $size (string) small, medium, large, null (for original)
 * @param $echo Either print the result or return it
 * @return string
 */
function sell_media_item_price( $post_id=null, $currency=true, $size=null, $echo=true ){

    /**
     * Get the default price for this Item.
     */
    $default_price = get_post_meta( $post_id, 'sell_media_price', true );
    $size_settings = get_option('sell_media_size_settings');

    /**
     * If we have a filtered price we use that if not we derive our own price
     */
    $filtered_price = apply_filters( 'sell_media_filtered_price', $size );
    if ( $filtered_price == $size ){

        /**
         * If we have no size and no default price for this item we fall back
         * on the defaults from the settings.
         */
        if ( empty( $size ) && ! empty( $default ) ){
            $price = $size_settings['default_price'];
        } else {

            /**
             * Get the price based on the size and id passed in.
             */
            $item_price = get_post_meta( $post_id, 'sell_media_price_' . $size, true );

            /**
             * If a size was not passed in we fall back on the default
             * price for this post.
             */
            if ( empty( $size ) ){
                if ( empty( $default_price ) ){
                    $price = $size_settings['default_price'];
                } else {
                    $price = $default_price;
                }
            } elseif ( empty( $item_price ) ){

                /**
                 * If this single item does not have a price, we fall back on the
                 * default prices set in the settings.
                 */
                $price = get_option('sell_media_size_settings');
                if ( in_array( $size, array('small','medium','large') ) ){
                    switch( $size ){
                        case 'small':
                            $k = 'small_size_price';
                            break;
                        case 'medium':
                            $k = 'medium_size_price';
                            break;
                        case 'large':
                            $k = 'large_size_price';
                            break;
                        default:
                            $k = 'default_price';
                    }
                } else {
                    switch( $size ){
                        case 'sell_media_small_file':
                            $k = 'small_size_price';
                            break;
                        case 'sell_media_medium_file':
                            $k = 'medium_size_price';
                            break;
                        case 'sell_media_large_file':
                            $k = 'large_size_price';
                            break;
                        default:
                            $k = 'default_price';
                    }
                }
                $price = $price[ $k ];

            } else {

                /**
                 * Else we assign our item price to the price.
                 */
                $price = $item_price;
            }
        }
    } else {
        $price = $filtered_price;
    }

    if ( $currency ){
        $price = sell_media_get_currency_symbol() . sprintf( '%0.2f', $price );
    }

    if ( $echo )
        print $price;
    else
        return $price;

}


/**
 * Determines the default icon used for an Attachment. If an
 * image mime type is detected than the attachment image is used.
 */
function sell_media_item_icon( $attachment_id=null, $size='medium', $echo=true ){

    if ( post_password_required() ) return;

    if ( ! empty( $_POST['attachment_id'] ) ){
        $attachment_id = $_POST['attachment_id'];
    }

    if ( empty( $attachment_id ) )
        return;

    if ( ! empty( $_POST['attachment_size'] ) )
        $size = $_POST['attachment_size'];

    $mime_type = get_post_mime_type( $attachment_id );
    $image_height = null;
    $image_width = null;
    $sell_media_item_id = get_post_meta( $attachment_id, '_sell_media_for_sale_product_id', true );
    $image_title = get_the_title( $sell_media_item_id );
    $_thumbnail_id = get_post_meta( $sell_media_item_id, '_thumbnail_id', true );

    /**
     * Since we always want to return the actual image associated with this item for sale
     * on the edit/add new item page. We check the global $pagenow variable, vs. adding
     * coniditionals through out the code.
     */
    global $pagenow;
    global $post_type;
    if ( ! empty( $_thumbnail_id ) && $post_type == 'sell_media_item' && $pagenow != 'post.php' ){
        $attachment_id = $_thumbnail_id;
    }

    $image = wp_get_attachment_image_src( $attachment_id, $size );

    switch( $mime_type ){
        case 'image/jpeg':
        case 'image/png':
        case 'image/gif':
            $image_src = $image[0];
            $image_height = $image[2];
            $image_width = $image[1];
            break;
        case 'video/mpeg':
        case 'video/mp4':
        case 'video/quicktime':
        case 'application/octet-stream':
            if ( $image ){
                $image_src = $image[0];
                $image_height = $image[2];
                $image_width = $image[1];
            } else {
                $image_src = wp_mime_type_icon( 'video/mpeg' );
            }
            break;
        case 'text/csv':
        case 'text/plain':
        case 'text/xml':
        case 'text/document':
        case 'application/pdf':
            if ( $image ){
                $image_src = $image[0];
                $image_height = $image[2];
                $image_width = $image[1];
            } else {
                $image_src = wp_mime_type_icon( 'text/document' );
            }
            break;
        case 'application/zip':
            if ( $image ){
                $image_src = $image[0];
                $image_height = $image[2];
                $image_width = $image[1];
            } else {
                $image_src = includes_url() . 'images/crystal/archive.png';
            }
            break;
        default:
            if ( $image ){
                $image_src = $image[0];
                $image_height = $image[2];
                $image_width = $image[1];
            } else {
                $image_src = wp_mime_type_icon( $mime_type );
            }
    }

    $medium_url = wp_get_attachment_image_src( $attachment_id, 'medium' );
    if ( $medium_url )
        $medium_url = $medium_url[0];
    else
        $medium_url = null;

    $icon =  '<img src="' . $image_src . '" class="sell_media_image wp-post-image" title="' . $image_title . '" alt="' . $image_title . '" data-sell_media_medium_url="' . $medium_url . '" data-sell_media_item_id="' . $sell_media_item_id . '" height="' . $image_height . '" width="' . $image_width . '" style="max-width:100%;height:auto;"/>';

    if ( $echo )
        print $icon;
    else
        return $icon;

    /**
     * If attachment ID is set via $_POST we are doing ajax. So we
     * must die.
     */
    if ( ! empty( $_POST['action'] ) && $_POST['action'] == 'sell_media_item_icon' ) die();
}
add_action( 'wp_ajax_sell_media_item_icon', 'sell_media_item_icon' );


/**
 * Adjust wp_query for when searh is submitted error no longer shows in "general-template.php"
 * detail here: http://wordpress.stackexchange.com/questions/71157/undefined-property-stdclasslabels-in-general-template-php-post-type-archive
 * @author Zane Matthew
 * @since 1.2.3
 */
function sell_media_search_warning_surpression( $wp_query ){
    if ( $wp_query->is_post_type_archive && $wp_query->is_tax )
        $wp_query->is_post_type_archive = false;
}
add_action( 'parse_query', 'sell_media_search_warning_surpression' );


function sell_media_item_form(){
    $general_settings = get_option( 'sell_media_general_settings' );
    $licenses = wp_get_post_terms( $_POST['product_id'], 'licenses' );
    $attachment_id = get_post_meta( $_POST['product_id'], '_sell_media_attachment_id', true );
    $disabled = null;
    $price = sell_media_item_price( $_POST['product_id'], $currency=false, false, false);
    if ( $licenses ) {
        $term_id = $licenses[0]->term_id;
    } else {
        $term_id = null;
    } ?>
    <?php do_action( 'sell_media_above_item_form' ); ?>
    <form action="javascript://" method="POST" class="sell-media-dialog-form sell-media-form">
        <input type="hidden" name="AttachmentID" value="<?php print $attachment_id; ?>" />
        <input type="hidden" name="ProductID" value="<?php print $_POST['product_id']; ?>" />
        <input type="hidden" name="CalculatedPrice" class="price-target" value="<?php sell_media_item_price( $_POST['product_id'], $currency=false); ?>" data-price="<?php sell_media_item_price( $_POST['product_id'], $currency=false); ?>" />
        <?php wp_nonce_field('sell_media_add_items','sell_media_nonce'); ?>

        <?php do_action( 'sell_media_cart_above_licenses' ); ?>
        <?php
        $wp_upload_dir = wp_upload_dir();
        $mime_type = wp_check_filetype( $wp_upload_dir['basedir'] . SellMedia::upload_dir . '/' . get_post_meta( $_POST['product_id'], '_sell_media_attached_file', true ) );
        if ( in_array( $mime_type['type'], array( 'image/jpeg', 'image/png', 'image/gif', 'image/bmp', 'image/tiff' ) ) ) : ?>
            <?php $size_settings = get_option('sell_media_size_settings'); $disabled = 'disabled'; $price = "0.00"; ?>
            <fieldset>
                <legend><?php _e('Size', 'sell_media'); ?></legend>

                <select id="sell_media_size_select" name="price_id">
                    <option value="" data-price="0">-- <?php _e( 'Select a size' ); ?> --</option>

                    <?php foreach( sell_media_image_sizes( $_POST['product_id'], false ) as $k => $v ) : ?>
                        <option value="sell_media_<?php print $k; ?>_file" data-price="<?php sell_media_item_price( $_POST['product_id'], false, $k ); ?>"><?php _e( ucfirst( $k ), 'sell_media' ); ?> (<?php print $size_settings[ $k . '_size_width'] . ' x ' . $size_settings[ $k . '_size_height']; ?>): <?php sell_media_item_price( $_POST['product_id'], true, $k ); ?></option>
                    <?php endforeach; ?>

                    <option value="sell_media_original_file" data-price="<?php sell_media_item_price( $_POST['product_id'], false ); ?>">
                        <?php _e( 'Original', 'sell_media' ); ?>
                        (<?php print sell_media_original_image_size( $_POST['product_id'] ); ?>):
                        <?php sell_media_item_price( $_POST['product_id'] ); ?>
                    </option>
                </select>

            </fieldset>
        <?php else : ?>
            <input type="hidden" id="sell_media_price" name="price_id" data-price="<?php sell_media_item_price( $_POST['product_id'], false ); ?>" value="<?php sell_media_item_price( $_POST['product_id'], false ); ?>" />
        <?php endif; ?>

        <?php do_action( 'sell_media_cart_below_licenses' ); ?>


        <?php do_action( 'sell_media_cart_above_size' ); ?>
        <?php if ( count( $licenses ) > 1 ) : ?>
            <fieldset>
                <legend><?php _e( 'License', 'sell_media' ); ?></legend>
                <select name="License" value="License" id="sell_media_license_select" disabled>
                    <option value="" data-price="0">-- <?php _e( 'Select a license' ); ?> --</option>
                    <?php sell_media_build_options( array( 'post_id' => $_POST['product_id'], 'taxonomy' => 'licenses', 'type'=>'select' ) ); ?>
                </select>
            </fieldset>
        <?php else : ?>
            <?php if ( ! empty( $term_id ) ) : ?>
                <input id="sell_media_single_price" type="hidden" name="License" value="<?php print $term_id; ?>" data-price="<?php sell_media_item_price( $_POST['product_id'], $currency=false); ?>" />
                <input type="hidden" value="<?php print str_replace('%', '', sell_media_get_term_meta( $licenses[0]->term_id, 'markup', true ) ); ?>" id="sell_media_single_license_markup" />
                <?php _e( 'License', 'sell_media'); ?>: <?php print $licenses[0]->name; ?>
            <?php endif; ?>
        <?php endif; ?>
        <?php do_action( 'sell_media_cart_below_size' ); ?>

        <div class="total-container group">
            <div class="left">
                <strong><?php _e( 'Total' ); ?></strong>
            </div>
            <div class="right">
                <span class="price-container"><?php print sell_media_get_currency_symbol(); ?><span class="price-target"><?php print $price; ?></span></span>
            </div>
        </div>
        <div class="button-container group">
            <div class="left">
                <?php if ( empty( $_SESSION['cart']['items']) ) : ?>
                    <span class="cart empty"><?php _e( 'Cart', 'sell_media' ); ?> (0)</span>
                <?php else: ?>
                    <span class="cart full"><a href="<?php print get_permalink( $general_settings['checkout_page'] ); ?>" class="cart-handle"><?php _e( 'Cart', 'sell_media' ); ?> (<span class="count-container"><span class="count-target"></span></span>)</a></span>
                <?php endif; ?>
                <a href="<?php print get_permalink( $general_settings['checkout_page'] ); ?>" class="cart-handle" style="display: none;"><?php _e( 'Cart', 'sell_media' ); ?></a>
            </div>
            <div class="right">
                <input type="submit" value="<?php print apply_filters('sell_media_add_to_cart_text', __('Add to Cart'), $_POST['product_id'] ); ?>" class="sell-media-buy-button" <?php print $disabled; ?> />
            </div>
        </div>
    </form>
    <?php do_action( 'sell_media_below_item_form' ); ?>
<?php }


/**
 * Determines the available download sizes based on the current image width/height.
 * Note not ALL images are available in ALL download sizes.
 *
 * @since 1.2.4
 * @author Zane Matthew
 * @return Prints an li or returns an array of available download sizes
 */
function sell_media_image_sizes( $post_id=null, $echo=true ){
    $size_settings = get_option('sell_media_size_settings');
    $html = null;
    $aid = get_post_meta( $post_id, '_sell_media_attachment_id', true );
    $attached_file = get_post_meta( $post_id, '_sell_media_attached_file', true );

    if ( empty( $attached_file ) ) return false;

    $wp_upload_dir = wp_upload_dir();
    $attached_path_file  = $wp_upload_dir['basedir'] . SellMedia::upload_dir . '/' . $attached_file;

    $attached_file_fix = file_exists( $attached_path_file );
    if ( ! $attached_file_fix ){
        @list( $broken, $url, $attached_file ) = explode( 'uploads/', $attached_path_file );
        $attached_path_file = $wp_upload_dir['basedir'] . SellMedia::upload_dir . '/' . $attached_file;
    }

    list( $orig_w, $orig_h, $type, $attr ) = @getimagesize( $attached_path_file );

    $null = null;
    $original = $download_sizes = array();
    list( $original['url'], $original['width'], $original['height'] ) = wp_get_attachment_image_src( get_post_meta( $post_id, '_sell_media_attachment_id', true ), 'full' );

    foreach( array( 'small', 'medium', 'large' ) as $size ){

        /**
         * This looks ugly because image_resize_dimensions() returns empty indexes
         */
        list( $null, $null, $null, $null, $download_sizes[ $size ]['width'], $download_sizes[ $size ]['height'] ) = image_resize_dimensions( $orig_w, $orig_h, $size_settings[$size . '_size_width'], $size_settings[$size . '_size_height'], $crop=false );

        /**
         * If no width/height can be determined we remove it from our array of available download sizes.
         */
        if ( empty( $download_sizes[ $size ]['width'] ) ) unset( $download_sizes[ $size ] );

    	/**
	     * Check for portraits and if the available download size is larger than the original we remove it.
	     */
        if ( $original['height'] > $original['width']
            && isset( $download_sizes[ $size ] )
            && $download_sizes[ $size ]['height'] <  $size_settings['small_size_height'] ) unset( $download_sizes[ $size ] );
    }

    if ( $echo ){
        foreach( $download_sizes as $k => $v ){
            $name = ucfirst( $k );
            $html .= '<li class="price">';
            $html .= '<span class="title">' . __( $name, 'sell_media' ) . ' (' . $download_sizes[ $k ]['width'] . ' x ' . $download_sizes[ $k ]['height'] . '): </span>';
            $html .= sell_media_item_price( $post_id, true, $k, false );
            $html .= '</li>';
        }
        print $html;
    } else {
        return $download_sizes;
    }
}


/**
 * Prints the original image resolution
 *
 * @since 1.2.4
 * @author Zane Matthew
 */
function sell_media_original_image_size( $item_id=null, $echo=true ){
    $original_size = wp_get_attachment_image_src( get_post_meta( $item_id, '_sell_media_attachment_id', true ), 'full' );

    if ( $echo ){
        print $original_size[1] . ' x ' . $original_size[2];
    } else {
        return array(
            'original'=> array(
                'height' => $original_size[2],
                'width' => $original_size[1]
                )
            );
    }
}


/**
 * Optionally prints the plugin credit
 * Off by default in compliance with WordPress best practices
 * http://wordpress.org/extend/plugins/about/guidelines/
 *
 * @since 1.2.6
 * @author Thad Allender
 */
function sell_media_plugin_credit() {
    $settings = get_option( 'sell_media_general_settings' );
    if ( true == $settings['plugin_credit'] ) {
        printf( __( 'Shopping cart by <a href="http://graphpaperpress.com/plugins/sell-media/" title="Sell Media WordPress plugin">Sell Media</a>', 'sell_media' ) );
    }
}


/**
 * Calculates the price of an item based on qty, license id and price id
 *
 * @param $item array containing the following
 * ['price_id']['id']
 * ['price_id']['quantity']
 * ['license_id']['quantity']
 * @return Download size, dollar amount, markup percent, license name, quantity
 */
function sell_media_cart_price( $item=array() ){

    $filtered_price = apply_filters( 'sell_media_filtered_price', $item['price_id'] );

    $qty = is_array( $item['price_id'] ) ? $item['price_id']['quantity'] : '1';

    $default_price_array = get_option('sell_media_size_settings');

    /**
     * @todo Make size array repeatable and fully db driven
     */
    $sizes_array = array(
        'sell_media_small_file',
        'sell_media_medium_file',
        'sell_media_large_file',
        'sell_media_original_file',
    );
    if ( in_array( $filtered_price, $sizes_array ) ){
        switch( $item['price_id'] ){
            case 'sell_media_small_file':
                $price = sell_media_item_price( $item['item_id'], $currency=false, $size='small', $echo=false );
                $size = "Small";
                break;
            case 'sell_media_medium_file':
                $price = sell_media_item_price( $item['item_id'], $currency=false, $size='medium', $echo=false );
                $size = "Medium";
                break;
            case 'sell_media_large_file':
                $price = sell_media_item_price( $item['item_id'], $currency=false, $size='large', $echo=false );
                $size = "Large";
                break;
            default:
                $price = sell_media_item_price( $item['item_id'], $currency=false, $size=null, $echo=false );
                $size = "Original";
                break;
        }
    } else {
        $total = $filtered_price * $qty;
        $price = $filtered_price;
    }

    $license_obj = empty( $item['license_id'] ) ? null : get_term_by( 'id', $item['license_id'], 'licenses' );
    $price = array(
        'size' => empty( $size ) ? null : sprintf( "%s: %s", __("Size", "sell_media"), $size ),
        'amount' => sprintf("%0.2f",$price),
        'total' => empty( $total ) ? sprintf("%0.2f",$price) : sprintf("%0.2f",$total),
        'markup' => empty( $license_obj ) ? null : str_replace( '%', '', sell_media_get_term_meta( $license_obj->term_id, 'markup', true ) ),
        'license' => empty( $license_obj ) ? null : sprintf( "%s: %s", __("License", "sell_media"), $license_obj->name ),
        'qty' => $qty
        );

    return $price;
}


/**
 * This function determines the sizes and prices available for purchase and prints out the HTML.
 * If no sizes are available the original price is displayed.
 */
function sell_media_item_prices( $post ){

    $wp_upload_dir = wp_upload_dir();
    $mime_type = wp_check_filetype( $wp_upload_dir['basedir'] . SellMedia::upload_dir . '/' . get_post_meta( $post->ID, '_sell_media_attached_file', true ) );
    $html = null;

    if ( in_array( $mime_type['type'], array( 'image/jpeg', 'image/png', 'image/gif', 'image/bmp', 'image/tiff' ) ) ) {

        $download_sizes = array_merge( sell_media_image_sizes( $post->ID, false ), sell_media_original_image_size( $post->ID, false ) );

        foreach( $download_sizes as $k => $v ){
            $name = ucfirst( $k );
            $html .= '<li class="price">';
            $html .= '<span class="title">' . __( $name, 'sell_media' ) . ' (' . $download_sizes[ $k ]['width'] . ' x ' . $download_sizes[ $k ]['height'] . '): </span>';
            $k = ( $k == 'original' ) ? null : $k;
            $html .= sell_media_item_price( $post->ID, true, $k, false );
            $html .= '</li>';
        }
    } else {
        $html .= '<li class="price">';
        $html .= '<span class="title">'.__( 'Original', 'sell_media' ) . '</span>';
        $html .= sell_media_item_price( $post->ID, true, null, false );
        $html .= '</li>';
    }

    print apply_filters( 'sell_media_item_prices_filter', $html, $post->ID );

}
