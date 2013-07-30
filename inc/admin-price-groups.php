<?php

Class SellMediaPriceGroups {

    /**
     * Hooks used
     */
    public function __construct(){
        add_action( 'wp_ajax_update_term', array( &$this, 'update_term' ) );
        add_action( 'wp_ajax_save_term', array( &$this, 'save_term' ) );
        add_action( 'wp_ajax_delete_term', array( &$this, 'delete_term' ) );
        add_action( 'wp_ajax_add_term', array( &$this, 'add_term' ) );
        add_action('admin_enqueue_scripts', array( &$this, 'admin_scripts' ) );
    }


    /**
     * Enqueue the needed JS
     */
    public function admin_scripts(){
        global $pagenow;
        if ( ! empty( $pagenow ) && $pagenow == 'edit.php' && ! empty( $_GET['tab'] ) && $_GET['tab'] == 'sell_media_size_settings' )
            wp_enqueue_script( 'sell_media-admin-price-groups' );
    }


    /**
     * Saves/Updates term children (price group children).
     *
     * @author Zane M. Kolnik
     * @since 1.5.1
     */
    public function save_term(){
        parse_str( $_POST['form'], $form_data );
        wp_verify_nonce( $_POST['security'], $_POST['action'] );

        if ( ! empty( $form_data['terms_children'] ) ){
            foreach( $form_data['terms_children'] as $k => $v ){
                wp_update_term( $k, 'price-group', array( 'name' => $v['name'] ) );
                sell_media_update_term_meta( $k, 'width', $v['width'] );
                sell_media_update_term_meta( $k, 'height', $v['height'] );
                sell_media_update_term_meta( $k, 'price', $v['price'] );
            }
        }

        if ( ! empty( $form_data['new_child'] ) ){
            foreach( $form_data['new_child'] as $child ){
                if ( ! empty( $child['name'] ) ){
                    $term = wp_insert_term( $child['name'], 'price-group', array('parent'=> $child['parent'] ) );
                    sell_media_update_term_meta( $term['term_id'], 'width', $child['width'] );
                    sell_media_update_term_meta( $term['term_id'], 'height', $child['height'] );
                    sell_media_update_term_meta( $term['term_id'], 'price', $child['price'] );
                }
            }
        }

        die();
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
            $terms = get_term_children( $_POST['term_id'], 'price-group' );
            $terms[] = $_POST['term_id'];
            foreach( $terms as $term_id ){
                wp_delete_term( $term_id, 'price-group' );
            }
        }
        die();
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
        $terms = get_terms( 'price-group', array( 'hide_empty' => 0, 'parent' => 0 ) );
        foreach( $terms as $term ) {
            $termarray[] = $term->name;
        }

        if ( ! empty( $_POST['term_name'] )  && !in_array( $_POST['term_name'], $termarray ) ) {
            $term = wp_insert_term( $_POST['term_name'], 'price-group' );
            $timestamp = time();
            print admin_url('edit.php?post_type=sell_media_item&page=sell_media_plugin_options&tab=sell_media_size_settings' . '&term_parent=' . $term['term_id'] .'&cache_buster='.$timestamp);
        }
        die();
    }


    public function setting_ui( $parent_terms=null ){
        $current_parent = $_GET['term_parent'];


        $tmp = array();
        $final = array();
        // Build our menu array
        foreach( $parent_terms as $term ) {
            if ( $current_parent == $term->term_id ) {
                $current = $term->term_id;
            } else {
                $current = false;
            }
            $tmp[] = array(
                'term_id' => $term->term_id,
                'name' => $term->name,
                'current_id' => $current
                );
            $final['menu'] = $tmp;
        }

        // Build our list of price group items
echo '<pre>';
print_r( $final );
echo '</pre>';
        // $final['items'] = array(
        //     'name'
        //     'width'
        //     'height'
        //     'price'
        //     )

        ?>
        <div id="menu-management-liquid" class="sell-media-price-groups-container">
            <div id="menu-management">
                <div class="nav-tabs-nav">
                    <div class="nav-tabs-wrapper">
                        <div class="nav-tabs" style="padding: 0px; margin-right: -491px;">
                            <?php foreach( $parent_terms as $term ) : ?>
                                <?php if ( ! empty( $current_parent ) && $current_parent == $term->term_id ) : ?>
                                    <span class="nav-tab nav-tab-active"><?php echo $term->name; ?></span>
                                    <?php
                                    $current_term = $term->name;
                                    $current_term_id = $term->term_id;
                                    ?>
                                <?php else : ?>
                                    <a href="<?php echo admin_url('edit.php?post_type=sell_media_item&page=sell_media_plugin_options&tab=sell_media_size_settings' . '&term_parent=' . $term->term_id ); ?>" class="nav-tab" data-term_id="<?php echo $term->term_id; ?>"><?php echo $term->name; ?></a>
                                <?php endif; ?>
                            <?php endforeach; ?>
                            <?php if ( ! empty( $current_parent ) && $current_parent == 'new_term' ) : ?>
                                <span class="nav-tab-active nav-tab menu-add-new"><abbr title="Add menu">+</abbr></span>
                            <?php else : ?>
                                <a href="<?php echo admin_url('edit.php?post_type=sell_media_item&page=sell_media_plugin_options&tab=sell_media_size_settings&term_parent=new_term'); ?>" class="nav-tab menu-add-new"><abbr title="Add menu">+</abbr></a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <div class="menu-edit">
                    <!-- Print out the input field w/term name and delete button -->
                    <div id="nav-menu-header">
                        <?php if ( empty( $current_term_id ) ) : ?>
                            <input name="sell_media_term_name" id="sell_media_term_name" type="text" class="regular-text" placeholder="<?php _e('Enter price group name here', 'sell_media'); ?>" value="" data-term_id="">
                            <a href="#" class="button-primary sell-media-add-term"><?php _e('Create Price Group','sell_media'); ?></a>
                        <?php else : ?>
                            <input name="sell_media_term_name" id="sell_media_term_name" type="text" class="regular-text" placeholder="<?php _e('Enter price group name here', 'sell_media'); ?>" value="<?php echo $current_term; ?>" data-term_id="<?php echo $current_term_id; ?>">
                            <a class="submitdelete sell-media-delete-term-group" href="#" data-term_id="<?php echo $current_term_id; ?>" data-message='<?php printf( "%s %s?\n\n%s", __("Are you sure you want to delete the price group:", "sell_media" ), $current_term, __("This will delete the price group and ALL its prices associated with it.","sell_media") ); ?>'><?php _e('Delete Group','sell_menu'); ?></a>
                        <?php endif; ?>
                    </div>


                    <table class="form-table sell-media-price-groups-table">
                        <tbody>
                            <tr>
                                <td colspan="4"><p><?php _e('The sizes listed below determine the maximum dimensions in pixels.','sell_media'); ?></p></td>
                            </tr>
                            <?php if ( empty( $current_term_id ) ) : ?>
                                <tr>
                                    <td><p class="description"></p></td>
                                </tr>
                            <?php else : ?>
                                <?php
                                $terms = get_terms( 'price-group', array( 'hide_empty' => false, 'child_of' => $current_term_id ) );
                                foreach( $terms as $term ): ?>
                                <tr>
                                    <td>
                                        <input type="text" class="" name="terms_children[ <?php echo $term->term_id; ?> ][name]" size="24" value="<?php echo $term->name; ?>">
                                        <p class="description"><?php _e('Name<','sell_media'); ?>/p>
                                    </td>
                                    <td>
                                        <input type="text" class="small-text" name="terms_children[ <?php echo $term->term_id; ?> ][width]" value="<?php echo sell_media_get_term_meta( $term->term_id, 'width', true ); ?>">
                                        <p class="description"><?php _e('Max Width','sell_media'); ?></p>
                                    </td>
                                    <td>
                                        <input type="text" class="small-text" name="terms_children[ <?php echo $term->term_id; ?> ][height]" value="<?php echo sell_media_get_term_meta( $term->term_id, 'height', true ); ?>">
                                        <p class="description"><?php _e('Max Height','sell_media'); ?></p>
                                    </td>
                                    <td>
                                        <span class="description"><?php echo sell_media_get_currency_symbol(); ?></span>
                                        <input type="text" class="small-text" name="terms_children[ <?php echo $term->term_id; ?> ][price]" value="<?php echo sprintf( '%0.2f', sell_media_get_term_meta( $term->term_id, 'price', true ) ); ?>">
                                        <p class="description"><?php _e('Price','sell_media'); ?></p>
                                    </td>
                                    <td>
                                        <a href="#" class="sell-media-xit sell-media-delete-term" data-term_id="<?php echo $term->term_id; ?>" data-type="price" data-message="<?php printf( '%s: %s?', __('Are you sure you want to delete the price', 'sell_media'), $term->name ); ?>">×</a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>

                                <!-- This is our default list of items -->
                                <?php $max = count( $terms ) < 1 ? 3 : 1; for( $i = 1; $i <= $max; $i++ ) : ?>
                                <tr class="sell-media-price-groups-row" data-index="<?php echo $i; ?>">
                                    <td>
                                        <input type="text" class="" name="new_child[ <?php echo $i; ?> ][name]" size="24" value="">
                                        <p class="description"><?php _e('Name','sell_media'); ?></p>
                                    </td>
                                    <td>
                                        <input type="hidden" class="sell-media-price-group-parent-id" name="new_child[ <?php echo $i; ?> ][parent]" value="<?php echo $current_term_id; ?>" />
                                        <input type="text" class="small-text" name="new_child[ <?php echo $i; ?> ][width]" value="">
                                        <p class="description"><?php _e('Max Width','sell_media'); ?></p>
                                    </td>
                                    <td>
                                        <input type="text" class="small-text" name="new_child[ <?php echo $i; ?> ][height]" value="">
                                        <p class="description"><?php _e('Max Height','sell_media'); ?></p>
                                    </td>
                                    <td>
                                        <span class="description">$</span>
                                        <input type="text" class="small-text" name="new_child[ <?php echo $i; ?> ][price]" value="">
                                        <p class="description"><?php _e('Price','sell_media'); ?></p>
                                    </td>
                                </tr>
                                <?php endfor; ?>


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
                            <a href="#" class="button-primary sell-media-save-term"><?php _e('Save Price Group','sell_media'); ?></a>
                        <?php endif; ?>
                    </div><!-- /.nav-menu-footer -->
                </div>
            </div><!-- /.menu-edit -->
        </div><!-- /#menu-management -->
    <?php }
}
New SellMediaPriceGroups();