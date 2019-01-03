<?php

/**
 * Helper Functions
 *
 * @package Sell Media
 * @author Thad Allender <support@graphpaperpress.com>
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Template Redirect
 * @since 1.0.4
 */
function sell_media_template_redirect( $original_template ) {

	global $post;

	$post_type = array( 'sell_media_item', 'attachment' );
	$sell_media_taxonomies = get_object_taxonomies( $post_type );
	$sm_archive_template = SELL_MEDIA_PLUGIN_DIR . '/themes/archive.php';

	/**
	 * Archive -- Check if this is an archive page AND post type is sell media
	 */
	if ( is_post_type_archive( $post_type ) || is_tax( $sell_media_taxonomies ) ) {
		// allow users to override SM archive template by adding their own to their theme
		if ( $overridden_template = locate_template( 'archive-sell-media.php' ) ) {
			$template = $overridden_template;
		} elseif ( file_exists( $sm_archive_template ) ) {
			$template = $sm_archive_template;
		} else {
			$template = $original_template;
		}
	} elseif ( ! empty( $post ) && sell_media_attachment( $post->ID ) ) {
		// sell media attachments should use single.php, not attachment.php
		// not all attachment.php templates contain the_content
		// which we modify heavily using filters.
		$template = locate_template( 'single.php' );
	} else {
		$template = $original_template;
	}

	return $template;
}
add_filter( 'template_include', 'sell_media_template_redirect', 6 );

/**
 * Loads a template from a specified path
 *
 * @package Ajax
 * @uses load_template()
 * @since 0.1
 */
function sell_media_load_template() {

	if ( $overridden_template = locate_template( 'cart.php' ) ) {
		load_template( apply_filters( 'sell_media_cart_template', $overridden_template ) );
	} else {
		load_template( apply_filters( 'sell_media_cart_template', SELL_MEDIA_PLUGIN_DIR . '/themes/cart.php' ) );
	}
	die();
}
add_action( 'wp_ajax_nopriv_sell_media_load_template', 'sell_media_load_template' );
add_action( 'wp_ajax_sell_media_load_template', 'sell_media_load_template' );


/**
 * Redirect admins to the WP dashboard and other users Sell Media Dashboard
 *
 * @package Sell Media
 * @since 1.4.6
 */
function sell_media_redirect_login_dashboard( $redirect_to, $request, $user ) {
	if ( isset( $user->roles ) && is_array( $user->roles ) ) {
		// check for customers
		if ( in_array( 'sell_media_customer', $user->roles ) ) {
			$settings = sell_media_get_plugin_options();
			// redirect them to the dashboard
			$redirect_to = get_permalink( $settings->dashboard_page );
		}
	}
	return $redirect_to;
}
add_filter( 'login_redirect', 'sell_media_redirect_login_dashboard', 10, 3 );

/**
 * An array of pages required for plugin setup.
 * No need to define this in multiple places.
 *
 * @return an array of pages required for plugin setup.
 */
function sell_media_get_pages_array() {
	$pages = array( 'checkout', 'thanks', 'dashboard', 'login', 'search', 'lightbox' );

	return $pages;
}

/**
 * Checks if on sell media gallery page
 *
 * @return boolean true/false
 * @since 2.0.1
 */
function sell_media_page() {
	global $post;
	$settings = sell_media_get_plugin_options();

	if ( $post
	&& ( 'sell_media_item' === get_post_type( $post->ID )
	or sell_media_attachment( $post->ID )
	or is_tax( 'collection' )
	or is_tax( 'keywords' )
	or is_tax( 'creator' )
	or ( isset( $settings->search_page ) && is_page( $settings->search_page ) ) ) ) {
		return true;
	}
}

/**
 * Checks if attachment is for sale
 *
 * @var  $post_id the post or attachment id
 * @return  boolean true if has post meta (the product id)
 */
function sell_media_attachment( $post_id = null ) {

	global $post;
	$sale_product_id = get_post_meta( $post->ID, $key = '_sell_media_for_sale_product_id' );
	if ( is_singular( 'attachment' ) && ! empty( $sale_product_id ) ) {
		return true;
	}
}

/**
 * Builds html select field
 *
 * @since 0.1
 */
function sell_media_build_options( $taxonomy = null ) {

	if ( is_array( $taxonomy ) ) {
		extract( $taxonomy ); }

	if ( ! isset( $label ) ) {
		$label = $taxonomy; }

	// @todo need to merge
	$defaults = array(
		'value' => 'term_id',
	);

	// white list
	if ( empty( $prepend ) ) {
		$prepend = null; }

	if ( empty( $current_term ) ) {
		$current_term = null; }

	extract( $defaults );

	/** All Terms */
	$args = array(
		'orderby' => 'id',
		'hide_empty' => false,
		 );

	$terms = null;

	if ( isset( $post_id ) ) {
		$terms = wp_get_post_terms( $post_id, $taxonomy );
	} else {
		$terms = get_terms( $taxonomy, $args );
	}

	?>
	<?php if ( $terms ) : ?>
		<?php do_action( 'sell_media_build_options_before' ); ?>
		<?php foreach ( $terms as $term ) : ?>
			<?php $price = str_replace( '%', '', get_term_meta( $term->term_id, 'markup', true ) ); ?>
			<option
				value="<?php echo $prepend; ?><?php echo $term->$value; ?>"
				class="taxonomy-<?php echo $taxonomy; ?> term-<?php echo $term->slug; ?> <?php echo $taxonomy; ?>-<?php echo $term->term_id; ?>"
				data-value="<?php echo $term->slug; ?>"
				data-taxonomy="<?php echo $taxonomy; ?>"
				data-name="<?php echo $term->name; ?>"
				data-price="<?php echo $price; ?>"
				id="<?php echo $taxonomy; ?>-<?php echo $term->slug; ?>"
				title="<?php echo $term->description; ?>"
				name="<?php echo $taxonomy; ?>"
				>
			<?php echo $term->name; ?>
		</option>
		<?php endforeach; ?>
		</optgroup>
		<?php do_action( 'sell_media_build_options_after' ); ?>
	<?php endif; ?>
<?php }


