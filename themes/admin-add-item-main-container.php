<?php
/**
 * Template file for sell media add item main container.
 *
 * @package Sell Media
 */

global $post;
$i = 0;
$tabs = $this->get_tabs();
if ( empty( $tabs ) ) {
	return false;
}
?>
<div class="sell-media-add-item-main-container-wrap">
	<ul class="main-container-tabs-nav">
		<?php
		foreach ( $tabs as $key => $tab ) :
			$class = ( 0 === $i ) ? 'sell-media-tab-nav-active' : '';
		?>
		<li id="sell-media-tab-<?php echo esc_attr( $key ); ?>"><a href="#sell-media-<?php echo esc_attr( $key ); ?>" class="<?php echo esc_attr( $class ); ?>"><?php echo esc_attr( $tab['tab_label'] ); ?></a></li>
		<?php endforeach; ?>
	</ul>
	<div class="main-container-tabs-contents">
		<?php
		foreach ( $tabs as $key => $tab ) :
		?>
		<div id="sell-media-<?php echo esc_attr( $key ); ?>" class="ui-state-active sell-media-tab-content">
			<h3 class="sell-media-tab-content-title"><?php echo esc_attr( $tab['content_title'] ); ?></h3>
			<?php
			do_action( 'sell_media_add_item_tab_before_content_callback', $key, $post );
			call_user_func( $tab['content_callback'], $post );
			do_action( 'sell_media_add_item_tab_after_content_callback', $key, $post );
			?>
		</div>
		<?php endforeach; ?>
	</div>
</div>
