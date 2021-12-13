<?php

if ( ! class_exists( 'Sell_Media_Image_Exif_Widget' ) ) {

	class Sell_Media_Image_Exif_Widget extends WP_Widget {

		function __construct(){
			$plugin_name = apply_filters( 'sell_media_plugin_name', __( 'Sell Media', 'sell_media' ), 10 );
			$widget_ops = array( 'description' => __( 'Displays image exif data (shutter speed, aperture, ISO, etc) of current product.', 'sell_media' ) );
			$control_ops = array( 'width' => 200, 'height' => 200 );
			parent::__construct( false, $plugin_name . ' Exif', $widget_ops, $control_ops );
		}

		/* Displays the Widget in the front-end */
		function widget($args, $instance){
			extract($args);
			$title = apply_filters( 'widget_title', empty( $instance['title'] ) ? '' : $instance['title'] );
			extract($args);
			_e($before_widget,'sell_media');

			global $post;
			$imgdata = wp_get_attachment_metadata( get_post_meta( $post->ID, '_sell_media_attachment_id', true ) );
			if ( $imgdata) {
				if ( $title )
					_e($before_title . $title . $after_title,'sell_media');
				?>
				<div class="sell-media-exif-widget sell-media-widget">
					<ul class="exif-info">

						<?php
						if ( isset($imgdata['image_meta']['camera']) ) { ?>
							<li class="camera"><div class="genericon genericon-small genericon-image"></div><span class="exif-title"><?php _e( 'Camera ', 'sell_media' ); ?></span><?php _e($imgdata['image_meta']['camera'],'sell_media'); ?></li>
						<?php } ?>

						<?php
						if ( isset($imgdata['image_meta']['aperture']) ) { ?>
							<li class="aperture"><div class="genericon genericon-small genericon-aside"></div><span class="exif-title"><?php _e( 'Aperture ', 'sell_media' ); ?></span><?php _e('f/' .  $imgdata['image_meta']['aperture'],'sell_media'); ?></li>
						<?php } ?>

						<?php
						if ( isset($imgdata['image_meta']['focal_length']) ) { ?>
							<li class="focal-length"><div class="genericon genericon-small genericon-share"></div><span class="exif-title"><?php _e( 'Focal Length ', 'sell_media' ); ?></span><?php _e($imgdata['image_meta']['focal_length'],'sell_media'); ?></li>
						<?php } ?>

						<?php
						if ( isset($imgdata['image_meta']['shutter_speed']) ) { ?>
							<li class="shutter-speed"><div class="genericon genericon-small genericon-time"></div><span class="exif-title"><?php _e( 'Shutter Speed ', 'sell_media' ); ?></span>
								<?php
								if ((1 / $imgdata['image_meta']['shutter_speed']) > 1) {
									_e("1/",'sell_media');
									if (number_format((1 / $imgdata['image_meta']['shutter_speed']), 1) == number_format((1 / $imgdata['image_meta']['shutter_speed']), 0)) {
										_e(number_format((1 / $imgdata['image_meta']['shutter_speed']), 0, '.', '') . ' sec','sell_media');
									} else {
										_e(number_format((1 / $imgdata['image_meta']['shutter_speed']), 1, '.', '') . ' sec','sell_media');
									}
									} else {
										_e($imgdata['image_meta']['shutter_speed'].' sec','sell_media');
								}
								?>
							</li>
						<?php } ?>

						<?php
						if ( isset($imgdata['image_meta']['iso']) ) { ?>
							<li class="iso"><div class="genericon genericon-small genericon-maximize"></div><span class="exif-title"><?php _e( 'ISO ', 'sell_media' ); ?></span><?php 
							_e($imgdata['image_meta']['iso'],'sell_media'); ?></li>
						<?php } ?>
						<?php
						if ( isset($imgdata['image_meta']['credit']) ) { ?>
							<li class="credit"><div class="genericon genericon-small genericon-user"></div><span class="exif-title"><?php _e( 'Credit ', 'sell_media' ); ?></span><?php _e($imgdata['image_meta']['credit'],'sell_media'); ?></li>
						<?php } ?>

						<?php
						if ( isset($imgdata['image_meta']['created_timestamp']) ) { ?>
							<li class="timestamp"><div class="genericon genericon-small genericon-month"></div><span class="exif-title"><?php _e( 'Date ', 'sell_media' ); ?></span><?php 
							_e(date("M d, Y", $imgdata['image_meta']['created_timestamp']),'sell_media'); ?></li>
						<?php } ?>

						<?php
						if ( isset($imgdata['image_meta']['copyright']) ) { ?>
							<li class="copyright"><div class="genericon genericon-small genericon-warning"></div><span class="exif-title"><?php _e( 'Copyright ', 'sell_media' ); ?></span><?php _e($imgdata['image_meta']['copyright'],'sell_media'); ?></li>
						<?php } else { ?>
							<li class="copyright"><div class="genericon genericon-small genericon-warning"></div><span class="exif-title"><?php _e( 'Copyright ', 'sell_media' ); ?></span><?php the_time('Y '); _e( 'by ', 'sell_media' ); $author = get_the_author(); 
							_e($author." / ",'sell_media'); bloginfo( 'name' ); ?><?php _e($imgdata['image_meta']['copyright'],'sell_media'); ?></li>						<?php } ?>

					</ul>

				</div><!-- .sell-media-exif-widget -->

				<?php
				_e($after_widget,'sell_media');

			}
		}

		/* Saves the settings. */
		function update($new_instance, $old_instance){
			$instance = $old_instance;
			$instance['title'] = stripslashes($new_instance['title']);

			return $instance;
		}

		/* Creates the form for the widget in the back-end. */
		function form($instance){
			// Defaults
			$instance = wp_parse_args( (array) $instance, array( 'title' => __( 'Camera Info', 'sell_media' ) ) );
			$title = htmlspecialchars($instance['title']);

			/* Title */
			_e('<p><label for="' . $this->get_field_id('title') . '">' . 'Title:' . '</label><input class="widefat" id="' . $this->get_field_id('title') . '" name="' . $this->get_field_name('title') . '" type="text" value="' . $title . '" /></p>','sell_media');

		}

	}

	function Sell_Media_Image_Exif_Widget_Init() {
		register_widget( 'Sell_Media_Image_Exif_Widget' );
	}

	add_action( 'widgets_init', 'Sell_Media_Image_Exif_Widget_Init' );

}
