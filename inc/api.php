<?php
/**
 * API
 */

/**
 * Extend Rest API response
 */
function sell_media_extend_rest_post_response() {

	register_rest_route( 'sell-media/v2', '/api', array(
		'methods'  => WP_REST_Server::READABLE,
		'callback' => 'sell_media_api_response',
	) );

	register_rest_route( 'sell-media/v2', '/search', array(
		'methods'  => WP_REST_Server::READABLE,
		'callback' => 'sell_media_api_search_response',
		'args'     => sell_media_api_get_search_args(),
	) );

	register_rest_route( 'sell-media/v2', '/licensing', array(
		'methods'  => WP_REST_Server::READABLE,
		'callback' => 'sell_media_api_licensing_response',
	) );

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

	register_rest_field( array( 'sell_media_item', 'attachment' ),
		'sell_media_pricing',
		array(
			'get_callback'    => 'sell_media_api_get_pricing',
			'update_callback' => null,
			'schema'          => null,
		)
	);

	register_rest_field( array( 'sell_media_item', 'attachment' ),
		'sell_media_meta',
		array(
			'get_callback'    => 'sell_media_api_get_meta',
			'update_callback' => null,
			'schema'          => null,
		)
	);

	// Add meta to price groups
	register_rest_field( array( 'price-group', 'reprints-price-group' ),
		'sell_media_meta',
		array(
			'get_callback'    => 'sell_media_api_get_price_group_meta',
			'update_callback' => null,
			'schema'          => null,
		)
	);

	// Add meta to markup taxonomies
	$markups = new SellMediaTaxMarkup();
	register_rest_field( $markups->markup_taxonomies(),
		'sell_media_meta',
		array(
			'get_callback'    => 'sell_media_api_get_markup_meta',
			'update_callback' => null,
			'schema'          => null,
		)
	);

}
add_action( 'rest_api_init', 'sell_media_extend_rest_post_response' );


function sell_media_api_response() {
	if ( ! isset( $_GET['action'] ) || '' === $_GET['action'] ) {
		$data = array(
			'error' => __( 'No action found.', 'sell_media' ),
		);
		return $data;
	}
	$action = esc_html( $_GET['action'] );
	$data = apply_filters( 'sell_media_api_response', array(), $action );
	return $data;
}

/**
 * Get current logged in user
 * @param  array $array
 * @param  string $action
 * @return array containing user data
 */
function sell_media_api_get_user( $data, $action ) {

	if ( 'get_user' !== $action ) {
		return $data;
	}

	$current_user = wp_get_current_user();
	return $current_user;
}
add_filter( 'sell_media_api_response', 'sell_media_api_get_user', 10, 2 );

/**
 * Download a file.
 *
 * @param  array $array
 * @param  string $action
 * @return array containing download data
 */
