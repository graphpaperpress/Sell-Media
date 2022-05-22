<?php

/**
 * Template Tags
 *
 * @package Sell Media
 * @author Thad Allender <support@graphpaperpress.com>
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Print the buy button
 *
 * @access      public
 * @since       0.1
 * @return      html
 */
function sell_media_item_buy_button( $post_id = null, $attachment_id = null, $button = null, $text = null, $is_output = true ) {

	$attachment_id = ( empty( $attachment_id ) ) ? sell_media_get_attachment_id( $post_id ) : $attachment_id;
	$text = apply_filters( 'sell_media_purchase_text', $text, $post_id );
	$html = '<a href="' . esc_url( get_permalink( $post_id ) ) . '" title="' . $text . '" data-product-id="' . esc_attr( $post_id ) . '" data-attachment-id="' . esc_attr( $attachment_id ) . '" class="sell-media-' . $button . '">' . $text . '</a>';
	$html = apply_filters( 'sell_media_item_buy_button', $html, $post_id, $attachment_id, $button, $text, $is_output );

	$arr = array(
	    'button' => array(
	        'class' => true,
	        'disabled' => true,
	        'type' => true
	    ),
	);

	if ( $is_output ) {
	    echo wp_kses( $html, $arr );
	} else {
		return $html;
	}
}

function sell_media_item_add_to_cart_button( $post_id = null, $attachment_id = null, $button = null, $text = null, $is_output = true, $type = 'download' ) {

	if ( is_null( $text ) ) {
		$text = __( 'Add to cart', 'sell_media' );
	}

	$attachment_id = ( empty( $attachment_id ) ) ? sell_media_get_attachment_id( $post_id ) : $attachment_id;
	// check if is package
	$is_package = Sell_Media()->products->is_package( $post_id );
	// check if has assigned price group
	$has_price_group = Sell_Media()->products->has_price_group( $post_id );
	$text = apply_filters( 'sell_media_add_to_cart_text', $text, $post_id, $attachment_id, $type );
	$disable = ( ! $is_package && $has_price_group ) ? 'disabled' : '';

	$classes[] = 'item_add';
	$classes[] = 'sell-media-button';
	$classes[] = 'sell-media-button-medium';
	$classes[] = 'sell-media-button-dark';
	if ( ! is_null( $button ) ) {
		$classes[] = 'sell-media-' . $button;
	}

	$classes = implode( ' ', $classes );
	$classes = apply_filters( 'sell_media_button_classes', $classes );

	$html = '<button class="' . $classes . '" ' . $disable . '>' . $text . '</button>';
	$html = apply_filters( 'sell_media_item_add_to_cart_button', $html, $post_id, $attachment_id, $button, $text, $is_output, $type );

	$arr = array(
	    'button' => array(
	        'class' => true,
	        'disabled' => true,
	        'type' => true
	    ),
	);

	if ( $is_output ) {
	    echo wp_kses( $html, $arr );
	} else {
		return $html;
	}
}

/**
 * Determines the image source for a product
 * @return (string) url to product image or feature image
 */
function sell_media_item_image_src( $post_id = null, $attachment_id = null ) {
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
	} elseif ( '' !== get_the_post_thumbnail( $post_id ) ) {
		$image_attributes = wp_get_attachment_image_src( get_post_thumbnail_id( $post_id ), $size );
		// no featured image, so get the attachment src from the attached file
	} else {
		$image_attributes = wp_get_attachment_image_src( $attachment_id, $size );
	}
	$image_attributes = array_map( 'esc_attr', $image_attributes);
	
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
function sell_media_get_filetype( $post_id = null ) {
	$filetype = wp_check_filetype( get_post_meta( $post_id, '_sell_media_attached_file', true ) );
	return esc_attr($filetype['ext']);
}

/**
 * Determines the image used to represent an item for sale. If an
 * image mime type is detected than the attachment image is used.
 *
 * @param  int     $post_id Post ID.
 * @param  string  $size    Size of icon.
 * @param  boolean $is_output Return/ Display.
 *
 * @return string           An image tag.
 */
