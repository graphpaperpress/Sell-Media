<?php

/**
 * Template Tags
 *
 * @package Sell Media
 * @author Thad Allender <support@graphpaperpress.com>
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

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
    $html = '<a href="javascript:void(0)" title="' . $text . '" data-sell_media-product-id="' . esc_attr( $post_id ) . '" data-sell_media-thumb-id="' . esc_attr( $thumb_id ) . '" class="sell-media-cart-trigger sell-media-' . $button . '">' . $text . '</a>';

    if ( $echo )
        echo $html;
    else
        return $html;
}


/**
 * Determines the image source for a product
 * @return (string) url to product image or feature image
 */
function sell_media_item_image_src( $post_id=null ) {

    $attachment_id = get_post_meta( $_POST['product_id'], '_sell_media_attachment_id', true );
    $image = wp_get_attachment_image_src( $attachment_id, 'medium' );
    $featured_image_id = get_post_thumbnail_id( $_POST['product_id'] );
    $featured_image = wp_get_attachment_image_src( $featured_image_id, 'medium' );

    if ( $image[0] )
        $image = $image[0];
    elseif ( $featured_image[0] )
        $image = $featured_image[0];
    else
        $image = wp_mime_type_icon();

    return $image;
}


/**
 * Echo the attachment thumbnail image. Used in ajax calls in admin.
 * @param $attachment_id
 * @return (html) image
 */
function sell_media_item_get_thumbnail( $attachment_id=null ){
    // ajax on single item addition page
    if ( ! empty( $_POST['attachment_id'] ) )
        $attachment_id = $_POST['attachment_id'];
    $image_attributes = wp_get_attachment_image_src( $attachment_id );
    $image = '<img src="' . $image_attributes[0] . '" width="' . $image_attributes[1] . '" height="' . $image_attributes[2] . '" />';
    echo $image;
    // we're going ajax, so we must die
    if ( ! empty( $_POST['action'] ) && $_POST['action'] == 'sell_media_item_get_thumbnail' )
        die();
}
add_action( 'wp_ajax_sell_media_item_get_thumbnail', 'sell_media_item_get_thumbnail' );


/**
 * Returns the file extension of the product file
 * @return (string) file extension
 */
function sell_media_get_filetype( $post_id=null ){
    $filetype = wp_check_filetype( get_post_meta( $post_id, '_sell_media_attached_file', true ) );
    return $filetype['ext'];
}


/**
 * Determines the image used to represent an item for sale. If an
 * image mime type is detected than the attachment image is used.
 *
 * @return (string) an image tag
 */
function sell_media_item_icon( $post_id=null, $size='medium', $echo=true ){

    $attachment_id = get_post_meta( $post_id, '_sell_media_attachment_id', true );

    // legacy function passed the $attachment_id into sell_media_item_icon
    // that means the above get_post_meta wouldn't exist
    // if that's the case, than we assume the $post_id is actually the $attachment_id
    if ( empty( $attachment_id ) ){
        $attachment_id = $post_id;
    }

    // check if featured image exists
    if ( '' != get_the_post_thumbnail( $post_id ) ) {
        $image = get_the_post_thumbnail( $post_id, $size, array( 'class' => apply_filters( 'sell_media_image_class', 'sell_media_image' ) ) );

    // check if attachment thumbnail exists
    } elseif ( '' != wp_get_attachment_image( $attachment_id ) ) {
        $image = wp_get_attachment_image( $attachment_id, $size, array( 'class' => apply_filters( 'sell_media_image_class', 'sell_media_image' ) ) );

    // use default WP icons
    } else {
        $mime_type = get_post_mime_type( $attachment_id );
        switch ( $mime_type ) {
            case 'image/jpeg':
            case 'image/png':
            case 'image/gif':
                $src = wp_mime_type_icon( 'image/jpeg' ); break;
            case 'video/mpeg':
            case 'video/mp4':
            case 'video/quicktime':
                $src = wp_mime_type_icon( 'video/mpeg' ); break;
            case 'text/csv':
            case 'text/pdf':
            case 'text/plain':
            case 'text/xml':
                $src = wp_mime_type_icon( 'application/pdf' ); break;
            default:
                $src = wp_mime_type_icon(); break;
        }

        $image =  '<img src="' . $src . '" class="'. apply_filters( 'sell_media_image_class', 'sell_media_image' ) . ' wp-post-image" title="' . get_the_title( $post_id ) . '" alt="' . get_the_title( $post_id ) . '" data-sell_media_medium_url="' . $src . '" data-sell_media_large_url="' . $src . '" data-sell_media_item_id="' . $post_id . '" style="max-width:100%;height:auto;"/>';
    }

    if ( $echo )
        echo $image;
    else
        return $image;
}

