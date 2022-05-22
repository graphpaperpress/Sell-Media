<?php

/**
 * Term Meta
 *
 * @package Sell Media
 * @author Thad Allender <support@graphpaperpress.com>
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Retrieve term meta field for a term.
 *
 * @param int $term_id Term ID.
 * @param string $key The meta key to retrieve.
 * @param bool $single Whether to return a single value.
 * @return mixed Will be an array if $single is false. Will be value of meta data field if $single
 *  is true.
 */
function sell_media_get_term_meta( $term_id, $key, $single = false ) {
	return get_term_meta( $term_id, $key, $single );
}

/**
 * Set Default Terms
 * Used in attachment-functions.php
 *
 * @since 0.1
 */
function sell_media_set_default_terms( $post_id, $post=null, $term_ids=null ){

	if ( is_null( $post ) ){
		$post_type = get_post_type( $post_id );
	} else {
		$post_type = $post->post_type;
		$post_status = $post->post_status;
	}

	if ( empty( $post_status ) )
		return;

	if ( empty( $term_ids ) || $term_ids === true )
		$term_ids = sell_media_get_default_terms();

	$taxonomy = 'licenses';
	$default_terms = array();
	$term_ids = array_map( 'intval', $term_ids);
	
	foreach( $term_ids as $term_id ){
		$tmp_term_id = get_term_by( 'id', $term_id, $taxonomy );

		if ( $tmp_term_id ) {
			$default_terms[] = (int)$tmp_term_id->term_id;
			$default_terms[] = (int)$tmp_term_id->parent;
		}
	}

	$defaults = array(
		$taxonomy => $default_terms
		);

	$taxonomies = get_object_taxonomies( $post_type );

	foreach( ( array )$taxonomies as $taxonomy ) {
		$terms = wp_get_post_terms( $post_id, $taxonomy );
		if ( empty( $terms ) && array_key_exists( $taxonomy, $defaults ) ) {
			wp_set_object_terms( $post_id, $defaults[$taxonomy], $taxonomy );
		}
	}
}
add_action( 'save_post_sell_media_item', 'sell_media_set_default_terms', 100, 3 );

/**
 * Get Default Terms from database
 *
 * @return array
 * @since 0.1
 */
function sell_media_get_default_terms(){
	$args['hide_empty'] = false;
	$args['meta_query'] = array(
				array(
					'key' => 'default',
					'value' => 'on',
				)
			);
	$default_licenses = get_terms ( 'licenses', $args  );
	$term_ids = array();

	if( !is_wp_error( $default_licenses ) ){
		foreach( $default_licenses as $meta ) {
			$term_ids[] = $meta->term_id;
		}
	}
	return $term_ids;
}


/**
 * Add description to add new licenses admin page
 *
 * @return string
 * @since 0.1
 */
function sell_media_license_description(){
	esc_attr_e( 'When a buyer decides to purchase a item from your site, they must choose a license which most closely identifies their intended use of the item.', 'sell_media' );
}
add_action( 'licenses_pre_add_form', 'sell_media_license_description' );

/**
 * Function for building the slider on Add/Edit License admin page
 *
 * @since 0.1
 */
