<?php

/**
 * Admin Items
 *
 * @package Sell Media
 * @author Thad Allender <support@graphpaperpress.com>
 */

/**
 * Add meta boxes on Add New screen for Sell Media
 *
 * @author Thad Allender
 * @since 0.1
 */
function sell_media_meta_boxes($post_type)
{
	// Files
	add_meta_box(
		'files_meta_box', // $id
		'Files', // $title
		'sell_media_files_meta_box', // $callback
		'sell_media_item', // $page
		'normal', // $context
		'high' // $priority
	);
	// Options
	add_meta_box(
		'options_meta_box', // $id
		'Options', // $title
		'sell_media_options_meta_box', // $callback
		'sell_media_item', // $page
		'normal', // $context
		'high' // $priority
	);

	// Stats
	add_meta_box(
		'stats_meta_box', // $id
		'Stats', // $title
		'sell_media_stats_meta_box', // $callback
		'sell_media_item', // $page
		'normal', // $context
		'high' // $priority
	);
	do_action('sell_media_additional_meta_boxes');
}
add_action('add_meta_boxes', 'sell_media_meta_boxes');


/**
 * Files meta box
 */
function sell_media_files_meta_box($post)
{
	wp_nonce_field('_sell_media_meta_box_nonce', 'sell_media_meta_box_nonce');
	do_action('sell_media_before_files_meta_box', $post); ?>

<div id="sell-media-upload-field" class="sell-media-field">
    <p><input type="button" class="sell-media-upload-button button"
            data-id="<?php esc_attr_e($post->ID, 'sell_media'); ?>"
            value="<?php esc_attr_e('Upload', 'sell_media'); ?>" /></p>
</div>

<?php do_action('sell_media_after_file_uploader', $post); ?>

<ul class="attachments sell-media-upload-list">
    <?php
		$attachment_ids = sell_media_get_attachments($post->ID);
		if ($attachment_ids) foreach ($attachment_ids as $attachment_id) {
			echo wp_kses(sell_media_list_uploads($attachment_id), GPP_WP_KSES_SELL_MEDIA_LIST_UPLOADS);
		}
		?>
</ul>

<?php do_action('sell_media_after_files_meta_box', $post); ?>
<!-- This hidden field holds all attached file ids -->
<input type="hidden" name="_sell_media_attachment_id" id="sell-media-attachment-id" class="sell-media-attachment-id"
    value="<?php esc_attr_e((!empty($attachment_ids)) ? implode(',', $attachment_ids) : ''); ?>" />
<?php }

function sell_media_uploader_meta_box($post)
{
	wp_nonce_field('_sell_media_meta_box_nonce', 'sell_media_meta_box_nonce');
	do_action('sell_media_before_uploader_meta_box', $post); ?>

<div class="sell-media-uploader-wrap">
    <div id="sell-media-upload-error"></div>
    <?php media_upload_form(); ?>
</div>
<script type="text/javascript">
var post_id = <?php echo (int) $post->ID; ?>,
    shortform = 3;
</script>

<?php do_action('sell_media_after_file_uploader', $post); ?>

<ul class="attachments sell-media-upload-list">
    <?php
		$attachment_ids = sell_media_get_attachments($post->ID);
		if ($attachment_ids) foreach ($attachment_ids as $attachment_id) {
			echo wp_kses(sell_media_list_uploads($attachment_id), GPP_WP_KSES_SELL_MEDIA_LIST_UPLOADS);
		}
		?>
</ul>

<?php do_action('sell_media_after_uploader_meta_box', $post); ?>
<!-- This hidden field holds all attached file ids -->
<?php
	// Moved from bottom line
	$gpp_tmp_val = (!empty($attachment_ids))  ? implode(',', $attachment_ids) : '';
	?>
<input type="hidden" name="_sell_media_attachment_id" id="sell-media-attachment-id" class="sell-media-attachment-id"
    value="<?php echo esc_attr($gpp_tmp_val); ?>" />
<?php }

/**
 * After file uploader
 */
