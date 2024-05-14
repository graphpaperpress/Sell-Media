<?php

if ( ( class_exists( 'SellMedia_Gutenberg_Block' ) ) && ( ! class_exists( 'Sell_Media_Gutenberg_Block_Search_Form' ) ) ) {
    /**
     * Class for handling Sell Media Search Form Block
     */
    class Sell_Media_Gutenberg_Block_Search_Form extends SellMedia_Gutenberg_Block{

        /**
         * Object constructor
         *
         * @access public
         * @since 2.4.6
         */
        public function __construct() {
            
            $this->shortcode_slug   = 'sell_media_search_form_gutenberg';
            $this->block_slug       = 'sell-media-search-form';
            $this->block_base       = 'sellmedia';
            $this->block_attributes = array(
                'custom_label' => array(
                    'type' => 'string',
                    'default' => esc_attr__( 'Search Form' , 'sell_media' ),
                ),
                'custom_description' => array(
                    'type' => 'string',
                    'default' => esc_attr__( 'You can search for the items based on keywords, different media files i.e images, videos, audios' , 'sell_media' ),
                ),
                'custom_color' => array(
                    'type' => 'string',
                    'default' => '#ccc'
                ),
                'bgImage' => array(
                    'type' => 'object',
                ),
                'align' => array(
                    'type' => 'string',
                    'default' => 'full'
                ),
                'bgImageId' => array(
                    'type' => 'integer',
                ),
                'position_image' => array(
                    'type' => 'string',
                    'default' => 'wide'
                ),
                
            );
            $this->self_closing     = true;

            /* Added shortcode for search form with different design options */
            add_shortcode( 'sell_media_search_form_gutenberg', array( $this, 'sell_media_search_form_gutenberg_shortcode' ) ); 

            $this->init();

        }

        /**
         * Render Block
         *
         * This function is called per the register_block_type() function above. This function will output
         * the block rendered content. In the case of this function the rendered output will be for the
         * [sell_media_search_form_gutenberg] shortcode.
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
                $shortcode_params_str = '[' . $this->shortcode_slug . ' ' .  ($shortcode_params_str) . ']';
                
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

                if ( 'bgImage' === $key ) {

                    if(isset($val['url']) && $val['url']){
                        $val = esc_url($val['url']);
                    }else{
                        $val = '';
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
         * Shortcode callback function
         * 
         * @access public    
         * @since 2.4.6         
         */
        public function sell_media_search_form_gutenberg_shortcode( $atts, $url = null, $used = null ) {

            $settings = sell_media_get_plugin_options();
            $html = '';

            extract( shortcode_atts( array(
                'custom_label' => '',          
                'custom_description' => '',
                'bgimage' => '',
                'position_image' => 1,
                'custom_color' => '#ccc'
                ), $atts )
            );

            // Show a message to admins if they don't have search page set in settings.
            if ( current_user_can( 'administrator' ) && empty( $settings->search_page ) ) {
                $html .= __( 'For search to work, you must assign your Search Page in Sell Media -> Settings.', 'sell_media' );
                return $html;
            }

            // Get the search term(s)
            $search_term = htmlspecialchars(trim(stripslashes(strip_tags(get_query_var( 'search_query' )))));

            // Get the file type
            $search_file_type = get_query_var( 'search_file_type' );

            $custom_style = '';
            $custom_class = ' background_color_cls';

            $custom_color_style = "";
            $image_div_add = 0;
            if(trim($bgimage)){
                if (filter_var($bgimage, FILTER_VALIDATE_URL) && $position_image == 'wide') {
                    $custom_style = "background-image: url(".$bgimage.");background-size: 100% 100%;background-repeat: no-repeat;";
                    $custom_class = ' background_image_cls';
                }else{
                    $custom_color_style = 'background-color:'.$custom_color;
                }
                $image_div_add = 1;
            }

            $custom_class_image_div = '';
            $custom_class_form_div = '';

            if( $position_image == 'right' && $image_div_add){ 
                $custom_class_image_div = ' sell-media-image-align-left'; 
                $custom_class_form_div = ' sell-media-search-form-align-right'; 
            }
            if( $position_image == 'full' && $image_div_add){ 
                $custom_class_image_div = ' sell-media-image-align-top'; 
                $custom_class_form_div = ' sell-media-search-form-align-bottom'; 
            }

            // only use this method if it hasn't already been used on the page
            static $used;
            if ( ! isset( $used ) ) {
                $used = true;

                $html .= '<div class="sell-media-search'. $custom_class.$custom_class_image_div .'" style="'. $custom_style .'">';

                /* Image section */
                if($image_div_add && $position_image != 'wide'){
                    $html .= '<div class="sell-media-search-inner-image-only cf">';
                        $html .= '<img class="sell-media-back-image" src="'.$bgimage.'">';
                    $html .= '</div>';
                }

                /* Form section */
                $html .= '<div class="sell-media-search-inner-form-only'.$custom_class_form_div.' cf" style="'.$custom_color_style.'">';

                $html .= '<form role="search" method="get" id="sell-media-search-form" class="sell-media-search-form" action="' . esc_url( get_permalink( $settings->search_page ) ) . '" style="'.$custom_color_style.'">';

                $html .= '<div class="sell-media-search-inner cf">';

                    $html .= '<div class="sell-media-search-form-custom-div">';
                        $html .= '<label class="sell-media-search-form-label cf">'. $custom_label .'</label>';

                        $html .= '<p class="sell-media-search-form-description cf">'. $custom_description .'</p>';
                    $html .= '</div>';

                    // Visible search options wrapper
                    $html .= '<div id="sell-media-search-visible" class="sell-media-search-visible cf">';

                        // Input field
                        $html .= '<div id="sell-media-search-query" class="sell-media-search-field sell-media-search-query">';
                            $html .= '<input type="text" value="' . $search_term . '" name="search_query" id="sell-media-search-text" class="sell-media-search-text" placeholder="' . apply_filters( 'sell_media_search_placeholder', sprintf( __( 'Search for %1$s (comma separated)', 'sell_media' ), empty( $settings->post_type_slug ) ? 'keywords' : $settings->post_type_slug ) ) . '"/>';
                        $html .= '</div>';

                        // Submit button
                        $html .= '<div id="sell-media-search-submit" class="sell-media-search-field sell-media-search-submit">';
                            $html .= '<input type="submit" id="sell-media-search-submit-button" class="sell-media-search-submit-button" value="' . apply_filters( 'sell_media_search_button', __( 'Search', 'sell_media' ) ) . '" />';
                        $html .= '</div>';

                    $html .= '</div>';
                
                // Hidden search options wrapper
                $html .= '<div id="sell-media-search-hidden" class="sell-media-search-hidden cf">';

                    // File type field
                    $html .= '<div id="sell-media-search-file-type" class="sell-media-search-field sell-media-search-file-type">';
                    $html .= '<label for="search_file_type">' . __( 'File Type', 'sell_media' ) . '</label>';
                    $html .= '<select name="search_file_type">';
                    $html .= '<option value="">' . __( 'All', 'sell_media' ) . '</option>';
                    $mimes = array( 'image', 'video', 'audio' );
                    foreach ( $mimes as $mime ) {
                        $selected = ( $search_file_type === $mime ) ? 'selected' : '';
                        $html .= '<option value="' . $mime . '" ' . $selected . '>';
                        $html .= ucfirst( $mime );
                        $html .= '</option>';
                    }

                    $html .= '</select>';
                    $html .= '</div>';

                // Hidden search options wrapper
                $html .= '</div>';

                $html .= '</div>';
                $html .= '</div>';
                $html .= '</form>';
                $html .= '</div>';

            }

            // only run the query on the actual search results page.
            if ( is_page( $settings->search_page ) && in_the_loop() ) {

                // Find comma-separated search terms and format into an array
                $search_term_cleaned = preg_replace( '/\s*,\s*/', ',', $search_term );
                $search_terms = str_getcsv( $search_term_cleaned, ',' );

                // Exclude negative keywords in search query like "-cow"
                $negative_search_terms = '';
                $negative_search_terms = preg_grep( '/(?:^|[^\-\d])(\d+)/', $search_terms );
                $negative_search_terms = preg_replace( '/[-]/', '', $negative_search_terms );

                // now remove negative search terms from search terms
                $search_terms = array_diff( $search_terms, $negative_search_terms );
                $search_terms = array_filter( $search_terms );

                // Get the file/mimetype
                $mime_type = $this->get_mimetype( $search_file_type );

                // Current pagination
                $paged = ( get_query_var( 'paged' ) ) ? get_query_var( 'paged' ) : 1;

                if ( ! empty( $settings->search_relation ) && 'and' === $settings->search_relation ) {
                    $tax_array = array();
                    foreach ( $search_terms as $s ) {
                        $array = array(
                            'taxonomy' => 'keywords',
                            'field'    => 'name',
                            'terms'    => $s,
                        );
                        $tax_array[] = $array;
                    }
                    foreach ( $negative_search_terms as $n ) {
                        $array = array(
                            'taxonomy' => 'keywords',
                            'field'    => 'name',
                            'terms'    => array( $n ),
                            'operator' => 'NOT IN'
                        );
                        $tax_array[] = $array;
                    }

                    $tax_query = array(
                        'relation' => 'AND',
                        $tax_array
                    );
                } else {
                    // Add original full keyword to the search terms array
                    // This ensures that multiple word keyword search works
                    $one_big_keyword = str_replace( ',', ' ', $search_term );
                    $search_terms[] .= $one_big_keyword;
                    $tax_query = array(
                        array(
                            'taxonomy' => 'keywords',
                            'field'    => 'name',
                            'terms'    => $search_terms,
                        )
                    );
                }

                // The Query
                $args = array(
                    'post_type' => 'attachment',
                    'paged'     => $paged,
                    'post_status' => array( 'publish', 'inherit' ),
                    'post_mime_type' => $mime_type,
                    'post_parent__in' => sell_media_ids(),
                    'tax_query' => $tax_query
                );
                $args = apply_filters( 'sell_media_search_args', $args );
                $search_query = new WP_Query( $args );
                $i = 0;

                // The Loop
                if ( $search_query->have_posts() ) {

                    $html .= '<p class="sell-media-search-results-text">' . sprintf( __( 'We found %1$s results for "%2$s."', 'sell_media' ), $search_query->found_posts, $search_term ) . '</p>';

                    // hook for related keywords, etc.
                    $html .= sell_media_format_related_search_results( $search_terms );

                    //$html .= $this->search_help();

                    $html .= '<div id="sell-media-search-results" class="sell-media">';
                    $html .= '<div class="' . apply_filters( 'sell_media_grid_item_container_class', 'sell-media-grid-item-container' ) . '">';

                    while ( $search_query->have_posts() ) {
                        $search_query->the_post();

                        $post_id = intval(get_the_ID());
                        $parent_id = intval(get_post_meta( $post_id, $key = '_sell_media_for_sale_product_id', true ));

                        $html .= '<div id="sell-media-' . intval($post_id) . '" class="' . apply_filters( 'sell_media_grid_item_class', 'sell-media-grid-item', intval($parent_id) ) . ' sell-media-grid-single-item">';
                        $html .= '<a href="' . esc_url( get_permalink() ) . '" ' . sell_media_link_attributes( $post_id ) . ' class="sell-media-item">';

                        if ( ! empty( $settings->titles ) ) {
                            $html .= '<h2 class="entry-title">' . get_the_title() . '</h2>';
                        }

                        if ( wp_get_attachment_image( $post_id, apply_filters( 'sell_media_thumbnail', 'medium' ) ) ) {

                            $html .= wp_get_attachment_image( $post_id, apply_filters( 'sell_media_thumbnail', 'medium' ) );
                        } else {
                            $html .= sell_media_item_icon( $parent_id, apply_filters( 'sell_media_thumbnail', 'medium' ), false );
                        }
                        $html .= '<div class="sell-media-quick-view" data-product-id="' . esc_attr( $parent_id ) . '" data-attachment-id="' . esc_attr( $post_id ) . '">' . apply_filters( 'sell_media_quick_view_text', __( 'Quick View', 'sell_media' ), $parent_id, $post_id ) . '</div>';
                        $html .= '</a>';
                        $html .= '</div>';
                    }
                    $html .= '</div>';
                    $html .= '</div>';
                    $html .= sell_media_pagination_filter( $search_query->max_num_pages );
                    $text = __( 'Explore more from our store', 'sell_media' );
                    $html .= '<p class="sell-media-search-results-text">' . $text . '</p>';
                    $html .= do_shortcode( '[sell_media_filters]' );

                } else {

                    if ( $search_terms ) {
                        $text = sprintf( __( 'Sorry, no results for "%1$s."', 'sell_media' ), $search_term );
                        $html .= $this->search_help();
                    } else {
                        $html .= $this->search_help();
                    }
                    $html .= '<p class="sell-media-search-results-text">' . $text . '</p>';
                    $html .= do_shortcode( '[sell_media_filters]' );
                }

                /* Restore original Post Data */
                wp_reset_postdata();
                $i = 0;

            } // end search results page check

            return apply_filters( 'sell_media_search_results', $html );
        }

        public function search_help() {

            $html  = '<div class="sell-media-search-help">';
            $html .= '<h6>' . __( 'Search Tips', 'sell_media' ) . '</h6>';
            $html .= '<ul>';
            $html .= '<li>' . __( 'Separate keywords with a comma.', 'sell_media' ) . '</li>';
            $html .= '<li>' . __( 'Use fewer keywords to expand search results.', 'sell_media' ) . '</li>';
            $html .= '<li>' . __( 'Use negative keywords (like -dogs) to exclude dogs from search results.', 'sell_media' ) . '</li>';
            $html .= '</ul>';
            $html .= '</div>';

            return $html;
        }

        /**
         * Get the select value of the filetype field and conver it into a WP mimtype for WP_Query
         *
         * @param  string       The filetype (image, video, audio)
         * @return array        The WP mimetype format for each filetype
         */
        private function get_mimetype( $filetype ) {
            if ( 'image' === $filetype ) {
                $mime = array( 'image/jpeg', 'image/gif', 'image/png', 'image/bmp', 'image/tiff', 'image/x-icon' );
            } elseif ( 'video' === $filetype ) {
                $mime = array( 'video/x-ms-asf', 'video/x-ms-wmv', 'video/x-ms-wmx', 'video/x-ms-wm', 'video/avi', 'video/divx', 'video/x-flv', 'video/quicktime', 'video/mpeg', 'video/mp4', 'video/ogg', 'video/webm', 'video/x-matroska' );
            } elseif ( 'audio' === $filetype ) {
                $mime = array( 'audio/mpeg', 'audio/x-realaudio', 'audio/wav', 'audio/ogg', 'audio/midi', 'audio/x-ms-wma', 'audio/x-ms-wax', 'audio/x-matroska' );
            } else {
                $mime = '';
            }

            return $mime;
        }
  
    }   
}

new Sell_Media_Gutenberg_Block_Search_Form();