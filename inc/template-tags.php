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
    $text = apply_filters('sell_media_purchase_text', $text, $post_id );
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
 * Retrieves and prints the price of an item
 *
 * @since 0.1
 * @param $post_id The Item ID
 * @param $currency (bool) Display the currency symbol or not
 * @param $size (string) small, medium, large, null (for original)
 * @param $echo Either print the result or return it
 * @return string
 */
function sell_media_item_price( $post_id=null, $currency=true, $size=null, $echo=true ){

    /**
     * Get the unique price of the item if it exists
     * Otherwise, use the default price from Settings
     */

    $price = get_post_meta( $post_id, 'sell_media_price', true );
    $settings = sell_media_get_plugin_options();

    // if the item does not have specific price set, use default from settings
    if ( empty( $price ) )
        $price = $settings->default_price;
    // show the currency symbol if currency is set to true
    if ( $currency )
        $price = sell_media_get_currency_symbol() . sprintf( '%0.2f', $price );
    // echo or return the price
    if ( $echo )
        echo $price;
    else
     return $price;
}


/**
 * Determines the default icon used for an Attachment. If an
 * image mime type is detected than the attachment image is used.
 */
function sell_media_item_icon( $attachment_id=null, $size='medium', $echo=true ){

    if ( ! empty( $_POST['attachment_id'] ) ){
        $attachment_id = $_POST['attachment_id'];
    }

    if ( empty( $attachment_id ) )
        return;

    if ( ! empty( $_POST['attachment_size'] ) )
        $size = $_POST['attachment_size'];

    $mime_type = get_post_mime_type( $attachment_id );
    $image_height = $image_width = null;
    $post_id = get_post_meta( $attachment_id, '_sell_media_for_sale_product_id', true );
    $image_title = get_the_title( $post_id );
    $_thumbnail_id = get_post_thumbnail_id( $post_id );


    /**
     * Since we always want to return the actual image associated with this item for sale
     * on the edit/add new item page. We check the global $pagenow variable, vs. adding
     * coniditionals through out the code.
     */
    global $pagenow;
    global $post_type;
    if ( ! empty( $_thumbnail_id )
        && $post_type == 'sell_media_item'
        && $pagenow != 'post.php'
        || ! empty( $_thumbnail_id ) ){
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

    $icon =  '<img src="' . $image_src . '" class="sell_media_image wp-post-image" title="' . $image_title . '" alt="' . $image_title . '" data-sell_media_medium_url="' . $medium_url . '" data-sell_media_item_id="' . $post_id . '" height="' . $image_height . '" width="' . $image_width . '" style="max-width:100%;height:auto;"/>';

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
    $settings = sell_media_get_plugin_options();
    $licenses = wp_get_post_terms( $_POST['product_id'], 'licenses' );
    $attachment_id = get_post_meta( $_POST['product_id'], '_sell_media_attachment_id', true );
    $disabled = null;
    $price = sell_media_item_price( $_POST['product_id'], $currency=false, false, false);
    $subtotal = empty( $_SESSION['cart']['subtotal'] ) ? "0.00" : $_SESSION['cart']['subtotal'];

    if ( $licenses ) {
        $term_id = $licenses[0]->term_id;
    } else {
        $term_id = null;
    }

    ?>
    <?php do_action( 'sell_media_above_item_form' ); ?>
    <form action="javascript://" method="POST" class="sell-media-dialog-form sell-media-form">
        <input type="hidden" name="AttachmentID" value="<?php print $attachment_id; ?>" />
        <input type="hidden" name="ProductID" value="<?php print $_POST['product_id']; ?>" />
        <input type="hidden" name="CalculatedPrice" class="" value="<?php sell_media_item_price( $_POST['product_id'], $currency=false); ?>" data-price="<?php sell_media_item_price( $_POST['product_id'], $currency=false); ?>" />
        <?php wp_nonce_field('add_items','sell_media_nonce'); ?>

        <?php do_action( 'sell_media_cart_above_licenses' ); ?>

        <?php if ( sell_media_is_mimetype( get_post_meta( $_POST['product_id'], '_sell_media_attachment_id', true ) ) ) : ?>
            <?php $disabled = 'disabled'; $price = "0.00"; ?>
            <fieldset>
                <legend><?php _e('Size', 'sell_media'); ?></legend>
                <select id="sell_media_size_select" name="price_id">
                    <option value="" data-price="0">-- <?php _e( 'Select a size', 'sell_media' ); ?> --</option>
                    <?php if ( ! empty( $sizes_array ) ) : foreach( $sizes_array as $k => $v ) : ?>
                        <option value="<?php echo $k; ?>" data-price="<?php echo $v['price']; ?>"><?php echo $v['name']; ?> (<?php echo $v['width'] . ' x ' . $v['height']; ?>): <?php echo sell_media_get_currency_symbol() . sprintf( '%0.2f', $v['price'] ); ?></option>
                    <?php endforeach; endif; ?>
                    <?php if ( $settings->hide_original_price !== 'yes' ) : ?>
                        <option value="sell_media_original_file" data-price="<?php sell_media_item_price( $_POST['product_id'], false ); ?>">
                            <?php _e( 'Original', 'sell_media' ); ?>
                            (<?php print sell_media_original_image_size( $_POST['product_id'] ); ?>):
                            <?php sell_media_item_price( $_POST['product_id'] ); ?>
                        </option>
                    <?php endif; ?>
                </select>
            </fieldset>
        <?php else : ?>
            <input type="hidden" id="sell_media_price" name="price_id" data-price="<?php sell_media_item_price( $_POST['product_id'], false ); ?>" value="default_price" />
        <?php endif; ?>

        <?php do_action( 'sell_media_cart_below_licenses' ); ?>


        <?php do_action( 'sell_media_cart_above_size' ); ?>
        <?php if ( count( $licenses ) > 1 ) : ?>
            <fieldset>
                <legend><?php _e( 'License', 'sell_media' ); ?></legend>
                <select name="License" value="License" id="sell_media_license_select" <?php if ( ! empty( $terms ) ) : ?>disabled<?php endif; ?>>
                    <option value="" data-price="0" title="Select a license to learn more about each license.">-- <?php _e( 'Select a license', 'sell_media'); ?> --</option>
                    <?php sell_media_build_options( array( 'post_id' => $_POST['product_id'], 'taxonomy' => 'licenses', 'type'=>'select' ) ); ?>
                </select>
                <div class="license_desc sell-media-tooltip" data-tooltip="<?php _e( 'Select a license to learn more about each license.', 'sell_media' ); ?>"> <?php _e( 'View Details', 'sell_media' ); ?></div>
            </fieldset>
        <?php else : ?>
            <?php if ( ! empty( $term_id ) ) : ?>
                <input id="sell_media_single_price" type="hidden" name="License" value="<?php print $term_id; ?>" data-price="<?php sell_media_item_price( $_POST['product_id'], $currency=false); ?>" />
                <input type="hidden" value="<?php print str_replace('%', '', sell_media_get_term_meta( $licenses[0]->term_id, 'markup', true ) ); ?>" id="sell_media_single_license_markup" />
                <div class="license_text"><?php _e( 'License', 'sell_media'); ?>: <?php print $licenses[0]->name; ?></div>
                <?php if ( ! empty( $licenses[0]->description ) ) : ?>
                    <div class="license_desc sell-media-tooltip" data-tooltip="<?php print esc_attr( $licenses[0]->description ); ?>"><?php _e( 'View Details', 'sell_media' ); ?></div>
                <?php endif; ?>
            <?php endif; ?>
        <?php endif; ?>
        <?php do_action( 'sell_media_cart_below_size' ); ?>

        <div class="total-container group">
            <div class="left">
                <strong><?php _e( 'Total' ); ?></strong>
            </div>
            <div class="right">
                <span class="price-container"><?php print sell_media_get_currency_symbol(); ?><span class="sell-media-item-price"><?php print $subtotal; ?></span></span>
            </div>
        </div>
        <div class="button-container group">
            <div class="left">
                <?php if ( empty( $_SESSION['cart']['items']) ) : ?>
                    <span class="cart empty"><?php _e( 'Cart', 'sell_media' ); ?> (0)</span>
                <?php else: ?>
                    <span class="cart full"><a href="<?php print get_permalink( $settings->checkout_page ); ?>" class="cart-handle"><?php _e( 'Cart', 'sell_media' ); ?> (<span class="count-container"><span class="count-target"></span></span>)</a></span>
                <?php endif; ?>
                <a href="<?php print get_permalink( $settings->checkout_page ); ?>" class="cart-handle" style="display: none;"><?php _e( 'Cart', 'sell_media' ); ?></a>
            </div>
            <div class="right">
                <input type="submit" value="<?php print apply_filters('sell_media_add_to_cart_text', __('Add to Cart', 'sell_media'), $_POST['product_id'] ); ?>" class="sell-media-buy-button" <?php print $disabled; ?> />
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
 *
 * @return Prints an li or returns an array of available download sizes
 */
function sell_media_image_sizes( $post_id=null, $echo=true ){

    $download_sizes = sell_media_get_downloadable_size( $post_id );

    if ( empty( $download_sizes ) ) return;

    $html = null;
    if ( $echo ){
        foreach( $download_sizes as $k => $v ){
            $html .= '<li class="price">';
            $html .= '<span class="title"> '.$download_sizes[ $k ]['name'].' (' . $download_sizes[ $k ]['width'] . ' x ' . $download_sizes[ $k ]['height'] . '): </span>';
            $html .= sell_media_get_currency_symbol() . sprintf( '%0.2f', $download_sizes[ $k ]['price'] );
            $html .= '</li>';
        }

        $original_size = sell_media_original_image_size( $post_id, false );
        $mime_type = wp_check_filetype( wp_get_attachment_url( get_post_meta( $post_id, '_sell_media_attachment_id', true ) ) );
        $image_mimes = array(
            'image/jpg',
            'image/jpeg',
            'image/png',
            'image/gif',
            'image/bmp',
            'image/tiff'
            );

        if ( in_array( $mime_type['type'], $image_mimes ) ){
            $og_size = ' (' . $original_size['original']['width'] . ' x ' . $original_size['original']['height'] . ')';
        } else {
            $og_size = null;
        }

        $settings = sell_media_get_plugin_options();
        if ( $settings->hide_original_price !== 'yes' ){
            $html .= '<li class="price">';
            $html .= '<span class="title">'.__( 'Original', 'sell_media' ) . $og_size . '</span>: ';
            $html .= sell_media_item_price( $post_id, true, null, false );
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
 * @todo This function, sell_media_item_price(), and anything related
 * to price should be in the class-price.php file
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
    $settings = sell_media_get_plugin_options();

    if ( true == $settings->plugin_credit ) {
        printf( '%s <a href="http://graphpaperpress.com/plugins/sell-media/" title="Sell Media WordPress plugin">Sell Media</a>', __( 'Shopping cart by ', 'sell_media' ) );
    }
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
        $terms = wp_get_post_terms( $post->ID, 'price-group' );

        if ( empty( $terms ) ){
            $default = get_term_by( 'name', 'Default', 'price-group' );
            $terms = get_terms( 'price-group', array( 'hide_empty' => false, 'parent' => $default->term_id ) );
        }

        foreach( $terms as $term ){
            if ( $term->parent != 0 ){
                $width = sell_media_get_term_meta( $term->term_id, 'width', true );
                $height = sell_media_get_term_meta( $term->term_id, 'height', true );
                $price = sell_media_get_term_meta( $term->term_id, 'price', true );

                $html .= '<li class="price">';
                $html .= '<span class="title">' . $term->name . ' (' . $width . ' x ' . $width . '): </span>';
                $html .= sell_media_get_currency_symbol() . sprintf( '%0.2f', $price );
                $html .= '</li>';
            }
        }
        $original_size = sell_media_original_image_size( $post->ID, false );
        $og_size = ' (' . $original_size['original']['width'] . ' x ' . $original_size['original']['height'] . ')';
    } else {
        $og_size = null;
    }

    $html .= '<li class="price">';
    $html .= '<span class="title">'.__( 'Original', 'sell_media' ) . $og_size . '</span>: ';
    $html .= sell_media_item_price( $post->ID, true, null, false );
    $html .= '</li>';

    print apply_filters( 'sell_media_item_prices_filter', $html, $post->ID );

}


/**
 * Retrives the lowest price available of an item from the price groups
 *
 * @param $post_id (int) The post_id, must be a post type of "sell_media_item"
 * @return Lowest price of an item
 */
function sell_media_item_min_price( $post_id=null, $echo=true, $key='price' ){

    $terms = wp_get_post_terms( $post_id, 'price-group' );

    $prices[] = '';

    $original_price = get_post_meta( $post_id, 'sell_media_price', true );
    if ( ! empty( $original_price ) ){
        $prices[] = $original_price;
    }

    if ( ! empty( $terms ) ){
        foreach( $terms as $term ){
            if ( $term->parent != 0 ){
                $price = sell_media_get_term_meta( $term->term_id, $key, true );
                $prices[] = $price;
            }
        }
    }

    return ( $echo ) ? printf( sell_media_get_currency_symbol() .'%0.2f', min( $prices ) ) : min( $prices );
}



/**
 * @param $post_id (int) The post to a sell media item post type
 * @param $term_id (int) The term id for a term from the price-group taxonomy
 *
 * @return Array of downloadable sizes or single size if $term_id is present
 */
function sell_media_get_downloadable_size( $post_id=null, $term_id=null, $size_not_available=null ){
    $attached_file = get_post_meta( $post_id, '_sell_media_attached_file', true );
    $wp_upload_dir = wp_upload_dir();
    $attached_path_file  = $wp_upload_dir['basedir'] . SellMedia::upload_dir . '/' . $attached_file;
    $parent_price_group = null;

    // Fix for legacy code?
    $attached_file_fix = file_exists( $attached_path_file );

    if ( ! $attached_file_fix ){
        @list( $broken, $url, $attached_file ) = explode( 'uploads/', $attached_path_file );
        $attached_path_file = $wp_upload_dir['basedir'] . SellMedia::upload_dir . '/' . $attached_file;
    }

    list( $orig_w, $orig_h, $type, $attr ) = @getimagesize( $attached_path_file );

    $null = null;
    $original = $download_sizes = array();
    list( $original['url'], $original['width'], $original['height'] ) = wp_get_attachment_image_src( get_post_meta( $post_id, '_sell_media_attachment_id', true ), 'full' );

    /**
     * Loop over price groups checking for children,
     * compare the width and height assigned to a price group
     * with the width and height of the current image. Remove
     * sizes that are not downloadable.
     */
    $cart = New Sell_Media_Cart;
    $price_groups = sell_media_get_price_groups( $post_id = $post_id, $taxonomy = 'price-group' );
    if ( ! empty( $price_groups ) ){
        foreach( $price_groups as $price ){

            /**
             * Check for children only
             */
            if ( $price->parent > 0 ){

                /**
                 * Retrieve the height and width for our price group
                 */
                $pg_width = sell_media_get_term_meta( $price->term_id, 'width', true );
                $pg_height = sell_media_get_term_meta( $price->term_id, 'height', true );

                /**
                 * Build our array to be returned, the downloadable width and height
                 * are calculated later and added to this array
                 */
                $download_sizes[ $price->term_id ] = array(
                    'name' => $price->name,
                    'price' => $cart->item_price( $post_id, $price->term_id )
                    );

                /**
                 * Calculate dimensions and coordinates for a resized image that fits
                 * within a specified width and height. If $crop is true, the largest
                 * matching central portion of the image will be cropped out and resized
                 * to the required size.
                 */
                list( $null, $null, $null, $null, $download_sizes[ $price->term_id ]['width'], $download_sizes[ $price->term_id ]['height'] ) = image_resize_dimensions( $orig_w, $orig_h, $pg_width, $pg_height, $crop=false );

                /**
                 * If no width/height can be determined we remove it from our array of
                 * available download sizes.
                 */
                if ( empty( $download_sizes[ $price->term_id ]['width'] ) ) {

                    $unavailable_size[ $price->term_id ] = array(
                        'name' => $download_sizes[ $price->term_id ]['name'],
                        'price' => $download_sizes[ $price->term_id ]['price'],
                        'height' => $pg_height,
                        'width' => $pg_width
                        );

                    unset( $download_sizes[ $price->term_id ] );
                }

                /**
                 * Check for portraits and if the available download size is larger than
                 * the original we remove it.
                 */
                $smallest_height = sell_media_item_min_price( $post_id, false, 'height' );
                if ( $original['height'] > $original['width']
                    && isset( $download_sizes[ $price->term_id ] )
                    && $download_sizes[ $price->term_id ]['height'] <  $smallest_height ){

                        $unavailable_size[ $price->term_id ] = array(
                            'name' => $download_sizes[ $price->term_id ]['name'],
                            'price' => $download_sizes[ $price->term_id ]['price'],
                            'height' => $pg_height,
                            'width' => $pg_width
                            );

                        unset( $download_sizes[ $price->term_id ] );
                }
            }
        }
    }

    if ( ! empty( $size_not_available ) ){
        $sizes = array(
            'available' => $download_sizes,
            'unavailable' => empty( $unavailable_size ) ? null : $unavailable_size
            );
    } elseif ( empty( $term_id ) ) {
        $sizes = $download_sizes;
    } else {
        $sizes = $download_sizes[ $term_id ];
    }

    return $sizes;
}