function sell_media_after_file_uploader($post)
{
?>

<p class="description">
    <?php esc_html_e('Upload one file to create a single product or many files to create a gallery.', 'sell_media'); ?>
</p>
<p class="description"><a href="#" class="sell-media-upload-options"><span
            class="dashicons dashicons-arrow-down"></span> <?php esc_html_e('Importing Options', 'sell_media'); ?></a>
</p>

<div id="sell-media-upload-show-options" class="sell-media-upload-show-options" style="display:none;">
    <h4><?php esc_html_e('Importing', 'sell_media'); ?></h4>
    <p class="description">
        <?php echo wp_kses(sprintf(__('Quickly import folders of images using this option. Use FTP or <a href="%1$s" target="_blank">export directly from Lightroom</a> and place new folders into the server path listed below. Then, select the folder below to import into WordPress.', 'sell_media'), 'http://graphpaperpress.com/docs/sell-media/#add-bulk'), ['a' => ['href' => true, 'target' => true]]); ?>
    </p>
    <p class="description"><strong><?php esc_html_e('Server Path', 'sell_media'); ?>:</strong>
        <?php echo esc_html(sell_media_get_import_dir()); ?></p>
    <select id="sell-media-upload-bulk-selector" value="">
        <option value=""><?php esc_html_e('Select a folder', 'sell_media'); ?></option>
        <?php
			$directories = sell_media_get_directories();
			if ($directories) foreach ($directories as $directory) : ?>
        <option value="<?php echo esc_attr(basename($directory)); ?>"><?php echo esc_attr(basename($directory)); ?>
        </option>
        <?php endforeach; ?>
    </select>
    <button id="sell-media-upload-bulk-processor" type="button" class="button button-large"
        data-default-text="<?php esc_attr_e('Add more files', 'sell_media'); ?>"
        data-uploading-text="<?php esc_attr_e('Importing files...', 'sell_media'); ?>"
        disabled><?php esc_html_e('Add files', 'sell_media'); ?></button><br /><br />
    <?php do_action('sell_media_after_files_show_options_meta_box', $post); ?>
</div>

<?php if (Sell_Media()->products->is_package($post->ID)) : ?>
<div id="sell-media-packages" class="sell-media-field">
    <h4><?php esc_html_e('Packages', 'sell_media'); ?></h4>
    <p><?php esc_html_e('This feature was retired in version 2.0 because product galleries can now be created. Your old package file will still be available for sale and is listed below.', 'sell_media'); ?>
    </p>
    <p><strong><?php echo esc_html(get_post_meta($post->ID, '_sell_media_attached_file', true)); ?></strong></p>
</div>
<?php endif; ?>
<?php
}
add_action('sell_media_after_file_uploader', 'sell_media_after_file_uploader');

/**
 * Options meta box
 */
function sell_media_options_meta_box($post)
{

	$settings = sell_media_get_plugin_options();
	$price = (get_post_meta($post->ID, 'sell_media_price', true)) ? get_post_meta($post->ID, 'sell_media_price', true) : $settings->default_price;
	$ecommerce_enabled = sell_media_ecommerce_enabled($post->ID);
	$style = 'display:none;';
	if ($ecommerce_enabled) {
		$style = 'display:block;';
	}
	do_action('sell_media_before_options_meta_box', $post); ?>

<div id="sell-media-price-field" class="sell-media-field" style="<?php echo esc_attr($style, 'sell_media'); ?>">
    <label for="sell-media-price"><?php esc_html_e('Price', 'sell_media'); ?></label>
    <span class="sell-media-currency-field"><?php echo esc_html(sell_media_get_currency_symbol()); ?>
        <input name="sell_media_price" id="sell-media-price" class="small-text" type="number" step="0.01" min="0"
            placeholder="<?php echo esc_attr($price); ?>" value="<?php echo esc_attr($price); ?>" /></span>
    <?php if (sell_media_has_multiple_attachments($post->ID)) { ?>
    <span class="desc"><?php esc_html_e('The price of each original source file.', 'sell_media'); ?></span>
    <?php } ?>
</div>

<?php do_action('sell_media_after_options_meta_box', $post->ID);
}

/**
 * Stats meta box
 */
