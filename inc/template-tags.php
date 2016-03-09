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
function sell_media_item_buy_button( $post_id=null, $attachment_id=null, $button=null, $text=null, $echo=true ) {

	$attachment_id = ( empty( $attachment_id ) ) ? sell_media_get_attachment_id( $post_id ) : $attachment_id;
	$text = apply_filters('sell_media_purchase_text', $text, $post_id );
	$html = '<a href="' . esc_url( get_permalink( $post_id ) ). '" title="' . $text . '" data-product-id="' . esc_attr( $post_id ) . '" data-attachment-id="' . esc_attr( $attachment_id ) . '" class="sell-media-' . $button . '">' . $text . '</a>';
	$html = apply_filters( 'sell_media_item_buy_button', $html, $post_id, $attachment_id, $button, $text, $echo );

	if ( $echo )
		echo $html;
	else
		return $html;
}

function sell_media_item_add_to_cart_button( $post_id=null, $attachment_id=null, $button=null, $text=null, $echo=true, $type = 'download' ) {

	if( is_null( $text ) ){
		$text = __( 'Add to cart', 'sell_media' );
	}

	$attachment_id = ( empty( $attachment_id ) ) ? sell_media_get_attachment_id( $post_id ) : $attachment_id;
	// check if is package
    $is_package = Sell_Media()->products->is_package( $post_id );
    // check if has assigned price group
    $has_price_group = Sell_Media()->products->has_price_group( $post_id );
	$text = apply_filters('sell_media_add_to_cart_text', $text, $post_id, $attachment_id, $type );
	$disable = ( ! $is_package && $has_price_group ) ? "disabled":"";

	$classes[] = 'item_add';
	$classes[] = 'sell-media-button';
	if( !is_null( $button ) )
		$classes[] = 'sell-media-' . $button;

	$classes = implode( ' ', $classes );

	$html = '<button class="' . $classes . '" '.$disable.'>' . $text . '</button>';
	$html = apply_filters( 'sell_media_item_add_to_cart_button', $html, $post_id, $attachment_id, $button, $text, $echo, $type );

	if ( $echo )
		echo $html;
	else
		return $html;
}

/**
 * Determines the image source for a product
 * @return (string) url to product image or feature image
 */
