<?php class Sell_Media_Recent_Widget extends WP_Widget
{
	function __construct(){
		$plugin_name = apply_filters( 'sell_media_plugin_name', esc_attr__( 'Sell Media', 'sell_media' ), 10 );
		$widget_ops = array('description' => 'Displays recently added products.');
		$control_ops = array('width' => 200, 'height' => 200);
		parent::__construct( false, $plugin_name . ': Recent Products', $widget_ops, $control_ops );
	}

	/* Displays the Widget in the front-end */
	function widget($args, $instance){
		$before_widget = isset( $args['before_widget'] ) ? $args['before_widget'] : null;
		$after_widget  = isset( $args['after_widget'] ) ? $args['after_widget'] : null;
		$before_title  = isset( $args['before_title'] ) ? $args['before_title'] : null;
		$after_title   = isset( $args['after_title'] ) ? $args['after_title'] : null;

		$title = apply_filters('widget_title', empty($instance['title']) ? '' : $instance['title']);
		extract($args);
		echo wp_kses_post( $before_widget );

		if ( $title ) {
			echo wp_kses_post( $before_title ) . esc_attr( $title ) . wp_kses_post( $after_title );
		}
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
		
			$args['post__not_in'] = array( get_the_ID() );
			$type_posts = new WP_Query ( $args );
			$i = 0;
			?>

			<?php while ( $type_posts->have_posts() ) : $type_posts->the_post();
			global $post;
			$i++;
			$loop_args['context'] = "widget";
			?>
			<?php echo wp_kses( apply_filters( 'sell_media_content_loop', get_the_ID(), $i, $loop_args ), [
					'img' => [
						'src'      => true,
						'srcset'   => true,
						'sizes'    => true,
						'class'    => true,
						'id'       => true,
						'width'    => true,
						'height'   => true,
						'alt'      => true,
						'align'    => true,
						'data-*' => true,
					],
					'div' => [
						'id' => true,
						'class' => true,
						'data-*' => true,
					],
					'a' => [
						'href' => true,
						'id' => true,
						'target' => true,
						'class' => true,
						'data-*' => true,
					],
					'script' => [
						'type' => true
					]
				] ); ?>

			<?php
			endwhile;
			wp_reset_postdata();
			$i = 0;
			?>

		</div><!-- .sell-media-recent-widget -->

<?php
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
		$instance = wp_parse_args( (array) $instance, array('title'=>'Recent Items') );
		$title = htmlspecialchars($instance['title']);

		// Title
		?><p><label for="<?php echo esc_attr( $this->get_field_id('title') ); ?>"><?php echo esc_html__( 'Title', 'sell_media' ) ?>:</label><input class="widefat" id="<?php echo esc_attr( $this->get_field_id('title') ); ?>" name="<?php echo esc_attr( $this->get_field_name('title') ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>" /></p><?php
	}

}

function Sell_Media_Recent_WidgetInit() {
	register_widget( 'Sell_Media_Recent_Widget' );
}

add_action( 'widgets_init', 'Sell_Media_Recent_WidgetInit' );
