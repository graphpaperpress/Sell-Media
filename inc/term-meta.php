<?php

/**
 * Set Default Terms
 * Used in attachment-functions.php
 *
 * @since 0.1
 */
function sell_media_set_default_terms( $post_id, $post=null, $term_ids=null ){

    if ( is_null( $post ) ){
        $post_type = get_post_type( $post_id );
    } else {
        $post_type = $post->post_type;
        $post_status = $post->post_status;
    }

    if ( empty( $post_status ) )
        return;

    if ( is_null( $term_ids ) )
        $term_ids = sell_media_get_default_terms();

    $taxonomy = 'licenses';
    $default_terms = array();

    foreach( $term_ids as $term_id ){
        $tmp_term_id = get_term_by( 'id', $term_id, $taxonomy );

        if ( $tmp_term_id ) {
            $default_terms[] = (int)$tmp_term_id->term_id;
            $default_terms[] = (int)$tmp_term_id->parent;
        }
    }

    $defaults = array(
        $taxonomy => $default_terms
        );

    $taxonomies = get_object_taxonomies( $post_type );

    foreach( ( array )$taxonomies as $taxonomy ) {
        $terms = wp_get_post_terms( $post_id, $taxonomy );
        if ( empty( $terms ) && array_key_exists( $taxonomy, $defaults ) ) {
            wp_set_object_terms( $post_id, $defaults[$taxonomy], $taxonomy );
        }
    }
}
add_action( 'save_post', 'sell_media_set_default_terms', 100, 3 );

/**
 * Get Default Terms from database
 *
 * @return array
 * @since 0.1
 */
function sell_media_get_default_terms(){
    global $wpdb;

    $query = "SELECT * FROM {$wpdb->prefix}taxonomymeta WHERE `meta_value` LIKE 'on'";
    $terms_meta = $wpdb->get_results( $query );

    $term_ids = array();
    $default_terms = array();

    foreach( $terms_meta as $meta ) {
        $term_ids[] = $meta->taxonomy_id;
    }

    return $term_ids;
}


/**
 * Add description to add new licenses admin page
 *
 * @return string
 * @since 0.1
 */
function sell_media_license_description(){
    echo __( 'When a buyers decides to purchase a item from your site, they must choose a license which most closely identifies their intended use of the item. We have included some default license types, grouped into two "parent" categories: Personal and Commercial. Each of these two categories have specific "child" licenses, such as "Print Advertising" (a child of Commercial) and "Website" (a child of Personal). You can create as many parent and child licenses as you want.', 'sell_media' );
}
add_action( 'licenses_pre_add_form', 'sell_media_license_description' );


/**
 * Add form fields to add terms page for our custom taxonomies
 *
 * @since 0.1
 */
function sell_media_add_custom_term_form_fields( $tag ){
    if ( is_object( $tag ) )
        $term_id = $tag->term_id;
    else
        $term_id = null;
    ?>
    <div class="form-field">
        <label for="markup"><?php _e('Markup', 'sell_media'); ?></label>
        <?php sell_media_the_markup_slider( $tag ); ?>
    </div>
    <div class="form-field">
        <?php sell_media_the_default_checkbox( $term_id ); ?>
    </div>
<?php }
add_action( 'licenses_add_form_fields', 'sell_media_add_custom_term_form_fields' );


/**
 * Edit form fields to edit terms page for our custom taxonomies
 *
 * @since 0.1
 */
function sell_media_edit_custom_term_form_fields( $tag ){ ?>
    <tr class="form-field sell_media-markup-container">
        <th scope="row" valign="top">
            <label for="markup"><?php _e( 'Markup', 'sell_media' ); ?></label>
        </th>
        <td>
            <?php sell_media_the_markup_slider( $tag ); ?>
        </td>
    </tr>
    <tr class="form-field sell_media-markup-container">
        <td><?php sell_media_the_default_checkbox( $tag->term_id ); ?></td>
    </tr>
<?php }
add_action( 'licenses_edit_form_fields', 'sell_media_edit_custom_term_form_fields' );