/**
 * Builds html input field (radio or checkbox)
 *
 * @since 0.1
 */
function sell_media_build_input( $taxonomy = null ) {

	if ( is_array( $taxonomy ) ) {
		extract( $taxonomy ); }

	if ( ! isset( $label ) ) {
		$label = $taxonomy; }

	// @todo need to merge
	$defaults = array(
		'value' => 'term_id',
	);

	// white list
	if ( empty( $prepend ) ) {
		$prepend = null; }

	if ( empty( $current_term ) ) {
		$current_term = null; }

	extract( $defaults );

	/** All Terms */
	$args = array(
		'orderby' => 'id',
		'hide_empty' => false,
		 );

	$terms = null;

	if ( isset( $post_id ) ) {
		$terms = wp_get_post_terms( $post_id, $taxonomy );
	} else {
		$terms = get_terms( $taxonomy, $args );
	}

	?>
	<?php if ( $terms ) : ?>
		<?php do_action( 'sell_media_build_input_before' ); ?>
		<?php foreach ( $terms as $term ) : ?>
			<?php $price = get_term_meta( $term->term_id, 'markup', true ); ?>
			<input
				value="<?php echo $prepend; ?><?php echo $term->$value; ?>"
				class="taxonomy-<?php echo $taxonomy; ?> term-<?php echo $term->slug; ?> <?php echo $taxonomy; ?>-<?php echo $term->term_id; ?>"
				data-value="<?php echo $term->slug; ?>"
				data-taxonomy="<?php echo $taxonomy; ?>"
				data-name="<?php echo $term->name; ?>"
				data-price="<?php echo $price; ?>"
				id="<?php echo $taxonomy; ?>-<?php echo $term->slug; ?>"
				name="<?php echo $taxonomy; ?>"
				type="<?php echo $type; ?>"
				/>
			<?php echo $term->name; ?> <?php if ( $price ) : ?>+<?php echo $price; ?>%<?php endif; ?><br />
		<?php endforeach; ?>
		<?php do_action( 'sell_media_build_input_after' ); ?>
	<?php endif; ?>
<?php }


/**
 * Determine if we're on a Sell Media page in the admin
 *
 * @since 0.1
 */
function sell_media_is_sell_media_post_type_page() {

	if ( isset( $_GET['post_type'] ) && $_GET['post_type'] == 'sell_media_item' ) {
		return true;
	} else {
		return false;
	}
}


/**
 * Determine if the license page is being displayed on the admin
 *
 * @since 0.1
 */
function sell_media_is_license_page() {
	if ( isset( $_GET['action'] )
		&& $_GET['action'] == 'edit'
		&& isset( $_GET['taxonomy'] )
		&& $_GET['taxonomy'] == 'licenses' ) {
		return true;
	} else {
		return false;
	}
}


/**
 * Determine if the license term page is being displayed on the admin
 *
 * @since 0.1
 */
function sell_media_is_license_term_page() {

	if ( isset( $_GET['post_type'] ) && 'sell_media_item' === $_GET['post_type'] && isset( $_GET['taxonomy'] ) && 'licenses' === $_GET['taxonomy'] ) {
		return true;
	} else {
		return false;
	}
}


/**
 * Get Attachments
 *
 * Get attachment ids from post meta.
 * This function checks for both and returns a WP_Post object
 *
 * @param $post_id
 * @return WP_Post object
 * @since 2.0.1
 */
function sell_media_get_attachments( $post_id ) {
	$meta = get_post_meta( $post_id, '_sell_media_attachment_id', true );
	if ( is_array( $meta ) ) {
		return $meta;
	}
	return ( ! empty( $meta ) ) ? explode( ',', $meta ) : false;
}


/**
 * Get Attachment ID
 *
 * If the ID is an attachment, the $post_id is the $attachment_id.
 * Otherwise, get the attachments and assign
 * the first as the $attachment_id.
 *
 * @param int $post_id
 * @return int $attachment_id
 * @since 2.0.1
 */
function sell_media_get_attachment_id( $post_id = null ) {

	if ( 'attachment' === get_post_type( $post_id ) ) {
		$attachment_id = $post_id;
	} else {
		$attachments = sell_media_get_attachments( $post_id );
		$attachment_id = ( is_array( $attachments ) && ! empty( $attachments ) ) ? reset( $attachments ) : $post_id;
	}

	return $attachment_id;
}

/**
 * Check if item has multiple attachments
 */
function sell_media_has_multiple_attachments( $post_id ) {

	$attachments = sell_media_get_attachments( $post_id );
	if ( is_array( $attachments ) && count( $attachments ) > 0 ) {
		return true;
	}
	return false;
}

/**
 * Get Attachment Meta
 *
 * Returns the attachment meta field.
 * Use to get title, caption, description
 * Or anything else listed here:
 * https://codex.wordpress.org/Function_Reference/wp_prepare_attachment_for_js
 *
 * @param int $post_id
 * @param int $field
 * @uses wp_prepare_attachment_for_js();
 * @return string field (id, caption, title, description, etc)
 * @since 2.0.4
 */
function sell_media_get_attachment_meta( $post_id = null, $field = 'id' ) {

	if ( sell_media_has_multiple_attachments( $post_id ) ) {
		$attachment_id = get_query_var( 'id' );
	} else {
		$attachments = sell_media_get_attachments( $post_id );
		$attachment_id = $attachments[0];
	}

	$attachment_meta = wp_prepare_attachment_for_js( $attachment_id );

	return $attachment_meta[ $field ];
}


/**
 * Get the parent id of an attachment.
 * This is used in search and ajax requests for [sell_media_filters]
 * So that we can return only relevant attachments that are for sale
 * And have keywords.
 *
 * @param  attachment_id the attachment id
 * @return integer the parent id
 */
