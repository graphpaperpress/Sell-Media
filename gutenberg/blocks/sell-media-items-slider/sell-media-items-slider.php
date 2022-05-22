<?php

if ( ( class_exists( 'SellMedia_Gutenberg_Block' ) ) && ( ! class_exists( 'Sell_Media_Gutenberg_Block_Item_Slider_List' ) ) ) {
    /**
     * Class for handling Sell Media Recent Item List Block
     */
    class Sell_Media_Gutenberg_Block_Item_Slider_List extends SellMedia_Gutenberg_Block{

        /**
         * Object constructor
         *
         * @access public
         * @since 2.4.6
         */
        public function __construct() {          
            
            $this->shortcode_slug   = 'sell_media_items_slider_gutenberg';
            $this->block_slug       = 'sell-media-items-slider';
            $this->block_base       = 'sellmedia';
            $this->block_attributes = array(
                'item_title' => array(
                    'type' => 'string',
                    'default' => esc_attr__('Recent Products', 'sell_media')
                ),
                'total_items' => array(
                    'type' => 'string',
                    'default' => 10,
                ),
                'total_visible_items' => array(
                    'type' => 'string',
                    'default' => 3,
                ),                
                'show_title' => array(
                    'type' => 'boolean',
                    'default' => 1,
                ),
                'gutter' => array(
                    'type' => 'string',
                    'default' => 10,
                ),
                'slider_controls' => array(
                    'type' => 'boolean',
                    'default' => 1,
                ),
                'align' => array(
                    'type' => 'string',
                    'default' => 'full',
                ),
            );
            $this->self_closing     = true;

            add_shortcode( 'sell_media_items_slider_gutenberg', array( $this, 'sell_media_items_slider_gutenberg_shortcode' ) );

            $this->init();
        }

        /**
         * Render Block
         *
         * This function will output the block rendered content.
         * In the case of this function the rendered output will be for the [sell_media_recent_items_gutenberg] shortcode.
         *
         * @access public
         * @since 2.4.6
         *
         * @param array $attributes Shortcode attrbutes.
         * @return none The output is echoed.
         */
        public function render_block( $attributes = array() ) {

            /**
            * Filters WordPress block content shortcode attributes.
            * 
            * @param array  $attributes     An array of shortcode attributes.
            * @param string $shortcode_slug Slug of the shortcode.
            */
                
            $shortcode_params_str = $this->prepare_recent_items_list_atts_to_param( $attributes );
            $shortcode_params_str = '[' . $this->shortcode_slug . ' ' . ($shortcode_params_str) . ']';
                
            $shortcode_out        = do_shortcode( $shortcode_params_str );

            // This is mainly to protect against emty returns with the Gutenberg ServerSideRender function.
            return $this->render_block_wrap( $attributes, $shortcode_out, true );
        }

        /**
         * Prepare attributes
         *
         * @access public
         * @since 2.4.6
         *
         * @param array $attributes Shortcode attrbutes.
         * @return string
         */
        public function prepare_recent_items_list_atts_to_param( $attributes = array() ) {
            $shortcode_params_str = '';            
            foreach ( $attributes as $key => $val ) {                
                if( $key == 'slider_controls' &&  $val == '' ) {                    
                    $val = 0;
                }
                if ( ! empty( $shortcode_params_str ) ) {
                    $shortcode_params_str .= ' ';
                }
                $shortcode_params_str .= $key . '="' . esc_attr( $val ) . '"';                                
            }
            return $shortcode_params_str;
        }

        /**
         * Render block
         *
         * @access public
         * @since 2.4.6
         *         
         */
        public function render_block_wrap( $attributes, $content = '', $with_inner = true ) {

            $return_content  = '';
            $return_content .= '<!-- ' . $this->block_slug . ' sell media item block begin -->';

            if ( true === $with_inner ) {
                $return_content .= '<div className="sell-media-block-inner" class="sell-media-block-inner align'. $attributes["align"].'">';
            }

            $return_content .= $content;

            if ( true === $with_inner ) {
                $return_content .= '</div>';
            }

            $return_content .= '<!-- ' . $this->block_slug . ' sell media item block end -->';

            return $return_content;
        }

        /**
         * Registers a custom block category.
         *
         * Fires on `block_categories` hook.
         *
         * @since 2.4.6
         *
         * @param array  $block_categories Optional. An array of current block categories. Default empty array.         
         *
         * @return array An array of block categories.
         */
        public function register_block_categories( $block_categories = array(), $post = false ) {

             $block_categories[] = array(
                'slug'  => 'sellmedia-blocks',
                'title' => esc_attr__( 'Sell Media Blocks', 'sell_media' ),
                'icon'  => false,
            );

            // Always return $default_block_categories.
            return $block_categories;
        }       

        /**
         * Shortcode callback function
         * 
         * @access public    
         * @since 2.4.6         
         */
        public function sell_media_items_slider_gutenberg_shortcode( $atts ) {
        
            wp_enqueue_script('sell_media_recent_items_tiny_slider', SELL_MEDIA_PLUGIN_URL . 'gutenberg/js/tiny-slider.js', array('jquery') );
            wp_enqueue_style('sell_media_recent_items_style', SELL_MEDIA_PLUGIN_URL . 'gutenberg/css/tiny-slider.css', array() );
            $html   =   '';
            extract( shortcode_atts( array(		
                'total_items' => 10,
                'show_title' => "1",
                'item_title'=>esc_attr__('Recent Products', 'sell_media' ),
                'slider_controls'=>"1",
                ), $atts )
            );

            $args = array(
                'post_type' => 'sell_media_item',
                'posts_per_page' => $total_items,
                'orderby' => 'id',
                'order'	=> 'DESC'
            );
            
            $recent_items = new WP_Query( $args );
            if ( $recent_items->have_posts() ) :
                $html = '<div class="sell-media-recent-item-wrap">';
                $html .= !empty( $item_title ) ? '<h1 class="sell-media-recent-items-title">'. esc_attr($item_title) .'</h1>' : '';
                $html .= '<div class="sell-media-recent-items" id="sell-media-recent-items">';		
                while ( $recent_items->have_posts() ) : $recent_items->the_post();
                    global $mime_type;
                    $post_id = get_the_id();			
                    if ( post_password_required( $post_id ) ) {
                        return;
                    }
                    
                    $html  .= '<div id="sell-media-' . $post_id . '" class="sell-media-recent-item item">';       
                    
                    $html .= '<a href="' . esc_url( get_permalink( $post_id ) ) . '" ' . sell_media_link_attributes( $post_id ) . ' class="sell-media-item">';
                    $html .= sell_media_item_icon( $post_id, 'medium', false );
                    if ( !empty( $show_title ) ) { /* if show title */
                        $html .= '<h2 class="sell-media-recent-item-title">' . get_the_title( $post_id ) . '</h2>';
                    }
                    $html .= '</a>';
                    $html .= '</div>';        
                endwhile;
                wp_reset_query();
                $i = 0;		
                $html .= '</div>';
                if( !is_admin() ){
                    $html .= '<script>window.onload = function() {                     
                            var slider = tns({
                                container: "#sell-media-recent-items",
                                items: '.$atts['total_visible_items'].',
                                navPosition:"bottom",
                                controls:false,
                                mouseDrag:true,
                                gutter:'.$atts['gutter'].',
                                nav:'.$atts['slider_controls'].',
                                autoplayButtonOutput:false,
                                autoplay:true,
                            });
                            };</script>';
                }
            endif;
            wp_reset_postdata();
            $html .= '</div>';
            return $html;
        }
    }
}
new Sell_Media_Gutenberg_Block_Item_Slider_List();