function sell_media_item_icon( $post_id = null, $size = 'medium', $is_output = true, $has_collection_icon = false ) {

	/**
	 * Legacy function passed the $attachment_id into sell_media_item_icon.
	 * That means the above get_post_meta wouldn't exist.
	 * If that's the case, than we assume the $post_id is actually the $attachment_id.
	 *
	 * @var int
	 */
	$attachment_id = sell_media_get_attachment_id( $post_id );

	if ( $has_collection_icon ) {
		$term = get_the_terms( $post_id, 'collection' );

		if ( isset( $term[0]->term_id ) ) {
			$icon_attachment_id = get_term_meta( $term[0]->term_id, 'collection_icon_id', true );
			if ( $icon_attachment_id ) {
				$attachment_id = $icon_attachment_id;
			}
		}
	}

	// Post Thumbnail
	// Uploaded Sell Media Item
	if ( '' != get_the_post_thumbnail( $post_id ) ) {
		$image = get_the_post_thumbnail( $post_id, $size, array( 'class' => apply_filters( 'sell_media_image_class', 'sell-media-image sell_media_image' ) ) );
		$image = apply_filters('sell_media_item_icon_after', $image, $post_id, $size );
	// Attachment, so use attachment src
	} elseif ( '' != wp_get_attachment_image_src( $attachment_id, $size ) ) {
		$image_attr = wp_get_attachment_image_src( $attachment_id, $size );
		$src = $image_attr[0];
		$image = wp_get_attachment_image( $attachment_id, $size, '', array( 'class' => apply_filters( 'sell_media_image_class', 'sell-media-image sell_media_image' ), 'data-sell_media_medium_url' => $src, 'data-sell_media_large_url' => $src, 'data-sell_media_item_id' => $post_id ) );
		$image = apply_filters('sell_media_item_icon_after', $image, $attachment_id, $size );
	// Item, so use first attachment image src
	} elseif ( wp_attachment_is_image( sell_media_get_attachment_id( $post_id ) ) ) {
		$attachment_id = sell_media_get_attachment_id( $post_id );
		$image_attr = wp_get_attachment_image_src( $attachment_id, $size );
		$src = $image_attr[0];
		$image = wp_get_attachment_image( $attachment_id, $size, '', array( 'class' => apply_filters( 'sell_media_image_class', 'sell-media-image sell_media_image' ), 'data-sell_media_medium_url' => $src, 'data-sell_media_large_url' => $src, 'data-sell_media_item_id' => $post_id ) );
		$image = apply_filters('sell_media_item_icon_after', $image, $attachment_id, $size );
	} else {
		global $post;
		if ( '' != get_the_post_thumbnail( $post->ID ) ) {
			$image = get_the_post_thumbnail( $post->ID, $size, array( 'class' => apply_filters( 'sell_media_image_class', 'sell-media-image sell_media_image' ) ) );
			$image = apply_filters('sell_media_item_icon_after', $image, $post->ID, $size );
		} else {
			$mime_type = get_post_mime_type( $attachment_id );
			switch ( $mime_type ) {
				case 'image/jpeg':
				case 'image/png':
				case 'image/gif':
					$src = wp_mime_type_icon( 'image/jpeg' );
					break;
				case 'video/mpeg':
				case 'video/mp4':
				case 'video/quicktime':
					$src = wp_mime_type_icon( 'video/mpeg' );
					break;
				case 'text/csv':
				case 'text/pdf':
				case 'text/plain':
				case 'text/xml':
				case 'application/pdf':
					$src = wp_mime_type_icon( 'application/document' );
					break;
				case 'application/x-gzip':
				case 'application/zip':
					$src = wp_mime_type_icon( 'application/archive' );
					break;
				default:
					$src = wp_mime_type_icon();
					break;
			}

			$src = apply_filters( 'sell_media_item_icon_src', $src, $attachment_id, $mime_type );
			$image = '<img src="' . esc_url($src) . '" class="' . apply_filters( 'sell_media_image_class', 'sell_media_image' ) . ' wp-post-image" title="' . get_the_title( $post_id ) . '" alt="' . get_the_title( $post_id ) . '" data-sell_media_medium_url="' . $src . '" data-sell_media_large_url="' . esc_url($src) . '" data-sell_media_item_id="' . $post_id . '" style="max-width:100%;height:auto;"/>';
			$image = apply_filters('sell_media_item_icon_after', $image, $attachment_id, $size );
		
		}
	}

	$arr = array(
	    'img' => array(
	        'src' => true,
	        'class' => true,
	        'title' => true,
	        'data' => true,
	        'alt' => true,
	        'style' => true,
	    ),
	    'script' => array('type' => true),
	    'ul' => ['data-*' => true, 'class' => true, 'id' => true],
	    'li' => ['data-*' => true, 'class' => true, 'value' => true],
	    'a' => ['data-*' => true, 'target' => true, 'href' => true, 'class' => true, 'id' => true],
        'div' => array('class' => true, 'data-*' => true, 'id' => true, 'style' => true),
	    'span' => array('class' => true, 'data-*' => true, 'id' => true, 'style' => true),
	    'strong' => array('class' => true, 'data-*' => true, 'id' => true, 'style' => true),
	    'i' => array('class' => true, 'data-*' => true, 'id' => true, 'style' => true),
	    'u' => array('class' => true, 'data-*' => true, 'id' => true, 'style' => true),
	    'p' => array('class' => true, 'data-*' => true, 'id' => true, 'style' => true),
	);

	if ( $is_output ) {
		echo wp_kses( $image, $arr );
	} else {
		return $image;
	}
}


