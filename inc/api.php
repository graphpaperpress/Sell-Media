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

/**
 * Extend rest response
 */
function sell_media_extend_rest_post_response() {

	register_rest_route( 'sell-media/v2', '/search', array(
		'methods'  => WP_REST_Server::READABLE,
		'callback' => 'sell_media_api_search_response',
		'args'     => sell_media_api_get_search_args(),
	) );

	// Add featured image
	register_rest_field( 'sell_media_item',
		'sell_media_featured_image',
		array(
			'get_callback'    => 'sell_media_api_get_image',
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
 */
function sell_media_api_get_image( $object, $field_name = '', $request = '' ) {
	$post_id       = $object['id'];
	$attachment_id = ( has_post_thumbnail( $post_id ) ) ? get_post_thumbnail_id( $post_id ) : sell_media_get_attachment_id( $post_id );
	if ( empty( $attachment_id ) ) {
		return;
	}

	// set title and alt text
	$img_array['title'] = get_the_title( $attachment_id );
	$img_array['alt']   = get_post_meta( $attachment_id, '_wp_attachment_image_alt', true );

	// set sizes
	$sizes = array( 'full', 'large', 'medium_large', 'medium', 'thumbnail', 'srcset', 'sell_media_square' );
	foreach ( $sizes as $size ) {
		$img_array['sizes'][ $size ] = wp_get_attachment_image_src( $attachment_id, $size, false );
	}

	return ( is_array( $img_array ) ) ? $img_array : '';
}

/**
 * Attachments for rest api
 */
function sell_media_api_get_attachments( $object, $field_name = '', $request = '' ) {
	$post_id        = $object['id'];
	$attachment_ids = get_post_meta( $post_id, '_sell_media_attachment_id', true );
	if ( empty( $attachment_ids ) ) {
		return;
	}

	foreach ( $attachment_ids as $key => $value ) {
		$attachment_array[ $key ]['id']       = absint( $value );
		$attachment_array[ $key ]['title']    = get_the_title( $value );
		$attachment_array[ $key ]['alt']      = get_post_meta( $value, '_wp_attachment_image_alt', true );
		$attachment_array[ $key ]['url']      = get_permalink( $value );
		$attachment_array[ $key ]['slug']     = get_post_field( 'post_name', $value );
		$attachment_array[ $key ]['type']     = get_post_mime_type( $value );
		$attachment_array[ $key ]['keywords'] = wp_get_post_terms( $value, 'keywords', array( 'fields' => 'names' ) );
		$attachment_array[ $key ]['file']     = wp_get_attachment_url( $value );
		$attachment_array[ $key ]['embed']    = get_post_meta( $post_id, 'sell_media_embed_link', true ); // we might want to consider setting this meta on attachment instead. Use case: Video galleries.

		// set sizes
		$sizes = array( 'full', 'large', 'medium_large', 'medium', 'thumbnail', 'srcset', 'sell_media_square' );
		foreach ( $sizes as $size ) {
			$attachment_array[ $key ]['sizes'][ $size ] = wp_get_attachment_image_src( $value, $size, false );
		}
	}
	return ( is_array( $attachment_array ) ) ? (array) $attachment_array : '';
}

/**
 * Pricing for rest api
 */
function sell_media_api_get_pricing( $object, $field_name = '', $request = '' ) {
	$post_id              = $object['id'];
	$attachment_id        = sell_media_get_attachment_id( $post_id );
	$products             = new SellMediaProducts();
	$pricing['downloads'] = $products->get_prices( $post_id, $attachment_id );
	$pricing['prints']    = $products->get_prices( $post_id, $attachment_id, 'reprints-price-group' );
	// remove parent containing term
	unset( $pricing['downloads'][1] );
	unset( $pricing['prints'][1] );
	return $pricing;
}

/**
 * Meta to rest api
 */
function sell_media_api_get_meta( $object, $field_name = '', $request = '' ) {
	$meta['sell'] = array( 'Downloads' );
	return apply_filters( 'sell_media_filter_api_get_meta', $meta, $object, $field_name, $request );
}

/**
 * Search endpoint for rest api.
 * This is a pluggable function.
 * /wp-json/sell-media/v2/search/?s=water&type=image&page=4
 */
if ( ! function_exists( 'sell_media_api_search_response' ) ) :

	function sell_media_api_search_response( $request ) {

		$results = array();
		$posts   = array();
		$page    = empty( $request->get_param( 'page' ) ) ? $request->get_param( 'page' ) : 1;
		$page    = intval( $page );
		$args    = array(
			'paged'          => $page,
			'post_type'      => 'sell_media_item',
			'posts_per_page' => get_option( 'posts_per_page' ),
		);

		// Search Query
		if ( isset( $request['s'] ) ) {
			$search    = $request->get_param( 's' );
			$search    = implode( ' ', explode( '+', $search ) );
			$search    = urldecode( $search );
			$args['s'] = $search;
		}

		$query = new WP_Query();
		$posts = $query->query( $args );

		// mirror formatting of normal rest api endpoint for sell_media_item
		foreach ( $posts as $post ) {
			$obj['id'] = $post->ID;

			$img_array         = sell_media_api_get_image( $obj );
			$attachments_array = sell_media_api_get_attachments( $obj );
			$pricing           = sell_media_api_get_pricing( $obj );

			$results[] = [
				'id'                        => $post->ID,
				'title'                     => [
					'rendered' => $post->post_title,
				],
				'slug'                      => $post->post_name,
				'link'                      => get_permalink( $post->ID ),
				'content'                   => [
					'rendered' => get_the_content(),
				],
				'sell_media_featured_image' => $img_array,
				'sell_media_attachments'    => $attachments_array,
				'sell_media_pricing'        => $pricing,
				'sell_media_meta'           => [
					'sell' => apply_filters( 'sell_media_filter_api_sell', array( __( 'Downloads', 'sell_media' ) ), $post->ID ),
				],
			];
		}

		if ( empty( $results ) ) {
			return new WP_Error( 'sell_media_no_search_results', __( 'No results', 'sell_media' ) );
		}

		return rest_ensure_response( $results );
	}

endif;

/**
 * Accepted $_GET parameters for custom search rest api.
 * This is a pluggable function.
 * /wp-json/sell-media/v2/search/?s=water&type=image&page=4
 */

if ( ! function_exists( 'sell_media_api_get_search_args' ) ) :

	function sell_media_api_get_search_args() {
		$args         = [];
		$args['s']    = [
			'description' => esc_html__( 'The search term.', 'sell_media' ),
			'type'        => 'string',
		];
		$args['type'] = [
			'description' => esc_html__( 'The product type.', 'sell_media' ),
			'type'        => 'string',
		];
		$args['page'] = [
			'description' => esc_html__( 'The current page of results.', 'sell_media' ),
			'type'        => 'integer',
		];

		return $args;
	}

endif;

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

