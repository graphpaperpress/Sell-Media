<?php

Class SellMediaNavStyleUI {

    public $data = array();

    /**
     * Hooks used
     */
    public function __construct(){
        add_action( 'wp_ajax_update_term', array( &$this, 'update_term' ) );
        add_action( 'wp_ajax_save_term', array( &$this, 'save_term' ) );
        add_action( 'wp_ajax_delete_term', array( &$this, 'delete_term' ) );
        add_action( 'wp_ajax_add_term', array( &$this, 'add_term' ) );
        add_action( 'admin_enqueue_scripts', array( &$this, 'admin_scripts' ) );
    }


    public function __set($name, $value){
        return $this->data[ $name ] = $value;
    }


    public function __get($name){
        return array_key_exists( $name, $this->data ) ? $this->data[$name] : false;
    }


    /**
     * Enqueue the needed JS
     */
    public function admin_scripts(){
        global $pagenow;
        if ( ! empty( $pagenow ) && $pagenow == 'edit.php' && ! empty( $_GET['tab'] ) && $_GET['tab'] == 'sell_media_size_settings' ){
            wp_enqueue_script( 'sell_media-admin-price-groups', plugin_dir_url( dirname( __FILE__ ) ) . 'js/admin-price-groups.js', array( 'jquery' ) );
            wp_localize_script('sell_media-admin-price-groups', 'sell_media_price_groups',
                array(
                    'currency_symbol' => sell_media_get_currency_symbol()
                )
            );


        }
    }


    /**
     * Saves/Updates term children (price group children).
     *
     * @author Zane M. Kolnik
     * @since 1.5.1
     * @package AJAX
     */
    public function save_term(){
        parse_str( $_POST['form'], $form_data );

        wp_verify_nonce( $_POST['security'], $_POST['action'] );
        $taxonomy = $_POST['taxonomy'];

        // Update the price group name if it has changed
        if ( ! empty( $form_data['price_group']['term_id'] ) ){
            $parent_obj = get_term_by( 'id', $form_data['price_group']['term_id'], $taxonomy );
            if ( ! empty( $parent_obj ) && $parent_obj->name != $form_data['price_group']['term_name'] ){
                wp_update_term( $form_data['price_group']['term_id'], $taxonomy, array( 'name' => $form_data['price_group']['term_name'] ) );
            }
        }


        if ( ! empty( $form_data['terms_children'] ) ){
            foreach( $form_data['terms_children'] as $k => $v ){
                wp_update_term( $k, $taxonomy, array( 'name' => $v['name'] ) );
                sell_media_update_term_meta( $k, 'width', $v['width'] );
                sell_media_update_term_meta( $k, 'height', $v['height'] );
                sell_media_update_term_meta( $k, 'price', $v['price'] );
            }
        }

        // Dynamically add new prices for this price group
        if ( ! empty( $form_data['new_child'] ) ){
            foreach( $form_data['new_child'] as $child ){
                if ( ! empty( $child['name'] ) ){
                    $term = wp_insert_term( $child['name'], $taxonomy, array('parent'=> $child['parent'] ) );

                    // Dynamically save ALL fields that are NOT 'name' as
                    // term meta!
                    if ( ! is_wp_error( $term ) ){
                        foreach( $child as $k => $v ){
                            if ( $k != 'name' ){
                                sell_media_update_term_meta( $term['term_id'], $k, $v );
                            }
                        }
                    }
                }
            }
        }

        wp_send_json( array( 'sell_media' => true ) );
    }


    /**
     * Deletes a single term or a single term and ALL ITS CHILDREN!!
     *
     * @author Zane M. Kolnik
     * @since 1.5.1
     */
    public function delete_term(){
        wp_verify_nonce( $_POST['security'], $_POST['action'] );
        if ( ! empty( $_POST['term_id'] ) ) {
            $terms = get_term_children( $_POST['term_id'], $_POST['taxonomy'] );
            $terms[] = $_POST['term_id'];
            foreach( $terms as $term_id ){
                wp_delete_term( $term_id, $_POST['taxonomy'] );
            }
        }
        wp_send_json( array( 'sell_media' => true ) );
    }


    /**
     * Adds the term and prints the admin url with the needed $_GET params
     * to redirect to the open tab.
     *
     * @author Zane M. Kolnik
     * @since 1.5.1
     */
    public function add_term(){
        wp_verify_nonce( $_POST['security'], $_POST['action'] );

        $termarray = array();
        $terms = get_terms( $_POST['taxonomy'], array( 'hide_empty' => 0, 'parent' => 0 ) );
        foreach( $terms as $term ) {
            $termarray[] = $term->name;
        }

        if ( ! empty( $_POST['term_name'] )  && ! in_array( $_POST['term_name'], $termarray ) ) {
            $term = wp_insert_term( $_POST['term_name'], $_POST['taxonomy'] );
            $timestamp = time();
            $return_url = admin_url('edit.php?post_type=sell_media_item&page=sell_media_plugin_options&tab=sell_media_size_settings' . '&term_parent=' . $term['term_id'] .'&cache_buster='.$timestamp);
        } else {
            $return_url = null;
        }
        wp_send_json( array( 'sell_media' => true, 'return_url' => $return_url ) );
    }


    public function setting_ui(){

        $parent_terms = get_terms( $this->taxonomy, array( 'hide_empty' => false, 'parent' => 0 ) );
        $current_parent = $_GET['term_parent'];

        $tmp = array();
        $final = array();
        $final['terms'] = null;
        $count = count( $parent_terms );
        $current_term_id = null;
        $current_term = null;

        // Build our menu array
        foreach( $parent_terms as $term ) {
            if ( $current_parent == $term->term_id ) {
                $current_term = $term->name;
                $current_term_id = $term->term_id;
                $link = '<span class="nav-tab nav-tab-active">' . $term->name . '</span>';
            } else {
                $link = '<a href="' . admin_url('edit.php?post_type=sell_media_item&page=sell_media_plugin_options&tab=sell_media_size_settings&term_parent=' . $term->term_id ) . '" class="nav-tab" data-term_id="' . $term->term_id . '">' . $term->name . '</a>';
            }

            $tmp[] = array(
                'term_id' => $term->term_id,
                'name' => $term->name,
                'current_id' => $current_term_id,
                'current_term' => $current_term,
                'html' => $link
                );

            $final['menu'] = $tmp;
        }

        if ( ! empty( $current_parent ) && $current_parent == 'new_term' ) {
            $final['menu'][] = array(
                'html' => '<span class="nav-tab-active nav-tab menu-add-new"><abbr title="Add menu">+</abbr></span>'
                );
        } else {
            $final['menu'][] = array(
                'html' => '<a href="' . admin_url('edit.php?post_type=sell_media_item&page=sell_media_plugin_options&tab=sell_media_size_settings&term_parent=new_term') .'" class="nav-tab menu-add-new"><abbr title="Add menu">+</abbr></a>'
                );
        }

        // header
        if ( empty( $current_term_id ) ) {
            $value = null;
            $data_term_id = null;
            $class = "button-primary sell-media-add-term";
            $link_text = __('Create Price Group','sell_media');
            $message = null;
        } else {
            $data_term_id = $current_term_id;
            $class = "submitdelete sell-media-delete-term-group";
            $message = sprintf( "%s %s?\n\n%s", __("Are you sure you want to delete the price group:", "sell_media" ), $current_term, __("This will delete the price group and ALL its prices associated with it.","sell_media") );
            $link_text = __('Delete Group','sell_menu');
        }

        $final['header'] = array(
            'html' => '<input name="price_group[term_name]" type="text" class="regular-text" placeholder="'. __('Enter price group name here', 'sell_media') . '" value="' . $current_term . '" data-term_id="' . $data_term_id . '" data-taxonomy="' . $this->taxonomy . '" />
                <input type="hidden" value="'.$data_term_id.'" name="price_group[term_id]" />
                <a href="#" class="' . $class . '" data-term_id="' . $data_term_id . '" data-message="' . $message . '" data-taxonomy="' . $this->taxonomy . '">' . $link_text . '</a>'
            );


        // build temrs array
        $tmp = null;
        $terms_obj = get_terms( $this->taxonomy, array( 'hide_empty' => false, 'child_of' => $current_term_id ) );
        foreach( $terms_obj as $term ){
            $tmp[] = array_merge( (array)$term,
                array(
                    'field' => array(
                        'html' => '<input type="text" class="" name="terms_children[' . $term->term_id . '][name]" size="24" value="' . $term->name . '" /><p class="description">'. __('Name','sell_media') . '</p>'
                        )
                    ),
                array(
                    'meta'=> array(
                        'html' => '
                        <td><input type="text" class="small-text" name="terms_children[' . $term->term_id . '][width]" value="'. sell_media_get_term_meta( $term->term_id, 'width', true ) . '">
                        <p class="description">'. __('Max Width','sell_media') . '</p></td>

                        <td><input type="text" class="small-text" name="terms_children['. $term->term_id . '][height]" value="'. sell_media_get_term_meta( $term->term_id, 'height', true ) . '">
                        <p class="description">'. __('Max Height','sell_media') . '</p></td>

                        <td><span class="description">'. sell_media_get_currency_symbol() . '</span>
                        <input type="text" class="small-text" name="terms_children['. $term->term_id . '][price]" value="'. sprintf( '%0.2f', sell_media_get_term_meta( $term->term_id, 'price', true ) ) . '">
                        <p class="description">'. __('Price','sell_media') . '</p></td>'
                    )
                ),
                array(
                    'delete' => array(
                        'html' => '<a href="#" class="sell-media-xit sell-media-delete-term" data-taxonomy="'.$this->taxonomy.'" data-term_id="' . $term->term_id .'" data-type="price" data-message="' . sprintf( '%s: %s?', __('Are you sure you want to delete the price', 'sell_media'), $term->name ) . '">×</a>'
                        )
                    )
            );
            $final['terms'] = $tmp;
        }


        // $final['terms'] = apply_filters('sell_media_rp_meta', $this->taxonomy, $final['terms']);

        // Default terms
        $max = count( $final['terms'] ) < 1 ? 3 : 1;
        $html = null;
        for( $i = 1; $i <= $max; $i++ ) {
            $html .=
                '<tr class="sell-media-price-groups-row" data-index="' . $i . '">
                    <td class="name">
                        <input type="text" class="" name="new_child['.$i.'][name]" size="24" value="">
                        <p class="description">' . __('Name','sell_media') . '</p>
                    </td>
                    <td>
                        <input type="hidden" class="sell-media-price-group-parent-id" name="new_child[' . $i . '][parent]" value="' . $current_term_id . '" />
                        <input type="text" class="small-text" name="new_child[' . $i . '][width]" value="">
                        <p class="description">' . __('Max Width','sell_media') . '</p>
                    </td>
                    <td>
                        <input type="text" class="small-text" name="new_child[' . $i . '][height]" value="">
                        <p class="description">' . __('Max Height','sell_media') . '</p>
                    </td>
                    <td>
                        <span class="description">' . sell_media_get_currency_symbol() . '</span>
                        <input type="text" class="small-text" name="new_child[' . $i . '][price]" value="">
                        <p class="description">' . __('Price','sell_media') . '</p>
                    </td>
                </tr>';
        }

        $price_copy = apply_filters( 'sell_media_rp_price_copy', __('The sizes listed below determine the maximum dimensions in pixels.', 'sell_media'), $this->taxonomy );
        $price_group_copy = apply_filters( 'sell_media_rp_price_group_copy', __('Create a price group to add prices to.', 'sell_media'), $this->taxonomy );

        ?>
        <div id="menu-management-liquid" class="sell-media-price-groups-container">
            <input type="hidden" value="<?php echo $this->taxonomy; ?>" name="taxonomy" id="smtaxonomy" />
            <div id="menu-management">

                <div class="nav-tabs-nav">
                    <div class="nav-tabs-wrapper">
                        <div class="nav-tabs" style="padding: 0px; margin-right: -491px;">
                            <?php foreach( $final['menu'] as $menu ) : ?>
                                <?php echo $menu['html']; ?>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
                <!-- Nav menu -->

                <div class="menu-edit">

                    <!-- Print out the input field w/term name and delete button -->
                    <div id="nav-menu-header">
                        <?php foreach( $final['header'] as $header ) : ?>
                            <?php echo $header; ?>
                        <?php endforeach; ?>
                    </div>


                    <table class="form-table sell-media-price-groups-table">
                        <tbody>
                            <tr>
                                <td colspan="4">
                                    <p>
                                        <?php if ( isset( $_GET['term_parent'] ) && $_GET['term_parent'] == 'new_term' ) : ?>
                                            <?php echo $price_group_copy; ?>
                                        <?php else : ?>
                                            <?php echo $price_copy; ?>
                                        <?php endif; ?>
                                    </p>
                                </td>
                            </tr>
                            <?php if ( empty( $current_term_id ) ) : ?>

                            <?php else : ?>
                                <?php if ( $final['terms'] ) : foreach( $final['terms'] as $term ) : ?>
                                    <tr>
                                        <td><?php echo $term['field']['html']; ?></td>
                                        <?php echo $term['meta']['html']; ?>
                                        <td><?php echo $term['delete']['html']; ?></td>
                                    </tr>
                                <?php endforeach; endif ;?>
                                <?php echo apply_filters( 'sell_media_pg_default_children', $html, $this->taxonomy, $current_term_id ); ?>
                            <?php endif; ?>
                        </tbody>

                        <?php if ( ! empty( $current_term_id ) ) : ?>
                            <tfoot>
                                <tr colspan="4">
                                    <td>
                                        <a href="#" class="button sell-media-price-groups-repeater-add"><?php _e('Add New Price','sell_media'); ?></a>
                                    </td>
                                </tr>
                            </tfoot>
                        <?php endif; ?>
                    </table>
                    <div class="nav-menu-footer">
                        <?php if ( ! empty( $current_term ) ) : ?>
                            <a href="#" class="button-primary sell-media-save-term" data-taxonomy="<?php echo $this->taxonomy; ?>"><?php _e('Save Price Group','sell_media'); ?></a>
                        <?php endif; ?>
                    </div><!-- /.nav-menu-footer -->
                </div>
            </div><!-- /.menu-edit -->
        </div><!-- /#menu-management -->
    <?php }
}
New SellMediaNavStyleUI();