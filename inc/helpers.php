<?php

/**
 * Helper Functions
 *
 * @package Sell Media
 * @author Thad Allender <support@graphpaperpress.com>
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) { exit; }

/**
 * Template Redirect
 * @since 1.0.4
 */
function sell_media_template_redirect( $original_template ) {

	$post_type = 'sell_media_item';
	$sell_media_taxonomies = get_object_taxonomies( $post_type );

	/**
	 * Archive -- Check if this is an archive page AND post type is sell media
	 */
	if ( is_post_type_archive( $post_type ) || is_tax( $sell_media_taxonomies ) ) {
		// allow users to override SM archive template by adding their own to their theme
		if ( $overridden_template = locate_template( 'archive-sell-media.php' ) ) {
			$template = $overridden_template;
		} else {
			$template = SELL_MEDIA_PLUGIN_DIR . '/themes/archive.php';
		}
	} else {
		$template = $original_template;
	}

	return $template;
}
add_filter( 'template_include', 'sell_media_template_redirect', 6 );

/**
 * Get search form
 *
 * @param  $form
 * @return $form
 */
function sell_media_get_search_form( $form ) {
	// Change the default WP search form if is Sell Media search
	if ( is_search() && 'sell_media_item' == get_query_var( 'post_type' ) ) {
		$form = Sell_Media()->search->form();
	}
	return $form;
}
add_filter( 'get_search_form', 'sell_media_get_search_form' );

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
	global $user;
	if ( isset( $user->roles ) && is_array( $user->roles ) ) {
		if ( in_array( 'sell_media_customer', $user->roles ) ) {
			return site_url( 'dashboard' );
		} else {
			return admin_url();
		}
	}
}
add_filter( 'login_redirect', 'sell_media_redirect_login_dashboard', 10, 3 );

/**
 * Add specific CSS classes to the body_class
 *
 * @since 1.9.2
 */
function sell_media_body_class( $classes ) {
	global $post;

	if ( empty( $post ) ) {
		return; }

	$settings = sell_media_get_plugin_options();

	// Pages assigned with shortcode
	$pages = sell_media_get_pages_array();
	foreach ( $pages as $page ) {
		$setting = $page . '_page';
		if ( isset( $settings->$setting ) && $post->ID == $settings->$setting ) {
			$classes[] = 'sell-media-page';
			$classes[] = 'sell-media-' . str_replace( '_', '-', $setting );
		}
	}

	// Shortcodes
	$shortcodes = array( 'sell_media_thanks', 'sell_media_searchform', 'sell_media_item', 'sell_media_all_items', 'sell_media_checkout', 'sell_media_download_list', 'sell_media_price_group', 'sell_media_list_all_collections', 'sell_media_login_form' );
	foreach ( $shortcodes as $shortcode ) {
		if ( isset( $post->post_content ) && has_shortcode( $post->post_content, $shortcode ) ) {
			$classes[] = 'sell-media-page';
		}
	}

	// All Sell Media pages
	if ( ! empty( $post->ID ) && 'sell_media_item' == get_post_type( $post->ID ) ) {
		$classes[] = 'sell-media-page';
	}

	// Layout
	if ( isset( $settings->layout ) ) {
		$classes[] = $settings->layout;
	}

	// Gallery
	if ( sell_media_is_gallery_page() ) {
		$classes[] = 'sell-media-gallery-page';
	}

	// Theme
	$theme = wp_get_theme();
	$classes[] = 'theme-' . sanitize_title_with_dashes( $theme->get( 'Name' ) );

	return $classes;
}
add_filter( 'body_class', 'sell_media_body_class' );

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
 * Adds a custom query var for gallery links
 *
 * @param  $vars Existing query vars
 * @return $vars Updated query vars
 * @since 2.0.1
 */
function sell_media_add_query_vars_filter( $vars ) {
	$vars[] = 'id';
	return $vars;
}
add_filter( 'query_vars', 'sell_media_add_query_vars_filter' );