function sell_media_stats_meta_box($post)
{ ?>

<?php do_action('sell_media_before_stats_meta_box', $post); ?>

<div id="sell-media-stats" class="sell-media-field">
    <ul>
        <li><strong><?php esc_html_e('Views', 'sell_media'); ?>:</strong>
            <?php echo esc_html(sell_media_get_post_views($post->ID)); ?></li>
        <li><strong><?php esc_html_e('Sales', 'sell_media'); ?>:</strong>
            <?php echo esc_html(Sell_Media()->payments->get_item_sales($post->ID)); ?></li>
    </ul>
</div>

<?php do_action('sell_media_after_stats_meta_box', $post);
}

/**
 * Editor meta box
 */
function sell_media_editor()
{
	global $post_type;
	if ('sell_media_item' !== $post_type) {
		return;
	}
	global $post;
	wp_editor($post->post_content, 'post_content', array('sell_media_editor' => 'post_content'));
}
add_action('edit_form_advanced', 'sell_media_editor');


/**
 * Saves post meta data.
 *
 * This function will verify permissions, save the
 * post meta data, along with handling file uploads.
 * The file upload is processed, a new post is created,
 * the attachment meta data is also updated.
 */
function sell_media_save_custom_meta($post_id)
{

	if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
	if (!isset($_POST['sell_media_meta_box_nonce']) || !wp_verify_nonce($_POST['sell_media_meta_box_nonce'], '_sell_media_meta_box_nonce')) return;
	if (!current_user_can('edit_post', $post_id)) return;

	// Prevents an infinite loop
	// See: http://codex.wordpress.org/Function_Reference/wp_update_post
	if (wp_is_post_revision($post_id)) return;
	remove_action('save_post', 'sell_media_save_custom_meta');

	// loop through sell media fields and save the data
	$fields = sell_media_meta_box_fields();

	foreach ($fields as $field) {
		if (isset($_POST[$field])) {

			// price groups fields
			if ($field == 'sell_media_price_group') {

				if (isset($_POST['sell_media_price_group'])) {
					wp_set_post_terms($post_id, sanitize_text_field($_POST['sell_media_price_group']), 'price-group');
				}

				// print price groups fields
			} elseif ($field == 'sell_media_print_price_group') {

				if (isset($_POST['sell_media_print_price_group'])) {
					wp_set_post_terms($post_id, sanitize_text_field($_POST['sell_media_print_price_group']), 'reprints-price-group');
				}
			} else {
				$old = get_post_meta($post_id, $field, true);
				$new = 0;
				if (isset($_POST[$field])) {
					$new = sanitize_text_field($_POST[$field]);
				}
				if (0 <= $new && $new != $old) {

					// Sanitize price
					if ($field == 'sell_media_price') {
						$new = sprintf('%0.2f', (float) $new);
					}

					// new meta and it's different than old saved value, so update it
					update_post_meta($post_id, $field, $new);

					// Loop over attachment ids and move files into protected directory
					if ($field == '_sell_media_attachment_id') {
						global $wpdb;
						// Remove attachment marked as sell media item.
						$wpdb->delete($wpdb->postmeta, array('meta_key' => '_sell_media_for_sale_product_id', 'meta_value' => $post_id), array('%s', '%d'));

						$attachment_ids = explode(',', sanitize_text_field($_POST[$field]));
						update_post_meta($post_id, $field, $attachment_ids);
						// Arguments to get attachment linked to post.
						$args = array(
							'post_parent' => $post_id,
							'post_type'   => 'attachment',
							'numberposts' => -1,
							'post_status' => 'any'
						);

						// Get post attachments.
						$childrens = get_children($args);

						if (!empty($childrens)) {
							foreach ($childrens as $key => $child) {
								// If attachment still linked to post do not remove.
								if (!in_array($key, $attachment_ids)) {
									$post_data['ID'] = intval($key);
									$post_data['post_parent'] = 0;
									wp_update_post($post_data);
								}
							}
						}

						if ($attachment_ids) foreach ($attachment_ids as $attachment_id) {

							// Update attachment parent to post.
							$attachment_update = array(
								'ID' => $attachment_id,
								'post_parent' => $post_id,
							);
							wp_update_post($attachment_update);

							// Mark attachment as sell media item.
							update_post_meta($attachment_id, '_sell_media_for_sale_product_id', intval($post_id));
							$post_views_count = get_post_meta($attachment_id, '_sell_media_post_views_count', true);
							if (false === $post_views_count || '' === $post_views_count) {
								update_post_meta($attachment_id, '_sell_media_post_views_count', '0');
							}
							// Store orientation.
							$orientation = get_post_meta($attachment_id, '_sell_media_attachment_orientation', true);
							if (false === $orientation || '' === $orientation) {
								$meta = get_post_meta($attachment_id, '_wp_attachment_metadata', true);
								if (isset($meta['height']) && isset($meta['width'])) {
									if ($meta['height'] < $meta['width']) {
										update_post_meta($attachment_id, '_sell_media_attachment_orientation', 'landscape');
									}

									if ($meta['height'] > $meta['width']) {
										update_post_meta($attachment_id, '_sell_media_attachment_orientation', 'portrait');
									}
								}
							}
							sell_media_move_file($attachment_id);

							// Check creator is assigned to item
							if (isset($_POST['tax_input']['creator']) && '' != sanitize_text_field($_POST['tax_input']['creator'])) {
								// List of creator ids
								$_creator_ids = (isset($_POST['tax_input']['creator'])) ? sanitize_text_field($_POST['tax_input']['creator']) : array();
								// Store creator to attachment.
								wp_set_post_terms($attachment_id, $_creator_ids, 'creator');
							}
						}
					}

					// new meta is empty, so delete it
				} elseif ('' == $new && $old) {

					delete_post_meta($post_id, $field, $old);
				}
			}
			// Checkbox field isn't set, so delete the meta
		} else {
			delete_post_meta($post_id, $field);
		}
	} // end foreach

	// Save the post content
	global $post_type;
	if (isset($_POST['sell_media_editor']) && $post_type == 'sell_media_item') {
		$new_content = isset($_POST['sell_media_editor']) ? sanitize_text_field($_POST['sell_media_editor']) : '';
		$old_content = get_post_field('post_content', $post_id);
		if (!wp_is_post_revision($post_id) && $old_content != $new_content) {
			$args = array(
				'ID' => $post_id,
				'post_content' => $new_content
			);
			// unhook this function so it doesn't loop infinitely
			remove_action('save_post', 'sell_media_save_custom_meta');
			// update the post, which calls save_post again
			wp_update_post($args);
			// re-hook this function
			add_action('save_post', 'sell_media_save_custom_meta');
		}
	}
	do_action('sell_media_extra_meta_save', $post_id);
}
add_action('save_post', 'sell_media_save_custom_meta');

