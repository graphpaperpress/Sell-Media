<?php class Sell_Media_Featured_Widget extends WP_Widget
{
	function __construct(){
		$plugin_name = apply_filters( 'sell_media_plugin_name', esc_attr__( 'Sell Media', 'sell_media' ), 10 );
		$widget_ops = array('description' => 'Displays featured products.');
		$control_ops = array('width' => 200, 'height' => 200);
		parent::__construct( false, $plugin_name . ': Featured Products', $widget_ops, $control_ops );
	}

	/* Displays the Widget in the front-end */
	function widget($args, $instance){
		$before_widget = isset( $args['before_widget'] ) ? $args['before_widget'] : null;
		$after_widget  = isset( $args['after_widget'] ) ? $args['after_widget'] : null;
		$before_title  = isset( $args['before_title'] ) ? $args['before_title'] : null;
		$after_title   = isset( $args['after_title'] ) ? $args['after_title'] : null;

		$title = apply_filters('widget_title', empty($instance['title']) ? '' : $instance['title']);
		$categoryNumber = empty($instance['categoryNumber']) ? '' : $instance['categoryNumber'];
		extract($args);
	
		echo wp_kses_post( $before_widget );
	
		if ( $title )
			echo wp_kses_post( $before_title ) . esc_attr( $title ) . wp_kses_post( $after_title );

		$taxonomy = 'collection';
		$term_ids = array();
		foreach( get_terms( $taxonomy ) as $term_obj ){
			$password = get_term_meta( $term_obj->term_id, 'collection_password', true );
			if ( $password ) $term_ids[] = $term_obj->term_id;
		}

		$args = array(
			'post_type' => 'sell_media_item',
			'posts_per_page' => 6,
			'field' => 'slug',
			'orderby' => 'rand',
			'has_password' => false,
			'post__not_in' => array( get_the_ID() ),
			'tax_query' => array(
				array(
					'taxonomy' => 'collection',
					'field' => 'id',
					'terms' => $term_ids,
					'operator' => 'NOT IN'
				)
			)
		);

		if ( ! empty( $categoryNumber ) ) {
			$args['taxonomy'] = 'collection';
			$args['term'] = $categoryNumber;
		}

		?>

		<div class="sell-media-featured-widget sell-media-widget">

			<?php
			$type_posts = new WP_Query( $args );
			$i = 0;
			?>
			<?php
			while ( $type_posts->have_posts() ) : $type_posts->the_post();

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

				<?php endwhile; wp_reset_postdata(); $i = 0; ?>

			</div><!-- .sell-media-featured -->

<?php

	echo wp_kses_post( $after_widget );

}
  /*Saves the settings. */
	function update($new_instance, $old_instance){
		$instance = $old_instance;
		$instance['title'] = stripslashes($new_instance['title']);
		$instance['categoryNumber'] = stripslashes($new_instance['categoryNumber']);

		return $instance;
	}

  /*Creates the form for the widget in the back-end. */
	function form($instance){
		//Defaults
		$instance = wp_parse_args( (array) $instance, array('title'=>'Featured Items', 'categoryNumber'=>'') );
		$title = htmlspecialchars($instance['title']);
		$categoryNumber = htmlspecialchars($instance['categoryNumber']);


		 # Title
		?><p><label for="<?php echo esc_attr( $this->get_field_id('title') ); ?>"><?php echo esc_html__('Title', 'sell_media'); ?>:</label>
        <input class="widefat" id="<?php echo esc_attr( $this->get_field_id('title') ); ?>"
               name="<?php echo esc_attr( $this->get_field_name('title') ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>" /></p><?php


		# Collection
		$productTerms = get_terms( 'collection');

		$i = 0;
		if ( ! is_wp_error( $productTerms ) && ! empty( $productTerms ) ) :
			foreach( $productTerms as $collection ){
				$password = get_term_meta( $collection->term_id, 'collection_password', true );
				if ( $password ) unset( $productTerms[ $i ] );
				$i++;
			}
		endif;
		?>
		<p><label for="<?php esc_attr_e($this->get_field_id('categoryNumber'),'sell_media') ?>"><?php esc_html_e( ' Select Collection', 'sell_media'); ?>: </label>
		<select id="<?php esc_attr_e($this->get_field_id('categoryNumber'),'sell_media'); ?>" name="<?php esc_attr_e($this->get_field_name('categoryNumber'),'sell_media'); ?>" value="<?php esc_attr_e($categoryNumber,'sell_media'); ?>">
			<option value="" <?php if($categoryNumber == '') esc_attr_e('selected="selected"','sell_media'); ?>><?php esc_html_e( 'All Collections', 'sell_media'); ?></option>
				<?php
				if ( ! is_wp_error( $productTerms ) && ! empty( $productTerms ) ) :
				 foreach ($productTerms as $term) : ?>
					<option value="<?php esc_attr_e($term->slug); ?>" <?php if($categoryNumber == $term->slug) { ?>selected="selected"<?php } ?>><?php echo esc_html($term->name); ?></option>
				<?php endforeach; ?>
				<?php endif; ?>
		</select>
		</p>

		<?php
	}

}

function Sell_Media_Featured_WidgetInit() {
	register_widget('Sell_Media_Featured_Widget');
}

add_action('widgets_init', 'Sell_Media_Featured_WidgetInit');