function sell_media_item_image_src( $post_id=null, $attachment_id=null ) {
	$size = apply_filters( 'sell_media_thumbnail', 'medium' );

	/**
	 * The $attachment_id might be empty if product doesn't have a featured image
	 * and it was sold from a page without ?id (not a gallery).
	 * If so, derive the $attachment_id from the post_meta of the product.
	 */
	$attachment_id = ( $attachment_id ) ? $attachment_id : sell_media_get_attachment_id( $post_id );

	/**
	 * If the post has multiple attachments, get the attributes of the attachment.
	 * Otherwise, get the attributes of the featured image,
	 * or finally the attached image.
	 */
	
	// is gallery
	if ( sell_media_has_multiple_attachments( $post_id ) ) {
		$image_attributes = wp_get_attachment_image_src( $attachment_id, $size, true );
	// has a featured image
	} elseif ( '' != get_the_post_thumbnail( $post_id ) ) {
		$image_attributes = wp_get_attachment_image_src( get_post_thumbnail_id( $post_id ), $size );
	// no featured image, so get the attachment src from the attached file
	} else {
		$image_attributes = wp_get_attachment_image_src( $attachment_id, $size );
	}

	if ( $image_attributes ) {
		$file_url = $image_attributes[0];
	} else {
		$file_url = wp_mime_type_icon();
	}

	return $file_url;
}


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
		$image = get_the_post_thumbnail( $post_id, $size, array( 'class' => apply_filters( 'sell_media_image_class', 'sell-media-image sell_media_image' ) ) );

	// check if attachment thumbnail exists
	} elseif ( '' != wp_get_attachment_image( $attachment_id ) ) {
		$image_attr = wp_get_attachment_image_src( $attachment_id, $size );
		$src = $image_attr[0];
		$image = wp_get_attachment_image( $attachment_id, $size, '', array( 'class' => apply_filters( 'sell_media_image_class', 'sell-media-image sell_media_image' ), 'data-sell_media_medium_url' => $src, 'data-sell_media_large_url' => $src, 'data-sell_media_item_id' => $post_id ) );
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
 * Gallery
 *
 * If a user has uploaded multiple attachments to the post
 * we display them in a three column gallery. We then add
 * a query variable to all image links which act as both
 * our gallery navigation and a way to conditionally
 * show the large, single photo layout.
 *
 * @param $post_id
 * @return html the gallery html with filter
 * @since 2.0.1
 */
function sell_media_gallery( $post_id ) {
	$html = '';
	if ( sell_media_has_multiple_attachments( $post_id ) ) {

		/**
		 * We pass the attachment id as a query var
		 * So if it exists, we show the attachment image
		 */
		$attachment_id = get_query_var( 'id' );
		if ( ! empty( $attachment_id ) && sell_media_post_exists( $attachment_id ) ) {
			do_action( 'sell_media_above_gallery', $post_id );
			$html .= sell_media_item_icon( $attachment_id, 'large', false );
			$html .= '<p class="sell-media-caption">';
			$html .= '<span class="sell-media-title">' . sell_media_get_attachment_meta( $post_id, 'title' ) . '</span>';
			if ( sell_media_get_attachment_meta( $post_id, 'caption' ) ) {
				$html .= ' &mdash; ';
				$html .= sell_media_get_attachment_meta( $post_id, 'caption' );
			}
			$html .= '</p>';
		}
		/**
		 * If the query var doesn't exist,
		 * show the gallery grid view
		 */
		else {
			$attachment_ids = sell_media_get_attachments ( $post_id );
			$container_class = apply_filters( 'sell_media_grid_item_container_class', 'sell-media-grid-item-container' );
			$html .= '<div id="sell-media-gallery-' . $post_id . '" class="sell-media-gallery '. $container_class .'">';
			if ( $attachment_ids ) foreach ( $attachment_ids as $attachment_id ) {
				$attr = array(
					'class' => 'sell-media-image sell_media_image sell_media_watermark'
				);
				$item_class = apply_filters( 'sell_media_grid_item_class', 'sell-media-grid-item' );
				$html .= '<div id="sell-media-' . $attachment_id . '" class="' . $item_class . ' sell-media-grid-single-item">';
				$html .= '<a href="' . esc_url( add_query_arg( 'id', $attachment_id, get_permalink() ) ) . '" class="sell-media-item">';
				$html .= wp_get_attachment_image( $attachment_id, apply_filters( 'sell_media_thumbnail', 'medium' ), '', $attr );
				$html .= '<div class="sell-media-quick-view" data-product-id="' . esc_attr( $post_id ) . '" data-attachment-id="' . esc_attr( $attachment_id ) . '">' . apply_filters( 'sell_media_quick_view_text', __( 'Quick View', 'sell_media' ) ) . '</div>';
				$html .= '</a>';
				$html .= '</div>';
			}
			$html .= '</div>';
		}
	}
	return apply_filters( 'sell_media_gallery', $html, $post_id );
}

/**
 * Display Gallery Image Navigation
 *
 * @param $attachment_id
 * @return html the previous image / next image links
 * @since 2.0.1
 */
function sell_media_gallery_navigation( $post_id ) {

	if ( sell_media_has_multiple_attachments( $post_id ) && get_query_var( 'id' ) == true ) {

		$attachment_id  = get_query_var( 'id' );
		$attachment_ids = sell_media_get_attachments( $post_id );
		$current_image  = array_search( $attachment_id, $attachment_ids );

		$html = '<div class="sell-media-gallery-nav">';
		$html .= '<a href="' . esc_url( get_permalink() ) . '"class="sell-media-gallery-index" title="' . __( 'Back to Thumbnails', 'sell_media' ) . '">' . __( 'Back to Thumbnails', 'sell_media' ) . '</a>';
		if ( array_key_exists( $current_image - 1, $attachment_ids ) )
			$html .= '<a href="' . esc_url( add_query_arg( 'id', $attachment_ids[$current_image - 1], get_permalink() ) ) . '" class="sell-media-gallery-prev" title="' . __( 'Previous Image', 'sell_media' ) . '"><span class="dashicons dashicons-arrow-left-alt2"></span></a>';
		if ( array_key_exists( $current_image + 1, $attachment_ids ) )
			$html .= '<a href="' . esc_url( add_query_arg( 'id', $attachment_ids[$current_image + 1], get_permalink() ) ) . '"class="sell-media-gallery-next" title="' . __( 'Next Image', 'sell_media' ) . '"><span class="dashicons dashicons-arrow-right-alt2"></span></a>';
		$html .= '</div>';

		return $html;
	}
}

/**
 * Main content loop used in all themes
 * @return string html
 */
function sell_media_content_loop( $post_id, $i ){
	$class = apply_filters( 'sell_media_grid_item_class', 'sell-media-grid-item' );
	if ( ! sell_media_has_multiple_attachments( $post_id ) )
		$class .= ' sell-media-grid-single-item';

	$html  = '<div id="sell-media-' . $post_id . '" class="' . $class . '">';
	// if there is a post parent, change link to gallery
	$parent = sell_media_attachment_parent_post( $post_id );
	if ( $parent ) {
		$link = add_query_arg( array(
			'id' => $post_id
		), get_permalink( $parent->ID ) );
		$post_id = $parent->ID;
	} else {
		$link = get_permalink( $post_id );
	}
	$html .= '<a href="' . esc_url( $link ) . '" ' . sell_media_link_attributes( $post_id ) . ' class="sell-media-item">';
	$html .= sell_media_item_icon( $post_id, apply_filters( 'sell_media_thumbnail', 'medium' ), false );
	if ( ! sell_media_has_multiple_attachments( $post_id ) ) {
		$attachment_id = sell_media_get_attachment_id( $post_id );
		$html .= '<div class="sell-media-quick-view" data-product-id="' . esc_attr( $post_id ) . '" data-attachment-id="' . esc_attr( $attachment_id ) . '">' . apply_filters( 'sell_media_quick_view_text', __( 'Quick View', 'sell_media' ) ) . '</div>';
	}
	else {
		$html .= '<div class="sell-media-view-gallery">' . apply_filters( 'sell_media_view_gallery_text', __( 'View Gallery', 'sell_media' ) ) . '</div>';
	}
	$html .= '</a>';
	$html .= '</div>';

	return $html;
}
add_filter( 'sell_media_content_loop', 'sell_media_content_loop', 10, 2 );


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
		printf( __( '<span id="sell-media-credit" class="sell-media-credit">Powered by <a href="%1$s" title="Sell Media">%2$s</a></span>', 'sell_media' ), 'http://graphpaperpress.com/plugins/sell-media/', 'Sell Media' );
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

	if ( count( $words ) > $excerpt_length )
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
function sell_media_breadcrumbs(){
	global $post;
	$post_type = 'sell_media_item';

	if ( $post_type != get_post_type( $post->ID ) || is_post_type_archive( $post_type ) || is_search() )
		return;

	$settings = sell_media_get_plugin_options();

	if ( isset( $settings->breadcrumbs ) && $settings->breadcrumbs ) {
		$obj = get_post_type_object( 'sell_media_item' );

		$html = '<div class="sell-media-breadcrumbs">';
		$html .= '<a href="' . esc_url( home_url() ) . '" title="' . __( 'Home', 'sell_media' ) . '">' . __( 'Home', 'sell_media' ) . '</a>';
		$html .= '<a href="' . get_post_type_archive_link( 'sell_media_item' ) . '" title="' . $obj->rewrite['slug'] . '">' . $obj->rewrite['slug'] . '</a>';
		if ( wp_get_post_terms( $post->ID, 'collection' ) ) {
			$html .= sell_media_get_taxonomy_terms( 'collection' );
		}
		$html .= '</div>';

		return $html;
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
	$new_content = '';
	$sell_media_taxonomies = get_object_taxonomies( 'sell_media_item' );

	if ( $post && $post->post_type == 'sell_media_item' && is_main_query() && ! post_password_required() ) {
		ob_start();
		$new_content .= do_action( 'sell_media_before_content', $post->ID );
		if ( is_post_type_archive( 'sell_media_item' ) || is_tax( $sell_media_taxonomies ) ) {
			$new_content .= '<div class="sell-media-content">';
			$new_content .= ob_get_clean() . $content;
			$new_content .= '</div>';
		} else {
			$new_content .= sell_media_breadcrumbs();
			$new_content .= sell_media_gallery_navigation( $post->ID );
			$new_content .= '<div class="sell-media-content">';
			$new_content .= ob_get_clean() . $content;
			if ( sell_media_has_multiple_attachments( $post->ID ) && get_query_var( 'id' ) == true ) {
				$new_content .= sell_media_below_content_widgets();
			}
			$new_content .= '</div>';
		}
		$content = $new_content;
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
	$html = '';
	$sell_media_taxonomies = get_object_taxonomies( 'sell_media_item' );
	if ( is_post_type_archive( 'sell_media_item' ) || is_tax( $sell_media_taxonomies ) || is_search() ) {
		$html .= '<a href="' . get_permalink( $post_id ) . '" ' . sell_media_link_attributes( $post_id ) . '>' . sell_media_item_icon( $post_id, 'large', false ) . '</a>';
	} elseif ( is_singular( 'sell_media_item' ) ) {
		sell_media_set_post_views( $post_id );
		if ( sell_media_has_multiple_attachments( $post_id ) ) {
			$html .= sell_media_gallery( $post_id );
		} else {
			sell_media_item_icon( $post_id, 'large' );
			$html .= '<p class="sell-media-caption">';
			$html .= '<span class="sell-media-title">' . sell_media_get_attachment_meta( $post_id, 'title' ) . '</span>';
			if ( sell_media_get_attachment_meta( $post_id, 'caption' ) ) {
				$html .= ' &mdash; ';
				$html .= sell_media_get_attachment_meta( $post_id, 'caption' );
			}
			$html .= '</p>';
		}
	}
	echo apply_filters( 'sell_media_append_media_filter', $html, $post_id );
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

	// We're on gallery page, so return
	if ( sell_media_is_gallery_page() )
		return;

	if ( is_post_type_archive( 'sell_media_item' ) || is_tax( $sell_media_taxonomies ) || is_search() ) {
		echo sell_media_item_links( $post_id );
	} elseif ( is_singular( 'sell_media_item' ) ) {
		
		$location = "";

		if( isset( $_GET['id'] ) && '' !== $_GET['id'] ){
			$attachment_id = absint( $_GET['id'] );
		}
		else{
			$attachment_id = get_post_meta( $post_id, '_sell_media_attachment_id', true );
		}
		
		ob_start();

		echo '<div class="sell-media-meta">';
		do_action( 'sell_media_above_buy_button', $post_id );
		do_action( 'sell_media_add_to_cart_fields', $post_id, $attachment_id );
		do_action( 'sell_media_below_buy_button', $post_id );
		sell_media_plugin_credit();
		echo '</div>';

		$cart_markup = ob_get_contents();
		ob_end_clean();

		echo apply_filters( 'sell_media_cart_output', $cart_markup, $post_id, $attachment_id, $location );
	}
}
add_action( 'sell_media_after_content', 'sell_media_append_meta', 20 );

/**
 * Show item links
 *
 * @param $post_id
 * @return $html
 * @since 2.0.1
 */
function sell_media_item_links( $post_id ) {

	$links = sell_media_item_buy_button( $post_id, $attachment_id = '', 'text', __( 'Buy', 'sell_media' ), false ) . ' | ' . sell_media_lightbox_link( $post_id ) . ' | <a href="' . get_permalink( $post_id ) . '" class="sell-media-permalink">' . __( 'More', 'sell_media' ) . ' &raquo;</a>';

	$html  = '<p id="sell-media-item-links-' . $post_id . '" class="sell-media-item-links">';
	$html .= apply_filters( 'sell_media_item_links_filter', $links, $post_id );
	$html .= '</p>';

	return $html;
}

/**
 * Link attributes
 * Use for adding data-attributes for lightboxes, etc.
 *
 * @param $post_id
 * @return $html
 */
function sell_media_link_attributes( $post_id ) {
	return apply_filters( 'sell_media_link_attribute', $attributes='', $post_id );
}

/**
 * Show additional file info
 *
 * @since 1.9.2
 * @param int $post_id Item ID
 * @return void
 */
function sell_media_show_file_info(){

	// Bail if file info isn't set to be shown
	$settings = sell_media_get_plugin_options();
	if ( empty( $settings->file_info ) )
		return;

	$post = get_post();
	$attachment_id = sell_media_get_attachment_id( $post->ID );
	$media_dims = '';
	$meta = wp_get_attachment_metadata( $attachment_id );
	$filename = basename( get_attached_file( $attachment_id ) );
	$postguid = get_the_guid( $attachment_id );

	echo '<h2 class="widget-title sell-media-item-details-title">' . __( 'Details', 'sell_media' ) . '</h2>';
	echo '<ul class="sell-media-item-details">';
	echo '<li class="filename"><span class="title">' . __( 'File Name', 'sell_media' ) . ':</span> ' . $filename . '</li>';
	echo '<li class="fileid"><span class="title">' . __( 'File ID', 'sell_media' ) . ':</span> ' . $attachment_id . '</li>';
	preg_match('/^.*?\.(\w+)$/',$filename,$ext);
	echo '<li class="filetype"><span class="title">' . __( 'File Type', 'sell_media' ) . ':</span> ' . esc_html( strtoupper( $ext[1] ) ) .' ('. get_post_mime_type( $attachment_id ) . ')</li>';
	echo '<li class="filesize"><span class="title">' . __( 'File Size', 'sell_media' ) . ':</span> ' . sell_media_get_filesize( $post->ID, $attachment_id ) . '</li>';
	if ( isset( $meta['width'], $meta['height'] ) ) {
		echo '<li class="filedims"><span class="title">' . __( 'Dimensions', 'sell_media' ) . ':</span> ' . $meta['width']. ' x '. $meta['height'] .'</li>';
	}
	if ( wp_get_post_terms( $post->ID, 'collection' ) ) {
		echo '<li class="collections"><span class="title">' . __( 'Collections', 'sell_media' ) . ':</span> ' . sell_media_get_taxonomy_terms( 'collection' ) . '</li>';
	}
	if ( wp_get_post_terms( $post->ID, 'keywords' ) && ! get_query_var( 'id' ) ) {
		echo '<li class="keywords"><span class="title">' . __( 'Keywords', 'sell_media' ) . ':</span> ' . sell_media_get_taxonomy_terms( 'keywords' ) . '</li>';
	}

	if ( preg_match( '#^(audio|video)/#', get_post_mime_type( $attachment_id ) ) ) {
		echo '<li class="length"><span class="title">' . __( 'Length', 'sell_media' ) . ':</span> ' . $meta['length_formatted'] . '</li>';
		echo '<li class="bitrate"><span class="title">' . __( 'Bitrate', 'sell_media' ) . ':</span> ' . round( $meta['bitrate'] / 1000 ) . 'kb/s</li>';
	}
	echo do_action( 'sell_media_additional_list_items', $post->ID );
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
 * Filter the wp_title
 *
 * @param  $title
 * @param  $sep
 * @return title
 */
function sell_media_wp_title( $title, $sep ) {

	global $paged, $page;
	$post_type 	= 'sell_media_item';
	$name   	= get_bloginfo( 'name' );
	$settings 	= sell_media_get_plugin_options();

	if ( is_post_type_archive( $post_type ) ) {
		
		$obj    = get_post_type_object( $post_type );
		$slug   = ( $settings->post_type_slug ) ? ucfirst( preg_replace( '/[^a-zA-Z0-9]+/', ' ', $settings->post_type_slug ) ) : $obj->labels->name;
		$title  = "$slug $sep $name";
	}

	if ( is_singular( $post_type ) ) {
		$title = "$title $name";
	}

	return $title;
}
add_filter( 'wp_title', 'sell_media_wp_title', 10, 2 );

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
 * Put the cart dialog markup in the footer
 *
 * @since 1.8.5
 */
function sell_media_cart_dialog(){

	global $post;

	if ( empty( $post ) )
		return;
	
	$post_type = 'sell_media_item';
	$settings = sell_media_get_plugin_options();

	// Check if shortcode has been used
	$shortcode = false;
	if ( ! empty( $post->post_content ) ) {
		$content = $post->post_content;
		if ( has_shortcode( $content, 'sell_media_item' ) || has_shortcode( $content, 'sell_media_all_items' ) || has_shortcode( $content, 'sell_media_lightbox' ) || is_search() ) {
			$shortcode = true;
		}
	}

	// Check if on Sell Media taxonomy archive page
	$sell_media_taxonomies = get_object_taxonomies( $post_type );

	// Only inject markup on specific pages
	// if ( is_singular( $post_type ) || is_post_type_archive( $post_type ) || is_tax( $sell_media_taxonomies ) || $shortcode || is_page( $settings->search_page ) ) {
		$popup_restricted_pages = array( $settings->login_page, $settings->dashboard_page, $settings->checkout_page );

		if ( ! in_array( $post->ID, $popup_restricted_pages ) ) : ?>
			<div id="sell-media-dialog-box" class="sell-media-dialog-box">
				<div id="sell-media-dialog-box-target">
				<a href="javascript:void(0);" class="sell-media-dialog-box-prev sell-media-dialog-box-arrow"><span class="dashicons dashicons-arrow-left-alt2"></span></a>
					<div class="sell-media-dialog-box-content">
					</div>
				<a href="javascript:void(0);" class="sell-media-dialog-box-next sell-media-dialog-box-arrow"><span class="dashicons dashicons-arrow-right-alt2"></span></a>
				</div>
				<span class="close">&times;</span>
			</div>
		<?php endif; 
	// }
	if ( is_page( $settings->checkout_page ) && ! empty ( $settings->terms_and_conditions ) ){ ?>
		<div id="sell-media-empty-dialog-box" class="sell-media-dialog-box sell-media-dialog-box-terms">
			<div id="sell-media-dialog-box-target">
				<span class="close">&times;</span>
				<div class="content">
					<p><?php echo stripslashes_deep( nl2br( $settings->terms_and_conditions ) ); ?></p>
				</div>
			</div>
		</div>
	<?php 
	}
}
add_action( 'wp_footer', 'sell_media_cart_dialog' );

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

/**
 * Get the number of post views
 * Shown in admin on add/edit post screen
 *
 * @return string
 */
function sell_media_get_post_views( $post_id=null ) {

	$key = '_sell_media_post_views_count';
	$count = get_post_meta( $post_id, $key, true );

	if ( $count == '' ) {
		delete_post_meta( $post_id, $key );
		add_post_meta( $post_id, $key, 0 );

		return 0;
	}

	return $count;
}


/**
 * Set the number of post views
 * Used in templates, filters and functions to increase post view count
 *
 * @return void
 */
function sell_media_set_post_views( $post_id ) {
	$key = '_sell_media_post_views_count';
	$count = get_post_meta( $post_id, $key, true );

	if ( $count=='' ) {
		$count = 0;
		delete_post_meta( $post_id, $key );
		add_post_meta( $post_id, $key, 0 );
	} else {
		$count++;
		update_post_meta( $post_id, $key, $count );
	}
}

// Remove issues with prefetching adding extra views
remove_action( 'wp_head', 'adjacent_posts_rel_link_wp_head', 10, 0 );