/**
 * Upload callback
 */
function sell_media_upload_callback()
{
	$html = '';
	check_ajax_referer('_sell_media_meta_box_nonce', 'security');

	// Display thumbnails after upload/selection
	if ($_POST['attachments']) foreach ($_POST['attachments'] as $attachment) {
		$html .= sell_media_list_uploads($attachment['id']);
	}
	echo $html;
	die();
}
add_action('wp_ajax_sell_media_upload_callback', 'sell_media_upload_callback');


function sell_media_upload_bulk_move()
{
	check_ajax_referer('_sell_media_meta_box_nonce', 'security');

	/**
	 * The WP function media_handle_sideload requires a files array ($_FILES).
	 * Since we want to keep the original file, we copy the original file
	 * and create a tmp file, which we can then unlink (delete)
	 */

	if (isset($_POST['dir'])) {
		$path = sell_media_get_import_dir() . '/' . $_POST['dir'] . '/';
		$filename = $_POST['image'];
		$filename = $path . $filename;

		$tmp = $filename . '.tmp';
		copy($filename, $tmp);

		$file_array = array(
			'name' => basename($filename),
			'tmp_name' => $tmp
		);

		if (is_wp_error($tmp)) {
			@unlink($file_array['tmp_name']);
			return $tmp;
		}

		$post_id = $_POST['id'];
		$attachment_id = media_handle_sideload($file_array, $post_id);


		// Check for upload errors
		if (is_wp_error($attachment_id)) {
			@unlink($file_array['tmp_name']);
			return $attachment_id;
		}

		$html = '';

		if (is_wp_error($attachment_id)) {
			$html .= '<li class="attachment">' . sprintf(__('Sorry, %1$s could\'t be added.', 'sell_media'), basename($file)) . '</li>';
		} else {
			$html .= sell_media_list_uploads($attachment_id);
		}
		echo $html;
	}
	die();
}
add_action('wp_ajax_sell_media_upload_bulk_move', 'sell_media_upload_bulk_move');



