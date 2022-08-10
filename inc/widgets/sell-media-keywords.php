<?php class Sell_Media_Keywords_Widget extends WP_Widget
{
	function __construct(){
		$plugin_name = apply_filters( 'sell_media_plugin_name', esc_attr__( 'Sell Media', 'sell_media' ), 10 );
		$widget_ops = array('description' => 'Displays keywords of the product');
		$control_ops = array('width' => 200, 'height' => 200);
		parent::__construct( false, $plugin_name . ': Keywords', $widget_ops, $control_ops );
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
		<div class="sell-media-keywords-widget sell-media-keywords-widget">

			<?php
			$keywords = get_terms('keywords');

			if ( !empty( $keywords ) ) {

				foreach ( $keywords as $keyword ) {
					?><a href="<?php echo esc_url(get_term_link( $keyword->slug, 'keywords' )); ?>"><?php echo esc_html( $keyword->name ); ?></a> <?php
				}
			}
			?>

		</div><!-- .sell-media-keywords-widget -->

<?php
		echo wp_kses_post( $after_widget );

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
		$instance = wp_parse_args( (array) $instance, array('title'=> esc_attr__( 'Keywords', 'sell_media') ) );
		$title = htmlspecialchars($instance['title']);

		# Title
		?><p><label for="<?php echo esc_attr( $this->get_field_id('title') ); ?>"><?php echo esc_html__( 'Title', 'sell_media');?>:</label>
        <input class="widefat" id="<?php echo esc_attr( $this->get_field_id('title') ); ?>"
               name="<?php echo esc_attr( $this->get_field_name('title') ); ?>"
               type="text" value="<?php echo esc_attr( $title ); ?>" /></p><?php

	}

}

function Sell_Media_Keywords_WidgetInit() {
	register_widget('Sell_Media_Keywords_Widget');
}

add_action('widgets_init', 'Sell_Media_Keywords_WidgetInit');
