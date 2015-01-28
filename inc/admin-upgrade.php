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
if ( $version <= '1.6.5' ){

    global $wpdb;
    $current_settings = $wpdb->get_results( "SELECT option_name, option_value FROM {$wpdb->prefix}options WHERE option_name LIKE 'sell_media_%';" );

    if ( empty( $current_settings ) ){
        return;
    }

    $new_settings = array();
    foreach( $current_settings as $r ){
        $serialized = maybe_unserialize( $r->option_value );
        if ( is_array( $serialized ) && ! empty( $serialized ) ){
            foreach( $serialized as $k => $v ){
                if ( ! empty( $v ) ){
                    /**
                     * The legacy format wasn't saved in the same format of the
                     * new settings API, we update the format and take some time
                     * to prefix our options.
                     */
                    if ( in_array( $k, array('show_collection', 'show_license', 'show_keywords', 'show_creators') ) ){
                        $new_settings['admin_columns'][] = $k;
                    }

                    // sell_media_watermark
                    elseif ( $k == 'image_url' ) {
                        unset( $k );
                        $new_settings['watermark_attachment_url'] = $v;
                    } elseif( $k == 'attachment_id' ) {
                        unset( $k );
                        $new_settings['watermark_attachment_id'] = $v;
                    } elseif( $k == 'all' ){
                        unset( $k );
                        $new_settings['watermark_all'][] = "yes";
                    }

                    // sell_media_free_downloads
                    elseif ( $r->option_name == 'sell_media_free_downloads' && $k == 'api_key' ){
                        unset( $k );
                        $new_settings['free_downloads_api_key'] = $v;
                    } elseif ( $r->option_name == 'sell_media_free_downloads' && $k == 'list' ){
                        unset( $k );
                        $new_settings['free_downloads_list'] = $v;
                    }

                    // sell_media_mailchimp
                    elseif( $k == 'api_key' ){
                        unset( $k );
                        $new_settings['mailchimp_api_key'] = $v;
                    }
                    elseif( $k == 'list' ){
                        unset( $k );
                        $new_settings['mailchimp_list'] = $v;
                    }

                    // Reprints
                    elseif( $k == 'hide_download_tab' ){
                        unset( $k );
                        $new_settings['reprints_hide_download_tabs'][] = "yes";
                    }

                    elseif ( $k == 'base_region' ){
                        unset( $k );
                        $new_settings['reprints_base_region'] = $v;
                    }

                    elseif ( $k == 'unit_measurement' ){
                        unset( $k );
                        $new_settings['reprints_unit_measurement'] = $v;
                    }

                    else {
                        $new_settings[ $k ] = $v;
                    }
                }
            }
        }
    }
    $update_option_result = update_option( 'sell_media_options', $new_settings );
}