/**
 * Checks if on sell media gallery page
 *
 * @return boolean true/false
 * @since 2.0.1
 */
function sell_media_is_gallery_page() {
	global $post;

	if ( ! $post ) {
		return false; }

	if ( $post->ID && sell_media_has_multiple_attachments( $post->ID ) && get_query_var( 'id' ) == false ) {
		return true; }
}

/**
 * Add custom class to nav menu items
 */
function sell_media_nav_menu_css_class( $classes, $item ) {
	$settings = sell_media_get_plugin_options();

	if ( $item->object == 'page' ) {
		if ( $item->object_id == $settings->lightbox_page ) {
			$classes[] = 'lightbox-menu';
		}
		if ( $item->object_id == $settings->checkout_page ) {
			if ( in_array( 'total', $item->classes ) ) {
				$classes[] = 'checkout-total';
			} else {
				$classes[] = 'checkout-qty';
			}
		}
	}

	return $classes;
}
add_filter( 'nav_menu_css_class', 'sell_media_nav_menu_css_class', 10, 2 );

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
 * Parse the iptc info and retrieve the given value.
 *
 * @since 0.1
 */
function sell_media_iptc_parser( $value = null, $image = null ) {

	$size = getimagesize( $image, $info );

	if ( ! isset( $info['APP13'] ) ) {
		return; }

	$iptc = iptcparse( $info['APP13'] );

	switch ( $value ) {
		case 'keywords':
			if ( isset( $iptc['2#025'] ) ) {
				return $iptc['2#025']; }

			case 'city':
				if ( isset( $iptc['2#090'] ) ) {
					return $iptc['2#090']; }

				case 'region':
					if ( isset( $iptc['2#095'] ) ) {
						return $iptc['2#095']; }

					case 'country':
						if ( isset( $iptc['2#101'] ) ) {
							return $iptc['2#101']; }

						default:
			return false;
	}
}


/**
 * Update/Saves iptc info as term. Does not check for valid iptc keys!
 *
 * @param $key 'string', see list of values in sell_media_iptc_parser();
 * @param $values the value that is lifted from sell_media_iptc_parser();
 * @param $post_id, duh, the post_id, NOT the attachment_id
 * @since 0.1
 */
function sell_media_iptc_save( $keys = null, $values = null, $post_id = null ) {
	if ( is_null( $keys ) ) {
		return false; }

	foreach ( $values as $value ) {
		$result = wp_set_post_terms( $post_id, $value, $keys, true );
	}
	return;
}


/**
 * Determine if we're on a Sell Media page in the admin
 *
 * @since 0.1
 */
function sell_media_is_sell_media_post_type_page() {

	if ( isset( $_GET['post_type'] ) && $_GET['post_type'] == 'sell_media_item' ) {
		return true;
	} else { 		return false; }
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

	if ( isset( $_GET['post_type'] ) && $_GET['post_type'] == 'sell_media_item' && isset( $_GET['taxonomy'] ) && $_GET['taxonomy'] == 'licenses' ) {
		return true;
	} else { 		return false; }
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
	return ( ! empty( $meta ) ) ? explode( ',', $meta ) : false;
}


/**
 * Get Attachment
 *
 * If the item has multiple attachments,
 * set the attachment_id to the query variable.
 * Otherwise, get the attachments and assign
 * the first as the $attachment_id.
 *
 * @param int $post_id
 * @return int $attachment_id
 * @since 2.0.1
 */
function sell_media_get_attachment_id( $post_id = null ) {

	if ( sell_media_has_multiple_attachments( $post_id ) ) {
		$attachment_id = get_query_var( 'id' );
	} else {
		$attachments = sell_media_get_attachments( $post_id );
		$attachment_id = $attachments[0];
	}

	return $attachment_id;
}

/**
 * Check if item has multiple attachments
 */
