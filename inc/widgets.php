<?php
/**
 * Widgets
 *
 * @package Sell Media
 * @author Thad Allender <support@graphpaperpress.com>
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) { exit; }

/**
 * Include Widgets
 */
require_once( SELL_MEDIA_PLUGIN_DIR . '/inc/widgets/sell-media-exif.php' );
require_once( SELL_MEDIA_PLUGIN_DIR . '/inc/widgets/sell-media-featured.php' );
require_once( SELL_MEDIA_PLUGIN_DIR . '/inc/widgets/sell-media-keywords.php' );
require_once( SELL_MEDIA_PLUGIN_DIR . '/inc/widgets/sell-media-recent.php' );
require_once( SELL_MEDIA_PLUGIN_DIR . '/inc/widgets/sell-media-search.php' );
require_once( SELL_MEDIA_PLUGIN_DIR . '/inc/widgets/sell-media-similar.php' );

/**
 * Register Widgets
 */
function sell_media_widgets_init() {

	register_sidebar( array(
		'name' => __( 'Sell Media Below Single Content', 'sell_media' ),
		'id' => 'sell-media-below-single-content',
		'before_widget' => '<aside id="%1$s" class="widget %2$s">',
		'after_widget' => '</aside>',
		'before_title' => '<h3 class="widget-title">',
		'after_title' => '</h3>',
	) );

	register_sidebar( array(
		'name' => __( 'Sell Media Below Single Sidebar', 'sell_media' ),
		'id' => 'sell-media-below-single-sidebar',
		'before_widget' => '<aside id="%1$s" class="widget %2$s">',
		'after_widget' => '</aside>',
		'before_title' => '<h3 class="widget-title">',
		'after_title' => '</h3>',
	) );

}
add_action( 'widgets_init', 'sell_media_widgets_init', 20 );

/**
 * Display Widget Below Single Content
 *
 * Output buffering is required because this needs
 * to return content (we're filtering the_content)
 * and dynamic_sidebar() echos content.
 */
function sell_media_below_content_widgets() {

	if ( is_active_sidebar( 'sell-media-below-single-content' ) ) : ?>

		<div id="sell-media-below-single-content" class="sell-media-widget-area">
		<?php dynamic_sidebar( 'sell-media-below-single-content' ); ?>
		</div>

	<?php endif;
}
add_action( 'sell_media_below_content', 'sell_media_below_content_widgets', 20 );

/**
 * Display Widget Below Single Sidebar
 */
function sell_media_widgets_below_single_sidebar() {
	?>

	<?php if ( is_active_sidebar( 'sell-media-below-single-sidebar' ) ) : ?>
		<section id="sell-media-below-single-sidebar" class="sell-media-widget-area" role="complementary">
			<?php dynamic_sidebar( 'sell-media-below-single-sidebar' ); ?>
		</section>
	<?php endif; ?>

<?php

}
add_action( 'sell_media_below_buy_button', 'sell_media_widgets_below_single_sidebar', 20 );
