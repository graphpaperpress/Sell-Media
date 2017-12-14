<?php
/**
 * API
 */

/**
 * Shortcodes
 */
$pages = sell_media_get_pages_array();
foreach ( $pages as $page ) {
	add_shortcode( 'sell_media_' . $page, function() {
		echo '<div id="sell-media-app"></div>';
	} );
}

/**
 * Make permalinks a relative path
 */
function sell_media_get_relative_permalink( $id ) {
	return str_replace( home_url(), '', get_permalink( $id ) );
}

// add_action( 'init', function() {
// 	add_rewrite_rule( '\/items\/.*', 'index.php', 'top' );
// });

/**
 * Extend rest response
 */
function sell_media_extend_rest_post_response() {

	// Add featured image
	register_rest_field( 'sell_media_item',
		'sell_media_featured_image',
		array(
			'get_callback'    => 'sell_media_api_get_image_src',
			'update_callback' => null,
			'schema'          => null,
		)
	);

	register_rest_field( 'sell_media_item',
		'sell_media_attachments',
		array(
			'get_callback'    => 'sell_media_api_get_attachments',
			'update_callback' => null,
			'schema'          => null,
		)
	);

	register_rest_field( 'sell_media_item',
		'sell_media_pricing',
		array(
			'get_callback'    => 'sell_media_api_get_pricing',
			'update_callback' => null,
			'schema'          => null,
		)
	);

	register_rest_field( 'sell_media_item',
		'sell_media_meta',
		array(
			'get_callback'    => 'sell_media_api_get_meta',
			'update_callback' => null,
			'schema'          => null,
		)
	);

}
add_action( 'rest_api_init', 'sell_media_extend_rest_post_response' );

/**
 * Images for rest api
 * @param  [type] $object     [description]
 * @param  [type] $field_name [description]
 * @param  [type] $request    [description]
 * @return [type]             [description]
 */
function sell_media_api_get_image_src( $object, $field_name, $request ) {

	// check feature media, then first attachment
	$attachment_id = ( ! empty( $object['featured_media'] ) ) ? $object['featured_media'] : sell_media_get_attachment_id( $object['id'] );
	if ( empty( $attachment_id ) ) {
		return;
	}

	// set title and alt text
	$img_array['title'] = get_the_title( $attachment_id );
	$img_array['alt'] = get_post_meta( $attachment_id, '_wp_attachment_image_alt', true );

	// set sizes
	$sizes = array( 'full', 'large', 'medium_large', 'medium', 'thumbnail', 'srcset' );
	foreach ( $sizes as $size ) {
		$img_array['sizes'][ $size ] = wp_get_attachment_image_src( $attachment_id, $size, false );
	}

	return ( is_array( $img_array ) ) ? $img_array : '';
}

function sell_media_api_get_attachments( $object, $field_name, $request ) {
	$attachment_ids = get_post_meta( $object['id'], '_sell_media_attachment_id', true );
	if ( empty( $attachment_ids ) ) {
		return;
	}

	foreach ( $attachment_ids as $key => $value ) {
		$attachment_array[ $key ]['id'] = absint( $value );
		$attachment_array[ $key ]['title'] = get_the_title( $value );
		$attachment_array[ $key ]['alt'] = get_post_meta( $value, '_wp_attachment_image_alt', true );
		$attachment_array[ $key ]['url'] = get_permalink( $value );
		$attachment_array[ $key ]['slug'] = get_post_field( 'post_name', $value );
		$attachment_array[ $key ]['type'] = get_post_mime_type( $value );
		$attachment_array[ $key ]['keywords'] = wp_get_post_terms( $value, 'keywords', array( 'fields' => 'names' ) );
		$attachment_array[ $key ]['file'] = wp_get_attachment_url( $value );
		$attachment_array[ $key ]['embed'] = get_post_meta( $object['id'], 'sell_media_embed_link', true ); // we might want to consider setting this meta on attachment instead. Use case: Video galleries.

		// set sizes
		$sizes = array( 'full', 'large', 'medium_large', 'medium', 'thumbnail', 'srcset' );
		foreach ( $sizes as $size ) {
			$attachment_array[ $key ]['sizes'][ $size ] = wp_get_attachment_image_src( $value, $size, false );
		}
	}
	return ( is_array( $attachment_array ) ) ? (array) $attachment_array : '';
}

function sell_media_api_get_pricing( $object, $field_name, $request ) {
	$attachment_id = sell_media_get_attachment_id( $object['id'] );
	$products = new SellMediaProducts();
	$pricing['downloads'] = $products->get_prices( $object['id'], $attachment_id );
	$pricing['prints'] = $products->get_prices( $object['id'], $attachment_id, 'reprints-price-group' );
	// remove parent containing term
	unset( $pricing['downloads'][1] );
	unset( $pricing['prints'][1] );
	return $pricing;
}

/**
 * Add meta to rest api
 */
function sell_media_api_get_meta( $object, $field_name, $request ) {
	$meta['sell'] = array( 'Downloads' );
	return apply_filters( 'sell_media_filter_api_get_meta', $meta, $object, $field_name, $request );
}

/**
 * Template Redirect
 * @since 1.0.4
 */
function sell_media_api_template_redirect( $original_template ) {

	global $post;

	$sell_media_post_type = 'sell_media_item';
	$post_type = array( $sell_media_post_type, 'attachment' );
	$sell_media_taxonomies = get_object_taxonomies( $post_type );

	// use index.php which contains vue.js app on Sell Media archives, singles, and attachments.
	if ( is_post_type_archive( $post_type ) // Sell Media archive
		|| is_tax( $sell_media_taxonomies ) // Sell Media taxonomies
		|| get_post_type() === $sell_media_post_type // Sell Media single
		|| ( ! empty( $post ) && sell_media_attachment( $post->ID ) ) // Sell Media attachment
	) {
		$template = SELL_MEDIA_PLUGIN_DIR . '/themes/index.php';
	} else {
		$template = $original_template;
	}

	return $template;
}
add_filter( 'template_include', 'sell_media_api_template_redirect', 10 );

