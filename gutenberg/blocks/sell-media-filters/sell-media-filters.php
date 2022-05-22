<?php

if ( ( class_exists( 'SellMedia_Gutenberg_Block' ) ) && ( ! class_exists( 'Sell_Media_Gutenberg_Block_Filters' ) ) ) {

    /**
     * Class for handling Sell Media Item Filters Block
     */
    class Sell_Media_Gutenberg_Block_Filters extends SellMedia_Gutenberg_Block{

        /**
         * Object constructor
         *
         * @access public
         * @since 2.4.6
         */
        public function __construct() {
            
            $this->shortcode_slug   = 'sell_media_filters';
            $this->block_slug       = 'sell-media-filters';
            $this->block_base       = 'sellmedia';
            $this->block_attributes = array(
                'all' => array(
                    'type' => 'boolean',
                    'default' => 'all',
                ),
                'newest' => array(
                    'type' => 'boolean',
                    'default' => '0',
                ),
                'most_popular' => array(
                    'type' => 'boolean',
                    'default' => '0',
                ),
                'collections' => array(
                    'type' => 'boolean',
                    'default' => '0',
                ),
                'keywords' => array(
                    'type' => 'boolean',
                    'default' => '0',
                ),
                'align' => array(
                    'type' => 'string',
                    'default' => 'full'
                ),
            );
            $this->self_closing     = true;

            $this->init();
        }

        /**
         * Render Block
         *
         * This function is called per the register_block_type() function above. This function will output
         * the block rendered content. In the case of this function the rendered output will be for the
         * [sell_media_filters] shortcode.
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
                 * @param string $block_slug     Slug of the gutenberg block.
                 * @param string $content        Shortcode content.
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
            $static_key = "filters";
            $val_aaray = array();
            foreach ( $attributes as $key => $val ) {

                if ( 'preview_show' === $key ) {
                    continue;
                } elseif ( 'all' === $key && !empty($val) ) {
                    if ( '' === $val ) {
                        continue;
                    }
                    $val_aaray[] = 'all';
                    break;

                } elseif ( 'newest' === $key && !empty($val) ) {
                    $val_aaray[] = '1';
                } elseif ( 'most_popular' === $key && !empty($val) ) {
                    $val_aaray[] = '2';
                } elseif ( 'collections' === $key && !empty($val) ) {
                    $val_aaray[] = '3';
                } elseif ( 'keywords' === $key && !empty($val) ) {
                    $val_aaray[] = '4';
                }                           
            }
            if(!count($val_aaray))
                $val_aaray[] = 'all';
            $val = implode(',', $val_aaray);  
            $shortcode_params_str .= $static_key . '="' . esc_attr( $val ) . '"';        

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

    }   
}

new Sell_Media_Gutenberg_Block_Filters();