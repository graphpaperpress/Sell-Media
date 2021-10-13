<?php
/**
 * Base class for all SellMedia Gutenberg Blocks.
 * @access public
 * @since 2.4.6
 */

if ( ! class_exists( 'SellMedia_Gutenberg_Block' ) ) {
	/**
	 * Abstract Parent class to hold common functions used by specific LearnDash Blocks.
	 */
	class SellMedia_Gutenberg_Block {

		protected $block_base = 'sellmedia';
		protected $shortcode_slug;
		protected $block_slug;
		protected $block_attributes;
		protected $self_closing;

		/**
		 * Constructor.
		 */
		public function __construct() {

		}

		/**
		 * Initialize the hooks.
		 */
		public function init() {

			if ( function_exists( 'register_block_type' ) ) {
				add_action( 'init', array( $this, 'register_blocks' ) );
			}
		}

		/**
		 * Register Block for Gutenberg
		 */
		public function register_blocks() {
			register_block_type(
				$this->block_base . '/' . $this->block_slug,
				array(
					'render_callback' => array( $this, 'render_block' ),
					'attributes'      => $this->block_attributes,
				)
			);
		}

		/**
         * Render Block
         *
         * This function is called per the register_block_type() function above. This function will output
         * the block rendered content. In the case of this function the rendered output will be for the
         * [shortcode] shortcode.
         *
         * @access public
         * @since 2.4.6
         *
         * @param array $attributes Shortcode attrbutes.
         * @return none The output is echoed.
         */
		public function render_block( $attributes = array() ) {
			return;
		}

		// End of functions.
	}
}