function sell_media_the_markup_slider( $tag ){

	if ( isset( $_GET['tag_ID'] ) )
		$term_id = sanitize_key( $_GET['tag_ID'] );
	else
		$term_id = null;

	if ( get_term_meta( $term_id, 'markup', true) ) {
		$initial_markup = str_replace( "%", "", get_term_meta( $term_id, 'markup', true ) );
	} else {
		$initial_markup = 0;
	}

	$settings = sell_media_get_plugin_options(); ?>
	<script>
	jQuery(document).ready(function($){

		if ( ! jQuery().slider )
			return;

		function calc_price( markUp ){

			var price = <?php echo (float) $settings->default_price; ?>;

			if ( markUp == undefined )
				var markUp = <?php echo esc_js( $initial_markup ); ?>;

			finalPrice = ( +price + ( +markUp * .01 ) * price );
			finalPrice = finalPrice.toFixed(2);

			return finalPrice;
		}

		$( ".menu-cart-total" ).html( calc_price() );

		$( "#markup_slider" ).slider({
			range: "min",
			value: <?php echo esc_js( $initial_markup ); ?>,
			min: 0,
			step: .1,
			max: 1000,
			slide: function( event, ui ) {
				var markUp = ui.value;
				$( ".markup-target" ).val(  markUp + "%" );
				$( ".markup-target" ).html(  markUp + "%" );

				$( ".menu-cart-total" ).html( calc_price( markUp ) );
			}
		});
		$( ".markup-target" ).val( $( "#markup_slider" ).slider( "value" ) + "%" );
	});
	</script>
	<div class="sell_media-slider-container">
		<div id="markup_slider"></div>
		<div class="sell_media-price-container">
			<input name="meta_value[markup]" class="markup-target" type="text" value="<?php echo (float) get_term_meta($term_id, 'markup', true); ?>" size="40" />
		</div>
		<p class="description">
			<?php esc_html_e( 'Increase the price of a item if a buyer selects this license by dragging the slider above.', 'sell_media' ); ?>
			<?php
				if ( get_term_meta( $term_id, 'markup', true ) )
					$default_markup = get_term_meta( $term_id, 'markup', true );
				else
					$default_markup = '0%';

			if ( $settings->default_price ){
				$price = sell_media_get_currency_symbol() . $settings->default_price;
			} else {
				$price = __('you have not set a default price', 'sell_media');
			}

			printf(
				__( ' The %1$s of %2$s with %3$s markup is %4$s', 'sell_media' ),
				'<a href="' . admin_url() . 'edit.php?post_type=sell_media_item&page=sell_media_plugin_options&tab=sell_media_general_settings">default item price</a>',
				'<strong>' . $price . '</strong>',
				'<strong><span class="markup-target">' . esc_attr($default_markup) . '</span></strong>',
				'<strong>' . sell_media_get_currency_symbol() . '<span class="menu-cart-total"></span></strong>'
				);
			?>
		</p>
	</div>
<?php }


/**
 * Prints the checkbox for the default license type
 *
 * @since 0.1
 */
function sell_media_the_default_checkbox( $term_id=null, $desc=null ){
	if ( is_null( $desc ) )
		$desc = __( 'Check to add this as a default license option for all newly created items.', 'sell_media' );
	?>
	<tr class="form-field sell_media-markup-container">
		<th scope="row" valign="top">
			<label for="markup"><?php esc_html_e( 'Add as default license?', 'sell_media' ); ?></label>
		</th>
		<td>
			<input name="meta_value[default]" style="width: auto;" id="meta_value[default]" type="checkbox" <?php checked( get_term_meta($term_id, 'default', true), "on" ); ?> size="40" />
			<span class="description"><label for="meta_value[default]"><?php echo esc_html( $desc ); ?></label></span>
			<?php wp_nonce_field( 'sell_media_taxonomy_admin_nonce', 'taxonomy_wpnonce'); ?>
		</td>
	</tr>
<?php }

/**
 * Save new taxonomy fields
 * Used to both save and update
 *
 * @since 0.1
 */