/**
 * Bulk upload callback
 */
function sell_media_upload_bulk_callback()
{

	check_ajax_referer('_sell_media_meta_box_nonce', 'security');
	@ini_set('max_execution_time', '600');

	if (isset($_POST['dir'])) {
		$path = sell_media_get_import_dir() . '/' . $_POST['dir'] . '/';
		$images = [];

		if (file_exists($path)) {
			$files = glob($path . '*.*');
			if ($files) foreach ($files as $file) {
				$images[] = basename($file);
			}
			echo json_encode($images);
		}
	}
	die();
}
add_action('wp_ajax_sell_media_upload_bulk_callback', 'sell_media_upload_bulk_callback');

/**
 * Returns the default meta box fields
 *
 * @since 2.0.1
 * @author Thad Allender
 */
function sell_media_meta_box_fields()
{

	$fields = array(
		'_sell_media_attachment_id',
		'sell_media_price',
		'sell_media_price_group',
	);

	return apply_filters('sell_media_meta_box_fields', $fields);
}


/**
 * Filter column headers names on the edit media table.
 *
 * @since 0.1
 */
function sell_media_item_header($columns)
{

	$columns_local = array();

	// Allows to "move the checkbox" to be first
	if (isset($columns['cb'])) {
		$columns_local['cb'] = $columns['cb'];
		unset($columns['cb']);
	}

	// Our next column header is the 'icon', we use this,
	// to ensure that our head has the class 'column-icon'
	if (!isset($columns_local['icon']))
		$columns_local['icon'] = "Item";

	// We have to unset default columns to "move" them
	if (isset($columns['title'])) {
		$columns_local['title'] = $columns['title'];
		unset($columns['title']);
	}

	if (!isset($columns_local['sell_media_price']))
		$columns_local['sell_media_price'] = "Price";


	return array_merge($columns_local, $columns);
}
add_filter('manage_edit-sell_media_item_columns', 'sell_media_item_header');


/**
 * Make column headers sortable
 *
 * @since 0.1
 */
function sell_media_sortable_column($columns)
{
	$columns['sell_media_price'] = 'sell_media_price';
	$columns['author'] = 'author';
	return $columns;
}
add_filter('manage_edit-sell_media_item_sortable_columns', 'sell_media_sortable_column');


/**
 * Sort the column headers
 *
 * @since 0.1
 */
function sell_media_column_orderby($vars)
{
	if (isset($vars['orderby']) && 'sell_media_price' == $vars['orderby']) {
		$vars = array_merge($vars, array(
			'meta_key' => 'sell_media_price',
			'orderby' => 'meta_value_num'
		));
	}

	if (isset($vars['orderby']) && 'author' == $vars['orderby']) {
		$vars = array_merge($vars, array(
			'orderby' => 'author'
		));
	}
	return $vars;
}
add_filter('request', 'sell_media_column_orderby');


/**
 * Filter custom column content on the edit media table.
 *
 * @since 0.1
 */
function sell_media_item_content($column, $post_id)
{
	switch ($column) {
		case "icon":
			$html = '<a href="' . site_url() . '/wp-admin/post.php?post=' . $post_id . '&action=edit">';
			$html .= sell_media_item_icon($post_id, 'thumbnail', false);
			$html .= '</a>';

			$arr = array(
				'a'      => array(
					'href' => true,
				),
				'img'    => array(
					'width'   => true,
					'height'  => true,
					'src'     => true,
					'data-*'  => true,
					'alt'     => true,
					'srcset'  => true,
					'loading' => true,
					'class'   => true,
					'sizes'   => true,
					'style'   => true,
				),
				'div'    => array(
					'class' => true,
				),
				'script' => array(
					'type' => true,
				),
			);

			echo wp_kses($html, $arr);

			break;
		case "sell_media_price":
			$ecommerce_enabled = sell_media_ecommerce_enabled($post_id);

			if ($ecommerce_enabled) {

				$price = get_post_meta($post_id, 'sell_media_price', true);
				$settings = sell_media_get_plugin_options();
				if ($price) {
					echo esc_html(sell_media_get_currency_symbol()) . number_format($price, 2, '.', '');
				} elseif (isset($settings->default_price) && '' !== $settings->default_price) {
					echo esc_html(sell_media_get_currency_symbol()) . number_format($settings->default_price, 2, '.', '');
				} else {
					esc_attr_e('No price set', 'sell_media');
				}
			}
			break;
		default:
			break;
	}
}
add_filter('manage_sell_media_item_posts_custom_column', 'sell_media_item_content', 10, 2);


