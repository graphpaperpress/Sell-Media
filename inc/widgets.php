<?php
/**
 * Load scripts
 */

function sell_media_widgets_load_scripts() {

	wp_enqueue_style( 'sell-media-widgets-style', plugin_dir_url( __FILE__ ) . '../css/sell_media_widgets.css' );

}

add_action( 'wp_enqueue_scripts', 'sell_media_widgets_load_scripts' );

/**
 * Register Sidebar
 */

function sell_media_widgets_register_sidebar() {

	register_sidebar( array(
		'name' => __( 'Sell Media Single Product', 'sell_media' ),
		'id' => 'sell-media-single-sidebar',
		'before_widget' => '<aside id="%1$s" class="widget %2$s">',
		'after_widget' => "</aside>",
		'before_title' => '<h3 class="widget-title">',
		'after_title' => '</h3>',
	) );

}
add_action( 'widgets_init', 'sell_media_widgets_register_sidebar' );

/**
* Register Widgets
*/
require_once( dirname(__FILE__) . '/widgets/sell-media-recent.php' );
require_once( dirname(__FILE__) . '/widgets/sell-media-similar.php' );
require_once( dirname(__FILE__) . '/widgets/sell-media-featured.php' );
require_once( dirname(__FILE__) . '/widgets/sell-media-keywords.php' );

/**
 * Display Sidebar
 */
function sell_media_widgets_single_sidebar() { ?>
	
	<?php if ( is_active_sidebar( 'sell-media-single-sidebar' ) ) : ?>
		<section id="sell-media-single-sidebar" class="widget-area" role="complementary">
			<?php dynamic_sidebar( 'sell-media-single-sidebar' ); ?>
		</section>
	<?php endif; ?>
	
<?php 

}
add_action( 'sell_media_single_bottom_hook', 'sell_media_widgets_single_sidebar' );
?>