function sell_media_save_extra_taxonomy_fields( $term_id ) {
	
	if( ! isset($_POST['taxonomy_wpnonce'] ) || ! wp_verify_nonce($_POST['taxonomy_wpnonce'], 'sell_media_taxonomy_admin_nonce' ) ) {
		return;
	}

	if ( ! isset( $_POST['meta_value']['default'] ) ) {
		update_term_meta( $term_id, 'default', 'off');
	}

	if ( ! isset( $_POST['meta_value']['collection_hidden'] ) ) {
		if ( isset( $_SESSION['sell_media']['collection_password'] ) )
			unset( $_SESSION['sell_media']['collection_password'] );
	}

	if ( isset( $_POST['meta_value'] ) && is_array( $_POST['meta_value'] ) ) {

	    // Process only safe keys
		$allowed_post_keys = [ 'related_keywords', 'markup', 'default', 'collection_icon_id', 'collection_password' ];

		foreach ( $allowed_post_keys as $key ) {
			if ( isset( $_POST['meta_value'][$key] ) ) {
				if ( 'related_keywords' === $key ) {
					$related_keywords = trim( sanitize_text_field( $_POST['meta_value'][$key] ), ',' );
					$related_keywords = preg_replace( '/\s*,\s*/', ',', $related_keywords );
					$related_keywords = explode( ',', $related_keywords );
					$related_keywords = array_filter( $related_keywords );
					update_term_meta( $term_id, $key, $related_keywords );
				} elseif ( 'markup' === $key ) {
					$markup = trim( sanitize_text_field( $_POST['meta_value'][ $key ] ) );
					$pos = strpos( $markup, '%' );
					if ( false === $pos ) {
						$markup .= '%';
					}
					update_term_meta( $term_id, $key, $markup );
				} else {
					$meta_value[$key] = sanitize_text_field( $_POST['meta_value'][$key] );
					update_term_meta( $term_id, $key, $meta_value[$key] );
				}
			} else {
				delete_term_meta( $term_id, $key );
			}
		}
	}
}
add_action( 'edited_licenses', 'sell_media_save_extra_taxonomy_fields' );
add_action( 'create_licenses', 'sell_media_save_extra_taxonomy_fields' );
add_action( 'edited_collection', 'sell_media_save_extra_taxonomy_fields' );
add_action( 'create_collection', 'sell_media_save_extra_taxonomy_fields' );
add_action( 'edited_keywords', 'sell_media_save_extra_taxonomy_fields' );
add_action( 'create_keywords', 'sell_media_save_extra_taxonomy_fields' );


/**
 * Add icon field to collections
 *
 * @since 0.1
 */
function sell_media_add_collection_icon( ){ ?>
	<div class="form-field collection-icon">
		<label for="collection_icon"><?php esc_html_e( 'Icon', 'sell_media' ); ?></label>
	<?php sell_media_collection_icon_field(); ?>
	</div>
	<?php }
add_action( 'collection_add_form_fields', 'sell_media_add_collection_icon' );


/**
 * Helper function to add upload field for collection icon edit form
 *
 * @since 0.1
 */
function sell_media_collection_icon_field( $icon_id=null ){
	wp_enqueue_media();
	if ( empty( $icon_id ) ){
		$image = $url = null;
	} else {
		$url = wp_get_attachment_url( $icon_id );
		$image = wp_get_attachment_image( $icon_id, 'thumbnail' );
		$image .= '<br /><a href="javascript:void(0);" class="upload_image_remove">Remove</a>';
	}
	?>
	<input name="meta_value[collection_icon_id]" type="hidden" id="collection_icon_input_field" value="<?php esc_attr_e($icon_id,'sell_media'); ?>" />
	<input name="" type="text" id="collection_icon_url" value="<?php echo esc_url( $url ); ?>" />
	<input class="button sell-media-upload-trigger-collection-icon" type="button" value="<?php esc_attr_e( 'Upload or Select Image', 'sell_media'); ?>" />
	<div class="upload_image_preview" style="display: block;">
		<span id="collection_icon_target"><?php echo wp_kses( $image, array(
		        'a' => ['href' => true, 'class' => true],
		        'br' => [],
		        'img' => [
			        'src'    => true,
			        'srcset' => true,
			        'sizes'  => true,
			        'class'  => true,
			        'id'     => true,
			        'width'  => true,
			        'height' => true,
			        'alt'    => true,
			        'align'  => true,
			        'data-*' => true,
		        ],
            )) ?></span>
	</div>
	<p class="description"><?php esc_html_e( 'The icon is not prominent by default; however, some themes may show it. If no icon is used the featured image to the most recent post will be displayed', 'sell_media' ); ?></p>
<?php }


/**
 * Hide collections from archive view
 *
 * @since 0.1
 */
function sell_media_edit_collection_icon( $tag ){
	$term_id = is_object( $tag ) ? $tag->term_id : null; ?>
	<tr class="form-field sell-media-collection-form-field">
		<th scope="row" valign="top">
			<label for="collection_icon"><?php esc_html_e( 'Icon', 'sell_media' ); ?></label>
		</th>
		<td>
			<?php sell_media_collection_icon_field( get_term_meta( $term_id, 'collection_icon_id', true ) ); ?>
		</td>
	</tr>
<?php }
add_action( 'collection_edit_form_fields', 'sell_media_edit_collection_icon' );