/**
 * Get the media files
 *
 * This function is pluggable, meaning you can override it.
 * Just copy the function below, and add it to your theme or plugin.
 * Modify the bits you need to display the media.
 * @return $html the media shown for sale.
 */
if ( ! function_exists( 'sell_media_get_media' ) ) :
	function sell_media_get_media( $post_id = null ) {
		global $post, $content_width;
		$width = get_option( 'medium_size_w', $content_width );
		$post_id = ( $post_id ) ? $post_id : $post->ID;
		$html = '';
        if (!$post_id) {
            return $html;
        }
		$mime_type = get_post_mime_type( $post_id );

		if( 'video/mpeg' == $mime_type || 'video/mp4' == $mime_type || 'video/quicktime' == $mime_type ) {
			$url = get_post_meta( wp_get_post_parent_id($post_id), 'sell_media_embed_link', true );
            if ( '' != $url ) {
                $html .= wp_oembed_get( esc_url( $url ), array( 'width' => 600 ) );
            }
		} else if ( sell_media_has_multiple_attachments( $post_id ) ) {
			$html .= sell_media_gallery( $post_id );
		} else {
			$html .= sell_media_item_icon( $post_id, apply_filters( 'sell_media_large_item_size', 'large' ), false );
			$html .= sell_media_caption( $post_id );
		}
		$html = apply_filters('sell_media_after_media_item_content', $html, $post_id); 
		return $html;
	}
endif;

/**
 * Gallery
 * @param  integer $post_id the post id
 * @return html the full gallery markup
 */
