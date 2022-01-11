<?php class Sell_Media_Similar_Widget extends WP_Widget
{
	function __construct(){
		$plugin_name = apply_filters( 'sell_media_plugin_name', __( 'Sell Media', 'sell_media' ), 10 );
		$widget_ops = array( 'description' => 'Displays similar products.' );
		$control_ops = array( 'width' => 200, 'height' => 200 );
		parent::__construct( false, $plugin_name . ': Similar Products', $widget_ops, $control_ops );
	}

	/* Displays the Widget in the front-end */
	function widget( $args, $instance ){
		extract( $args );
		$title = apply_filters( 'widget_title', empty( $instance['title'] ) ? '' : $instance['title'] );
		extract( $args );
		echo $before_widget;

		if ( $title ) {
			echo $before_title . $title . $after_title;
		}

		global $post;
		//Returns Array of Term Names for "collection"
		$terms = wp_get_post_terms( $post->ID, 'collection', array( 'fields' => 'slugs' ) );

		if ( ! empty( $terms ) ) {

			$args = array(
				'tax_query' => array(
					array(
						'taxonomy' => 'collection',
						'field' => 'slug',
						'terms' => $terms,
					),
				),
				'posts_per_page' => '6',
				'has_password' => false,
				'orderby' => 'rand',
			);

		} else {
			$taxonomy = 'collection';
			$term_ids = array();
			foreach( get_terms( $taxonomy ) as $term_obj ){
				$password = get_term_meta( $term_obj->term_id, 'collection_password', true );
				if ( $password ) $term_ids[] = $term_obj->term_id;
			}

			$args = array(
				'post_type' => 'sell_media_item',
				'field' => 'slug',
				'orderby' => 'rand',
				'posts_per_page' => '6',
				'has_password' => false,
				'tax_query' => array(
					array(
						'taxonomy' => 'collection',
						'field' => 'id',
						'terms' => $term_ids,
						'operator' => 'NOT IN'
					)
				)
			);
		}

		$args['post__not_in'] = array( $post->ID );
		?>

		<div class="sell-media-similar-widget sell-media-widget">

			<?php
			// Get available image sizes
			$image_sizes = get_intermediate_image_sizes(); ?>

			<?php
			$type_posts = new WP_Query( $args );
			$i = 0;
			?>
			<?php while ( $type_posts->have_posts() ) : $type_posts->the_post();

			global $post;
			$i++;
			$loop_args['context'] = "widget";
			?>

			<?php echo apply_filters( 'sell_media_content_loop', get_the_ID(), $i, $loop_args ); ?>

	<?php endwhile; wp_reset_postdata(); $i = 0; ?>

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
		$instance = wp_parse_args( ( array ) $instance, array( 'title'=>'Similar Items' ) );
		$title = htmlspecialchars($instance['title']);

		// Title
		echo '<p><label for="' . $this->get_field_id('title') . '">' . 'Title:' . '</label><input class="widefat" id="' . $this->get_field_id('title') . '" name="' . $this->get_field_name('title') . '" type="text" value="' . $title . '" /></p>';
	}

}

function Sell_Media_Similar_WidgetInit() {
	register_widget( 'Sell_Media_Similar_Widget' );
}

add_action( 'widgets_init', 'Sell_Media_Similar_WidgetInit' );
?>