function sell_media_get_attachment_parent_id( $attachment_id = null ) {

	if ( wp_get_post_parent_id( $attachment_id ) ) {
		$parent_id = wp_get_post_parent_id( $attachment_id );
	} elseif ( get_post_meta( $attachment_id, '_sell_media_for_sale_product_id', true ) ) {
		$parent_id = get_post_meta( $attachment_id, '_sell_media_for_sale_product_id', true );
		if ( false === sell_media_post_exists( $parent_id ) ) {
			$parent_id = '';
		}
	} else {
		$parent_id = '';
	}

	return $parent_id;
}


/**
 * Determines if a post, identified by the specified ID, exist
 * within the WordPress database.
 *
 * @param    int $id    The ID of the post to check
 * @return   bool          True if the post exists; otherwise, false.
 * @since    2.0.1
 */
function sell_media_post_exists( $id ) {
	return is_string( get_post_status( $id ) );
}


/**
 * Get all sell media post ids
 * This is an expensive query, so let's cache it using transients.
 * This function is used in search queries to check if an attachment
 * has a post parent of one of the sell media entry ids.
 */
function sell_media_ids() {

	if ( false === ( $cached_ids = get_transient( 'sell_media_cached_ids' ) ) ) {

		$ids = get_posts(
			array(
				'post_type' => 'sell_media_item',
				'posts_per_page' => -1,
			)
		);

		$cached_ids = wp_list_pluck( $ids, 'ID' );

		set_transient( 'sell_media_cached_ids', $cached_ids, 12 * HOUR_IN_SECONDS );

	}

	return $cached_ids;
}


/**
 * If a new sell media entry is added, delete the cached ids transient.
 */
function sell_media_delete_cached_ids( $post_id ) {

	if ( 'sell_media_item' === get_post_type( $post_id ) ) {
		delete_transient( 'sell_media_cached_ids' );
	}

}
add_action( 'save_post', 'sell_media_delete_cached_ids' );


/**
 * Get Currency
 *
 * @since 0.1
 **/
function sell_media_get_currency() {
	$settings = sell_media_get_plugin_options();
	return apply_filters( 'sell_media_currency', empty( $settings->currency ) ? null : $settings->currency );
}


/**
 * Build currency values
 *
 * @since 0.1
 **/
function sell_media_get_currency_symbol( $currency = '' ) {
	if ( ! $currency ) { $currency = sell_media_get_currency(); }
	$currency_symbol = '';
	switch ( $currency ) :
		case 'BRL' : $currency_symbol = 'R&#36;';
break; // in Brazil the correct is R$ 0.00,00
		case 'AUD' :
		case 'CAD' :
		case 'MXN' :
		case 'NZD' :
		case 'HKD' :
		case 'SGD' :
		case 'USD' : $currency_symbol = '&#36;';
break;
		case 'EUR' : $currency_symbol = '&euro;';
break;
		case 'RMB' :
		case 'JPY' : $currency_symbol = '&yen;';
break;
		case 'TRY' : $currency_symbol = 'TL';
break;
		case 'NOK' : $currency_symbol = 'kr';
break;
		case 'ZAR' : $currency_symbol = 'R';
break;
		case 'CZK' : $currency_symbol = '&#75;&#269;';
break;
		case 'MYR' : $currency_symbol = 'RM';
break;
		case 'DKK' :
		case 'HUF' :
		case 'ILS' :
		case 'PHP' :
		case 'PLN' :
		case 'SEK' :
		case 'CHF' :
		case 'TWD' :
		case 'THB' : $currency_symbol = $currency;
break;
		case 'GBP' : $currency_symbol = '&pound;';
break;
		default    : $currency_symbol = '&#36;';
break;
	endswitch;
	return apply_filters( 'sell_media_currency_symbol', $currency_symbol, $currency );
}


/**
 * Returns the test mode option
 *
 * @since 0.1
 */
function sell_media_test_mode() {
	$settings = sell_media_get_plugin_options();
	return $settings->test_mode;
}


/**
 * Change order by on frontend
 *
 * @since 0.1
 * @return void
 */
function sell_media_order_by( $orderby_statement ) {
	
	$settings = sell_media_get_plugin_options();
	if ( ! empty( $settings->order_by )
		&& (
			is_post_type_archive( 'sell_media_item' )
			|| is_tax('collection')
			|| is_tax('licenses')
			|| is_tax('keywords')
			|| is_tax('creator')
			)
	) {
		global $wpdb;
		switch ( $settings->order_by ) {
			case 'title-asc' :
				$order_by = "{$wpdb->prefix}posts.post_title ASC";
				break;
			case 'title-desc' :
				$order_by = "{$wpdb->prefix}posts.post_title DESC";
				break;
			case 'date-asc' :
				$order_by = "{$wpdb->prefix}posts.post_date ASC";
				break;
			case 'date-desc' :
				$order_by = "{$wpdb->prefix}posts.post_date DESC";
				break;
		}
	} else {
		$order_by = $orderby_statement;
	}
	return $order_by;
}
if ( ! is_admin() ) {
	add_filter( 'posts_orderby', 'sell_media_order_by' );
}


/**
 * Returns the attachment ID file size
 *
 * @param $attachment_id ID of the attachment
 * @return string
 * @since 1.6.9
 */
function sell_media_get_filesize( $post_id = null, $attachment_id = null ) {

	$file_path = Sell_Media()->products->get_protected_file( $post_id, $attachment_id );

	if ( file_exists( $file_path ) ) {

		$bytes = filesize( $file_path );
		$s = array( 'b', 'Kb', 'Mb', 'Gb' );
		$e = floor( log( $bytes ) / log( 1024 ) );

		return sprintf( '%.2f ' . $s[ $e ], ( $bytes / pow( 1024, floor( $e ) ) ) );
	}
}


/**
 * Update the sales stats
 *
 * @since 0.1
 */
