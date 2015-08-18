<?php class Sell_Media_Similar_Widget extends WP_Widget
{
	function Sell_Media_Similar_Widget(){
		$widget_ops = array( 'description' => 'Displays similar items' );
		$control_ops = array( 'width' => 200, 'height' => 200 );
		parent::__construct( false, $name = 'Sell Media Similar Items', $widget_ops, $control_ops );
	}

	/* Displays the Widget in the front-end */
	function widget( $args, $instance ){
		extract( $args );
		$title = apply_filters( 'widget_title', empty( $instance['title'] ) ? '' : $instance['title'] );
		extract( $args );
		echo $before_widget;

		if ( $title ) {
			echo $before_title . $title . $after_title; }

		global $post;
		//Returns Array of Term Names for "collection"
		$terms = wp_get_post_terms( $post->ID, 'collection', array( 'fields' => 'slugs' ) );

		if ( ! empty( $terms ) ) {

			$args = array(
				'tax_query' => array(
					array(
						'taxonomy' => 'collection',
						'field' => 'slug',
						'terms' => $terms
					)
				),
				'posts_per_page' => '6',
				'orderby' => 'rand'
			);

		} else {
			$args = array( 'post_type' => 'sell_media_item', 'field' => 'slug', 'orderby' => 'rand', 'posts_per_page' => '6' );
		} ?>

		<div class="sell-media-similar-widget sell-media">

			<?php
			// Get available image sizes
			$image_sizes = get_intermediate_image_sizes(); ?>

			<?php
			$type_posts = new WP_Query( $args );
			?>
			<?php while ( $type_posts->have_posts() ) : $type_posts->the_post();

			global $post;
			?>

			<div class="sell-media-widget-item-warp sell-media-grid">
				<div class="sell-media-widget-thumb-wrap">
					<a href="<?php echo get_permalink(); ?>">
						<?php sell_media_item_icon( $post->ID, apply_filters( 'sell_media_thumbnail', 'thumbnail' ) ); ?>
					</a>
				</div>
			</div> <!--  .sell-media-widget-item-warp  -->

	<?php endwhile; wp_reset_postdata(); ?>

</div><!-- .sell-media-recent -->


<?php

	echo $after_widget;

}
	/* Saves the settings. */
	function update( $new_instance, $old_instance ) {
		$instance = $old_instance;
		$instance['title'] = stripslashes( $new_instance['title'] );

		return $instance;
	}

	/* Creates the form for the widget in the back-end. */
	function form( $instance ){
		// Defaults
		$instance = wp_parse_args( ( array ) $instance, array( 'title' => 'Similar Items' ) );
		$title = htmlspecialchars( $instance['title'] );

		// Title
		echo '<p><label for="' . $this->get_field_id( 'title' ) . '">' . 'Title:' . '</label><input class="widefat" id="' . $this->get_field_id( 'title' ) . '" name="' . $this->get_field_name( 'title' ) . '" type="text" value="' . $title . '" /></p>';
	}

}

function Sell_Media_Similar_WidgetInit() {
	register_widget( 'Sell_Media_Similar_Widget' );
}

add_action( 'widgets_init', 'Sell_Media_Similar_WidgetInit' );
?>