function sell_media_gallery( $post_id ) {

	do_action( 'sell_media_before_gallery', $post_id );

	$html = '';

	$attachment_ids = sell_media_get_attachments( $post_id );
	$attachment_ids = array_map( 'intval', $attachment_ids);
	$container_class = apply_filters( 'sell_media_grid_item_container_class', 'sell-media-grid-item-container', $post_id );
	$html .= '<ul id="sell-media-gallery-' . esc_attr( $post_id ) . '" class="sell-media-gallery ' . esc_attr( $container_class ) . '">';
	if ( $attachment_ids ) foreach ( $attachment_ids as $attachment_id ) {
		//$mime_type = get_post_mime_type( $attachment_id );
		$attachment_attributes = wp_get_attachment_image_src( $attachment_id, 'large' );

		if( has_post_thumbnail() && ! is_array( $attachment_attributes ) ) {
			$attachment_attributes[0] = get_the_post_thumbnail_url($post_id, 'medium');
			$image_size = getimagesize( $attachment_attributes[0] );
			$attachment_attributes[1] = $image_size[0];
			$attachment_attributes[2] = $image_size[1];
		}


		$settings = sell_media_get_plugin_options();
		if ( 'sell-media-horizontal-masonry' === $settings->thumbnail_layout ) {
 			$class = 'sell-media-image sell_media_image sell_media_watermark horizontal-masonry-column overlay-container ';
						
			$image_data     = wp_get_attachment_image_src( $attachment_id, 'medium' );
			if( $image_data ) {
				$image_width    = $attachment_attributes[1];
				$image_height   = $attachment_attributes[2];
				$width          = $image_width * 250 / max($image_height, 1);
				$padding_bottom = $image_height / max($image_width, 1) * 100;
	 			$html  .= '<li id="sell-media-' . $attachment_id  . '" class="' . $class . ' sell-media-grid-single-item"  data-src="' . esc_url( $attachment_attributes[0] ) . '" style="width:' . $width . 'px; flex-grow:' . $width . '; " >';
	 		}
		} else {
			$attr = array(
				'class' => 'sell-media-image sell_media_image sell_media_watermark',
			);
			$item_class = apply_filters( 'sell_media_grid_item_class', 'sell-media-grid-item', $post_id );
			$html .= '<li id="sell-media-' . $attachment_id . '" class="' . $item_class . ' sell-media-grid-single-item" data-src="' . esc_url( $attachment_attributes[0] ) . '">';
		}
		$html .= '<a href="' . esc_url( get_permalink( $attachment_id ) ) . '" ' . sell_media_link_attributes( $attachment_id ) . ' class="sell-media-item">';
		if ( 'sell-media-horizontal-masonry' === $settings->thumbnail_layout ) {
			if( $image_data ) {
				$html .= '<i style="padding-bottom:' . $padding_bottom . '%;" ></i>';
			}
		}
		$mime_type = get_post_mime_type( $attachment_id );
		
		$html .= sell_media_item_icon( $attachment_id, apply_filters( 'sell_media_thumbnail', 'medium' ), false );

		$enable_ecommerce = apply_filters( 'sell_media_enable_ecommerce', true, $post_id, $attachment_id );
		if ( $enable_ecommerce ) {
			$html .= '<div class="sell-media-quick-view" data-product-id="' . esc_attr( $post_id ) . '" data-attachment-id="' . esc_attr( $attachment_id ) . '">' . apply_filters( 'sell_media_quick_view_text', __( 'Quick View', 'sell_media' ), $post_id, $attachment_id ) . '</div>';
		}
		$html .= '</a>';
		$html .= '</li>';
	}
	$html .= '</ul>';

	return $html;
}

/**
 * The attachment caption
 * @param  $attachment_id the attachment id
 * @return  $html the title and caption of the attachment
 **/
function sell_media_caption( $attachment_id ) {

	$html = '';
	$caption = sell_media_get_attachment_meta( $attachment_id, 'caption' );

	if ( $caption ) {
		$html .= '<p class="sell-media-caption">' . $caption . '</p>';
	}

	return $html;
}

/**
 * Retrives the lowest price available of an item from the price groups
 *
 * @param $post_id (int) The post_id, must be a post type of "sell_media_item"
 * @return Lowest price of an item
 */
