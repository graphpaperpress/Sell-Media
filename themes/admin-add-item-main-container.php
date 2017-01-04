<?php
/**
 * Template file for sell media add item main container.
 *
 * @package Sell Media
 */

?>
<div class="sell-media-add-item-main-container-wrap">
	<ul class="main-container-tabs-nav">
		<li><a href="#sell-media-file-upload" class="sell-media-tab-nav-active"><?php esc_html_e( 'File Upload', 'sell_media' ); ?></a></li>
		<li><a href="#sell-media-settings"><?php esc_html_e( 'Settings', 'sell_media' ); ?></a></li>
		<li><a href="#sell-media-stats"><?php esc_html_e( 'Stats', 'sell_media' ); ?></a></li>
		<li><a href="#sell-media-seo"><?php esc_html_e( 'SEO', 'sell_media' ); ?></a></li>
		<li><a href="#sell-media-advanced"><?php esc_html_e( 'Advanced Options', 'sell_media' ); ?></a></li>
	</ul>
	<div class="main-container-tabs-contents">
		<div id="sell-media-file-upload" class="ui-state-active sell-media-tab-content">
			<?php echo sell_media_files_meta_box( $post ); ?>
		</div>
		<div id="sell-media-settings" class="sell-media-tab-content">
			<?php echo sell_media_options_meta_box( $post ); ?>
		</div>
		<div id="sell-media-stats" class="sell-media-tab-content">
			<?php echo sell_media_stats_meta_box( $post ); ?>
		</div>
		<div id="sell-media-seo" class="sell-media-tab-content">
			<?php sell_media_editor(); ?>
		</div>
		<div id="sell-media-advanced" class="sell-media-tab-content">
			<?php post_categories_meta_box( $post, array('args' =>array( 'taxonomy' => 'collection' )) ); ?>
			<?php post_categories_meta_box( $post, array('args' =>array( 'taxonomy' => 'licenses' )) ); ?>
			<?php post_tags_meta_box( $post, array('args' =>array( 'taxonomy' => 'creator' )) ); ?>
		</div>
	</div>
</div>
