<?php class Sell_Media_Keywords_Widget extends WP_Widget
{
	function __construct(){
		$plugin_name = apply_filters( 'sell_media_plugin_name', __( 'Sell Media', 'sell_media' ), 10 );
		$widget_ops = array('description' => 'Displays keywords of the product');
		$control_ops = array('width' => 200, 'height' => 200);
		parent::__construct( false, $plugin_name . ': Keywords', $widget_ops, $control_ops );
	}

	/* Displays the Widget in the front-end */
	function widget($args, $instance){
		extract($args);
		$title = apply_filters('widget_title', empty($instance['title']) ? '' : $instance['title']);
		extract($args);
		_e($before_widget,'sell_media');

		if ( $title )
			_e($before_title . $title . $after_title,'sell_media');
?>
		<div class="sell-media-keywords-widget sell-media-keywords-widget">

			<?php
			$keywords = get_terms('keywords');

			if ( !empty( $keywords ) ) {

				foreach ( $keywords as $keyword ) {
					_e('<a href="' . get_term_link( $keyword->slug, 'keywords' ) . '">' . $keyword->name . '</a> ','sell_media');
				}
			}
			?>

		</div><!-- .sell-media-keywords-widget -->

<?php
		_e($after_widget,'sell_media');

}
	/*Saves the settings. */
	function update($new_instance, $old_instance){
		$instance = $old_instance;
		$instance['title'] = stripslashes($new_instance['title']);

		return $instance;
	}

	/*Creates the form for the widget in the back-end. */
	function form($instance){
		//Defaults
		$instance = wp_parse_args( (array) $instance, array('title'=> __( 'Keywords', 'sell_media') ) );
		$title = htmlspecialchars($instance['title']);

		# Title
		_e('<p><label for="' . $this->get_field_id('title') . '">' . 'Title:' . '</label><input class="widefat" id="' . $this->get_field_id('title') . '" name="' . $this->get_field_name('title') . '" type="text" value="' . $title . '" /></p>','sell_media');

	}

}

function Sell_Media_Keywords_WidgetInit() {
	register_widget('Sell_Media_Keywords_Widget');
}

add_action('widgets_init', 'Sell_Media_Keywords_WidgetInit');
?>