function sell_media_api_download_file( $data, $action ) {
	if ( 'download_file' !== $action ) {
		return $data;
	}

	$response['message'] = __( 'Invalid download request.', 'sell_media' );
	$response['status'] = false;

	if ( ! isset( $_GET['post_id'] )
		|| '' === $_GET['post_id']
		|| ! isset( $_GET['attachment_id'] )
		|| '' === $_GET['attachment_id']
		|| ! isset( $_GET['size_id'] )
		|| '' === $_GET['size_id'] ) {
		return $response;
	}

	$post_id       = (int) esc_html( $_GET['post_id'] );
	$attachment_id = (int) esc_html( $_GET['attachment_id'] );
	$size_id       = esc_html( $_GET['size_id'] );
	$size          = get_term( $size_id, 'price-group' );
	$width         = (int) get_term_meta( $size_id, 'width', true );
	$height        = (int) get_term_meta( $size_id, 'height', true );
	$file_path     = Sell_Media()->products->get_protected_file( $post_id, $attachment_id, $size_id );
	$mime_type     = get_post_mime_type( $attachment_id );
	$img           = wp_get_image_editor( $file_path );
	$response      = array();

	// make a folder for each user's downloads
	$current_user = wp_get_current_user();
	$upload_dir   = wp_upload_dir();

	if ( isset( $current_user->user_login ) && ! empty( $upload_dir['basedir'] ) ) {
		$user_dirname = $upload_dir['basedir'] . '/downloads/' . $current_user->user_login;
		if ( ! file_exists( $user_dirname ) ) {
			wp_mkdir_p( $user_dirname );
		}
	}

	// crop images to requested size and send for download
	if ( ! is_wp_error( $img ) && $img->supports_mime_type( $mime_type ) && 'original' !== $size_id ) {

		if ( $width || $height ) {
			if ( $width >= $height ) {
				$max = $width;
			} else {
				$max = $height;
			}
			$img->resize( $max, $max, false );
		}
		$img->set_quality( 100 );
		$filename = $img->generate_filename( strtoupper( $size->slug ), $upload_dir['basedir'] . '/downloads/' . $current_user->user_login . '/', null );
		// remove params from file url, prevents save error below
		$filename = strtok( $filename, '?' );
		$response = $img->save( $filename );

		if ( ! is_wp_error( $response ) && file_exists( $response['path'] ) ) {
			$response['download'] = $upload_dir['baseurl'] . '/downloads/' . $current_user->user_login . '/' . $response['file'];
		}

	// download the original source file
	} else {
		$filename      = basename( $file_path );
		$new_file_path = $upload_dir['basedir'] . '/downloads/' . $current_user->user_login . '/' . $filename;

		if ( copy( $file_path, $new_file_path ) ) {
			$file_url              = $upload_dir['baseurl'] . '/downloads/' . $current_user->user_login . '/' . $filename;
			$file_url              = strtok( $file_url, '?' );
			$response['file']      = $filename;
			$response['mime-type'] = $mime_type;
			$response['download']  = $file_url;
		}
	}

	do_action( 'sell_media_record_file_download', $current_user->ID, $post_id, $attachment_id, $size_id );

	return apply_filters( 'sell_media_file_download_response', $response, $current_user->ID, $post_id, $attachment_id, $size_id );
}
add_filter( 'sell_media_api_response', 'sell_media_api_download_file', 10, 2 );

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
	$img_array['id']    = $attachment_id;
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

	/**
	 * @todo Searches can return attachments.
	 * In that case, the $post_id is the attachment id.
	 * Thus, trying to query post meta for attachment ids will fail.
	 */
	$post_id = $object['id'];
	if ( wp_get_post_parent_id( $post_id ) ) {
		$post_id = wp_get_post_parent_id( $post_id );
	}

	$attachment_ids = get_post_meta( $post_id, '_sell_media_attachment_id', true );

	// If ids are not an array.
	if ( ! is_array( $attachment_ids ) && '' !== $attachment_ids ) {
		$attachment_ids = explode( ',', $attachment_ids );
	}

	if ( empty( $attachment_ids ) ) {
		return;
	}

	// always start index at 0
	$attachment_ids = array_values( $attachment_ids );

	foreach ( $attachment_ids as $key => $value ) {
		$attachment_array[ $key ]['id']       = absint( $value );
		$attachment_array[ $key ]['parent']   = wp_get_post_parent_id( absint( $value ) );
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

	$attachments = ( is_array( $attachment_array ) ) ? (array) $attachment_array : '';

	return apply_filters( 'sell_media_filter_api_get_attachments', $attachments, $object, $field_name, $request );
}

/**
 * Pricing for rest api
 */
function sell_media_api_get_pricing( $object, $field_name = '', $request = '' ) {
	$post_id = $object['id'];

	/**
	 * Get post parent so we can show item pricing in attachment cartform
	 */
	if ( 'attachment' === get_post_type( $post_id ) ) {
		$attachment_id = $post_id;
		$post_id       = wp_get_post_parent_id( $attachment_id );
	}

	$attachment_id        = sell_media_get_attachment_id( $post_id );
	$products             = new SellMediaProducts();
	$pricing['downloads'] = $products->get_prices( $post_id, $attachment_id );
	$pricing['prints']    = $products->get_prices( $post_id, $attachment_id, 'reprints-price-group' );

	return $pricing;
}

/**
 * Meta to rest api
 */
function sell_media_api_get_meta( $object, $field_name = '', $request = '' ) {
	$meta['sell']         = array( 'Downloads' );
	$meta['product_type'] = wp_get_post_terms( $object['id'], 'product_type' );

	return apply_filters( 'sell_media_filter_api_get_meta', $meta, $object, $field_name, $request );
}