/**
 * Function for building the slider on Add/Edit License admin page
 *
 * @since 0.1
 */
function sell_media_the_markup_slider( $tag ){

    if ( isset( $_GET['tag_ID'] ) )
        $term_id = $_GET['tag_ID'];
    else
        $term_id = null;

    if ( sell_media_get_term_meta( $term_id, 'markup', true) ) {
        $initial_markup = str_replace( "%", "", sell_media_get_term_meta( $term_id, 'markup', true ) );
    } else {
        $initial_markup = 0;
    }

    $payment_settings = get_option( 'sell_media_size_settings' ); ?>
    <script>
    jQuery(document).ready(function($){

        if ( ! jQuery().slider )
            return;

        function calc_price( markUp ){

            var price = <?php if ( $payment_settings['default_price'] ) print $payment_settings['default_price']; else print 1; ?>;

            if ( markUp == undefined )
                var markUp = <?php print $initial_markup; ?>;

            finalPrice = ( +price + ( +markUp * .01 ) * price );
            finalPrice = finalPrice.toFixed(2);

            return finalPrice;
        }

        $( ".price-target" ).html( calc_price() );

        $( "#markup_slider" ).slider({
            range: "min",
            value: <?php print $initial_markup; ?>,
            min: 0,
            step: .1,
            max: 1000,
            slide: function( event, ui ) {
                var markUp = ui.value;
                $( ".markup-target" ).val(  markUp + "%" );
                $( ".markup-target" ).html(  markUp + "%" );

                $( ".price-target" ).html( calc_price( markUp ) );
            }
        });
        $( ".markup-target" ).val( $( "#markup_slider" ).slider( "value" ) + "%" );
    });
    </script>
    <div class="sell_media-slider-container">
        <div id="markup_slider"></div>
        <div class="sell_media-price-container">
            <input name="meta_value[markup]" class="markup-target" type="text" value="<?php echo sell_media_get_term_meta($term_id, 'markup', true); ?>" size="40" />
        </div>
        <p class="description">
            <?php _e( 'Increase the price of a item if a buyer selects this license by dragging the slider above.', 'sell_media' ); ?>
            <?php
                if ( sell_media_get_term_meta( $term_id, 'markup', true ) )
                    $default_markup = sell_media_get_term_meta( $term_id, 'markup', true );
                else
                    $default_markup = '0%';

            if ( $payment_settings['default_price'] ){
                $price = sell_media_get_currency_symbol() . $payment_settings['default_price'];
            } else {
                $price = __('you have not set a default price', 'sell_media');
            }

            printf(
                __( ' The %1$s of %2$s with %3$s markup is %4$s' ),
                '<a href="' . admin_url() . 'edit.php?post_type=sell_media_item&page=sell_media_plugin_options&tab=sell_media_general_settings
                ">default item price</a>',
                '<strong>' . $price . '</strong>',
                '<strong><span class="markup-target">' . $default_markup . '</span></strong>',
                '<strong>' . sell_media_get_currency_symbol() . '<span class="price-target"></span></strong>'
                );
            ?>
        </p>
    </div>
<?php }


/**
 * Prints the checkbox for the default license type
 *
 * @since 0.1
 */
function sell_media_the_default_checkbox( $term_id=null, $desc=null ){
    if ( is_null( $desc ) )
        $desc = __( 'Check to add this as a default license option for all newly created items.', 'sell_media' );
    ?>
    <tr class="form-field sell_media-markup-container">
        <th scope="row" valign="top">
            <label for="markup"><?php _e( 'Add as default license?', 'sell_media' ); ?></label>
        </th>
        <td>
            <input name="meta_value[default]" style="width: auto;" id="meta_value[default]" type="checkbox" <?php checked( sell_media_get_term_meta($term_id, 'default', true), "on" ); ?> size="40" />
            <span class="description"><label for="meta_value[default]"><?php echo $desc; ?></label></span>
        </td>
    </tr>
<?php }