function sell_media_update_sales_stats( $product_id = null, $license_id = null, $price = null ) {

	$prev = maybe_unserialize( get_post_meta( $product_id, 'sell_media_sales_stats', true ) );

	$new[ $license_id ]['count'] = $prev[ $license_id ]['count'] + 1;
	$new[ $license_id ]['total'] = $prev[ $license_id ]['total'] + $price;
	$sales_stats_s = serialize( $new );

	return update_post_meta( $product_id, 'sell_media_sales_stats', $sales_stats_s );
}


/**
 * Echos the pagination for Archive pages.
 *
 * @since 1.0.1
 */
function sell_media_pagination_filter( $max_pages = '' ) {

	global $wp_query;
	$max_num_pages = ( '' != $max_pages ) ? $max_pages : $wp_query->max_num_pages;

	$big = 999999999; // need an unlikely integer

	$params = array(
		// 'base' => str_replace( $big, '%#%', esc_url( get_pagenum_link( $big ) ) ),
		'format' => '?paged=%#%',
		'current' => max( 1, get_query_var( 'paged' ) ),
		'total' => $max_num_pages,// note sometimes max_num_pages needs to be sent over
	);

	return '<div class="sell-media-pagination-container">' . paginate_links( $params ) . '</div>';
}
add_filter( 'sell_media_pagination_filter', 'sell_media_pagination_filter', 10, 1 );


/**
 * Determine if the payment reports page is being displayed on the admin
 *
 * @since 1.2
 */
function sell_media_is_reports_page() {

	if ( isset( $_SERVER['QUERY_STRING'] ) && 'post_type=sell_media_item&page=sell_media_reports' == $_SERVER['QUERY_STRING'] ) {
		return true;
	}
	else {
		return false;
	}
}

/**
 * Get Plugin data
 *
 * @since 1.2
 */
function sell_media_plugin_data( $field = null ) {
	$plugin_data = get_plugin_data( SELL_MEDIA_PLUGIN_FILE, $markup = true, $translate = true );
	return $plugin_data[ $field ];
}


/**
 * Build select fields
 *
 * @since 1.2
 */
function sell_media_build_select( $items = array(), $args = array() ) {
	extract( $args );

	if ( $required ) {
		$required = ' required ';
	} else {
		$required = false;
		$required_html = false;
	}

	if ( ! $title ) {
		$title = false;
	}

	if ( empty( $name ) ) {
		$name = null; }

	if ( empty( $current ) ) {
		$current = null; }
	?>
	<select id="<?php print $name; ?>" class="sell_media_form_control" name="<?php print $name; ?>" <?php print $required; ?>>
		<option></option>
		<?php foreach ( $items as $key => $value ) : ?>
			<option value="<?php print $key; ?>" <?php selected( $key, $current ); ?>><?php print $value; ?></option>
		<?php endforeach; ?>
	</select>
<?php }


/**
 * Return either the the custom price group or the default price group from settings
 * Used for showing price groups on cart popup
 *
 * @param $post_id, $taxonomy
 * @return $price_groups (object)
 */
function sell_media_get_price_groups( $post_id = null, $taxonomy = null ) {

	// first, check price group set on the item
	$price_groups_custom = wp_get_post_terms( $post_id, $taxonomy );

	foreach ( $price_groups_custom as $price_group ) {
		if ( $price_group->parent == 0 ) {
			$parent_price_group = $price_group->term_id;
		}
	}

	// if the item doesn't have a price group set, use the default from settings
	if ( empty( $price_groups_custom ) ) {

		$settings = sell_media_get_plugin_options();

		if ( $taxonomy == 'reprints-price-group' ) {
			$price_group_id = $settings->reprints_default_price_group;
		} else {
			$price_group_id = $settings->default_price_group;
		}

		$default_price_group_obj = get_term( $price_group_id, $taxonomy );

		if ( is_null( $default_price_group_obj ) || is_wp_error( $default_price_group_obj ) ) {
			return; }

		$parent_price_group = $default_price_group_obj->term_id;
	}

	$args = array(
		'type' => 'sell_media_item',
		'hide_empty' => false,
		'parent' => $parent_price_group,
		'taxonomy' => $taxonomy,
		'orderby' => 'id',
		);

	$price_groups = get_categories( $args );

	return $price_groups;

}

/**
 * Get the assigned price group
 *
 * @param $post_id, $taxonomy
 * @since 2.0.1
 * @return integer $price_group_id
 */
function sell_media_get_item_price_group( $post_id, $taxonomy ) {
	$settings = sell_media_get_plugin_options();
	$terms = get_the_terms( $post_id, $taxonomy );
	if ( $terms && ! is_wp_error( $terms ) ) { foreach ( $terms as $term ) {
			if ( $term->parent == 0 ) {
				$price_group_id = $term->term_id;
			}
	}
	} elseif ( $taxonomy == 'reprints-price-group' ) {
		$price_group_id = $settings->reprints_default_price_group;
	} elseif ( $taxonomy == 'price-group' ) {
		$price_group_id = $settings->default_price_group;
	} else {
		$price_group_id = 0;
	}

	return $price_group_id;
}


/**
 * Retrieve the absolute path to the file upload directory without the trailing slash
 *
 * @since  1.8.5
 * @return string $path Absolute path to the sell_media upload directory
 */
function sell_media_get_upload_dir() {
	$wp_upload_dir = wp_upload_dir();
	wp_mkdir_p( $wp_upload_dir['basedir'] . '/sell_media' );
	$path = $wp_upload_dir['basedir'] . '/sell_media';

	return apply_filters( 'sell_media_get_upload_dir', $path );
}


/**
 * Retrieve the absolute path to the packages file upload directory without the trailing slash
 *
 * @since  1.8.5
 * @return string $path Absolute path to the sell_media/packages upload directory
 */
function sell_media_get_packages_upload_dir() {
	$wp_upload_dir = wp_upload_dir();
	wp_mkdir_p( $wp_upload_dir['basedir'] . '/sell_media/packages' );
	$path = $wp_upload_dir['basedir'] . '/sell_media/packages';

	return apply_filters( 'sell_media_get_packages_upload_dir', $path );
}


