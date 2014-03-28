<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Include Widgets
 */
require_once( SELL_MEDIA_PLUGIN_DIR . '/inc/widgets/sell-media-exif.php' );
require_once( SELL_MEDIA_PLUGIN_DIR . '/inc/widgets/sell-media-featured.php' );
require_once( SELL_MEDIA_PLUGIN_DIR . '/inc/widgets/sell-media-keywords.php' );
require_once( SELL_MEDIA_PLUGIN_DIR . '/inc/widgets/sell-media-recent.php' );
require_once( SELL_MEDIA_PLUGIN_DIR . '/inc/widgets/sell-media-similar.php' );

/**
 * Register Widgets
 */
function sell_media_widgets_init() {

	register_sidebar( array(
		'name' => __( 'Sell Media Below Single Item', 'sell_media' ),
		'id' => 'sell-media-single-sidebar',
		'before_widget' => '<aside id="%1$s" class="widget %2$s">',
		'after_widget' => "</aside>",
		'before_title' => '<h3 class="widget-title">',
		'after_title' => '</h3>',
	) );

	register_sidebar( array(
		'name' => __( 'Sell Media Below Single Sidebar', 'sell_media' ),
		'id' => 'sell-media-below-single-sidebar',
		'before_widget' => '<aside id="%1$s" class="widget %2$s">',
		'after_widget' => "</aside>",
		'before_title' => '<h3 class="widget-title">',
		'after_title' => '</h3>',
	) );

}
add_action( 'widgets_init', 'sell_media_widgets_init' );

/**
 * Display Widget Below Single Item
 */
function sell_media_widgets_below_single_item() { ?>

	<?php if ( is_active_sidebar( 'sell-media-single-sidebar' ) ) : ?>
		<section id="sell-media-single-sidebar" class="widget-area" role="complementary">
			<?php dynamic_sidebar( 'sell-media-single-sidebar' ); ?>
		</section>
	<?php endif; ?>

<?php

}
add_action( 'sell_media_single_bottom_hook', 'sell_media_widgets_below_single_item' );

/**
 * Display Widget Below Single Item Sidebar
 */
function sell_media_widgets_below_single_item_sidebar() { ?>

	<?php if ( is_active_sidebar( 'sell-media-below-single-sidebar' ) ) : ?>
		<section id="sell-media-below-single-sidebar" class="widget-area" role="complementary">
			<?php dynamic_sidebar( 'sell-media-below-single-sidebar' ); ?>
		</section>
	<?php endif; ?>

<?php

}
add_action( 'sell_media_additional_list_items', 'sell_media_widgets_below_single_item_sidebar' );