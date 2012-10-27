<?php
/**
 * Build menu along with submenu. This is built independtly
 * of Custom Post Types and Taxonomies.
 *
 * @todo this should be part of a core library ready to be extended
 * @uses add_menu_page( $page_title, $menu_title, $capability, $menu_slug, $function, $icon_url, $position );
 * @uses add_submenu_page( $parent_slug, $page_title, $menu_title, $capability, $menu_slug, $function );
 * @note We remove the default products link in place for a link that will sort them by date order
 */
function sell_media_menu( $params=array() ){
    $permission = 'manage_options';
    add_submenu_page( 'edit.php?post_type=sell_media_item', __('Payments', 'sell_media'), __('Payments', 'sell_media'),  $permission, 'sell_media_payments', 'sell_media_payments_callback_fn' );
    add_submenu_page( 'edit.php?post_type=sell_media_item', __('Settings', 'sell_media'), __('Settings', 'sell_media'),  $permission, 'sell_media_settings', 'sell_media_settings_callback_fn' );
    add_submenu_page( 'edit.php?post_type=sell_media_item', __('Extensions', 'sell_media'), __('Extensions', 'sell_media'),  $permission, 'sell_media_extensions', 'sell_media_extensions_callback_fn' );

    do_action( 'sell_media_menu_hook' );
}
add_action( 'admin_menu', 'sell_media_menu' );


/**
 * Registers our settings to be saved in the wp_options table.
 * Each setting is a form field name and field name in the table.
 */
function sell_media_register_settings() {
    register_setting( 'sell_media_plugin_options', 'sell_media_test_mode' );
    register_setting( 'sell_media_plugin_options', 'sell_media_cart_page' );
    register_setting( 'sell_media_plugin_options', 'sell_media_thanks_page' );

    register_setting( 'sell_media_plugin_options', 'sell_media_paypal_email' );
    register_setting( 'sell_media_plugin_options', 'sell_media_currency' );
    register_setting('sell_media_plugin_options', 'sell_media_original_price' );

    register_setting( 'sell_media_plugin_options', 'sell_media_from_name' );
    register_setting( 'sell_media_plugin_options', 'sell_media_from_email' );
    register_setting( 'sell_media_plugin_options', 'sell_media_success_email_subject' );
    register_setting( 'sell_media_plugin_options', 'sell_media_success_email_body' );

    do_action( 'sell_media_register_settings_hook' );
}
add_action( 'admin_init', 'sell_media_register_settings' );


/**
 * Prints out the form/table for the setting admin page. Note each
 * form field name matches the name in the sell_media_register_settings() function
 */