/**
 * Echoes transaction sales, shown in admin
 *
 * @since 0.1
 * @return string
 */
function sell_media_sales_stats()
{
	global $post_id;

	$stats_array = maybe_unserialize(get_post_meta($post_id, 'sell_media_sales_stats', true));

	if ($stats_array) {
		$i = 0;
		$count = count($stats_array);
		foreach ($stats_array as $license_id => $stats) {
			$term_obj = get_term($license_id, 'licenses');
			$i++;
			if ($i == $count) {
				$last_class = 'misc-pub-section-last';
			} else {
				$last_class = null;
			}
	?>
<div class="misc-pub-section <?php echo esc_attr($last_class); ?>"><?php echo esc_html($term_obj->name); ?>
    <?php echo esc_html($stats['count']); ?>
    <strong><?php echo esc_html(sell_media_get_currency_symbol()) . esc_html($stats['total']); ?></strong>
</div>
<?php }
	} else {
		esc_attr_e('No sales so far.', 'sell_media');
	}
}


/**
 * Deletes the uploaded file in sell_media/ when the
 * trash bin is emptied.
 *
 * @since 1.0.4
 */
function sell_media_before_delete_post($post_id, $attachment_id = null)
{

	$post_type = get_post_type($post_id);

	if ('' != $post_type) {
		return;
	}
	$wp_upload_dir = wp_upload_dir();

	/**
	 * Get and format the attachment ids
	 */
	$attachment_ids = (sell_media_has_multiple_attachments($post_id)) ? sell_media_get_attachments($post_id) : array(get_post_meta($post_id, '_sell_media_attachment_id', true));

	if ($attachment_ids) foreach ($attachment_ids as $attachment_id) {

		wp_delete_post($attachment_id);

		$attached_file = sell_media_get_public_filepath($attachment_id);
		$attached_file_protected = sell_media_get_upload_dir() . '/' . $attached_file;
		$attached_file_unprotected = $wp_upload_dir['basedir'] . '/' . $attached_file;

		// Delete the file stored in sell_media
		if (file_exists($attached_file_protected)) {
			// Copy our "original" back
			@copy($attached_file_protected, $attached_file_unprotected);
			@unlink($attached_file_protected);
		}
	}
	return;
}
add_action('before_delete_post', 'sell_media_before_delete_post');

/**
 * Redirect to custom url after move to trash in payments
 *
 * @since 1.6
 */
function sell_media_trash_payment_redirect()
{
	$screen = get_current_screen();
	if ('edit-sell_media_payment' == $screen->id) {
		if (isset($_GET['trashed']) &&  intval($_GET['trashed']) > 0) {
			$redirect = esc_url_raw(add_query_arg(array('post_type' => 'sell_media_payment'), admin_url() . "edit.php"));
			wp_redirect($redirect);
			exit();
		}
	}
}
add_action('load-edit.php', 'sell_media_trash_payment_redirect');

/**
 * Add quick/ bulk edit custom fields.
 * @param  string $column_name Column name
 * @param  string $post_type   Post type name.
 */