/**
 * Display Custom License Column Headers in wp-admin
 *
 * @since 0.1
 */
function sell_media_custom_license_columns_headers( $columns ){

    $columns_local = array();

    if ( isset( $columns['cb'] ) ) {
        $columns_local['cb'] = $columns['cb'];
        unset($columns['cb']);
    }

    if ( isset( $columns['name'] ) ) {
        $columns_local['name'] = $columns['name'];
        unset($columns['name']);
    }

    if (!isset($columns_local['license_term_price']))
        $columns_local['license_term_price'] = "% Markup";

    // Rename the post column to Images
    if ( isset( $columns['posts'] ) )
        $columns['posts'] = "Media";

     $columns_local = array_merge($columns_local, $columns);

    return array_merge($columns_local, $columns);
}
add_filter( 'manage_edit-licenses_columns', 'sell_media_custom_license_columns_headers' );


/**
 * Display Custom License Column Content below Headers in wp-admin
 *
 * @since 0.1
 */
function sell_media_custom_license_columns_content( $row_content, $column_name, $term_id ){
    switch( $column_name ) {
        case 'license_term_price':
            return sell_media_get_term_meta($term_id, 'markup', true);
            break;
        default:
            break;
    }
}
add_filter( 'manage_licenses_custom_column', 'sell_media_custom_license_columns_content', 10, 3 );


/**
 * Save new taxonomy fields
 * Used to both save and update
 *
 * @since 0.1
 */
function sell_media_save_extra_taxonomy_fields( $term_id ) {

    if ( ! isset( $_POST['meta_value']['default'] ) ) {
        sell_media_update_term_meta( $term_id, 'default', 'off');
    }

    if ( ! isset( $_POST['meta_value']['collection_hidden'] ) ) {
        if ( ! empty(  $_SESSION['sell_media']['collection_password'] ) )
            unset( $_SESSION['sell_media']['collection_password'] );
    }

    if ( isset( $_POST['meta_value'] ) ) {
        $cat_keys = array_keys( $_POST['meta_value'] );

        foreach ( $cat_keys as $key ) {
            if ( ! empty( $_POST['meta_value'][$key] ) ) {
                $meta_value[$key] = $_POST['meta_value'][$key];
                sell_media_update_term_meta( $term_id, $key, wp_filter_nohtml_kses( $meta_value[$key]) );
            } else {
                sell_media_delete_term_meta( $term_id, $key );
            }
        }
    }
}
add_action( 'edited_licenses', 'sell_media_save_extra_taxonomy_fields' );
add_action( 'create_licenses', 'sell_media_save_extra_taxonomy_fields' );
add_action( 'edited_collection', 'sell_media_save_extra_taxonomy_fields' );
add_action( 'create_collection', 'sell_media_save_extra_taxonomy_fields' );


/**
 * Add password field to collection
 *
 * @since 0.1
 */
function sell_media_add_collection_field( $tag ){
    if ( is_object( $tag ) )
        $term_id = $tag->term_id;
    else
        $term_id = null;
    ?>
    <div class="form-field">
        <label for="collection_password"><?php _e( 'Password', 'sell_media' ); ?></label>
        <input name="meta_value[collection_password]" type="text" id="meta_value[]" />
        <p class="description"><?php _e( 'This will password protect all items in this collection.', 'sell_media' ); ?></p>
    </div>
<?php }
add_action( 'collection_add_form_fields', 'sell_media_add_collection_field' );


/**
 * Add icon field to the edit collection page
 *
 * @since 0.1
 */