function sell_media_general_section(){

    if ( get_option( 'sell_media_from_email' ) ){
        $email = get_option( 'sell_media_from_email' );
        $name = get_option( 'sell_media_from_name' );
    } else {
        $email = get_bloginfo('admin_email');
        $admin = get_user_by('email', $email );
        $name = $admin->first_name . ' ' . $admin->last_name;
    }

    if ( get_option( 'sell_media_success_email_body' ) ){
        $body = get_option( 'sell_media_success_email_body' );
    } else {
        $body = "Hi {first_name} {last_name},\nThanks for purchasing from my site. Here are your download links:\n{download_links}\nThanks!";
    }

    ?>
    <div class="wrap">
        <form action="options.php" method="post" class="form-wrap validate" id="addtag">
            <?php settings_fields('sell_media_plugin_options'); ?>

            <h3><?php _e( 'General', 'sell_media' ); ?></h3>
            <?php do_action( 'sell_media_settings_above_general_section_hook' ); ?>
            <table class="form-table">
                <tbody>
                    <tr valign="top">
                        <th scope="row" class="titledesc"><?php _e( 'Test Mode', 'sell_media' ); ?></th>
                        <td class="forminp">
                            <select name="sell_media_test_mode" id="sell_media_test_mode">
                                <option value="1" <?php selected( get_option('sell_media_test_mode'), 1 ); ?>><?php _e( 'Yes', 'sell_media' ); ?></option>
                                <option value="0" <?php selected( get_option('sell_media_test_mode'), 0 ); ?>><?php _e( 'No', 'sell_media' ); ?></option>
                            </select>
                            <label for="sell_media_test_mode"><?php printf(__('To fully use test mode, you must have %1$s.'), '<a href="https://developer.paypal.com/" target="_blank">Paypal sandbox (test) account</a>' ); ?></label>
                        </td>
                    </tr>
                    <tr valign="top">
                        <th scope="row" class="titledesc"><?php _e( 'Checkout Page', 'sell_media' ); ?></th>
                        <td class="forminp">
                            <?php wp_dropdown_pages( array( 'name' => 'sell_media_cart_page', 'selected' => get_option('sell_media_cart_page') ) ); ?>
                            <label for="sell_media_cart_page"><?php _e( 'What page contains the <code>[sell_media_checkout]</code> shortcode? This shortcode will generate the checkout cart. Create a page now and add the shortcode if you have not completed this step yet.', 'sell_media' ); ?></label>
                        </td>
                    </tr>
                    <tr valign="top">
                        <th scope="row" class="titledesc"><?php _e( 'Thanks Page', 'sell_media' ); ?></th>
                        <td class="forminp">
                            <?php wp_dropdown_pages( array( 'name' => 'sell_media_thanks_page', 'selected' => get_option('sell_media_thanks_page') ) ); ?>
                            <label for="sell_media_thanks_page"><?php _e( 'What page contains the <code>[sell_media_thanks]</code> shortcode? This is the page that users return to after a successful purchase.', 'sell_media' ); ?></label>
                        </td>
                    </tr>
                </tbody>
            </table>
            <h3><?php _e( 'Payment Settings', 'sell_media' ); ?></h3>
            <table class="form-table">
                <tbody>
                    <tr valign="top">
                        <th scope="row" class="titledesc"><?php _e( 'Paypal Account Email', 'sell_media' ); ?></th>
                        <td class="forminp">
                            <div class="form-field form-required">
                                <input name="sell_media_paypal_email" id="sell_media_paypal_email" type="text" value="<?php print get_option('sell_media_paypal_email'); ?>" aria-required="true" style="width:170px;">
                                <label for="sell_media_paypal_email"><?php printf(__('The email address used to collect Paypal payments. IMPORTANT: You must setup IPN Notifications in Paypal to process transactions. %1$s. Here is the listener URL you need to add in Paypal: %2$s'), '<a href="https://cms.paypal.com/us/cgi-bin/?cmd=_render-content&content_ID=developer/e_howto_admin_IPNSetup#id089EG030E5Z" target="_blank">Read Paypal instructions</a>', '<code>' . home_url( '?sell_media-listener=IPN' ) . '</code>' ); ?></label>
                            </div>
                        </td>
                    </tr>
                    <tr valign="top">
                        <th scope="row" class="titledesc"><?php _e( 'Currency', 'sell_media' ); ?></th>
                        <td class="forminp">
                            <select name="sell_media_currency" id="sell_media_currency">
                                <option value="USD" <?php selected( get_option('sell_media_currency'), 'USD' ); ?>>US Dollars ($)</option>
                                <option value="EUR" <?php selected( get_option('sell_media_currency'), 'EUR' ); ?>>Euros (€)</option>
                                <option value="GBP" <?php selected( get_option('sell_media_currency'), 'GBP' ); ?>>Pounds Sterling (£)</option>
                                <option value="AUD" <?php selected( get_option('sell_media_currency'), 'AUD' ); ?>>Australian Dollars ($)</option>
                                <option value="BRL" <?php selected( get_option('sell_media_currency'), 'BRL' ); ?>>Brazilian Real ($)</option>
                                <option value="CAD" <?php selected( get_option('sell_media_currency'), 'CAD' ); ?>>Canadian Dollars ($)</option>
                                <option value="CZK" <?php selected( get_option('sell_media_currency'), 'CZK' ); ?>>Czech Koruna (Kč)</option>
                                <option value="DKK" <?php selected( get_option('sell_media_currency'), 'DKK' ); ?>>Danish Krone</option>
                                <option value="HKD" <?php selected( get_option('sell_media_currency'), 'HKD' ); ?>>Hong Kong Dollar ($)</option>
                                <option value="HUF" <?php selected( get_option('sell_media_currency'), 'HUF' ); ?>>Hungarian Forint</option>
                                <option value="ILS" <?php selected( get_option('sell_media_currency'), 'ILS' ); ?>>Israeli Shekel</option>
                                <option value="JPY" <?php selected( get_option('sell_media_currency'), 'JPY' ); ?>>Japanese Yen (¥)</option>
                                <option value="MYR" <?php selected( get_option('sell_media_currency'), 'MYR' ); ?>>Malaysian Ringgits</option>
                                <option value="MXN" <?php selected( get_option('sell_media_currency'), 'MXN' ); ?>>Mexican Peso ($)</option>
                                <option value="NZD" <?php selected( get_option('sell_media_currency'), 'NZD' ); ?>>New Zealand Dollar ($)</option>
                                <option value="NOK" <?php selected( get_option('sell_media_currency'), 'NOK' ); ?>>Norwegian Krone</option>
                                <option value="PHP" <?php selected( get_option('sell_media_currency'), 'PHP' ); ?>>Philippine Pesos</option>
                                <option value="PLN" <?php selected( get_option('sell_media_currency'), 'PLN' ); ?>>Polish Zloty</option>
                                <option value="SGD" <?php selected( get_option('sell_media_currency'), 'SGD' ); ?>>Singapore Dollar ($)</option>
                                <option value="SEK" <?php selected( get_option('sell_media_currency'), 'SEK' ); ?>>Swedish Krona</option>
                                <option value="CHF" <?php selected( get_option('sell_media_currency'), 'CHF' ); ?>>Swiss Franc</option>
                                <option value="TWD" <?php selected( get_option('sell_media_currency'), 'TWD' ); ?>>Taiwan New Dollars</option>
                                <option value="THB" <?php selected( get_option('sell_media_currency'), 'THB' ); ?>>Thai Baht</option>
                                <option value="TRY" <?php selected( get_option('sell_media_currency'), 'TRY' ); ?>>Turkish Lira (TL)</option>
                                <option value="ZAR" <?php selected( get_option('sell_media_currency'), 'ZAR' ); ?>>South African rand (R)</option>
                            </select>
                            <label for="sell_media_currency"><?php _e( 'The currency you accept payment in.', 'sell_media' ); ?></label>
                        </td>
                    </tr>

                    <tr valign="top">
                        <th scope="row" class="titledesc"><?php _e( 'Default Price', 'sell_media' ); ?></th>
                        <td class="forminp">
                            <input name="sell_media_original_price" id="sell_media_original_price" type="text" value="<?php print get_option('sell_media_original_price'); ?>">
                            <label for="sell_media_original_price"><?php _e( 'The default price for all newly created items. The price of individual items can be changed by editing the item itself.', 'sell_media' ); ?></label>
                        </td>
                    </tr>
                </tbody>
            </table>
            <h3><?php _e( 'Email Settings', 'sell_media' ); ?></h3>
            <table class="form-table">
                <tbody>
                    <tr valign="top">
                        <th scope="row" class="titledesc"><?php _e( 'From Name', 'sell_media' ); ?></th>
                        <td class="forminp">
                            <input name="sell_media_from_name" id="sell_media_from_name" type="text" style="" value="<?php print $name; ?>">
                            <label for="sell_media_from_name"><?php _e( 'Please enter sender name. All messages to buyers are sent using this name as "FROM:" header value.', 'sell_media' ); ?></label>
                        </td>
                    </tr>
                    <tr valign="top">
                        <th scope="row" class="titledesc"><?php _e( 'From Email', 'sell_media' ); ?></th>
                        <td class="forminp">
                            <input name="sell_media_from_email" id="sell_media_from_email" type="text" style="" value="<?php print $email; ?>">
                            <label for="sell_media_from_email"><?php _e( 'Please enter sender e-mail. All messages to buyers are sent using this e-mail as "FROM:" header value.', 'sell_media' ); ?></label>
                        </td>
                    </tr>
                    <tr valign="top">
                        <th scope="row" class="titledesc"><?php _e( 'Email Subject', 'sell_media' ); ?></th>
                        <td class="forminp">
                            <input name="sell_media_success_email_subject" id="sell_media_success_email_subject" type="text" style="" value="<?php print get_option( 'sell_media_success_email_subject' ); ?>">
                            <label for="sell_media_success_email_subject"><?php _e( 'In case of successful and cleared payment, your customers receive e-mail message about successful purchasing. This is subject field of the message.', 'sell_media' ); ?></label>
                        </td>
                    </tr>
                    <tr valign="top">
                        <th scope="row" class="titledesc"><?php _e( 'Email Body', 'sell_media' ); ?></th>
                        <td class="forminp">
                            <textarea name="sell_media_success_email_body" id="sell_media_success_email_body" style="width:50%;height:150px;"><?php print $body; ?></textarea>
                            <label for="sell_media_success_email_body"><?php _e( 'This e-mail message is sent to your customers in case of successful and cleared payment. You can use the following keywords: {first_name}, {last_name}, {payer_email}, {download_links}. Be sure to include the {download_links} tag, otherwise your buyers won\'t receive their download purchases.', 'sell_media' ); ?></label>
                        </td>
                    </tr>
                </tbody>
            </table>
            <?php do_action( 'sell_media_settings_below_general_section_hook' ); ?>
            <p>
                <input name="Submit" type="submit" class="button-primary" value="<?php esc_attr_e('Save Changes'); ?>" />
            </p>
        </form>
    </div>
<?php }