/**
 * Add meta to price group api endpoint
 */
function sell_media_api_get_price_group_meta( $object, $field_name, $request ) {
	$meta['price']  = get_term_meta( $object['id'], 'price', true );
	$meta['width']  = get_term_meta( $object['id'], 'width', true );
	$meta['height'] = get_term_meta( $object['id'], 'height', true );

	return $meta;
}

/**
 * Add meta to markup taxonomies api endpoint
 */
function sell_media_api_get_markup_meta( $object, $field_name, $request ) {
	$meta['markup']  = get_term_meta( $object['id'], 'markup', true );
	$meta['default'] = get_term_meta( $object['id'], 'default', true );

	return $meta;
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
		// $page    = empty( $request->get_param( 'page' ) ) ? $request->get_param( 'page' ) : 1;
		// $page    = intval( $page );
		// $args    = array(
		// 	'paged'          => $page,
		// 	'post_type'      => 'sell_media_item',
		// 	'posts_per_page' => get_option( 'posts_per_page' ),
		// );

		// // Search Query
		// if ( isset( $request['s'] ) ) {
		// 	$search    = $request->get_param( 's' );
		// 	$search    = implode( ' ', explode( '+', $search ) );
		// 	$search    = urldecode( $search );
		// 	$args['s'] = $search;
		// }

		$settings = sell_media_get_plugin_options();
		$search_term = $request->get_param( 's' );
		$product_type = $request->get_param( 'type' );

		// Find comma-separated search terms and format into an array
		$search_term_cleaned = preg_replace( '/\s*,\s*/', ',', $search_term );
		$search_terms = str_getcsv( $search_term_cleaned, ',' );

		// Find negative terms, like "-cow"
		$negative_search_terms = '';
		$negative_search_terms = preg_grep( '/\B-[^\B]+/', $search_terms );

		// Remove negative terms from the terms to be searched
		$search_terms = array_diff( $search_terms, $negative_search_terms );
		$search_terms = array_filter( $search_terms );

		// Remove negative sign (-) from negative search terms
		$negative_search_terms = preg_replace( '/[-]/', '', $negative_search_terms );

		// Get the file/mimetype
		$mime_type = sell_media_api_get_mimetype( $product_type );

		// Current pagination
		$paged = empty( $request->get_param( 'page' ) ) ? $request->get_param( 'page' ) : 1;
		$paged = intval( $paged );

		if ( ! empty( $settings->search_relation ) && 'and' === $settings->search_relation ) {
			$tax_array = array();
			foreach ( $search_terms as $s ) {
				$array = array(
					'taxonomy' => 'keywords',
					'field'    => 'name',
					'terms'    => $s,
				);
				$tax_array[] = $array;
			}
			foreach ( $negative_search_terms as $n ) {
				$array = array(
					'taxonomy' => 'keywords',
					'field'    => 'name',
					'terms'    => array( $n ),
					'operator' => 'NOT IN',
				);
				$tax_array[] = $array;
			}

			$tax_query = array(
				'relation' => 'AND',
				$tax_array
			);
		} else {
			// Add original full keyword to the search terms array
			// This ensures that multiple word keyword search works
			$one_big_keyword = str_replace( ',', ' ', $search_term );
			$search_terms[] .= $one_big_keyword;
			$tax_query = array(
				array(
					'taxonomy' => 'keywords',
					'field'    => 'name',
					'terms'    => $search_terms,
				)
			);
		}

		// The Query
		$args = array(
			'post_type' => 'attachment',
			'paged'		=> $paged,
			'post_status' => array( 'publish', 'inherit' ),
			'post_mime_type' => $mime_type,
			'post_parent__in' => sell_media_ids(),
			'tax_query' => $tax_query
		);
		$args = apply_filters( 'sell_media_search_args', $args );
		$search_query = new WP_Query();
		$posts = $search_query->query( $args );
		$posts = apply_filters( 'sell_media_api_search_result', $posts, $search_terms, $product_type, $paged );

		// mirror formatting of normal rest api endpoint for sell_media_item
		foreach ( $posts as $post ) {
			$obj['id'] = $post->ID;

			$img_array         = sell_media_api_get_image( $obj );
			$attachments_array = sell_media_api_get_attachments( $obj );
			$pricing           = sell_media_api_get_pricing( $obj );
			$meta              = sell_media_api_get_meta( $obj );

			// Get parent slug to build url
			$post_data   = get_post( $post->post_parent );
			$parent_slug = $post_data->post_name;

			$results[] = [
				'id'                        => $post->ID,
				'parent'                    => $post->post_parent,
				'title'                     => [
					'rendered' => $post->post_title,
				],
				'slug'                      => $post->post_name,
				'parent_slug'               => $parent_slug,
				'link'                      => get_permalink( $post->ID ),
				'content'                   => [
					'rendered' => get_the_content(),
				],
				'sell_media_featured_image' => $img_array,
				'sell_media_attachments'    => $attachments_array,
				'sell_media_pricing'        => $pricing,
				'sell_media_meta'           => $meta,
			];
		}

		if ( ! empty( $results ) ) {
			// Create the response object.
			$response = new WP_REST_Response( $results );

			// Add a custom header.
			$response->header( 'x-wp-total', $search_query->found_posts );
			$response->header( 'x-wp-totalpages', $search_query->max_num_pages );

			return $response;
		}
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
 * Licensing endpoint for rest api.
 * This is a pluggable function.
 * /wp-json/sell-media/v2/licensing/
 */
if ( ! function_exists( 'sell_media_api_licensing_response' ) ) :

	function sell_media_api_licensing_response( $request ) {

		$results = array();

		$markups    = new SellMediaTaxMarkup();
		$taxonomies = $markups->markup_taxonomies();

		foreach ( $taxonomies as $taxonomy ) {

			$tax_obj = get_taxonomy( $taxonomy );

			$results[$taxonomy]['name'] = $tax_obj->labels->name;

			$terms = get_terms( [
				'taxonomy'   => $taxonomy,
				'hide_empty' => false,
			] );
			if ( ! empty( $terms ) && ! is_wp_error( $terms ) ) {
				foreach ( $terms as $key => $term ) {
					$markup = get_term_meta( $term->term_id, 'markup', true );
					$results[$taxonomy]['terms'][$key]['id'] = $term->term_id;
					$results[$taxonomy]['terms'][$key]['name'] = $term->name;
					$results[$taxonomy]['terms'][$key]['description'] = $term->description;
					$results[$taxonomy]['terms'][$key]['markup'] = $markup;
					$results[$taxonomy]['terms'][$key]['taxonomy'] = $tax_obj->labels->name;
				}
			}
		}

		if ( empty( $results ) ) {
			return new WP_Error( 'sell_media_no_search_results', __( 'No results', 'sell_media' ) );
		}

		return rest_ensure_response( $results );
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
		if ( $overridden_template = locate_template( 'sell-media.php' ) ) {
			$template = $overridden_template;
		} else {
			$template = SELL_MEDIA_PLUGIN_DIR . '/themes/index.php';
		}
	} else {
		$template = $original_template;
	}

	return $template;
}
//add_filter( 'template_include', 'sell_media_api_template_redirect', 10 );

/**
 * Get the select value of the filetype field and conver it into a WP mimtype for WP_Query
 *
 * @param  string 		The filetype (image, video, audio)
 * @return array 		The WP mimetype format for each filetype
 */
function sell_media_api_get_mimetype( $filetype ) {
	if ( 'image' === $filetype ) {
		$mime = array( 'image/jpeg', 'image/gif', 'image/png', 'image/bmp', 'image/tiff', 'image/x-icon' );
	} elseif ( 'video' === $filetype ) {
		$mime = array( 'video/x-ms-asf', 'video/x-ms-wmv', 'video/x-ms-wmx', 'video/x-ms-wm', 'video/avi', 'video/divx', 'video/x-flv', 'video/quicktime', 'video/mpeg', 'video/mp4', 'video/ogg', 'video/webm', 'video/x-matroska' );
	} elseif ( 'audio' === $filetype ) {
		$mime = array( 'audio/mpeg', 'audio/x-realaudio', 'audio/wav', 'audio/ogg', 'audio/midi', 'audio/x-ms-wma', 'audio/x-ms-wax', 'audio/x-matroska' );
	} else {
		$mime = '';
	}

	return $mime;
}