function sell_media_edit_collection_password( $tag ){
    if ( is_object( $tag ) )
        $term_id = $tag->term_id;
    else
        $term_id = null; ?>
    <tr class="form-field">
        <th scope="row" valign="top">
            <label for="collection_password"><?php _e( 'Password', 'sell_media' ); ?></label>
        </th>
        <td>
            <input name="meta_value[collection_password]" id="meta_value[collection_password]" type="text" value="<?php print sell_media_get_term_meta( $term_id, 'collection_password', true ); ?>" />
            <p class="description"><?php _e( 'Password protect all items in this collection', 'sell_media' ); ?></p>
        </td>
    </tr>
<?php }
add_action( 'collection_edit_form_fields', 'sell_media_edit_collection_password' );


/**
 * Add icon field to collection
 *
 * @since 0.1
 */
function sell_media_add_collection_icon( ){ ?>
    <div class="form-field collection-icon">
        <label for="collection_icon"><?php _e( 'Icon', 'sell_media' ); ?></label>
    <?php sell_media_collection_icon_field(); ?>
    </div>
    <?php }
add_action( 'collection_add_form_fields', 'sell_media_add_collection_icon' );


function sell_media_collection_icon_field( $icon_id=null ){
    wp_enqueue_media();
    if ( empty( $icon_id ) ){
        $image = $url = null;
    } else {
        $url = wp_get_attachment_url( $icon_id );
        $image = wp_get_attachment_image( $icon_id, 'thumbnail' );
        $image .= '<br /><a href="javascript:void(0);" class="upload_image_remove">Remove</a>';
    }
    ?>
    <input name="meta_value[collection_icon_id]" type="hidden" id="collection_icon_input_field" value="<?php print $icon_id; ?>" />
    <input name="" type="text" id="collection_icon_url" value="<?php print $url; ?>" />
    <input class="button sell-media-upload-trigger-collection-icon" type="button" value="<?php _e( 'Upload or Select Image', 'sell_media'); ?>" />
    <div class="upload_image_preview" style="display: block;">
        <span id="collection_icon_target"><?php print $image; ?></span>
    </div>
    <p class="description"><?php _e( 'The icon is not prominent by default; however, some themes may show it. If no icon is used the featured image to the most recent post will be displayed', 'sell_media' ); ?></p>
<?php }

/**
 * Hide collections from archive view
 *
 * @since 0.1
 */
function sell_media_edit_collection_icon( $tag ){
    $term_id = is_object( $tag ) ? $tag->term_id : null; ?>
    <tr class="form-field sell-media-collection-form-field">
        <th scope="row" valign="top">
            <label for="collection_icon"><?php _e( 'Icon', 'sell_media' ); ?></label>
        </th>
        <td>
            <?php sell_media_collection_icon_field( sell_media_get_term_meta( $term_id, 'collection_icon_id', true ) ); ?>
        </td>
    </tr>
<?php }
add_action( 'collection_edit_form_fields', 'sell_media_edit_collection_icon' );


/**
 * This code is from the Plugin: Taxonomy Metadata with minor
 * modifications.
 */
class SELL_MEDIA_Taxonomy_Metadata {
    function __construct() {
        add_action( 'init', array($this, 'wpdbfix') );
        add_action( 'switch_blog', array($this, 'wpdbfix') );
        add_action('wpmu_new_blog', 'new_blog', 10, 6);
    }

    /*
     * Quick touchup to wpdb
     */
    static function wpdbfix() {
        global $wpdb;
        // $wpdb->taxonomymeta = "{$wpdb->prefix}taxonomymeta";
        $variable_name = 'taxonomymeta';
        $wpdb->$variable_name = $wpdb->prefix . $variable_name;
        $wpdb->tables[] = $variable_name;
    }

    /*
     * TABLE MANAGEMENT
     */
    function activate( $network_wide = false ) {
        global $wpdb;

        // if activated on a particular blog, just set it up there.
        if ( !$network_wide ) {
            $this->setup_blog();
            return;
        }

        $blogs = $wpdb->get_col( "SELECT blog_id FROM {$wpdb->blogs} WHERE site_id = '{$wpdb->siteid}'" );
        foreach ( $blogs as $blog_id ) {
            $this->setup_blog( $blog_id );
        }
        // I feel dirty... this line smells like perl.
        do {} while ( restore_current_blog() );
    }