/**
 * Retrieve the absolute path to the import directory without the trailing slash
 *
 * @since  2.0.1
 * @return string $path Absolute path to the sell_media/import directory
 */
function sell_media_get_import_dir() {
	$wp_upload_dir = wp_upload_dir();
	wp_mkdir_p( $wp_upload_dir['basedir'] . '/sell_media/import' );
	$path = $wp_upload_dir['basedir'] . '/sell_media/import';

	return apply_filters( 'sell_media_get_import_dir', $path );
}


/**
 * Get directories
 *
 * @since 2.0.1
 * @param $dir (packages or import)
 * @return array (directories)
 */
function sell_media_get_directories( $dir = null ) {

	$directories = '';
	$path = ( 'packages' === $dir ) ? sell_media_get_packages_upload_dir() : sell_media_get_import_dir();

	foreach ( glob( $path . '/*', GLOB_ONLYDIR ) as $directory ) {
		$directories[] = $directory;
	}
	return $directories;
}


/**
 * Retrieve the url to the file upload directory without the trailing slash
 *
 * @since  1.8.5
 * @return string $url url to the sell_media upload directory
 */
function sell_media_get_upload_dir_url() {
	$wp_upload_dir = wp_upload_dir();
	$url = $wp_upload_dir['baseurl'] . '/sell_media';

	return apply_filters( 'sell_media_get_upload_dir_url', $url );
}

/**
 * Disable cache on Checkout and Thanks pages
 *
 * @since 2.0.2
 * @return void
 */
function sell_media_nocache() {

	if ( is_admin() ) {
		return; }

	if ( false === ( $page_uris = get_transient( 'sell_media_cache_excluded_uris' ) ) ) {
		$settings       = sell_media_get_plugin_options();
		$checkout_page  = isset( $settings->checkout_page ) ? $settings->checkout_page: '';
		$thanks_page    = isset( $settings->thanks_page ) ? $settings->thanks_page: '';

		if ( empty( $checkout_page ) || empty( $thanks_page ) ) {
			return; }

		$page_uris   = array();
		// Exclude IPN listener
		$page_uris[] = '?sell_media-listener=IPN';
		// Exclude default permalinks for pages
		$page_uris[] = '?page_id=' . $checkout_page;
		$page_uris[] = '?page_id=' . $thanks_page;
		// Exclude nice permalinks for pages
		$checkout_page  = get_post( $checkout_page );
		$thanks_page    = get_post( $thanks_page );
		if ( ! is_null( $checkout_page ) ) {
			$page_uris[] = '/' . $checkout_page->post_name; }
		if ( ! is_null( $thanks_page ) ) {
			$page_uris[] = '/' . $thanks_page->post_name; }
		set_transient( 'sell_media_cache_excluded_uris', $page_uris );
	}

	if ( is_array( $page_uris ) ) {
		foreach ( $page_uris as $uri ) {
			if ( strstr( $_SERVER['REQUEST_URI'], $uri ) ) {
				if ( ! defined( 'DONOTCACHEPAGE' ) ) {
					define( 'DONOTCACHEPAGE', 'true' ); }
				nocache_headers();
				break;
			}
		}
	}
}
add_action( 'init', 'sell_media_nocache', 0 );


/**
 * Filters the default thumbnail size requested on archives and galleries
 *
 * @since 2.1.3
 *
 * @return string thumbnail size param
 */
function sell_media_thumbnail_crop() {
	$settings = sell_media_get_plugin_options();
	if ( $settings->thumbnail_crop ) {
		return $settings->thumbnail_crop;
	}

}
add_filter( 'sell_media_thumbnail', 'sell_media_thumbnail_crop', 10, 1 );


/**
 * Change buy button text to download if price if 0.
 *
 * @since 2.0.7
 *
 * @param  String $text     Button Text.
 * @param  int    $post_id  Id of post.
 * @return String           Button Text.
 */
function sell_media_free_download_button_text( $text, $post_id, $attachment_id = null, $type = 'download' ) {
	if ( 'download' != $type ) {
		return $text;
	}

	$price = get_post_meta( $post_id, 'sell_media_price', true );
	if ( $price <= 0 ) {
		 $text = __( 'Download', 'sell_media' ); }

	return $text;
}

add_filter( 'sell_media_purchase_text', 'sell_media_free_download_button_text', 10, 4 );
add_filter( 'sell_media_add_to_cart_text', 'sell_media_free_download_button_text', 10, 4 );

/**
 * Change button html.
 *
 * @since 2.0.7
 *
 * @param  string  $html             Html output of button.
 * @param  int     $post_id          ID of post.
 * @param  int     $attachment_id    ID of attachment
 * @param  string  $button           Button type.
 * @param  string  $text             Button Text.
 * @param  boolean $echo             Echo output or return.
 * @return string                    Html output of button.
 */
function sell_media_free_download_button_button( $html, $post_id, $attachment_id, $button, $text, $echo, $type = 'download' ) {

	if ( 'download' != $type ) {
		return $html;
	}

	$price = get_post_meta( $post_id, 'sell_media_price', true );
	$value = get_post_meta( $post_id, 'sell_media_free_downloads', true );
	if ( $price > 0 || $value ) {
		return $html; }

	$classes[] = 'item_add';
	$classes[] = 'sell-media-button';
	if ( ! is_null( $button ) ) {
		$classes[] = 'sell-media-' . $button;
	}
	$classes[] = 'sell-media-download-button';
	$classes = implode( ' ', $classes );

	$link = sprintf( '%s?download=free&product_id=%d&attachment_id=%d&payment_id=free', home_url(), $post_id, $attachment_id );
	$html = '<a href="' . $link . '" title="' . $text . '" data-product-id="' . esc_attr( $post_id ) . '" data-attachment-id="' . esc_attr( $attachment_id ) . '" class="' . $classes . '">' . $text . '</a>';
	return $html;
}

