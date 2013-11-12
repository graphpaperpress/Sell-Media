<?php

$version = get_option( 'sell_media_version' );

/**
 * This script pulls the current settings for Sell Media and extensions, then grooms them as needed
 * making them ready for the updated settings API.
 */
if ( $version <= '1.6.5' ){
    global $wpdb;
    $current_settings = $wpdb->get_results( "SELECT option_name, option_value FROM {$wpdb->prefix}options WHERE option_name LIKE 'sell_media_%';" );
    $new_settings = array();
    foreach( $current_settings as $r ){
        $serialized = maybe_unserialize( $r->option_value );
        if ( is_array( $serialized ) && ! empty( $serialized ) ){
            foreach( $serialized as $k => $v ){
                if ( ! empty( $v ) ){
                    /**
                     * The legacy format wasn't saved in the same format of the
                     * new settings API, we update the format
                     */
                    if ( in_array( $k, array('show_collection', 'show_license', 'show_keywords', 'show_creators') ) ){
                        $new_settings['admin_columns'][] = $k;
                    }
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
                    else {
                        $new_settings[ $k ] = $v;
                    }
                }
            }
        }
    }

    $update_option_result = update_option( 'sell_media_options', $new_settings );
}