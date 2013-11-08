<?php

/**
 * Custom CSS
 */
function sell_media_plugin_custom_css() {

    $theme_options = get_option( sell_media_get_current_plugin_id() . '_options' );

    if ( isset( $theme_options['css'] ) && '' != $theme_options['css'] ) {
        echo '<!-- BeginHeader --><style type="text/css">';
        echo stripslashes_deep( $theme_options['css'] );
        echo '</style><!-- EndHeader -->';
    }
}

add_action( 'wp_head', 'sell_media_plugin_custom_css', 11);

/**
 * Alternative styles
 */
function sell_media_plugin_alt_styles() {

    $theme_options = get_option( sell_media_get_current_plugin_id() . '_options' );
	if ( isset ( $theme_options['color'] ) && '' != $theme_options['color'] ) {
		$file = get_stylesheet_directory() . '/css/' . $theme_options['color'] . '.css';
		if ( file_exists( $file ) ) {
			wp_enqueue_style( 'sell-media-alt-style', get_stylesheet_directory_uri() . '/css/' . $theme_options['color'] . '.css', array( 'style' ) );
		}
	}
}
add_action( 'wp_enqueue_scripts', 'sell_media_alt_styles' );