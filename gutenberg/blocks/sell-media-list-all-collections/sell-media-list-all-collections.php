<?php

if ( ( class_exists( 'SellMedia_Gutenberg_Block' ) ) && ( ! class_exists( 'Sell_Media_Gutenberg_Block_collection_Item_List' ) ) ) {
    /**
     * Class for handling Sell Media Collection List Block
     */
    class Sell_Media_Gutenberg_Block_Collection_Item_List extends SellMedia_Gutenberg_Block{

        /**
         * Object constructor
         *
         * @access public
         * @since 2.4.6
         */
        public function __construct() {          
            
            $this->shortcode_slug   = 'sell_media_list_all_collections';
            $this->block_slug       = 'sell-media-list-all-collections';
            $this->block_base       = 'sellmedia';
            $this->block_attributes = array(
                'align' => array(
                    'type' => 'string',
                    'default' => 'full'
                )
            );
            $this->self_closing     = true;
            $this->init();
        }

        /**
         * Render Block
         *
         * This function will output the block rendered content.
         * In the case of this function the rendered output will be for the [sell_media_list_all_collections] shortcode.
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

                if ( ! empty( $shortcode_params_str ) ) {
                    $shortcode_params_str .= ' ';
                }

                $shortcode_params_str .= $key . '="' . $val . '"';                                
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
                $return_content .= '<div className="sell-media-collection-inner" class="sell-media-block-inner align'. $attributes["align"].'">';
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
         * @param array         $block_categories Optional. An array of current block categories. Default empty array.         
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
    }
}
new Sell_Media_Gutenberg_Block_Collection_Item_List();


