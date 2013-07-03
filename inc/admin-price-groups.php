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
}
New SellMediaPriceGroups();