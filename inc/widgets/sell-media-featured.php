<?php class Sell_Media_Featured_Widget extends WP_Widget
{
	function __construct(){
		$plugin_name = apply_filters( 'sell_media_plugin_name', __( 'Sell Media', 'sell_media' ), 10 );
		$widget_ops = array('description' => 'Displays featured products.');
		$control_ops = array('width' => 200, 'height' => 200);
		parent::__construct( false, $plugin_name . ': Featured Products', $widget_ops, $control_ops );
	}

	/* Displays the Widget in the front-end */
	function widget($args, $instance){
		extract($args);
		$title = apply_filters('widget_title', empty($instance['title']) ? '' : $instance['title']);
		$categoryNumber = empty($instance['categoryNumber']) ? '' : $instance['categoryNumber'];
		extract($args);
		echo $before_widget;

		if ( $title )
			echo $before_title . $title . $after_title;

		global $post;

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
			'post__not_in' => array( $post->ID ),
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

				<?php echo apply_filters( 'sell_media_content_loop', get_the_ID(), $i, $loop_args ); ?>

				<?php endwhile; wp_reset_postdata(); $i = 0; ?>

			</div><!-- .sell-media-featured -->

<?php

	echo $after_widget;

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
		echo '<p><label for="' . $this->get_field_id('title') . '">' . 'Title:' . '</label><input class="widefat" id="' . $this->get_field_id('title') . '" name="' . $this->get_field_name('title') . '" type="text" value="' . $title . '" /></p>';


		# Collection
		$productTerms = get_terms( 'collection');

		$i = 0;
		foreach( $productTerms as $collection ){
			$password = get_term_meta( $collection->term_id, 'collection_password', true );
			if ( $password ) unset( $productTerms[ $i ] );
			$i++;
		} ?>
		<p><label for="<?php echo $this->get_field_id('categoryNumber') ?>"><?php _e( ' Select Collection', 'sell_media'); ?>: </label>
		<select id="<?php echo $this->get_field_id('categoryNumber'); ?>" name="<?php echo $this->get_field_name('categoryNumber'); ?>" value="<?php echo $categoryNumber; ?>">
			<option value="" <?php if($categoryNumber == '') echo 'selected="selected"'; ?>><?php _e( 'All Collections', 'sell_media'); ?></option>
				<?php foreach ($productTerms as $term) : ?>
					<option value="<?php echo $term->slug; ?>" <?php if($categoryNumber == $term->slug) echo 'selected="selected"'; ?>><?php echo $term->name; ?></option>
				<?php endforeach; ?>
		</select>
		</p>

		<?php
	}

}

function Sell_Media_Featured_WidgetInit() {
	register_widget('Sell_Media_Featured_Widget');
}

add_action('widgets_init', 'Sell_Media_Featured_WidgetInit');
?>