add_filter( 'sell_media_item_buy_button', 'sell_media_free_download_button_button', 10, 7 );
add_filter( 'sell_media_item_add_to_cart_button', 'sell_media_free_download_button_button', 10, 7 );

/**
 * Forces the file to be downloaded for free.
 *
 * @since 2.0.7
 *
 * @param  init $post_id       ID of post
 * @param  init $attachment_id ID of attacment
 * @return void
 */
function sell_media_free_download_file( $post_id, $attachment_id ) {

	$price = get_post_meta( $post_id, 'sell_media_price', true );

	// product is not free, so die
	if ( $price > 0 ) {

		do_action( 'sell_media_zero_price_download_fail', $post_id, $attachment_id );

		wp_die( __( 'Nice try, but this file is not a free download.', 'sell_media' ), __( 'Purchase Verification Failed', 'sell_media' ) );

	} else {

		$requested_file = Sell_Media()->products->get_protected_file( $post_id, $attachment_id );
		$file_type = wp_check_filetype( $requested_file );

		if ( ! ini_get( 'safe_mode' ) ) {
			set_time_limit( 0 );
		}

		if ( function_exists( 'get_magic_quotes_runtime' ) && get_magic_quotes_runtime() ) {
			set_magic_quotes_runtime( 0 );
		}

		if ( function_exists( 'apache_setenv' ) ) { @apache_setenv( 'no-gzip', 1 ); }
		@ini_set( 'zlib.output_compression', 'Off' );

		nocache_headers();
		header( 'Robots: none' );
		header( 'Content-Type: ' . $file_type['type'] . '' );
		header( 'Content-Description: File Transfer' );
		header( 'Content-Disposition: attachment; filename="' . basename( $requested_file ) . '"' );
		header( 'Content-Transfer-Encoding: binary' );

		// Deliver the download
		Sell_Media()->download->download_file( $requested_file );

		exit();
	}

}

add_action( 'sell_media_before_failed_download', 'sell_media_free_download_file', 10, 2 );

/**
 * Get current sell media plugin version.
 * @return int Retrun current sell media plugin version.
 */
function sell_media_version() {
	$option_name = 'sell_media_version';
	$default_value = 0;

	if ( is_multisite() ) {
		$blog_id = get_current_blog_id();
		$version = get_blog_option( $blog_id, $option_name, $default_value );
		return $version;
	}

	$version = get_option( $option_name, $default_value );
	return $version;
}

/**
 * Get option based on the site type
 * @param  string $option  Name of option to add. Expected to not be SQL-escaped.
 * @param  mixed  $value       Optional. Option value. Must be serializable if non-scalar. Expected to not be SQL-escaped.
 * @return bool False if option was not added and true if option was added.
 */
function sell_media_get_option( $option, $default = false ) {
	if ( is_multisite() ) {
		return get_blog_option( get_current_blog_id(), $option, $default );
	}

	return get_option( $option, $default );
}

/**
 * Add a new option based on the site type
 * @param  string      $option    Name of option to add. Expected to not be SQL-escaped.
 * @param  mixed       $value      Optional. Option value. Must be serializable if non-scalar. Expected to not be SQL-escaped.
 * @param  string      $deprecated Optional. Description. Not used anymore.
 * @param  string|bool $autoload   Optional. Whether to load the option when WordPress starts up.
 * @return bool             False if option was not added and true if option was added.
 */
function sell_media_add_option( $option, $value = '', $deprecated = '', $autoload = 'yes' ) {
	if ( is_multisite() ) {
		return add_blog_option( get_current_blog_id(), $option, $value );
	}

	return add_option( $option, $value, $deprecated, $autoload );
}

/**
 * Update the value of an option that was already added based on site type.
 * @param  string      $option    Name of option to add. Expected to not be SQL-escaped.
 * @param  mixed       $value      Optional. Option value. Must be serializable if non-scalar. Expected to not be SQL-escaped.
 * @param  string|bool $autoload   Optional. Whether to load the option when WordPress starts up.
 * @return bool             False if option was not added and true if option was added.
 */
function sell_media_update_option( $option, $value, $autoload = null ) {
	if ( is_multisite() ) {
		return update_blog_option( get_current_blog_id(), $option, $value );
	}

	return update_option( $option, $value, $autoload );
}

/**
 * Search for the attachment parent post.
 * @param  int  $post_id attachment post id
 * @param  boolean $single  Return single value or array
 * @return mixed           Returns parent.
 */
function sell_media_attachment_parent_post( $post_id, $single = true ){
	$args['post_type'] = "sell_media_item";
	$args['meta_query'] = array(
			array(
				'key'     => '_sell_media_attachment_id',
				'value'   => $post_id,
				'compare' => 'LIKE'
			),
		);

	$items = get_posts( $args );
	if( empty( $items ) )
		return false;

	if( $single )
		return $items[0];

	return $items;
}


function sell_media_update_attachment_metadata1( $data, $post_id ){
	if( !isset( $data['sizes']['large'] ) || !isset( $data['file'] ) ){
		return $data;
	}
	$uploads = wp_upload_dir();
	$sm_file = trailingslashit( $uploads['basedir'] ) . 'sell_media/' . $data['file'];
	if( !file_exists( $sm_file ) )
		return $data;

	$main_file = trailingslashit( $uploads['basedir'] ) . $data['file'];
	$filename = basename( $data['file'] );
	$upload_folder = trailingslashit( dirname( $main_file ) );
	$copy = copy($sm_file, $main_file);

	if ( $copy ) {

		// If function doesn't exist, include function file.
		if ( ! function_exists( 'wp_generate_attachment_metadata' ) ) {
			include( ABSPATH . 'wp-admin/includes/image.php' );
		}

		$metadata = wp_generate_attachment_metadata( $post_id, $main_file );

		$date_folder = dirname( $data['file'] );
		$large_file = trailingslashit( $uploads['basedir'] ) .trailingslashit( $date_folder ) . $metadata['sizes']['large']['file'];
		$main_file = trailingslashit( $uploads['basedir'] ) . $data['file'];

		if( file_exists( $large_file ) ){
			$copy = copy( $large_file, $main_file );
			if( $copy ){

				$data['width'] = $metadata['sizes']['large']['width'];
				$data['height'] = $metadata['sizes']['large']['height'];
				$data['sizes']['large']['width'] = $metadata['sizes']['large']['width'];
				$data['sizes']['large']['height'] = $metadata['sizes']['large']['height'];
				$data['sizes']['large']['file'] = $metadata['sizes']['large']['file'];
				$data['sizes']['large']['mime-type'] = $metadata['sizes']['large']['mime-type'];
			}
		}
	}
	return $data;
}
add_filter( 'wp_update_attachment_metadata', 'sell_media_update_attachment_metadata1', 10, 2 );