/**
 * Add password field to collection
 *
 * @since 0.1
 */
function sell_media_add_collection_field( $tag ){
	if ( is_object( $tag ) )
		$term_id = $tag->term_id;
	else
		$term_id = null;
	?>
	<div class="form-field">
		<label for="collection_password"><?php esc_html_e( 'Password', 'sell_media' ); ?></label>
		<input name="meta_value[collection_password]" type="text" id="meta_value[]" />
		<p class="description"><?php esc_html_e( 'This will password protect all items in this collection.', 'sell_media' ); ?></p>
	</div>
	<?php wp_nonce_field( 'sell_media_taxonomy_admin_nonce', 'taxonomy_wpnonce');
}
add_action( 'collection_add_form_fields', 'sell_media_add_collection_field' );


/**
 * Add icon field to the edit collection page
 *
 * @since 0.1
 */
function sell_media_edit_collection_password( $tag ){
	if ( is_object( $tag ) )
		$term_id = $tag->term_id;
	else
		$term_id = null;

	$child_term = get_term( $term_id, 'collection' );

		$description = __( 'Password protect all items in this collection', 'sell_media' );
		$html_extra = null;
		$password = $password = get_term_meta( $term_id, 'collection_password', true );
		$password = get_term_meta( $term_id, 'collection_password', true );

	?>
	<tr class="form-field">
		<?php if ( ! empty( $parent ) && ! empty( $password ) ) : ?>
		<div class="updated">
			<p>
				<?php esc_html_e( 'This collection will inherit the password of the parent collection:', 'sell_media' ); ?>
				<a href="<?php echo esc_url(admin_url('edit-tags.php?action=edit&taxonomy=collection&tag_ID='.$parent->term_id.'&post_type=sell_media_item')); ?>"><?php echo esc_html($parent->name); ?></a>
			</p>
		</div>
		<?php endif; ?>
		<th scope="row" valign="top">
			<label for="collection_password"><?php esc_html_e( 'Password', 'sell_media' ); ?></label>
		</th>
		<td>
			<input name="meta_value[collection_password]" id="meta_value[collection_password]" type="text" value="<?php echo esc_attr( $password ); ?>" <?php echo esc_html( $html_extra ); ?> />
			<p class="description"><?php echo esc_html($description); ?></p>
			<?php wp_nonce_field( 'sell_media_taxonomy_admin_nonce', 'taxonomy_wpnonce'); ?>
		</td>
	</tr>
<?php }
add_action( 'collection_edit_form_fields', 'sell_media_edit_collection_password' );

/**
 * Custom collection column headers
 *
 * @since 0.1
 */
function sell_media_custom_collection_columns_headers( $columns ){

	$columns_local = array();

	if ( isset( $columns['cb'] ) ) {
		$columns_local['cb'] = $columns['cb'];
		unset($columns['cb']);
	}

	if (!isset($columns_local['collection_icon']))
		$columns_local['collection_icon'] = __("Icon", 'sell_media');

	// Rename the post column to Images
	if ( isset( $columns['posts'] ) )
		$columns['posts'] = __("Media", 'sell_media');

	 $columns_local = array_merge($columns_local, $columns);

	if (!isset($columns_local['collection_protected']))
		$columns_local['collection_protected'] = __("Protected", 'sell_media');

	return array_merge($columns_local, $columns);
}
add_filter( 'manage_edit-collection_columns', 'sell_media_custom_collection_columns_headers' );


/**
 * Custom collection column header content
 *
 * @since 0.1
 */
function sell_media_custom_collection_columns_content( $row_content, $column_name, $term_id ){
	switch( $column_name ) {
		case 'collection_icon':
			return wp_get_attachment_image( get_term_meta( $term_id, 'collection_icon_id', true ), 'thumbnail' );
			break;
		case 'collection_protected':
				if( get_term_meta( $term_id, 'collection_password', true ) ) {
					$colstatus = "Private";
				} else {
					$colstatus = "Public";
				}
				return $colstatus;
			break;
		default:
			break;
	}
}
add_filter( 'manage_collection_custom_column', 'sell_media_custom_collection_columns_content', 10, 3 );

