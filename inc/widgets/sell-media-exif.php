<?php

error_reporting(E_ALL);

if ( ! class_exists( 'Sell_Media_Image_Exif_Widget' ) ) {

	class Sell_Media_Image_Exif_Widget extends WP_Widget {

		function __construct(){
			$plugin_name = apply_filters( 'sell_media_plugin_name', esc_attr__( 'Sell Media', 'sell_media' ), 10 );
			$widget_ops = array( 'description' => esc_attr__( 'Displays image exif data (shutter speed, aperture, ISO, etc) of current product.', 'sell_media' ) );
			$control_ops = array( 'width' => 200, 'height' => 200 );
			parent::__construct( false, $plugin_name . ' Exif', $widget_ops, $control_ops );
		}

		/* Displays the Widget in the front-end */
		function widget($args, $instance){
			$before_widget = isset( $args['before_widget'] ) ? $args['before_widget'] : null;
			$after_widget  = isset( $args['after_widget'] ) ? $args['after_widget'] : null;
			$before_title  = isset( $args['before_title'] ) ? $args['before_title'] : null;
			$after_title   = isset( $args['after_title'] ) ? $args['after_title'] : null;

			$title = apply_filters( 'widget_title', !empty( $instance ) ? $instance['title'] : '' );

			echo wp_kses_post( $before_widget );

			$img_ids = get_post_meta( get_the_ID(), '_sell_media_attachment_id', true );

			if( ! empty( $img_ids ) && is_array( $img_ids ) ) {
				foreach( $img_ids as $img_id ) {
					$imgdata = wp_get_attachment_metadata( $img_id );
					if ( $imgdata ) {
						
						if ( $title )
							echo wp_kses_post( $before_title ) . esc_html( $title ) . wp_kses_post( $after_title );
						?>
						<div class="sell-media-exif-widget sell-media-widget">
							<ul class="exif-info">

								<?php
								if ( isset($imgdata['image_meta']['camera']) ) { ?>
									<li class="camera"><div class="genericon genericon-small genericon-image"></div><span class="exif-title"><?php esc_html_e( 'Camera ', 'sell_media' ); ?></span><?php echo esc_html($imgdata['image_meta']['camera']); ?></li>
								<?php } ?>

								<?php
								if ( isset($imgdata['image_meta']['aperture']) ) { ?>
									<li class="aperture"><div class="genericon genericon-small genericon-aside"></div><span class="exif-title"><?php esc_html_e( 'Aperture ', 'sell_media' ); ?></span><?php echo esc_html('f/' .  $imgdata['image_meta']['aperture']); ?></li>
								<?php } ?>

								<?php
								if ( isset($imgdata['image_meta']['focal_length']) ) { ?>
									<li class="focal-length"><div class="genericon genericon-small genericon-share"></div><span class="exif-title"><?php esc_html_e( 'Focal Length ', 'sell_media' ); ?></span><?php echo esc_html( $imgdata['image_meta']['focal_length'] ); ?></li>
								<?php } ?>

								<?php
								if ( isset($imgdata['image_meta']['shutter_speed']) ) { ?>
									<li class="shutter-speed"><div class="genericon genericon-small genericon-time"></div><span class="exif-title"><?php esc_html_e( 'Shutter Speed ', 'sell_media' ); ?></span>
										<?php
										if ($imgdata['image_meta']['shutter_speed'] && (1 / max($imgdata['image_meta']['shutter_speed'], 1)) > 1) {
											echo esc_html( "1/" );
											if (number_format((1 / max($imgdata['image_meta']['shutter_speed'], 1)), 1) == number_format((1 / max($imgdata['image_meta']['shutter_speed'], 1)), 0)) {
												echo esc_html(number_format((1 / max($imgdata['image_meta']['shutter_speed'], 1)), 0, '.', '') . ' sec' );
											} else {
												echo esc_html(number_format((1 / max($imgdata['image_meta']['shutter_speed'], 1)), 1, '.', '') . ' sec' );
											}
										} else {
											echo esc_html( $imgdata['image_meta']['shutter_speed'] . ' sec' );
										}
										?>
									</li>
								<?php } ?>

								<?php
								if ( isset($imgdata['image_meta']['iso']) ) { ?>
									<li class="iso"><div class="genericon genericon-small genericon-maximize"></div><span class="exif-title"><?php esc_html_e( 'ISO ', 'sell_media' ); ?></span><?php
										esc_html_e($imgdata['image_meta']['iso'],'sell_media'); ?></li>
								<?php } ?>
								<?php
								
								if ( isset($imgdata['image_meta']['credit']) && isset($imgdata['image_meta']['creditcxvcvx']) ) { ?>
									<li class="credit"><div class="genericon genericon-small genericon-user"></div><span class="exif-title"><?php esc_html_e( 'Credit ', 'sell_media' ); ?></span><?php esc_html_e($imgdata['image_meta']['creditcxvcvx'],'sell_media'); ?></li>
								<?php } ?>

								<?php
								if ( isset($imgdata['image_meta']['created_timestamp']) ) { ?>
									<li class="timestamp"><div class="genericon genericon-small genericon-month"></div><span class="exif-title"><?php esc_html_e( 'Date ', 'sell_media' ); ?></span><?php
									echo esc_html( date("M d, Y", esc_attr( $imgdata['image_meta']['created_timestamp'] ) ) ); ?></li>
								<?php } ?>

								<?php
								if ( isset($imgdata['image_meta']['copyright']) ) { ?>
									<li class="copyright"><div class="genericon genericon-small genericon-warning"></div><span class="exif-title"><?php esc_html_e( 'Copyright ', 'sell_media' ); ?></span><?php esc_html_e($imgdata['image_meta']['copyright'],'sell_media'); ?></li>
								<?php } else { ?>
									<li class="copyright"><div class="genericon genericon-small genericon-warning"></div><span class="exif-title"><?php esc_html_e( 'Copyright ', 'sell_media' ); ?></span><?php the_time('Y '); esc_html_e( 'by ', 'sell_media' ); $author = get_the_author();
										esc_html_e($author." / ",'sell_media'); bloginfo( 'name' ); ?><?php esc_html_e($imgdata['image_meta']['copyright'],'sell_media'); ?></li>						<?php } ?>

							</ul>
						</div><!-- .sell-media-exif-widget -->
						<?php
					}
				}
			}
			echo wp_kses_post( $after_widget );
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
			$instance = wp_parse_args( (array) $instance, array( 'title' => esc_html__( 'Camera Info', 'sell_media' ) ) );
			$title = htmlspecialchars($instance['title']);

			/* Title */
			?><p><label for="<?php echo esc_attr( $this->get_field_id('title') ); ?>"><?php echo esc_html__( 'Title', 'sell_media' ) ?>:</label><input class="widefat" id="<?php echo esc_attr( $this->get_field_id('title') ); ?>" name="<?php echo esc_attr( $this->get_field_name('title') ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>" /></p><?php

		}

	}

	function Sell_Media_Image_Exif_Widget_Init() {
		register_widget( 'Sell_Media_Image_Exif_Widget' );
	}

	add_action( 'widgets_init', 'Sell_Media_Image_Exif_Widget_Init' );

}