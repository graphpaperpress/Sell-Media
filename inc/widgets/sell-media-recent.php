<?php class Sell_Media_Recent_Widget extends WP_Widget
{
	function __construct(){
		$plugin_name = apply_filters( 'sell_media_plugin_name', __( 'Sell Media', 'sell_media' ), 10 );
		$widget_ops = array('description' => 'Displays recently added products.');
		$control_ops = array('width' => 200, 'height' => 200);
		parent::__construct( false, $plugin_name . ': Recent Products', $widget_ops, $control_ops );
	}

	/* Displays the Widget in the front-end */
	function widget($args, $instance){
		global $post;
		extract($args);
		$title = apply_filters('widget_title', empty($instance['title']) ? '' : $instance['title']);
		extract($args);
		echo $before_widget;

		if ( $title )
			echo $before_title . $title . $after_title;
?>
		<div class="sell-media-recent-widget sell-media-widget">
			<?php

			$taxonomy = 'collection';
			$term_ids = array();
			foreach( get_terms( $taxonomy ) as $term_obj ){
				$password = get_term_meta( $term_obj->term_id, 'collection_password', true );
				if ( $password ) $term_ids[] = $term_obj->term_id;
			}

			$args = array(
				'post_type' => 'sell_media_item',
				'field' => 'slug',
				'orderby' => 'ASC',
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
		
			$args['post__not_in'] = array( $post->ID );
			$type_posts = new WP_Query ( $args );
			$i = 0;
			?>

			<?php while ( $type_posts->have_posts() ) : $type_posts->the_post();
			global $post;
			$i++;
			$loop_args['context'] = "widget";
			?>
			<?php echo apply_filters( 'sell_media_content_loop', get_the_ID(), $i, $loop_args ); ?>

			<?php
			endwhile;
			wp_reset_postdata();
			$i = 0;
			?>

		</div><!-- .sell-media-recent-widget -->

<?php
		echo $after_widget;

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
		$instance = wp_parse_args( (array) $instance, array('title'=>'Recent Items') );
		$title = htmlspecialchars($instance['title']);

		// Title
		echo '<p><label for="' . $this->get_field_id('title') . '">' . 'Title:' . '</label><input class="widefat" id="' . $this->get_field_id('title') . '" name="' . $this->get_field_name('title') . '" type="text" value="' . $title . '" /></p>';

	}

}

function Sell_Media_Recent_WidgetInit() {
	register_widget( 'Sell_Media_Recent_Widget' );
}

add_action( 'widgets_init', 'Sell_Media_Recent_WidgetInit' );
?>