function sell_media_has_multiple_attachments( $post_id ) {

	$attachments = sell_media_get_attachments( $post_id );
	$count = count( $attachments );

	if ( $count > 1 ) {
		return true;
	}
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

	if ( ! empty( $settings->order_by ) && is_archive() ||
		 ! empty( $settings->order_by ) && is_tax() ) {
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
	add_filter( 'posts_orderby', 'sell_media_order_by' ); }


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

	if ( 'post_type=sell_media_item&page=sell_media_reports' == $_SERVER['QUERY_STRING'] ) {
		return true;
	} else { 		return false; }
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
	$path = ( $dir == 'packages' ) ? sell_media_get_packages_upload_dir() : sell_media_get_import_dir();

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
 * Filter the item container class
 * Needed to create the masonry layout
 *
 * @since  2.1.3
 * 
 * @return string css class
 */
function sell_media_grid_item_container_class() {
	$class = 'sell-media-grid-item-container';
	$settings = sell_media_get_plugin_options();
	if ( 'sell-media-masonry' == $settings->thumbnail_layout ) {
		$class = 'sell-media-grid-item-masonry-container';
	}
	return $class;
}
add_filter( 'sell_media_grid_item_container_class', 'sell_media_grid_item_container_class', 10, 1 );

/**
 * Filter the grid item class
 * Creates a 1, 2, 3, 4, 5 column or masonry layout
 *
 * @since  2.1.3
 * 
 * @return string css class
 */
function sell_media_grid_item_class( $class ) {
	$settings = sell_media_get_plugin_options();
	if ( ! empty( $settings->thumbnail_layout ) ) {
		return $class . ' ' . $settings->thumbnail_layout;
	}
}
add_filter( 'sell_media_grid_item_class', 'sell_media_grid_item_class', 10, 1 );

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
function sell_media_free_download_button_text( $text, $post_id, $attachment_id, $type ) {
	if( 'download' != $type ){
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
function sell_media_free_download_button_button( $html, $post_id, $attachment_id, $button, $text, $echo, $type ) {

	if( 'download' != $type ){
		return $html;
	}

	$price = get_post_meta( $post_id, 'sell_media_price', true );
	$value = get_post_meta( $post_id, 'sell_media_free_downloads', true );
	if ( $price > 0 || $value ) {
		return $html; }

	$classes[] = 'item_add';
	$classes[] = 'sell-media-button';
	if( !is_null( $button ) ){
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

	return update_option( $option, $value, $deprecated, $autoload );
}

/**
 * Modify the search form for keyword search.
 * @return string Modifed search form.
 */
function sell_media_modify_search_form(){

	$settings = sell_media_get_plugin_options();

	$search_keywords_only = false;

	if( ( !isset( $settings->search_everything[0] ) || 'yes' !== $settings->search_everything[0] ) && ( isset( $settings->search_page ) && '' !== $settings->search_page ) ){
		$search_keywords_only = true;
	}

	$action_url = esc_url( site_url() );
	$search_input_name = 's';

	if( $search_keywords_only ){
		$action_url = esc_url( get_permalink( (int) $settings->search_page ) );
		$search_input_name = 'keyword';
	}

	$query = ( get_search_query() ) ? get_search_query() : '';

	if( $search_keywords_only ){
		$query = ( isset( $_GET['keyword'] ) && '' != $_GET['keyword'] ) ? esc_html( $_GET['keyword'] ) : '';
	}

	$html = '';
	$html .= '<div class="sell-media-search">';
	$html .= '<form role="search" method="get" id="sell-media-search-form" class="sell-media-search-form" action="' . $action_url . '">';
	$html .= '<div class="sell-media-search-inner cf">';

	// Visible search options wrapper
	$html .= '<div id="sell-media-search-visible" class="sell-media-search-visible cf">';

	// Input field
	$html .= '<div id="sell-media-search-query" class="sell-media-search-field sell-media-search-query">';
	$html .= '<input type="text" value="' . $query . '" name="' . $search_input_name . '" id="sell-media-search-text" class="sell-media-search-text" placeholder="' . apply_filters( 'sell_media_search_placeholder', sprintf( __( 'Search for %1$s', 'sell_media' ), empty( $settings->post_type_slug ) ? 'items' : $settings->post_type_slug ) ) . '"/>';
	$html .= '</div>';

	// Submit button
	$html .= '<div id="sell-media-search-submit" class="sell-media-search-field sell-media-search-submit">';

	if( !$search_keywords_only ){
		$html .= '<input type="hidden" name="post_type" value="sell_media_item" />';
	}

	$html .= '<input type="submit" id="sell-media-search-submit-button" class="sell-media-search-submit-button" value="' . apply_filters( 'sell_media_search_button', __( 'Search', 'sell_media' ) ) . '" />';
	$html .= '</div>';

	$html .= '</div>';

	// Hidden search options wrapper
	$html .= '<div id="sell-media-search-hidden" class="sell-media-search-hidden cf">';

	$html .= '<p id="sell-media-search-help" class="sell-media-search-field sell-media-search-help">';
	$html .= __( 'Use a comma to separate keywords.', 'sell_media' );
	$html .= '</p>';

	// Search everything
	$checked_se = ( isset( $_GET['search_everything'] ) ) ? 'checked' : '';
	$html .= '<div id="sell-media-search-everything" class="sell-media-search-field sell-media-search-everything">';
	$html .= '<label for="search_everything" id="sell-media-search-everything-desc" class="sell-media-search-everything-desc sell-media-tooltip" data-tooltip="' . __( 'Search everywhere including titles, descriptions, captions, and keywords. For best results, only include one keyword per search.', 'sell_media' ) . '">' . __( 'Search everything (?)', 'sell_media' ) . '</label>';
	$html .= '<input type="checkbox" value="1" name="search_everything" id="search_everything" ' . $checked_se . '/>';
	$html .= '</div>';

	// Exact match field
	$checked_em = ( isset( $_GET['sentence'] ) ) ? 'checked' : '';
	$html .= '<div id="sell-media-search-exact-match" class="sell-media-search-field sell-media-search-exact-match">';
	$html .= '<label for="sentence" id="sell-media-search-exact-match-desc" class="sell-media-search-exact-match-desc sell-media-tooltip" data-tooltip="Check to limit search results to exact phrase matches. Without exact phrase match checked, a search for \'New York Yankees\' would return results containing any of the three words \'New\', \'York\' and \'Yankees\'.">' . __( 'Exact phrase match (?)', 'sell_media' ) . '</label>';
	$html .= '<input type="checkbox" value="1" name="sentence" id="sentence" ' . $checked_em . '/>';
	$html .= '</div>';

	// Collection field
	$html .= '<div id="sell-media-search-collection" class="sell-media-search-field sell-media-search-collection">';
	$html .= '<label for="collection">' . __( 'Collection', 'sell_media' ) . '</label>';
	$html .= '<select name="collection">';
	$html .= '<option value="">' . esc_attr( __( 'All', 'sell_media' ) ) . '</option>';

	$categories = get_categories( 'taxonomy=collection' );
	foreach ( $categories as $category ) {
		$selected = ( ! empty( $_GET['collection'] ) && $category->category_nicename == $_GET['collection'] ) ? 'selected="selected"' : '';
		$html .= '<option value="' . $category->category_nicename . '" '. $selected . '>';
		$html .= $category->cat_name;
		$html .= '</option>';
	}
	$html .= '</select>';
	$html .= '</div>';

	// Check if permalink is set to deafult.
	$permalink_structure = get_option( 'permalink_structure' );

	if( '' == $permalink_structure && isset( $settings->search_page  ) ){
		$html .= '<input type="hidden" name="page_id" value="' . $settings->search_page  . '" />';
	}

	// Hidden search options wrapper
	$html .= '</div>';

	// Close button
	$html .= '<a href="javascript:void(0);" class="sell-media-search-close">&times;</a>';

	$html .= '</div>';
	$html .= '</form>';
	$html .= '</div>';

	return $html;

}

/**
 * Hook search form.
 * @param  string $search_form Search form.
 * @return string              Modifed search form.
 */
function sell_media_search_form( $search_form ){
	if( !is_search() ){
		$search_form = sell_media_modify_search_form();
	}
	return $search_form;
}

add_filter( 'sell_media_searchform_filter', 'sell_media_search_form' );

/**
 * Custom search result.
 * @param  string $content Page Content.
 * @return string          Modified page content.
 */
function sell_media_search_results( $content ){
	global $post;
	$settings = sell_media_get_plugin_options();

	// Check search page is set or current page is search page.
	if( !isset( $settings->search_page ) || $post->ID != $settings->search_page ){
		return $content;
	}

	// Check if keyword is set.
	if( !isset( $_GET['keyword'] ) || '' == $_GET['keyword'] ){
		return $content;
	}

	// Get keyword.
	$keyword = esc_sql( $_GET['keyword'] );

	// Current pagination.
	$paged = ( get_query_var('paged') ) ? get_query_var('paged') : 1;

	$args['post_type'] = array( 'sell_media_item' );
	$args['paged'] = $paged;
	$args['post_status'] = array( 'publish' );
	$args['search_type'] = 'sell_media_search';
	

	if( isset( $_GET['search_everything'] ) && 1 == $_GET['search_everything'] ){
		$args['s'] = $keyword;
		$args['post_type'][] = 'attachment';
		$args['post_status'][] = 'inherit';
	}
	else{

		// There could be multiple keywords, so explode them if exact match isn't set
		if ( ! isset( $_GET['sentence'] ) ) {
			$keyword = explode( ',', $keyword );
		}
		
		$args['tax_query'][] = 	array(
				'taxonomy' => 'keywords',
				'field'    => 'slug',
				'terms'    => $keyword,
			);
	}

	if( isset( $_GET['collection'] ) && '' != $_GET['collection'] ){
		$collection = esc_html( $_GET['collection'] );
		$args['tax_query'][] = 	array(
			'taxonomy' => 'collection',
			'field'    => 'slug',
			'terms'    => $collection,
		);

	}

	$search_query = new WP_Query( $args );
	$content .= '<div id="sell-media-archive" class="sell-media">';
	$content .= '    <div id="content" role="main">';

	if( $search_query->have_posts() ):
		$i = 0;
		$content .= '<div class="' . apply_filters( 'sell_media_grid_item_container_class', 'sell-media-grid-item-container' ) . '">';

		while( $search_query->have_posts() ):

			$search_query->the_post();
			$i++;
			$content .= apply_filters( 'sell_media_content_loop', $post->ID, $i );

		endwhile;

		$content .= '</div><!-- .sell-media-grid-item-container -->';
		$content .= sell_media_pagination_filter( $search_query->max_num_pages );

		wp_reset_postdata();

	else:
			$content .= '<h2>' . __( 'Nothing Found', 'sell_media' ) . '</h2>';
			$content .= '<p>' . __( 'Sorry, but we couldn\'t find anything that matches your search query.', 'sell_media' ) . '</p>';
	endif;

	$content .= '    </div>';
	$content .= '</div>';

	return $content;

}

add_filter( 'the_content', 'sell_media_search_results' );

/**
 * Change the placeholder.
 * @param  string $placeholder Default placeholder.
 * @return string              Modified placeholder.
 */
function sell_media_search_placeholder( $placeholder ){
	$settings = sell_media_get_plugin_options();

	if( ( !isset( $settings->search_everything[0] ) || 'yes' !== $settings->search_everything[0] ) && ( isset( $settings->search_page ) && '' !== $settings->search_page ) ){
		
		return __( 'Search for Keywords', 'sell_media' );
	}	

	return $placeholder;
}

add_filter( 'sell_media_search_placeholder', 'sell_media_search_placeholder' );

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