    function setup_blog( $id = false ) {
        global $wpdb;

        if ( $id !== false)
            switch_to_blog( $id );

        $charset_collate = '';
        if ( ! empty($wpdb->charset) )
            $charset_collate = "DEFAULT CHARACTER SET $wpdb->charset";
        if ( ! empty($wpdb->collate) )
            $charset_collate .= " COLLATE $wpdb->collate";

        $tables = $wpdb->get_results("show tables like '{$wpdb->prefix}taxonomymeta'");
        if (!count($tables))
            $wpdb->query("CREATE TABLE IF NOT EXISTS {$wpdb->prefix}taxonomymeta (
                meta_id bigint(20) unsigned NOT NULL auto_increment,
                taxonomy_id bigint(20) unsigned NOT NULL default '0',
                meta_key varchar(255) default NULL,
                meta_value longtext,
                PRIMARY KEY (meta_id),
                KEY taxonomy_id (taxonomy_id),
                KEY meta_key (meta_key)
            ) $charset_collate;");
    }

    function new_blog( $blog_id, $user_id, $domain, $path, $site_id, $meta ) {
        if ( is_plugin_active_for_network(plugin_basename(__FILE__)) )
            $this->setup_blog($blog_id);
    }
}

// THE REST OF THIS CODE IS FROM http://core.trac.wordpress.org/ticket/10142
// BY sirzooro

//
// Taxonomy meta functions
//

/**
 * Add meta data field to a term.
 *
 * @param int $term_id Post ID.
 * @param string $key Metadata name.
 * @param mixed $value Metadata value.
 * @param bool $unique Optional, default is false. Whether the same key should not be added.
 * @return bool False for failure. True for success.
 */
function sell_media_add_term_meta($term_id, $meta_key, $meta_value, $unique = false) {
    SELL_MEDIA_Taxonomy_Metadata::wpdbfix();
    return add_metadata('taxonomy', $term_id, $meta_key, $meta_value, $unique);
}

/**
 * Remove metadata matching criteria from a term.
 *
 * You can match based on the key, or key and value. Removing based on key and
 * value, will keep from removing duplicate metadata with the same key. It also
 * allows removing all metadata matching key, if needed.
 *
 * @param int $term_id term ID
 * @param string $meta_key Metadata name.
 * @param mixed $meta_value Optional. Metadata value.
 * @return bool False for failure. True for success.
 */
function sell_media_delete_term_meta($term_id, $meta_key, $meta_value = '') {
    SELL_MEDIA_Taxonomy_Metadata::wpdbfix();
    return delete_metadata('taxonomy', $term_id, $meta_key, $meta_value);
}

/**
 * Retrieve term meta field for a term.
 *
 * @param int $term_id Term ID.
 * @param string $key The meta key to retrieve.
 * @param bool $single Whether to return a single value.
 * @return mixed Will be an array if $single is false. Will be value of meta data field if $single
 *  is true.
 */
function sell_media_get_term_meta($term_id, $key, $single = false) {
    SELL_MEDIA_Taxonomy_Metadata::wpdbfix();
    return get_metadata('taxonomy', $term_id, $key, $single);
}

/**
 * Update term meta field based on term ID.
 *
 * Use the $prev_value parameter to differentiate between meta fields with the
 * same key and term ID.
 *
 * If the meta field for the term does not exist, it will be added.
 *
 * @param int $term_id Term ID.
 * @param string $key Metadata key.
 * @param mixed $value Metadata value.
 * @param mixed $prev_value Optional. Previous value to check before removing.
 * @return bool False on failure, true if success.
 */
function sell_media_update_term_meta($term_id, $meta_key, $meta_value, $prev_value = '') {
    SELL_MEDIA_Taxonomy_Metadata::wpdbfix();
    return update_metadata('taxonomy', $term_id, $meta_key, $meta_value, $prev_value);
}
// End 'taxonomy meta plugin code'