/**
 * Regenerate thumbnails if original image is found in sell media upload folder.
 * @param  array $data          Meta data array.
 * @param  int 	 $attachment_id 	Attachment id.
 * @return array                Updated meta data array.
 */
function sell_media_generate_attachment_metadata( $data, $attachment_id ) {

	/**
	 * If $data['file'] isn't set, the files are missing.
	 * So, let's derive $data['file'] from the missing public filepath.
	 */
	if ( empty( $data['file'] ) ) {
		$data['file'] = sell_media_get_public_filepath( $attachment_id );
	}

	$uploads = wp_upload_dir();
	$sm_file_path = trailingslashit( $uploads['basedir'] ) . 'sell_media/' . $data['file'];
	$sm_file = apply_filters( 'sell_media_original_image_path', $sm_file_path, $attachment_id, $data );
	if ( ! file_exists( $sm_file ) )
		return $data;

	$main_file = trailingslashit( $uploads['basedir'] ) . $data['file'];

	@set_time_limit( 900 );
	$copy = copy( $sm_file, $main_file );

	if ( ! $copy )
		return $data;

	$file = $main_file;

	// Core Start
	$attachment = get_post( $attachment_id );

	$metadata = array();
	$support = false;
	if ( preg_match('!^image/!', get_post_mime_type( $attachment )) && file_is_displayable_image($file) ) {
		$imagesize = getimagesize( $file );
		$metadata['width'] = $imagesize[0];
		$metadata['height'] = $imagesize[1];

		// Make the file path relative to the upload dir.
		$metadata['file'] = _wp_relative_upload_path($file);

		// Make thumbnails and other intermediate sizes.
		global $_wp_additional_image_sizes;

		$sizes = array();
		foreach ( get_intermediate_image_sizes() as $s ) {
			$sizes[$s] = array( 'width' => '', 'height' => '', 'crop' => false );
			if ( isset( $_wp_additional_image_sizes[$s]['width'] ) )
				$sizes[$s]['width'] = intval( $_wp_additional_image_sizes[$s]['width'] ); // For theme-added sizes
			else
				$sizes[$s]['width'] = get_option( "{$s}_size_w" ); // For default sizes set in options
			if ( isset( $_wp_additional_image_sizes[$s]['height'] ) )
				$sizes[$s]['height'] = intval( $_wp_additional_image_sizes[$s]['height'] ); // For theme-added sizes
			else
				$sizes[$s]['height'] = get_option( "{$s}_size_h" ); // For default sizes set in options
			if ( isset( $_wp_additional_image_sizes[$s]['crop'] ) )
				$sizes[$s]['crop'] = $_wp_additional_image_sizes[$s]['crop']; // For theme-added sizes
			else
				$sizes[$s]['crop'] = get_option( "{$s}_crop" ); // For default sizes set in options
		}

		$sizes = apply_filters( 'intermediate_image_sizes_advanced', $sizes, $metadata );

		if ( $sizes ) {
			$editor = wp_get_image_editor( $file );

			if ( ! is_wp_error( $editor ) )
				$metadata['sizes'] = $editor->multi_resize( $sizes );
		} else {
			$metadata['sizes'] = array();
		}

		// Fetch additional metadata from EXIF/IPTC.
		$image_meta = wp_read_image_metadata( $file );
		if ( $image_meta )
			$metadata['image_meta'] = $image_meta;

	} elseif ( wp_attachment_is( 'video', $attachment ) ) {
		$metadata = wp_read_video_metadata( $file );
		$support = current_theme_supports( 'post-thumbnails', 'attachment:video' ) || post_type_supports( 'attachment:video', 'thumbnail' );
	} elseif ( wp_attachment_is( 'audio', $attachment ) ) {
		$metadata = wp_read_audio_metadata( $file );
		$support = current_theme_supports( 'post-thumbnails', 'attachment:audio' ) || post_type_supports( 'attachment:audio', 'thumbnail' );
	}

	if ( $support && ! empty( $metadata['image']['data'] ) ) {
		// Check for existing cover.
		$hash = md5( $metadata['image']['data'] );
		$posts = get_posts( array(
			'fields' => 'ids',
			'post_type' => 'attachment',
			'post_mime_type' => $metadata['image']['mime'],
			'post_status' => 'inherit',
			'posts_per_page' => 1,
			'meta_key' => '_cover_hash',
			'meta_value' => $hash
		) );
		$exists = reset( $posts );

		if ( ! empty( $exists ) ) {
			update_post_meta( $attachment_id, '_thumbnail_id', $exists );
		} else {
			$ext = '.jpg';
			switch ( $metadata['image']['mime'] ) {
			case 'image/gif':
				$ext = '.gif';
				break;
			case 'image/png':
				$ext = '.png';
				break;
			}
			$basename = str_replace( '.', '-', basename( $file ) ) . '-image' . $ext;
			$uploaded = wp_upload_bits( $basename, '', $metadata['image']['data'] );
			if ( false === $uploaded['error'] ) {
				$image_attachment = array(
					'post_mime_type' => $metadata['image']['mime'],
					'post_type' => 'attachment',
					'post_content' => '',
				);

				$image_attachment = apply_filters( 'attachment_thumbnail_args', $image_attachment, $metadata, $uploaded );

				$sub_attachment_id = wp_insert_attachment( $image_attachment, $uploaded['file'] );
				add_post_meta( $sub_attachment_id, '_cover_hash', $hash );
				$attach_data = wp_generate_attachment_metadata( $sub_attachment_id, $uploaded['file'] );
				wp_update_attachment_metadata( $sub_attachment_id, $attach_data );
				update_post_meta( $attachment_id, '_thumbnail_id', $sub_attachment_id );
			}
		}
	}

	// Remove the blob of binary data from the array.
	if ( $metadata ) {
		unset( $metadata['image']['data'] );
	}

	// Core END

	// Sometimes the original source file is smaller than the large size
	// this causes the copy to fail
	if ( array_key_exists( 'large', $metadata['sizes'] ) ) {

		$date_folder = dirname( $data['file'] );
		$large_file = trailingslashit( $uploads['basedir'] ) .trailingslashit( $date_folder ) . $metadata['sizes']['large']['file'];
		$main_file = trailingslashit( $uploads['basedir'] ) . $data['file'];

		if ( file_exists( $large_file ) ){
			$copy = copy( $large_file, $main_file );
			if ( $copy ) {

				$metadata['width'] = $metadata['sizes']['large']['width'];
				$metadata['height'] = $metadata['sizes']['large']['height'];
			}
		}
	}

	do_action( 'sell_media_after_generate_attachment_metadata', $attachment_id, $metadata );

	return $metadata;
}
add_filter( 'wp_generate_attachment_metadata', 'sell_media_generate_attachment_metadata', 10, 2 );