/**
 * Add related keywords to keywords
 *
 * @since 2.1.2
 */
function sell_media_add_related_keywords( ) { ?>
	<div class="form-field sell-media-related-keywords-form-field">
		<label for="meta_value[related_keywords]"><?php esc_html_e( 'Related Keywords', 'sell_media' ); ?></label>
		<input name="meta_value[related_keywords]" id="meta_value[related_keywords]" type="text" value="" />
			<p class="description"><?php esc_html_e( 'Separate related keywords with a comma and ensure the keyword actually exists.', 'sell_media' ); ?></p>
	</div>
	<?php 
	wp_nonce_field( 'sell_media_taxonomy_admin_nonce', 'taxonomy_wpnonce');
}
add_action( 'keywords_add_form_fields', 'sell_media_add_related_keywords' );

/**
 * Edit related keywords
 *
 * @since 2.1.2
 */
function sell_media_edit_related_keywords( $tag ) {
	$term_id = is_object( $tag ) ? $tag->term_id : null;
	$term_meta_key = 'related_keywords';
	$term_meta = sell_media_get_term_meta( $term_id, $term_meta_key, true );
	$terms = implode( ', ', $term_meta ); ?>
	<tr class="form-field sell-media-related-keywords-form-field">
		<th scope="row" valign="top">
			<label for="meta_value[related_keywords]"><?php esc_html_e( 'Related Keywords', 'sell_media' ); ?></label>
		</th>
		<td>
			<input name="meta_value[related_keywords]" id="meta_value[related_keywords]" type="text" value="<?php esc_attr_e( $terms, 'sell_media' ); ?>" />
			<p class="description"><?php esc_html_e( 'Separate related keywords with a comma and ensure the keyword actually exists.', 'sell_media' ); ?></p>
			<?php wp_nonce_field( 'sell_media_taxonomy_admin_nonce', 'taxonomy_wpnonce'); ?>
		</td>
	</tr>
<?php }
add_action( 'keywords_edit_form_fields', 'sell_media_edit_related_keywords' );

function sell_media_get_related_keywords( $terms ) {
	$related_keywords = array();
	// loop over terms
	if ( $terms ) {
		foreach ( $terms as $term ) {
			$term_id = term_exists( $term );
			if ( ! empty( $term_id ) ) {
				// the terms to check for related terms
				$term_meta = get_term_meta( $term_id, 'related_keywords' );
				if ( $term_meta ) {
					$term_meta_array = call_user_func_array( 'array_merge', $term_meta );
					if ( $term_meta_array ) {
						foreach ( $term_meta_array as $maybe_term ) {
							$related_term_id = term_exists( $maybe_term );
							if ( ! empty( $related_term_id ) ) {
								$related_term_obj = get_term( $related_term_id, 'keywords' );
								$related_keywords[] = $related_term_obj->name;
							}
						}
					}
				}
			}
		}
	}
	return $related_keywords;
}

function sell_media_format_related_search_results( $terms ) {

	if ( empty( $terms ) ) {
		return;
	}

	$settings = sell_media_get_plugin_options();
	$related_terms = sell_media_get_related_keywords( $terms );
	$html = '';

	if ( $related_terms ) {
		$html .= '<div class="sell-media-related-keywords">';
		$html .= '<span class="sell-media-related-keywords-title">' . __( 'Related Keywords', 'sell_media' ) . ':</span>';
		$html .= '<ul class="sell-media-related-keywords-list">';
		foreach ( $related_terms as $term ) {

			$url = add_query_arg( array(
				'search_query' => $term,
				), get_permalink( $settings->search_page ) );
			$html .= '<li class="sell-media-related-keywords-list-item"><a href="' . esc_url( $url ) . '">' . esc_attr( $term ) . '</a></li>';
		}
		$html .= '</ul>';
		$html .= '</div>';

	}

	return apply_filters( 'sell_media_related_keywords_html', $html );
}