function sell_media_item_min_price( $post_id = null, $attachmet_id = null ) {
	$value = get_post_meta( $post_id, 'sell_media_free_downloads', true );
	$price = Sell_Media()->products->get_lowest_price( $post_id );
	if ( empty( $price ) ) {
		$settings = sell_media_get_plugin_options();
		$price = $settings->default_price;
	}
	if ( isset( $value ) && 'on' === $value ) {
		return '0.00';
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
add_action( 'sell_media_below_buy_button', 'sell_media_plugin_credit', 90 );


/**
 * Gets the except of a post by post id
 *
 * @since 1.8.5
 * @author Thad Allender
 */
function sell_media_get_excerpt( $post_id, $excerpt_length = 140, $trailing_character = '&nbsp;&hellip;' ) {
	$the_post = get_post( $post_id );
	$the_excerpt = strip_tags( strip_shortcodes( $the_post->post_excerpt ) );

	if ( empty( $the_excerpt ) ) {
		$the_excerpt = strip_tags( strip_shortcodes( $the_post->post_content ) );
	}

	$words = explode( ' ', $the_excerpt, $excerpt_length + 1 );

	if ( count( $words ) > $excerpt_length ) {
		$words = array_slice( $words, 0, $excerpt_length );
	}

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
function sell_media_get_taxonomy_terms( $id, $taxonomy ) {
	global $post;
	
	$terms = wp_get_post_terms( $id, $taxonomy );

	if ( empty( $terms ) || is_wp_error( $terms ) ) {
		return;
	}

	$html = null;

	foreach ( $terms as $term ) {

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
			$item   = get_term_by( 'id', $parent, get_query_var( 'taxonomy' ) ); ?>
            <li><a href="<?php echo esc_url( get_term_link( $item->term_id, get_query_var( 'taxonomy' ) ) ); ?>"><?php echo esc_html( $item->name ); ?></a> <span class="raquo">&raquo;</span> </li><?php
		endforeach;
	endif;

	// Display the current term in the breadcrumb
	?>
    <li><a href="<?php echo esc_url( get_term_link( $term->term_id, get_query_var( 'taxonomy' ) ) ); ?>"><?php echo esc_html( $term->name ); ?></a></li><?php
}

/**
 * Breadcrumb navigation on single entries
 *
 * @since 1.9.2
 * @global $post
 *
 * @return string the breadcrumb navigation
 */
function sell_media_breadcrumbs() {
	global $post;

	$settings = sell_media_get_plugin_options();

	if ( isset( $settings->breadcrumbs ) && $settings->breadcrumbs ) {

		$obj = get_post_type_object( 'sell_media_item' );

		$html = '<div class="sell-media-breadcrumbs">';
		$html .= '<div><a href="' . esc_url( home_url() ) . '" title="' . __( 'Home', 'sell_media' ) . '">' . __( 'Home', 'sell_media' ) . '</a></div>';
		$html .= '<div><a href="' . get_post_type_archive_link( 'sell_media_item' ) . '" title="' . $obj->rewrite['slug'] . '">' . $obj->rewrite['slug'] . '</a></div>';
		if ( wp_get_post_terms( $post->ID, 'collection' ) ) {
			$html .= sell_media_get_taxonomy_terms( $post->ID, 'collection' );
		}
		if ( sell_media_attachment( $post->ID ) ) {
			$product_id = get_post_meta( $post->ID, $key = '_sell_media_for_sale_product_id', true );
			$html .= '<div><a href="' . esc_url( get_permalink( $product_id ) ) . '" title="' . __( 'Back to Gallery', 'sell_media' ) . '">' . __( 'Back to Gallery', 'sell_media' ) . '</a></div>';
		}
		$html .= '</div>';

		return apply_filters( 'sell_media_breadcrumbs', $html, $obj );
	}
}

/**
 * Count posts in a category, including subcategories
 *
 * @since 1.9.5
 */
function sell_media_get_cat_post_count( $category_id, $taxonomy = 'collection' ) {

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
	return apply_filters( 'sell_media_link_attribute', $attributes = '', $post_id );
}

/**
 * Show additional file info
 *
 * @since 1.9.2
 * @param int $post_id Item ID
 * @return void
 */
function sell_media_show_file_info() {

	// Bail if file info isn't set to be shown
	$settings = sell_media_get_plugin_options();
	if ( empty( $settings->file_info ) ) {
		return;
	}
	
	
	$parent_id = '';
	$post_obj = get_post();
	$attachment_id = sell_media_get_attachment_id( $post_obj->ID );
	
	if( 0 != wp_get_post_parent_id( $post_obj->ID ) ) {
		$parent_id = wp_get_post_parent_id( $post_obj->ID );
	} else {
		$parent_id = $post_obj->ID;
	}
	
	if ( '' == $attachment_id ) {
		return;
	}


	$media_dims = '';
	$meta = wp_get_attachment_metadata( $attachment_id );
	
	$filename = explode( '?', basename( get_attached_file( $attachment_id ) ) )[0];
	$post_guid = get_the_guid( $attachment_id );
	$image_size_info = getimagesize( Sell_Media()->products->get_protected_file( $post_obj->ID, $attachment_id ) );
	$video_metadata = wp_get_attachment_metadata( $attachment_id );

	?><h2 class="widget-title sell-media-item-details-title"><?php echo esc_html__( 'Details', 'sell_media' ); ?></h2><?php
	?><ul class="sell-media-item-details"><?php
	?><li class="filename"><span class="title"><?php echo esc_html__( 'File Name', 'sell_media' ); ?>:</span> <?php echo esc_html( $filename ); ?></li><?php
	?><li class="fileid"><span class="title"><?php echo esc_html__( 'File ID', 'sell_media' ); ?>:</span> <?php echo esc_html( $attachment_id ); ?></li><?php
	preg_match('/^.*?\.(\w+)$/',$filename,$ext);
	?><li class="filetype"><span class="title"><?php echo esc_html__( 'File Type', 'sell_media' ); ?>:</span> <?php echo esc_html( strtoupper( $ext[1] ) .' ('. get_post_mime_type( $attachment_id ) .')' ); ?></li><?php
	?><li class="filesize"><span class="title"><?php echo esc_html__( 'File Size', 'sell_media' ); ?>:</span> <?php echo esc_html( sell_media_get_filesize( $post_obj->ID, $attachment_id ) ); ?></li><?php
	if ( isset( $image_size_info[0], $image_size_info[1] ) ) {
		?><li class="filedims"><span class="title"><?php echo esc_html__( 'Dimensions', 'sell_media' ); ?>:</span> <?php echo esc_html( $image_size_info[0]. ' x '. $image_size_info[1] ); ?></li><?php
	}
	if ( wp_get_post_terms( $parent_id, 'collection' ) ) {
		?><li class="collections"><span class="title"><?php echo esc_html__( 'Collections', 'sell_media' ); ?>:</span> <?php echo wp_kses( sell_media_get_taxonomy_terms( $parent_id, 'collection' ), array( 'a' => array('href' => true, 'title' => true ) ) ); ?></li><?php
	}
	if ( wp_get_post_terms( $attachment_id, 'keywords' ) ) {
		?><li class="keywords"><span class="title"><?php echo esc_html__( 'Keywords', 'sell_media' );?>:</span> <?php echo wp_kses( sell_media_get_taxonomy_terms( $attachment_id, 'keywords' ), array( 'a' => array('href' => true, 'title' => true ) ) ); ?></li><?php
	}
	if ( preg_match( '#^(audio|video)/#', get_post_mime_type( $attachment_id ) ) ) {
		if( '' != $meta  && isset( $meta['length_formatted']) )  {
			?><li class="length"><span class="title"><?php echo esc_html__( 'Length', 'sell_media' ); ?>:</span> <?php echo esc_html( $meta['length_formatted'] ); ?></li><?php
		}
		if( '' != $meta  && isset( $meta['bitrate']) )  {
			?><li class="bitrate"><span class="title"><?php echo esc_html__( 'Bitrate', 'sell_media' ); ?>:</span> <?php echo esc_html( round( $meta['bitrate'] / 1000 ) . 'kb/s'); ?></li><?php
		}
	}
	do_action( 'sell_media_additional_list_items', $post_obj->ID );
	?></ul><?php

}
add_action( 'sell_media_below_buy_button', 'sell_media_show_file_info', 12 );

/**
 * Adds Sell Media Version to the <head> tag
 *
 * @since 1.9.2
 * @return void
*/
function sell_media_version_in_header() {
	?><meta name="generator" content="Sell Media v<?php echo esc_attr__( SELL_MEDIA_VERSION ); ?>" />
    <?php
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
function sell_media_theme_setup() {

	/**
	 * Don't filter the_content if custom template is used.
	 * Prevents duplicate content on the_content filter.
	 * For backwards compatibility in case users upgrade Sell Media
	 * but have old themes with custom Sell Media templates.
	 */
	if ( 'single-sell_media_item.php' === sell_media_return_template() ) {
		remove_filter( 'the_content', 'sell_media_before_content' );
		remove_filter( 'the_content', 'sell_media_after_content' );
	}

}
add_action( 'wp_head', 'sell_media_theme_setup', 999 );


/**
 * Put the cart dialog markup in the footer
 *
 * @since 1.8.5
 */
function sell_media_cart_dialog() {

	global $post;

	if ( empty( $post ) ) {
		return;
	}

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
	if ( is_page( $settings->checkout_page ) && ! empty ( $settings->terms_and_conditions ) ) { ?>
		<div id="sell-media-empty-dialog-box" class="sell-media-dialog-box sell-media-dialog-box-terms">
			<div id="sell-media-dialog-box-target">
				<span class="close">&times;</span>
				<div class="content">
					<p><?php echo esc_html( $settings->terms_and_conditions ); ?></p>
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
 * Check if on Sell Media search page
 * @return  boolean true if on search, false otherwise
 */
function sell_media_is_search() {

	$settings = sell_media_get_plugin_options();
	if ( ! empty( $settings->search_page ) && is_page( $settings->search_page ) ) {
		return true;
	}
}

/**
 * Get the number of post views
 * Shown in admin on add/edit post screen
 *
 * @return string
 */
function sell_media_get_post_views( $post_id = null ) {

	$key = '_sell_media_post_views_count';
	$count = get_post_meta( $post_id, $key, true );

	if ( '' === $count ) {
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

	if ( '' === $count ) {
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