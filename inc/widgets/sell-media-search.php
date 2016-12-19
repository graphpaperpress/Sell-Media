<?php
/**
 * Sell Media Search Widget
 *
 * Renders the sell_media_searchform as a new widget
 *
 * @since 2.0.5
 */

// Block direct requests
if ( ! defined( 'ABSPATH' ) )
	die( '-1' );


/**
 * Register the widget
 * @return [type] [description]
 */
function sell_media_search_widgets_init() {
	register_widget( 'Sell_Media_Search_Widget' );
}
add_action( 'widgets_init', 'sell_media_search_widgets_init' );

/**
 * Adds My_Widget widget.
 */
class Sell_Media_Search_Widget extends WP_Widget {
	/**
	 * Register widget with WordPress.
	 */
	function __construct() {
		$plugin_name = apply_filters( 'sell_media_plugin_name', __( 'Sell Media', 'sell_media' ), 10 );
		parent::__construct(
			'Sell_Media_Search_Widget', // Base ID
			$plugin_name . ': ' . __( 'Search', 'sell_media' ), // Name
			array( 'description' => __( 'Adds a search form for product search.', 'sell_media' ) ) // Args
		);
	}

	/**
	 * Front-end display of widget.
	 *
	 * @see WP_Widget::widget()
	 *
	 * @param array $args     Widget arguments.
	 * @param array $instance Saved values from database.
	 */
	public function widget( $args, $instance ) {

		echo $args['before_widget'];
		if ( ! empty( $instance['title'] ) ) {
			echo $args['before_title'] . apply_filters( 'widget_title', $instance['title'] ). $args['after_title'];
		}
		echo do_shortcode( '[sell_media_searchform]' );
		echo $args['after_widget'];
	}
	/**
	 * Back-end widget form.
	 *
	 * @see WP_Widget::form()
	 *
	 * @param array $instance Previously saved values from database.
	 */
	public function form( $instance ) {
		if ( isset( $instance[ 'title' ] ) ) {
			$title = $instance[ 'title' ];
		}
		else {
			$title = __( 'Search', 'sell_media' );
		}
		?>
		<p>
			<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title:', 'sell_media' ); ?></label>
			<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>">
		</p>
		<?php
	}
	/**
	 * Sanitize widget form values as they are saved.
	 *
	 * @see WP_Widget::update()
	 *
	 * @param array $new_instance Values just sent to be saved.
	 * @param array $old_instance Previously saved values from database.
	 *
	 * @return array Updated safe values to be saved.
	 */
	public function update( $new_instance, $old_instance ) {
		$instance = array();
		$instance['title'] = ( ! empty( $new_instance['title'] ) ) ? strip_tags( $new_instance['title'] ) : '';
		return $instance;
	}
} // class Sell_Media_Search_Widget