function sell_media_add_quick_edit($column_name, $post_type)
{
	if ('taxonomy-price-group' != $column_name) return;
	?>
<fieldset class="inline-edit-col-left">
    <div class="inline-edit-col">
        <span class="title"><?php esc_html_e('Price Group', 'sell_media'); ?></span>
        <?php
			$args = array(
				'show_option_none' => __('None', 'sell_media'),
				'option_none_value' => 0,
				'name' => 'sell_media_price_group',
				'id' => 'sell-media-price-group',
				'class' => 'sell-media-price-group',
				'taxonomy' => 'price-group',
				'hierarchical' => true,
				'depth' => 1,
				'hide_empty' => false
			);
			wp_dropdown_categories($args);
			wp_nonce_field('_sell_media_quick_edit_nonce', 'sell_media_quick_edit_nonce');
			?>
    </div>
    <div class="inline-edit-col">
        <span class="title"><?php esc_html_e('Price', 'sell_media'); ?></span>
        <span class="input-text-wrap">
            <input name="sell_media_price" id="sell-media-price" class="inline-edit-password-input" type="number"
                step="0.01" min="0" placeholder="<?php esc_attr_e('— No Change —', 'sell_media'); ?>" />
        </span>
    </div>
</fieldset>
<?php
}

add_action('quick_edit_custom_box', 'sell_media_add_quick_edit', 10, 2);
add_action('bulk_edit_custom_box', 'sell_media_add_quick_edit', 10, 2);

/**
 * Save quick edit values.
 * @param  int $post_id Post Id.
 */
function sell_media_save_quick_edit_custom_meta($post_id)
{

	if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
	if (!isset($_POST['sell_media_quick_edit_nonce']) || !wp_verify_nonce($_POST['sell_media_quick_edit_nonce'], '_sell_media_quick_edit_nonce')) return;
	if (!current_user_can('edit_post', $post_id)) return;

	if (wp_is_post_revision($post_id)) return;
	if (isset($_POST['sell_media_price_group'])) {
		wp_set_post_terms($post_id, sanitize_text_field($_POST['sell_media_price_group']), 'price-group');
	}

	if (isset($_POST['sell_media_price'])) {
		$sell_media_price = sprintf('%0.2f', (float) $_POST['sell_media_price']);
		update_post_meta($post_id, 'sell_media_price', $sell_media_price);
	}
}

add_action('save_post', 'sell_media_save_quick_edit_custom_meta');

/**
 * Save bulk edit values.
 */
function sell_media_save_bulk_edit()
{
	if (!isset($_POST['sell_media_quick_edit_nonce']) || !wp_verify_nonce($_POST['sell_media_quick_edit_nonce'], '_sell_media_quick_edit_nonce')) return;
	$post_ids = isset($_POST['post_ids']) ? sanitize_text_field($_POST['post_ids']) : array();
	$sell_media_price_group  = isset($_POST['sell_media_price_group']) ? sanitize_text_field($_POST['sell_media_price_group']) : null;
	$sell_media_price  = isset($_POST['sell_media_price']) ? sanitize_text_field($_POST['sell_media_price']) : null;

	if (!empty($post_ids) && is_array($post_ids)) {
		foreach ($post_ids as $post_id) {
			wp_set_post_terms($post_id, $sell_media_price_group, 'price-group');
			if (!is_null($sell_media_price)) {
				$sell_media_price = sprintf('%0.2f', (float) $sell_media_price);
				update_post_meta($post_id, 'sell_media_price', $sell_media_price);
			}
		}
	}

	die();
}

add_action('wp_ajax_sell_media_save_bulk_edit', 'sell_media_save_bulk_edit');

/**
 * Enable large image uploads since WordPress 5.3 disabled it
 */
add_filter('big_image_size_threshold', '__return_false');

/**
 * Filter media by user role in admin
 */
add_filter('pre_get_posts', 'sell_media_show_loggedin_user_items');
function sell_media_show_loggedin_user_items($query)
{

	// get logged in user data object
	$_current_user_obj = wp_get_current_user();

	if (is_admin() && in_array('contributor', $_current_user_obj->roles)) {

		//Assign user to media query
		$query->set('author', $_current_user_obj->ID);
	}
	return $query;
}

add_action('admin_enqueue_scripts', 'wps_cpt_admin_enqueue_scripts');
/**
 * Disable initial autosave/autodraft
 */
function wps_cpt_admin_enqueue_scripts()
{
	if ('sell_media_item' == get_post_type())
		wp_dequeue_script('autosave');
}