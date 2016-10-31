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
	echo __( 'When a buyer decides to purchase a item from your site, they must choose a license which most closely identifies their intended use of the item. We have included some default license types, grouped into two "parent" categories: Personal and Commercial. Each of these two categories have specific "child" licenses, such as "Print Advertising" (a child of Commercial) and "Website" (a child of Personal). You can create as many parent and child licenses as you want.', 'sell_media' );
}
add_action( 'licenses_pre_add_form', 'sell_media_license_description' );


/**
 * Add form fields to add terms page for our custom taxonomies
 *
 * @since 0.1
 */
function sell_media_add_custom_term_form_fields( $tag ){
	if ( is_object( $tag ) )
		$term_id = $tag->term_id;
	else
		$term_id = null;
	?>
	<div class="form-field">
		<label for="markup"><?php _e('Markup', 'sell_media'); ?></label>
		<?php sell_media_the_markup_slider( $tag ); ?>
	</div>
	<div class="form-field">
		<?php sell_media_the_default_checkbox( $term_id ); ?>
	</div>
<?php }
add_action( 'licenses_add_form_fields', 'sell_media_add_custom_term_form_fields' );


/**
 * Edit form fields to edit terms page for our custom taxonomies
 *
 * @since 0.1
 */
function sell_media_edit_custom_term_form_fields( $tag ){ ?>
	<tr class="form-field sell_media-markup-container">
		<th scope="row" valign="top">
			<label for="markup"><?php _e( 'Markup', 'sell_media' ); ?></label>
		</th>
		<td>
			<?php sell_media_the_markup_slider( $tag ); ?>
		</td>
	</tr>
	<tr class="form-field sell_media-markup-container">
		<td><?php sell_media_the_default_checkbox( $tag->term_id ); ?></td>
	</tr>
<?php }
add_action( 'licenses_edit_form_fields', 'sell_media_edit_custom_term_form_fields' );


/**
 * Function for building the slider on Add/Edit License admin page
 *
 * @since 0.1
 */
