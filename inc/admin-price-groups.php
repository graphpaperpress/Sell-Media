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
        wp_enqueue_script( 'sell_media-admin-price-groups' );
    }


    /**
     * Updates a single price group (parent only)
     *
     * @author Zane M. Kolnik
     * @since 1.5.1
     */
    public function update_term(){
        wp_verify_nonce( $_POST['security'], $_POST['action'] );

        if ( ! empty( $_POST['term_name'] ) ) {
            wp_update_term( $_POST['term_id'], 'price-group', array('name'=>$_POST['term_name'] ) );
        }
        die();
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

        foreach( $form_data['terms_children'] as $k => $v ){
            wp_update_term( $k, 'price-group', array( 'name' => $v['name'] ) );
            sell_media_update_term_meta( $k, 'width', $v['width'] );
            sell_media_update_term_meta( $k, 'height', $v['height'] );
            sell_media_update_term_meta( $k, 'price', $v['price'] );
        }

        if ( ! empty( $form_data['new_child']['name'] ) ){
            $term = wp_insert_term( $form_data['new_child']['name'], 'price-group', array('parent'=> $form_data['new_child']['parent'] ) );
            sell_media_update_term_meta( $term['term_id'], 'width', $form_data['new_child']['width'] );
            sell_media_update_term_meta( $term['term_id'], 'height', $form_data['new_child']['height'] );
            sell_media_update_term_meta( $term['term_id'], 'price', $form_data['new_child']['price'] );
        }
        die();
    }


    /**
     * Delets a single term or a single term and ALL ITS CHILDREN!!
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

        if ( ! empty( $_POST['term_name'] ) ) {
            $term = wp_insert_term( $_POST['term_name'], 'price-group' );
            print admin_url('edit.php?post_type=sell_media_item&page=sell_media_plugin_options&tab=sell_media_size_settings' . '&term_parent=' . $term['term_id'] );
        }
        die();
    }
}
New SellMediaPriceGroups();