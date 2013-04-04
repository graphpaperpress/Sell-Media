<?php class Sell_Media_Featured_Widget extends WP_Widget
{
    function Sell_Media_Featured_Widget(){
		$widget_ops = array('description' => 'Displays featured items');
		$control_ops = array('width' => 200, 'height' => 200);
		parent::WP_Widget(false,$name='Sell Media Featured Items',$widget_ops,$control_ops);
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
	
		if ( ! empty( $categoryNumber ) ) {
		
			$args = array(
				'tax_query' => array(
					array(
						'taxonomy' => 'collection',
						'field' => 'slug',
						'terms' => $categoryNumber
					)
				),
				'posts_per_page' => 5,
				'orderby' => 'rand'
			);
		 
		} else {
			$args = array( 'post_type' => 'sell_media_item', 'field'=>'slug', 'orderby' => 'rand', 'posts_per_page' => '5' );
		} ?>
		
		<div class="sell-media-featured-widget">
		
			<?php
			$type_posts = new WP_Query ( $args );
			?>
			<?php 
			while ( $type_posts->have_posts() ) : $type_posts->the_post();

				global $post;
			?>

				<?php 	
				$sell_media_attachment_id = get_post_meta( $post->ID, '_sell_media_attachment_id', true );
				if ( $sell_media_attachment_id ){
					$attachment_id = $sell_media_attachment_id;
				} else {
					$attachment_id = get_post_thumbnail_id( $post->ID );
				} ?>
				<div class="sell-media-widget-item-warp">
					<div class="sell-media-widget-thumb-wrap">
						<a href="<?php echo get_permalink(); ?>">
							<?php sell_media_item_icon( $attachment_id, 'sell_media_item' ); ?>
						</a>
					</div>

					<a href="<?php echo get_permalink(); ?>" class="sell-media-widget-title"><?php echo the_title() ?></a>
				</div> <!--  .sell-media-widget-item-warp  -->
	
				<?php endwhile; wp_reset_postdata(); ?>

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
		$productTerms = get_terms( 'collection');   ?>
		<p><label for="<?php echo $this->get_field_id('categoryNumber') ?>"> Select Collection : </label>
		<select id="<?php echo $this->get_field_id('categoryNumber'); ?>" name="<?php echo $this->get_field_name('categoryNumber'); ?>" value="<?php echo $categoryNumber; ?>">
			<option value="" <?php if($categoryNumber == '') echo 'selected="selected"'; ?>>All Collections</option>
				<?php foreach ($productTerms as $term) {  ?>
					<option value="<?php echo $term->slug; ?>" <?php if($categoryNumber == $term->slug) echo 'selected="selected"'; ?>><?php echo $term->name; ?></option>
					<?php } ?>
		</select>
		</p>
<?php	}

}

function Sell_Media_Featured_WidgetInit() {
	register_widget('Sell_Media_Featured_Widget');
}

add_action('widgets_init', 'Sell_Media_Featured_WidgetInit');
?>