/**
 * Main content loop used in all themes
 * @return string html
 */
function sell_media_content_loop( $post_id, $i ){
    $class = ( $i %3 == 0 ) ? ' end' : '';

    $html  = '<div id="sell-media-' . $post_id . '" class="sell-media-grid' . $class . '">';
    $html .= '<div class="item-inner">';
    $html .= '<a href="' . get_permalink( $post_id ) . '">' . sell_media_item_icon( $post_id, apply_filters( 'sell_media_thumbnail', 'medium' ), false ) . '</a>';
    $html .= '<span class="item-overlay">';
    $html .= '<h3><a href="' . get_permalink( $post_id ) . '">' . get_the_title( $post_id ) . '</a></h3>';
    $html .= sell_media_item_buy_button( $post_id, 'text', __( 'Buy' ), false );
    $html .= apply_filters( 'sell_media_item_overlay', $output='', $post_id );
    $html .= '</span>';
    $html .= '</div>';
    $html .= '</div>';

    return $html;
}
add_filter('sell_media_content_loop', 'sell_media_content_loop', 10, 2);


/**
 * Retrives the lowest price available of an item from the price groups
 *
 * @param $post_id (int) The post_id, must be a post type of "sell_media_item"
 * @return Lowest price of an item
 */
function sell_media_item_min_price( $post_id=null ){
    $value = get_post_meta( $post_id, 'sell_media_free_downloads', true );
    $price = Sell_Media()->products->get_lowest_price( $post_id );
    if ( empty( $price ) ) {
        $settings = sell_media_get_plugin_options();
        $price = $settings->default_price;
    }
    if ( isset( $value ) && "on" == $value ) {
        return "0.00";
    } else {
        return $price;
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

    if ( $settings->plugin_credit ) {
        printf( __( '<span id="sell-media-credit" class="sell-media-credit">Photo cart by <a href="%1$s" title="Photo cart">%2$s</a></span>', 'sell_media' ), 'http://graphpaperpress.com/plugins/sell-media/', 'Sell Media' );
    }
}


/**
 * Gets the except of a post by post id
 *
 * @since 1.8.5
 * @author Thad Allender
 */
function sell_media_get_excerpt( $post_id, $excerpt_length = 140, $trailing_character = '&nbsp;&hellip;' ) {
    $the_post = get_post( $post_id );
    $the_excerpt = strip_tags( strip_shortcodes( $the_post->post_excerpt ) );

    if ( empty( $the_excerpt ) )
      $the_excerpt = strip_tags( strip_shortcodes( $the_post->post_content ) );

    $words = explode( ' ', $the_excerpt, $excerpt_length + 1 );

    if( count( $words ) > $excerpt_length )
      $words = array_slice( $words, 0, $excerpt_length );

    $the_excerpt = implode( ' ', $words ) . $trailing_character;
    return $the_excerpt;
}


/**
 * Prints a semantic list of Collections, with "Collection" as the
 * title, the term slug is used for additional styling of each li
 * and a sell_media-last class is used for the last item in the list.
 *
 * @since 0.1
 */
function sell_media_get_taxonomy_terms( $taxonomy ){
    global $post;

    $terms = wp_get_post_terms( $post->ID, $taxonomy );

    if ( empty( $terms ) || is_wp_error( $terms ) )
        return;

    $html = null;

    foreach( $terms as $term ) {

        $html .= '<a href="' . get_term_link( $term->slug, $taxonomy ) . '" title="' . $term->description . '">';
        $html .= $term->name;
        $html .= '</a> ';

    }

    return apply_filters( 'sell_media_get_taxonomy_terms', $html );
}


/**
 * Put the cart dialog markup in the footer
 *
 * @since 1.8.5
 */
function sell_media_cart_dialog(){
    $settings = sell_media_get_plugin_options();
    if ( ! is_page( $settings->checkout_page ) || ! is_page( $settings->login_page ) || ! is_page( $settings->dashboard_page ) ) : ?>
        <div id="sell-media-dialog-box" class="sell-media-dialog-box" style="display:none">
            <div id="sell-media-dialog-box-target"></div>
        </div>
        <div id="sell-media-dialog-overlay" class="sell-media-dialog-overlay" style="display:none"></div>
    <?php endif; ?>
    <?php if ( is_page( $settings->checkout_page ) && ! empty ( $settings->terms_and_conditions ) ) : ?>
        <div id="sell-media-empty-dialog-box" class="sell-media-dialog-box sell-media-dialog-box-terms" style="display:none">
            <span class="close">&times;</span>
            <div class="content">
                <p><?php echo stripslashes_deep( nl2br( $settings->terms_and_conditions ) ); ?></p>
            </div>
        </div>
        <div id="sell-media-empty-dialog-overlay" class="sell-media-dialog-overlay" style="display:none"></div>
    <?php endif;
}
add_action( 'wp_footer', 'sell_media_cart_dialog' );


/**
 * Taxonomy Breadcrumbs
 *
 * @since 1.9.5
 */
function sell_media_taxonomy_breadcrumb() {
    // Get the current term
    $term = get_term_by( 'slug', get_query_var( 'term' ), get_query_var( 'taxonomy' ) );

    // Create a list of all the term's parents
    $parent = $term->parent;
    while ( $parent ) :
        $parents[]  = $parent;
        $new_parent = get_term_by( 'id', $parent, get_query_var( 'taxonomy' ) );
        $parent     = $new_parent->parent;
    endwhile;

    if ( ! empty( $parents ) ) :
        $parents = array_reverse( $parents );

        // For each parent, create a breadcrumb item
        foreach ( $parents as $parent ) :
            $item   = get_term_by( 'id', $parent, get_query_var( 'taxonomy' ) );
            echo '<li><a href="' . esc_url( get_term_link( $item->term_id, get_query_var( 'taxonomy' ) ) ) . '">' . $item->name . '</a> <span class="raquo">&raquo;</span> </li>';
        endforeach;
    endif;

    // Display the current term in the breadcrumb
    echo '<li><a href="' . esc_url( get_term_link( $term->term_id, get_query_var( 'taxonomy' ) ) ) . '">' . $term->name . '</a></li>';
}

/**
 * Breadcrumb navigation on single entries
 *
 * @since 1.9.2
 * @global $post
 *
 * @return string the breadcrumb navigation
 */
function sell_media_breadcrumbs( $post_id ){
    if ( is_post_type_archive( 'sell_media_item' ) || is_search() )
        return;

    $settings = sell_media_get_plugin_options();

    if ( isset( $settings->breadcrumbs ) && $settings->breadcrumbs ) {
        $obj = get_post_type_object( 'sell_media_item' );

        $html = '<div class="sell-media-breadcrumbs">';
        $html .= '<a href="' . get_post_type_archive_link( 'sell_media_item' ) . '">' . $obj->rewrite['slug'] . '</a>';
        $html .= ' <span class="sell-media-breadcrumbs-sep">&raquo;</span> ';
        if ( wp_get_post_terms( $post_id, 'collection' ) ) {
            $html .= sell_media_get_taxonomy_terms( 'collection' );
            $html .= ' <span class="sell-media-breadcrumbs-sep">&raquo;</span> ';
        }
        $html .= get_the_title( '', false );
        $html .= '</div>';

        return apply_filters( 'sell_media_breadcrumbs', $html );
    }
}


/**
 * Count posts in a category, including subcategories
 *
 * @since 1.9.5
 */
function sell_media_get_cat_post_count( $category_id, $taxonomy='collection' ) {

    $cat = get_category( $category_id );
    $count = 0;
    $args = array(
      'child_of' => $category_id,
    );
    $tax_terms = get_terms( $taxonomy, $args );
    foreach ( $tax_terms as $tax_term ) {
        $count += $tax_term->count;
    }

    return $count;
}

/**
 * Filter the_content for sell_media_item post types
 * and add an action before the content so we can do stuff.
 *
 * @since 1.9.2
 * @global $post
 *
 * @param $content The the_content field of the sell_media_item object
 * @return string the content with any additional data attached
 */
function sell_media_before_content( $content ) {
    global $post;
    $sell_media_taxonomies = get_object_taxonomies( 'sell_media_item' );

    if ( $post && $post->post_type == 'sell_media_item' && is_main_query() && ! post_password_required() ) {
        ob_start();
        do_action( 'sell_media_before_content', $post->ID );
        if ( is_post_type_archive( 'sell_media_item' ) || is_tax( $sell_media_taxonomies ) ) {
            $content = '<div class="sell-media-content">' . ob_get_clean() . $content . '</div>';
        } else {
            $content = sell_media_breadcrumbs( $post->ID ) . '<div class="sell-media-content">' . ob_get_clean() . $content . '</div>';
        }
    }

    return $content;
}
add_filter( 'the_content', 'sell_media_before_content' );

/**
 * Filter the_content on single templates for sell_media_item post types
 * and add an action after the content so we can do stuff.
 *
 * @since 1.9.2
 * @global $post
 *
 * @param $content The the_content field of the download object
 * @return string the content with any additional data attached
 */
function sell_media_after_content( $content ) {
    global $post;

    if ( $post && $post->post_type == 'sell_media_item' && is_main_query() && ! post_password_required() ) {
        ob_start();
        do_action( 'sell_media_after_content', $post->ID );
        $content .= ob_get_clean();
    }

    return $content;
}
add_filter( 'the_content', 'sell_media_after_content' );

/**
 * Add media (featured image, etc) before the_content
 *
 * @since 1.9.2
 * @global $post
 *
 * @param $content The the_content field of the item object
 * @return string the content with any additional data attached
 */
function sell_media_append_media( $post_id ) {
    $sell_media_taxonomies = get_object_taxonomies( 'sell_media_item' );
    if ( is_post_type_archive( 'sell_media_item' ) || is_tax( $sell_media_taxonomies ) || is_search() ) {
        echo '<a href="' . get_permalink( $post_id ) . '">' . sell_media_item_icon( $post_id, 'large', false ) . '</a>';
    } elseif ( is_singular( 'sell_media_item' ) ) {
        sell_media_item_icon( $post_id, 'large' );
    }
}
add_action( 'sell_media_before_content', 'sell_media_append_media', 10 );

/**
 * Append meta data
 *
 * Append buy button and add action to append more stuff (lightbox, keywords, etc)
 *
 * @since 1.9.2
 * @param int $post_id Item ID
 * @return void
 */
function sell_media_append_meta( $post_id ) {
    $sell_media_taxonomies = get_object_taxonomies( 'sell_media_item' );

    if ( is_post_type_archive( 'sell_media_item' ) || is_tax( $sell_media_taxonomies ) || is_search() ) {
        echo '<p>' . sell_media_item_buy_button( $post_id, 'text', __( 'Buy', 'sell_media' ), false ) . ' | <a href="javascript:void(0);" title="' . sell_media_get_lightbox_text( $post_id ) . '" class="add-to-lightbox" id="lightbox-' . $post_id . '" data-id="' . $post_id . '">' . sell_media_get_lightbox_text( $post_id ) . '</a> | <a href="' . get_permalink( $post_id ) . '" class="sell-media-permalink">' . __( 'More', 'sell_media' ) . ' &raquo;</a></p>';
    } elseif ( is_singular( 'sell_media_item' ) ) {
        echo '<div class="sell-media-meta">';
        echo '<p class="sell-media-buy-button">';
        echo sell_media_item_buy_button( $post_id, 'button', __( 'Buy', 'sell_media' ), false );
        echo '</p>';
        do_action( 'sell_media_below_buy_button', $post_id );
        sell_media_plugin_credit();
        echo '</div>';
    }
}
add_action( 'sell_media_after_content', 'sell_media_append_meta', 20 );

/**
 * Show lightbox
 *
 * @since 1.9.2
 * @param int $post_id Item ID
 * @return void
 */
function sell_media_show_lightbox( $post_id ) {
    echo '<p class="sell-media-lightbox"><a href="javascript:void(0);" title="' . sell_media_get_lightbox_text( $post_id ) . '" class="add-to-lightbox" id="lightbox-' . $post_id . '" data-id="' . $post_id . '">' . sell_media_get_lightbox_text( $post_id ) . '</a></p>';
}
add_action( 'sell_media_below_buy_button', 'sell_media_show_lightbox', 10 );

/**
 * Show additional file info
 *
 * @since 1.9.2
 * @param int $post_id Item ID
 * @return void
 */
function sell_media_show_file_info( $post_id ){
    echo '<h2 class="widget-title">' . __( 'Details', 'sell_media' ) . '</h2>';
    echo '<ul>';
    echo '<li class="filename"><span class="title">' . __( 'File ID', 'sell_media' ) . ':</span> ' . $post_id . '</li>';
    echo '<li class="filetype"><span class="title">' . __( 'File Type', 'sell_media' ) . ':</span> ' . sell_media_get_filetype( $post_id ) . '</li>';
    if ( wp_get_post_terms( $post_id, 'collection' ) ) {
        echo '<li class="collections"><span class="title">' . __( 'Collections', 'sell_media' ) . ':</span> ' . sell_media_get_taxonomy_terms( 'collection' ) . '</li>';
    }
    if ( wp_get_post_terms( $post_id, 'keywords' ) ) {
        echo '<li class="keywords"><span class="title">' . __( 'Keywords', 'sell_media' ) . ':</span> ' . sell_media_get_taxonomy_terms( 'keywords' ) . '</li>';
    }
    echo do_action( 'sell_media_additional_list_items', $post_id );
    echo '</ul>';
}
add_action( 'sell_media_below_buy_button', 'sell_media_show_file_info', 12 );

/**
 * Adds Sell Media Version to the <head> tag
 *
 * @since 1.9.2
 * @return void
*/
function sell_media_version_in_header(){
    echo '<meta name="generator" content="Sell Media v' . SELL_MEDIA_VERSION . '" />' . "\n";
}
add_action( 'wp_head', 'sell_media_version_in_header' );

/**
 * Return the name of the template served
 *
 * @since 1.9.2
 * @return string
 */
function sell_media_return_template() {
    global $template;
    return basename( $template );
}

/**
 * Theme setup tweaks
 *
 * @since 1.9.2
 * @return string
 */
function sell_media_theme_setup(){

    /**
     * Don't filter the_content if custom template is used.
     * Prevents duplicate content on the_content filter.
     * For backwards compatibility in case users upgrade Sell Media
     * but have old themes with custom Sell Media templates.
     */
    if ( 'single-sell_media_item.php' == sell_media_return_template() ) {
        remove_filter( 'the_content', 'sell_media_before_content' );
        remove_filter( 'the_content', 'sell_media_after_content' );
    }
    if ( 'archive-sell_media_item.php' == sell_media_return_template() ) {
    }
}
add_action( 'wp_head', 'sell_media_theme_setup', 999 );

/**
 * Check for Sell Media theme supprt
 * @return boolean
 */
function sell_media_theme_support() {
    if ( current_theme_supports( 'sell_media' ) ) {
        return true;
    }
}
add_action( 'after_setup_theme', 'sell_media_theme_support', 999 );