/*
 * Regenerate missing attachment files
 * If for some reason the file is missing in public uploads
 * we should attempt to regenerate the thumbnails from the private source file
 * and generate a new thumbnails.
 */
function sell_media_regenerate_missing_files( $post_id ) {

	if ( sell_media_has_multiple_attachments( $post_id ) ) {
		$attachment_ids = sell_media_get_attachments( $post_id );
	} else {
		$attachment_ids = array( get_post_meta( $post_id, '_sell_media_attachment_id', true ) );
	}

	if ( $attachment_ids ) foreach ( $attachment_ids as $attachment_id ) {

		// Check if attachment is image.
		if ( ! wp_attachment_is_image( $attachment_id ) )
			return false;

		// Retrieve attached file path based on attachment ID.
		$attached_file = get_attached_file( $attachment_id );

		// File exists, so bail
		if ( file_exists( $attached_file ) ) {
			return false;
		}

		/**
		 * Unlike photos, video and audio files aren't copied to public directory.
		 * This means $attachment_meta['file'] will be empty.
		 * So we only proceed if the file parameter exists.
		 */
		$attachment_metadata = wp_get_attachment_metadata( $attachment_id );

		// build url from public attachment url
		if ( empty( $attachment_metadata['file'] ) ) {
			$attachment_metadata['file'] = sell_media_get_public_filepath( $attachment_id );
		}

		if ( ! empty( $attachment_metadata['file'] ) ) {

			// build the public file path.
			$upload_dir = wp_upload_dir();
			$public_file_path = $upload_dir['basedir'] . '/' . $attachment_metadata['file'];

			// get the original protected file.
			$original_file_path = sell_media_get_upload_dir() . '/' . $attachment_metadata['file'];

			/**
			 * @todo Imported files are saved to filepath for sell_media_item publish date
			 */

			// check if the original protected file exists
			if ( file_exists( $original_file_path ) ) {
				copy( $original_file_path, $public_file_path );
				@set_time_limit( 900 );
				require_once( ABSPATH . 'wp-admin/includes/image.php' );
				$metadata = wp_generate_attachment_metadata( $attachment_id, $attached_file );
				if ( !is_wp_error( $metadata ) && !empty( $metadata )  ){
					wp_update_attachment_metadata( $attachment_id, $metadata );
				}
			}
		}
	}
}
add_action( 'sell_media_before_content', 'sell_media_regenerate_missing_files' );


/**
 * Gets the public filepath for an attachment
 * @param  int $attachment_id the attachment id
 * @return string returns something like 2016/02/image.jpg
 */
function sell_media_get_public_filepath( $attachment_id ) {
	$public_file = wp_get_attachment_url( $attachment_id );
	$string = '/uploads/';
	if ( ( $pos = strpos( $public_file, $string ) ) !== FALSE ) {
		return substr( $public_file, strpos( $public_file, $string ) + strlen( $string ) );
	}
}


/**
 * Clear cart after payment is completed.
 * @return void
 */
function sell_media_clear_cart_after_payment() {
	$clear = false;

	if ( isset( $_GET['tx'] ) && !empty( $_GET['tx'] ) ) {
		$clear = true;
	} else if ( isset( $_POST['txn_id'] ) && ! empty( $_POST['txn_id'] ) ) {
		$clear = true;
	}

	$clear = apply_filters( 'sell_media_clear_cart_after_payment', $clear );

	if ( ! $clear ) {
		return false;
	}

	global $sm_cart;
	$sm_cart->clear();
}

add_action( 'init', 'sell_media_clear_cart_after_payment' );

/**
 * Add migration cron event.
 * This is essentially the same code that fires during
 * plugin activation hook. For some reasons, the upgrade
 * event wasn't working. In case the user upgraded
 * @return void
 */
function sell_media_migration_cron_event() {
	$migrated = get_option( 'sell_media_keywords_migrated' );
	if ( ! $migrated ) {
		// Schedule an event that fires every minute to repair attachments in chunks.
		do_action( 'sell_media_migrate_keywords' );
	}
}
add_action( 'init', 'sell_media_migration_cron_event' );

/**
 * Check if e-commerce is enabled.
 */
function sell_media_ecommerce_enabled( $post_id ) {
	$status = true;
	$meta = get_post_meta( $post_id, 'sell_media_enable_ecommerce', true );
	if ( class_exists( 'VS_Platform' ) && 0 === $meta ) {
		$status = false;
	}
	return $status;
}