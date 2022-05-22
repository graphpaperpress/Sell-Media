<?php

if ( ( class_exists( 'SellMedia_Gutenberg_Block' ) ) && ( ! class_exists( 'Sell_Media_Gutenberg_Block_Item_List' ) ) ) {
    /**
     * Class for handling Sell Media Item List Block
     */
    class Sell_Media_Gutenberg_Block_Item_List extends SellMedia_Gutenberg_Block{

        /**
         * Object constructor
         *
         * @access public
         * @since 2.4.6
         */
        public function __construct() {
            
            $this->shortcode_slug   = 'sell_media_items_gutenberg';
            $this->block_slug       = 'sell-media-all-items';
            $this->block_base       = 'sellmedia';
            $this->block_attributes = array(
                'per_page' => array(
                    'type' => 'string',
                ),
                'show_title' => array(
                    'type' => 'boolean',
                ),
                'quick_view' => array(
                    'type' => 'boolean',
                ),
                'thumbnail_crop' => array(
                    'type' => 'string',
                ),
                'thumbnail_layout' => array(
                    'type' => 'string',
                ),
                'align' => array(
                    'type' => 'string',
                    'default' => 'full'
                ),
            );
            $this->self_closing     = true;

            add_action( 'wp_enqueue_scripts', array($this,'sell_media_items_gutenberg_wp_enqueue_scripts') );

            add_shortcode( 'sell_media_items_gutenberg', array( $this, 'sell_media_items_gutenberg_shortcode' ) );

            // Register new category for Sell Media Items
            add_filter( 'block_categories_all', array( $this, 'register_block_categories' ), 30, 2 ); 

            $this->init();

        }

        /**
         * Render Block
         *
         * This function is called per the register_block_type() function above. This function will output
         * the block rendered content. In the case of this function the rendered output will be for the
         * [sell_media_items_gutenberg] shortcode.
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
                $shortcode_params_str = '[' . $this->shortcode_slug . ' ' . $shortcode_params_str . ']';
                
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

                if ( 'preview_show' === $key ) {
                    continue;
                } elseif ( 'per_page' === $key ) {
                    if ( '' === $val ) {
                        continue;
                    }
                    $val = (int) $val;

                } elseif ( 'columns' === $key ) {

                    $val = absint( $val );
                    if ( $val < 1 ) {
                        $val = 24; // Default quantity
                    }                    
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
                $return_content .= '<div className="sell-media-block-inner" class="sell-media-block-inner align'. $attributes["align"] .'">';
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
         * @param WP_Post|false $post             Optional. The `WP_Post` instance of post being edited. Default false.
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
        public function sell_media_items_gutenberg_shortcode( $atts ) {

            
            $html = '';
            $settings = sell_media_get_plugin_options();

            global $paged;
            if ( get_query_var( 'paged' ) ) {
                $paged = get_query_var( 'paged' );
            } elseif ( get_query_var( 'page' ) ) {
                $paged = get_query_var( 'page' );
            } else {
                $paged = 1;
            }

            extract( shortcode_atts( array(
                'columns' => '3',
                'per_page' => 24,
                'thumbnail_crop' => 'medium',
                'thumbnail_layout' => 'sell-media-three-col',
                'show_title' => true,
                'quick_view' => true,
                'align' => 'full',
                ), $atts )
            );

            $args = array(
                'posts_per_page' => $per_page,
                'post_type' => 'sell_media_item',
                'paged' => $paged,
                'orderby' => 'publish_date',
                'order' => 'DESC',
            );

            $wp_query_items = null;
            $wp_query_items = new WP_Query();
            $wp_query_items->query( $args );

            $i = 0;

            $class = 'sell-media-grid-item-container';
            if ( isset( $thumbnail_layout ) ) {
                if ( 'sell-media-masonry' === $thumbnail_layout ) {
                    $class = 'sell-media-grid-item-masonry-container';
                }

                if ( 'sell-media-horizontal-masonry' === $thumbnail_layout ) {
                    $class = 'horizontal-masonry-columns';
                }
            }

            $class = apply_filters( 'sell_media_grid_class', $class );

            if ( $wp_query_items->have_posts() ) :

                $html = '<div class="sell-media">';
                $html .= '<ul class="' . $class . '">';
                
                while ( $wp_query_items->have_posts() ) : $wp_query_items->the_post(); $i++;
                    $post_id = get_the_id();
                    global $mime_type;
                    $original_id =intval($post_id);
                    if ( post_password_required( $original_id ) && sell_media_is_search() ) {
                        return;
                    }
                    if ( 'attachment' === get_post_type( $post_id ) ) {
                        $attachment_id = intval( $post_id); // always and attachment
                        $post_id = get_post_meta( $attachment_id, $key = '_sell_media_for_sale_product_id', false ); // always a sell_media_item
                    } else {
                        $attachment_id = sell_media_get_attachment_id( $post_id ); // always an attachment
                    }

                    $class = 'sell-media-grid-item';
                    if ( isset( $thumbnail_layout ) && ! empty( $thumbnail_layout ) ) {
                        $class = $class . ' ' . $thumbnail_layout;
                    }
                    
                    if ( ! sell_media_has_multiple_attachments( $post_id ) ) {
                        $class .= ' sell-media-grid-single-item';
                    }

                    $custom_style = '';
                    if ( isset( $thumbnail_layout ) && 'sell-media-horizontal-masonry' === $thumbnail_layout ) {
                        $class = 'horizontal-masonry-column overlay-container ';
                        // grab the thumbnail if its not photo
                        if ( SellMediaAudioVideo::is_video_item( $post_id ) || SellMediaAudioVideo::is_audio_item( $post_id ) || 'application/pdf' === $mime_type || 'application/zip' === $mime_type ) {
                            $image_data     = esc_url(get_the_post_thumbnail_url( $post_id, 'thumbnail' ));
                            $image_size    = getimagesize($image_data);
                            $image_width   = (isset($image_size[0])) ? esc_attr($image_size[0]) : 100;
                            $image_height   = (isset($image_size[1])) ? esc_attr($image_size[1]) : 100;
                            $width          = $image_width * 250 / max($image_height, 1);
                            $padding_bottom = $image_height / max($image_width, 1) * 100;
                        } else {                        
                            $image_data     = wp_get_attachment_image_src( $attachment_id, 'thumbnail' );
                            $image_width    = (isset($image_data[1])) ? $image_data[1] : 100;
                            $image_height   = (isset($image_data[2])) ? $image_data[2] : 100;
                            $width          = $image_width * 250 / max($image_height, 1);
                            $padding_bottom = $image_height / max($image_width, 1) * 100;
                        }
                                    
                    }
                    
                    $html  .= '<li id="sell-media-' . $original_id . '" class="' . $class . '">';       
                    
                    $html .= '<a href="' . esc_url( get_permalink( $post_id ) ) . '" ' . sell_media_link_attributes( $post_id ) . ' class="sell-media-item">';

                    // Show titles?
                    if ( isset( $show_title ) && 0 != $show_title && is_main_query() ) {
                        $html .= '<h2 class="sell-media-entry-title">' . get_the_title( $original_id ) . '</h2>';
                    }

                    $html .= sell_media_item_icon( $original_id, $thumbnail_crop, false );

                    $enable_ecommerce = apply_filters( 'sell_media_enable_ecommerce', true, $post_id, $attachment_id );

                    // Show quick view?
                    if ( isset( $quick_view ) && 0 != $quick_view && is_main_query() ) {                      
                        if ( sell_media_has_multiple_attachments( $post_id ) ) {
                            $html .= '<div class="sell-media-view-gallery">' . esc_attr__( 'View Gallery', 'sell_media' ) . '</div>';
                        } elseif ( $enable_ecommerce ) {
                            $html .= '<div class="sell-media-quick-view" data-product-id="' . esc_attr( $post_id ) . '" data-attachment-id="' . esc_attr( $attachment_id ) . '">' . esc_attr__( 'Quick View', 'sell_media' ) . '</div>';
                        }
                    }
                    $html .= '</a>';
                    $html .= '</li>';

                endwhile;
                wp_reset_query();
                $i = 0;

                $html .= '</ul><!-- .sell-media-grid-item-container -->';
                if ( ! is_front_page() && is_main_query() )
                    $html .= sell_media_pagination_filter( $wp_query_items->max_num_pages );
                $html .= '</div><!-- #sell-media-shortcode-all .sell_media -->';

            endif;
            wp_reset_postdata();
            if ( isset( $thumbnail_layout ) && 'sell-media-masonry' === $thumbnail_layout ) {
                $html .= '<script>window.onload = function() { macy_init("sell-media-masonry"); };</script>';
            }
            wp_enqueue_script( 'sell_media_all_items_macy_frontend' );
            return $html;
        }

        public function sell_media_items_gutenberg_wp_enqueue_scripts() {
            // Scripts for masonary layout
            wp_register_script(
                'sell_media_all_items_macy_frontend',
                 SELL_MEDIA_PLUGIN_URL . 'js/macy.min.js',
                array('jquery')
            ); 

        }
    }   
}
new Sell_Media_Gutenberg_Block_Item_List();