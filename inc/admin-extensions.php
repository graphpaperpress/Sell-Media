<?php

/**
 * Print and handle the data for the add-ons form
 */
function sell_media_extensions_callback_fn(){

    $extensions = get_transient( 'graphpaperpress_extensions_feed' );

    if ( empty( $extensions ) ) {
        $response = wp_remote_get( 'http://graphpaperpress.com/json-extensions-feed/' );
        if ( ! is_wp_error( $response ) && isset( $response['body'] ) ) {
            $extensions = json_decode( $response['body'] );
            set_transient('graphpaperpress_extensions_feed', $extensions, 3600);
        }
    }
?>
<div class="wrap sell_media-extensions">
    <?php screen_icon(); ?>
    <h2><?php _e( 'Extensions for Sell Media', 'sell_media' ); ?></h2>
    <p><?php _e( 'These extensions provide additonal functionality for the Sell Media plugin.', 'sell_media' ); ?></p>
    <?php if ( $extensions ) : foreach( $extensions as $extension ) : ?>
        <div class="row-container">
            <div class="extension">
                <h3 class="title"><a href="<?php print $extension->permalink; ?>"><?php print $extension->title; ?></a></h3>
                <div class="image"><a href="<?php print $extension->permalink; ?>"><img src="<?php print $extension->image[0]; ?>" /></a></div>
            </div>
        </div>
    <?php endforeach; endif; ?>
</div>
<?php }