function sell_media_the_markup_slider( $tag ){

	if ( isset( $_GET['tag_ID'] ) )
		$term_id = $_GET['tag_ID'];
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

			var price = <?php echo $settings->default_price; ?>;

			if ( markUp == undefined )
				var markUp = <?php print $initial_markup; ?>;

			finalPrice = ( +price + ( +markUp * .01 ) * price );
			finalPrice = finalPrice.toFixed(2);

			return finalPrice;
		}

		$( ".menu-cart-total" ).html( calc_price() );

		$( "#markup_slider" ).slider({
			range: "min",
			value: <?php print $initial_markup; ?>,
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
			<input name="meta_value[markup]" class="markup-target" type="text" value="<?php echo get_term_meta($term_id, 'markup', true); ?>" size="40" />
		</div>
		<p class="description">
			<?php _e( 'Increase the price of a item if a buyer selects this license by dragging the slider above.', 'sell_media' ); ?>
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
				'<a href="' . admin_url() . 'edit.php?post_type=sell_media_item&page=sell_media_plugin_options&tab=sell_media_general_settings
				">default item price</a>',
				'<strong>' . $price . '</strong>',
				'<strong><span class="markup-target">' . $default_markup . '</span></strong>',
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
			<label for="markup"><?php _e( 'Add as default license?', 'sell_media' ); ?></label>
		</th>
		<td>
			<input name="meta_value[default]" style="width: auto;" id="meta_value[default]" type="checkbox" <?php checked( get_term_meta($term_id, 'default', true), "on" ); ?> size="40" />
			<span class="description"><label for="meta_value[default]"><?php echo $desc; ?></label></span>
		</td>
	</tr>
<?php }


/**
 * Display Custom License Column Headers in wp-admin
 *
 * @since 0.1
 */
function sell_media_custom_license_columns_headers( $columns ){

	$columns_local = array();

	if ( isset( $columns['cb'] ) ) {
		$columns_local['cb'] = $columns['cb'];
		unset($columns['cb']);
	}

	if ( isset( $columns['name'] ) ) {
		$columns_local['name'] = $columns['name'];
		unset($columns['name']);
	}

	if (!isset($columns_local['license_term_price']))
		$columns_local['license_term_price'] = "% Markup";

	// Rename the post column to Images
	if ( isset( $columns['posts'] ) )
		$columns['posts'] = "Media";

	 $columns_local = array_merge($columns_local, $columns);

	return array_merge($columns_local, $columns);
}
add_filter( 'manage_edit-licenses_columns', 'sell_media_custom_license_columns_headers' );


/**
 * Display Custom License Column Content below Headers in wp-admin
 *
 * @since 0.1
 */
function sell_media_custom_license_columns_content( $row_content, $column_name, $term_id ){
	switch( $column_name ) {
		case 'license_term_price':
			return get_term_meta($term_id, 'markup', true);
			break;
		default:
			break;
	}
}
add_filter( 'manage_licenses_custom_column', 'sell_media_custom_license_columns_content', 10, 3 );


/**
 * Save new taxonomy fields
 * Used to both save and update
 *
 * @since 0.1
 */
function sell_media_save_extra_taxonomy_fields( $term_id ) {

	if ( ! isset( $_POST['meta_value']['default'] ) ) {
		update_term_meta( $term_id, 'default', 'off');
	}

	if ( ! isset( $_POST['meta_value']['collection_hidden'] ) ) {
		if ( ! empty(  $_SESSION['sell_media']['collection_password'] ) )
			unset( $_SESSION['sell_media']['collection_password'] );
	}

	if ( isset( $_POST['meta_value'] ) ) {
		$cat_keys = array_keys( $_POST['meta_value'] );

		foreach ( $cat_keys as $key ) {
			if ( ! empty( $_POST['meta_value'][$key] ) ) {
				$meta_value[$key] = $_POST['meta_value'][$key];
				update_term_meta( $term_id, $key, wp_filter_nohtml_kses( $meta_value[$key]) );
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


/**
 * Add icon field to collections
 *
 * @since 0.1
 */
function sell_media_add_collection_icon( ){ ?>
	<div class="form-field collection-icon">
		<label for="collection_icon"><?php _e( 'Icon', 'sell_media' ); ?></label>
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
	<input name="meta_value[collection_icon_id]" type="hidden" id="collection_icon_input_field" value="<?php print $icon_id; ?>" />
	<input name="" type="text" id="collection_icon_url" value="<?php print $url; ?>" />
	<input class="button sell-media-upload-trigger-collection-icon" type="button" value="<?php _e( 'Upload or Select Image', 'sell_media'); ?>" />
	<div class="upload_image_preview" style="display: block;">
		<span id="collection_icon_target"><?php print $image; ?></span>
	</div>
	<p class="description"><?php _e( 'The icon is not prominent by default; however, some themes may show it. If no icon is used the featured image to the most recent post will be displayed', 'sell_media' ); ?></p>
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
			<label for="collection_icon"><?php _e( 'Icon', 'sell_media' ); ?></label>
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
		<label for="collection_password"><?php _e( 'Password', 'sell_media' ); ?></label>
		<input name="meta_value[collection_password]" type="text" id="meta_value[]" />
		<p class="description"><?php _e( 'This will password protect all items in this collection.', 'sell_media' ); ?></p>
	</div>
<?php }
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

	//if ( $child_term->parent == 0 ){
		$description = __( 'Password protect all items in this collection', 'sell_media' );
		$html_extra = null;
		$password = $password = get_term_meta( $term_id, 'collection_password', true );
		$password = get_term_meta( $term_id, 'collection_password', true );
   /* } else {
		$parent = get_term( $child_term->parent, 'collection' );
		$password = get_term_meta( $parent->term_id, 'collection_password', true );
		$description = __('This colleciton inherits the password set in its parent collection: ', 'sell_media') . ' <a href="' . admin_url('edit-tags.php?action=edit&taxonomy=collection&tag_ID='.$parent->term_id.'&post_type=sell_media_item') . '">' . $parent->name . '</a>. ';
		$description .= __('To edit the password of this collection you must change the parent password.', 'sell_media');
		$html_extra = 'class="disabled" disabled ';
	}*/

	?>
	<tr class="form-field">
		<?php if ( ! empty( $parent ) && ! empty( $password ) ) : ?>
		<div class="updated">
			<p>
				<?php _e( 'This collection will inherit the password of the parent collection:', 'sell_media' ); ?>
				<a href="<?php echo admin_url('edit-tags.php?action=edit&taxonomy=collection&tag_ID='.$parent->term_id.'&post_type=sell_media_item'); ?>"><?php echo $parent->name; ?></a>
			</p>
		</div>
		<?php endif; ?>
		<th scope="row" valign="top">
			<label for="collection_password"><?php _e( 'Password', 'sell_media' ); ?></label>
		</th>
		<td>
			<input name="meta_value[collection_password]" id="meta_value[collection_password]" type="text" value="<?php print $password ?>" <?php echo $html_extra; ?> />
			<p class="description"><?php  echo $description; ?></p>
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