/**
 * Build the ENTIRE settings page, form submission is also handled here.
 * well part of the form submission.
 */
function sell_media_settings_callback_fn() {
    $sell_media_settings_temp['tabs'] = apply_filters( 'sell_media_tabs_settings', array(
        'general' => __( 'Settings', 'sell_media' )
        )
    );

    $current_tab = (isset($_GET['tab'])) ? $_GET['tab'] : 'general';
    $tabs_base_url = 'edit.php?post_type=sell_media_item&page=sell_media_settings';

    ?>
    <h2 class="nav-tab-wrapper">
        <?php foreach ( $sell_media_settings_temp[ 'tabs' ] as $tab => $label ) : ?>
            <a href="<?php echo admin_url( $tabs_base_url . '&tab=' . $tab ); ?>" class="nav-tab <?php if ( $current_tab==$tab ) echo 'nav-tab-active'; ?>"><?php echo $label; ?></a>
        <?php endforeach; ?>
    </h2>
    <?php
        if ( $current_tab ) {
            switch ($current_tab) {
                case "general" :
                    sell_media_general_section();
                    break;
                default :
                    print $current_tab;
                break;
            }
        } else {
            print 'no tab';
        }
    ?>
<?php }

/**
 * Admin notice when settings are saved.
 */
function sell_media_save_settings_admin_notice(){
    global $pagenow;
    if ( $pagenow == 'edit.php' && isset($_GET['page']) && $_GET['page'] == 'sell_media_settings' && isset($_GET['settings-updated']) && $_GET['settings-updated'] == 'true') {
         echo '<div class="updated">
             <p>Settings updated!</p>
         </div>';
    }
}
add_action( 'admin_notices', 'sell_media_save_settings_admin_notice' );


/**
 * Print and handle the data for the add-ons form
 */
function sell_media_extensions_callback_fn(){
    if ( false === ( $extensions = get_transient( 'graphpaperpress_extensions_feed' ) ) ) {
        $response = wp_remote_get( 'http://graphpaperpress.com/json-extensions-feed/' );
        if ( ! is_wp_error( $response ) && isset( $response['body'] ) ) {
            $extensions = json_decode( $response['body'] );
            set_transient('graphpaperpress_extensions_feed', $extensions, 3600);
        }
    }

    ?>
<div class="wrap sell_media-extensions">
    <h2><?php _e( 'Extensions for Sell Media', 'sell_media' ); ?></h2>
    <p><?php _e( 'These extensions provide additonal functionality for the Sell Media plugin.', 'sell_media' ); ?></p>
    <?php foreach( $extensions as $extension ) : ?>
        <div class="row-container">
            <div class="extension">
                <h3 class="title"><a href="<?php print $extension->permalink; ?>"><?php print $extension->title; ?></a></h3>
                <div class="image"><a href="<?php print $extension->permalink; ?>"><img src="<?php print $extension->image[0]; ?>" /></a></div>
            </div>
        </div>
    <?php endforeach; ?>
</div>
<?php }