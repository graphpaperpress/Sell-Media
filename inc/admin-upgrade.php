<?php

/**
 * Admin Upgrade
 *
 * @package Sell Media
 * @author Thad Allender <support@graphpaperpress.com>
 */

$version = get_option( 'sell_media_version' );

/**
 * This script pulls the current settings for Sell Media and extensions, then grooms them as needed
 * making them ready for the updated settings API.
 */
if ( $version <= '1.6.5' ) {

	global $wpdb;
	$current_settings = $wpdb->get_results( "SELECT option_name, option_value FROM {$wpdb->prefix}options WHERE option_name LIKE 'sell_media_%';" );

	if ( empty( $current_settings ) ) {
		return;
	}

	$new_settings = array();
	foreach ( $current_settings as $r ) {
		$serialized = maybe_unserialize( $r->option_value );
		if ( is_array( $serialized ) && ! empty( $serialized ) ) {
			foreach ( $serialized as $k => $v ) {
				if ( ! empty( $v ) ) {
					/**
					 * The legacy format wasn't saved in the same format of the
					 * new settings API, we update the format and take some time
					 * to prefix our options.
					 */
					if ( in_array( $k, array( 'show_collection', 'show_license', 'show_creators' ), true ) ) {
						$new_settings['admin_columns'][] = $k;
					} elseif ( 'image_url' === $k ) {
						unset( $k );
						$new_settings['watermark_attachment_url'] = $v;
					} elseif ( 'attachment_id' === $k ) {
						unset( $k );
						$new_settings['watermark_attachment_id'] = $v;
					} elseif ( 'all' === $k ) {
						unset( $k );
						$new_settings['watermark_all'][] = 'yes';
					} elseif ( 'sell_media_free_downloads' === $r->option_name && 'api_key' === $k ) {
						unset( $k );
						$new_settings['free_downloads_api_key'] = $v;
					} elseif ( 'sell_media_free_downloads' === $r->option_name && 'list' === $k ) {
						unset( $k );
						$new_settings['free_downloads_list'] = $v;
					} elseif ( 'api_key' === $k ) {
						unset( $k );
						$new_settings['mailchimp_api_key'] = $v;
					} elseif ( 'list' === $k ) {
						unset( $k );
						$new_settings['mailchimp_list'] = $v;
					} elseif ( 'hide_download_tab' === $k ) {
						unset( $k );
						$new_settings['reprints_hide_download_tabs'][] = 'yes';
					} elseif ( 'base_region' === $k ) {
						unset( $k );
						$new_settings['reprints_base_region'] = $v;
					} elseif ( 'unit_measurement' === $k ) {
						unset( $k );
						$new_settings['reprints_unit_measurement'] = $v;
					} else {
						$new_settings[ $k ] = $v;
					}
				}
			}
		}
	}
	$update_option_result = update_option( 'sell_media_options', $new_settings );
}

if ( $version <= '2.2.6' ) {
	/**
	 * Keyword search improvements
	 *
	 * In version prior to 2.2.7, Sell Media would assign keywords of the attachment
	 * to the actual sell_media_item post type. This created problems and unnecessary
	 * code complexity when it came to searching for keywords.
	 *
	 * To fix this, let's loop over Sell Media entries and find the ones
	 * with only one attachment. Next, we need to get the keywords of that entry and apply
	 * them to the attachment.
	 *
	 * Now, searching is simplified and we can search attachment post types for keywords.
	 */

	// Query args
	$args = array(
		'post_type' => 'sell_media_item',
		'posts_per_page' => -1,
	);

	// Query all sell_media_items
	$the_query = new WP_Query( $args );

	// The Loop
	if ( $the_query->have_posts() ) {
		while ( $the_query->have_posts() ) {
			$the_query->the_post();
			$attachments = sell_media_get_attachments( get_the_ID() );

			// In theory, this should loop over all sell media attachments
			// and parse/save iptc data as both post meta and custom taxonomy terms.
			// if ( $attachments ) foreach ( $attachments as $attachment ) {
			// 	$original_file = get_attached_file( $attachment );
			// 	if ( file_exists( $original_file ) ) {
			// 		$image_products->parse_iptc_info( $original_file, $attachment );
			// 	}
			// }

			$count = count( $attachments );

			// if there are more than one attachments, the attachments will already have keywords assigned
			// in that case, let's skip them
			if ( 1 === $count ) {
				// get the ids of keywords assigned to this post
				$keyword_ids = wp_get_post_terms( get_the_ID(), 'keywords', array( 'fields' => 'ids' ) );
				// make sure keywords exist
				if ( ! is_wp_error( $keyword_ids ) ) {
					wp_set_object_terms( $attachments[0], $keyword_ids, 'keywords', true );
				}
			}
		}
		// restore original post data
		wp_reset_postdata();
	}
}
