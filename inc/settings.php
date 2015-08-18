<?php

/**
 * Settings
 *
 * @package Sell Media
 * @author Thad Allender <support@graphpaperpress.com>
 */

/**
 * Init our settings
 * @since 1.8.5
 */
function sell_media_init_settings(){
    /**
     * Define the Tabs appearing on the Theme Options page
     * Tabs contains sections
     * Options are assigned to both Tabs and Sections
     * See README.md for a full list of option types
     */

    // General Tab
    $general_settings_tab = array(
        'name' => 'sell_media_general_settings',
        'title' => __( 'General', 'sell_media' ),
        'sections' => array(
            'general_plugin_section_1' => array(
                'name' => 'general_plugin_section_1',
                'title' => __( 'General', 'sell_media' ),
                'description' => ''
            )
        )
    );
    sell_media_register_plugin_option_tab( apply_filters( 'sell_media_general_tab', $general_settings_tab ) );

    // Size & Price Tab
    $size_price_tab = array(
        'name' => 'sell_media_size_settings',
        'title' => __( 'Pricing', 'sell_media' ),
        'sections' => array(
            'size_price_plugin_section_1' => array(
                'name' => 'size_price_plugin_section_1',
                'title' => __( 'Pricing', 'sell_media' ),
                'description' => ''
            )
        )
    );
    sell_media_register_plugin_option_tab( apply_filters( 'sell_media_size_price_tab', $size_price_tab ) );

    // Payment Tab
    $payment_tab = array(
        'name' => 'sell_media_payment_settings',
        'title' => __( 'Payment','sell_media' ),
        'sections' => array(
            'payment_section_1' => array(
                'name' => 'payment_section_1',
                'title' => __( 'Payment','sell_media' ),
                'description' => ''
                )
            )
        );
    sell_media_register_plugin_option_tab( apply_filters( 'sell_media_payment_tab', $payment_tab ) );

    // Email Tab
    $email_tab = array(
        'name' => 'email_plugin_tab',
        'title' => __( 'Email','sell_media' ),
        'sections' => array(
            'email_section_1' => array(
                'name' => 'email_section_1',
                'title' => __( 'Email Settings','sell_media' ),
                'description' => ''
                )
            )
        );
    sell_media_register_plugin_option_tab( $email_tab );

    // Misc Tab
    $misc_tab = array(
        'name' => 'misc_plugin_tab',
        'title' => __( 'Misc','sell_media' ),
        'sections' => array(
            'misc_section_1' => array(
                'name' => 'misc_section_1',
                'title' => __( 'Misc','sell_media' ),
                'description' => ''
                )
            )
        );
    sell_media_register_plugin_option_tab( apply_filters( 'sell_media_misc_tab', $misc_tab ) );

    /**
     * The following example shows you how to register theme options and assign them to tabs and sections:
     */
    $options = array(
        // General Tab
        'test_mode' => array(
            'tab' => 'sell_media_general_settings',
            'name' => 'test_mode',
            'title' => __( 'Mode','sell_media' ),
            'description' => sprintf( __( 'Select Live Mode to accept real transactions. Select Test Mode to accept test transactions (%s).', 'sell_media' ), "<a href='https://developer.paypal.com/' target='_blank'>PayPal sandbox account required</a>" ),
            'section' => 'general_plugin_section_1',
            'since' => '1.0',
            'id' => 'general_plugin_section_1',
            'type' => 'select',
            'default' => 1,
            'valid_options' => array(
                1 => array(
                    'name' => 1,
                    'title' => 'Test Mode',
                    ),
                0 => array(
                    'name' => 0,
                    'title' => 'Live Mode'
                    )
            )
        ),
        'checkout_page' => array(
            'tab' => 'sell_media_general_settings',
            'name' => 'checkout_page',
            'title' => __( 'Checkout Page','sell_media' ),
            'description' => __( 'Select the page that contains the checkout shortcode <code>[sell_media_checkout]</code>', 'sell_media' ),
            'section' => 'general_plugin_section_1',
            'since' => '1.0',
            'id' => 'general_plugin_section_1',
            'type' => 'select',
            'default' => '',
            'valid_options' => sell_media_pages_options()
        ),
        'thanks_page' => array(
            'tab' => 'sell_media_general_settings',
            'name' => 'thanks_page',
            'title' => __( 'Thanks Page','sell_media' ),
            'description' => __( 'Select the page that contains the thanks shortcode <code>[sell_media_thanks]</code>', 'sell_media' ),
            'section' => 'general_plugin_section_1',
            'since' => '1.0',
            'id' => 'general_plugin_section_1',
            'type' => 'select',
            'default' => '',
            'valid_options' => sell_media_pages_options()
        ),
        'dashboard_page' => array(
            'tab' => 'sell_media_general_settings',
            'name' => 'dashboard_page',
            'title' => __( 'Dashboard Page','sell_media' ),
            'description' => __( 'Select the page that contains the dashboard shortcode<code>[sell_media_download_list]</code>', 'sell_media' ),
            'section' => 'general_plugin_section_1',
            'since' => '1.0',
            'id' => 'general_plugin_section_1',
            'type' => 'select',
            'default' => '',
            'valid_options' => sell_media_pages_options()
        ),
        'login_page' => array(
            'tab' => 'sell_media_general_settings',
            'name' => 'login_page',
            'title' => __( 'Login Page','sell_media' ),
            'description' => __( 'Select the page that contains the login shortcode <code>[sell_media_login_form]</code>', 'sell_media' ),
            'section' => 'general_plugin_section_1',
            'since' => '1.0',
            'id' => 'general_plugin_section_1',
            'type' => 'select',
            'default' => '',
            'valid_options' => sell_media_pages_options()
        ),
        'search_page' => array(
            'tab' => 'sell_media_general_settings',
            'name' => 'search_page',
            'title' => __( 'Search Page','sell_media' ),
            'description' => __( 'Select the page that contains the search shortcode <code>[sell_media_searchform]</code>', 'sell_media' ),
            'section' => 'general_plugin_section_1',
            'since' => '1.0',
            'id' => 'general_plugin_section_1',
            'type' => 'select',
            'default' => '',
            'valid_options' => sell_media_pages_options()
        ),
        'lightbox_page' => array(
            'tab' => 'sell_media_general_settings',
            'name' => 'lightbox_page',
            'title' => __( 'Lightbox Page','sell_media' ),
            'description' => __( 'Select the page that contains the lightbox shortcode <code>[sell_media_lightbox]</code>', 'sell_media' ),
            'section' => 'general_plugin_section_1',
            'since' => '1.0',
            'id' => 'general_plugin_section_1',
            'type' => 'select',
            'default' => '',
            'valid_options' => sell_media_pages_options()
        ),
        'customer_notification' => array(
            'tab' => 'sell_media_general_settings',
            'name' => 'customer_notification',
            'title' => __( 'Customer Notification','sell_media' ),
            'description' => __( 'Notify the customer of their site registration.', 'sell_media' ),
            'section' => 'general_plugin_section_1',
            'since' => '1.0',
            'id' => 'general_plugin_section_1',
            'type' => 'select',
            'default' => 1,
            'valid_options' => array(
                0 => array(
                    'name' => 0,
                    'title' => __( 'No','sell_media' )
                    ),
                1 => array(
                    'name' => 1,
                    'title' => __( 'Yes','sell_media' ),
                    )
            )
        ),
        'style' => array(
            'tab' => 'sell_media_general_settings',
            'name' => 'style',
            'title' => __( 'Style','sell_media' ),
            'description' => __( 'Choose the style of your theme. Sell Media will load styles to match your theme.', 'sell_media' ),
            'section' => 'general_plugin_section_1',
            'since' => '1.0',
            'id' => 'general_plugin_section_1',
            'type' => 'select',
            'default' => 'light',
            'valid_options' => array(
                'dark' => array(
                    'name' => 'dark',
                    'title' => __( 'Dark','sell_media' )
                    ),
                'light' => array(
                    'name' => 'light',
                    'title' => __( 'Light','sell_media' )
                    )
            )
        ),
        'layout' => array(
            'tab' => 'sell_media_general_settings',
            'name' => 'layout',
            'title' => __( 'Layout','sell_media' ),
            'description' => __( "Select your layout preference for single Sell Media entries. If your theme already has a sidebar, you probably want to select the 'Single column' option.", 'sell_media' ),
            'section' => 'general_plugin_section_1',
            'since' => '1.0',
            'id' => 'general_plugin_section_1',
            'type' => 'select',
            'default' => 'two_col',
            'valid_options' => array(
                'two_col' => array(
                    'name' => 'sell-media-single-two-col',
                    'title' => 'Two columns',
                ),
                'one_col' => array(
                    'name' => 'sell-media-single-one-col',
                    'title' => 'One column',
                )
            )
        ),
        'breadcrumbs' => array(
            'tab' => 'sell_media_general_settings',
            'name' => 'breadcrumbs',
            'title' => __( 'Breadcrumbs','sell_media' ),
            'description' => __( 'Show breadcrumb navigation on single entries.', 'sell_media' ),
            'section' => 'general_plugin_section_1',
            'since' => '1.0',
            'id' => 'general_plugin_section_1',
            'type' => 'select',
            'default' => 1,
            'valid_options' => array(
                0 => array(
                    'name' => 0,
                    'title' => __( 'No','sell_media' )
                    ),
                1 => array(
                    'name' => 1,
                    'title' => __( 'Yes','sell_media' ),
                    )
            )
        ),
        'plugin_credit' => array(
            'tab' => 'sell_media_general_settings',
            'name' => 'plugin_credit',
            'title' => __( 'Plugin Credit','sell_media' ),
            'description' => __( 'Let your site visitors know you are using the Sell Media plugin?', 'sell_media' ),
            'section' => 'general_plugin_section_1',
            'since' => '1.0',
            'id' => 'general_plugin_section_1',
            'type' => 'select',
            'default' => 1,
            'valid_options' => array(
                0 => array(
                    'name' => 0,
                    'title' => __( 'No','sell_media' )
                    ),
                1 => array(
                    'name' => 1,
                    'title' => __( 'Yes','sell_media' ),
                    )
            )
        ),
        'post_type_slug' => array(
            'tab' => 'sell_media_general_settings',
            'name' => 'post_type_slug',
            'title' => __( 'Post Type Slug','sell_media' ),
            'description' => __( 'You can change the post type slug to: &quot;photos&quot; or &quot;downloads&quot;. The default slug is &quot;items&quot;.', 'sell_media' ),
            'section' => 'general_plugin_section_1',
            'since' => '1.0',
            'id' => 'general_plugin_section_1',
            'type' => 'text',
            'default' => '',
            'sanitize' => 'slug'
        ),
        'order_by' => array(
            'tab' => 'sell_media_general_settings',
            'name' => 'order_by',
            'title' => __( 'Order By','sell_media' ),
            'description' => __( 'Choose the order of items for the archive pages.', 'sell_media' ),
            'section' => 'general_plugin_section_1',
            'since' => '1.0',
            'id' => 'general_plugin_section_1',
            'type' => 'select',
            'default' => 'date-desc',
            'valid_options' => array(
                'date-desc' => array(
                    'name' => 'date-desc',
                    'title' => 'Date Desc'
                    ),
                'date-asc' => array(
                    'name' => 'date-asc',
                    'title' => 'Date ASC',
                    ),
                'title-asc' => array(
                    'name' => 'title-asc',
                    'title' => 'Title ASC',
                    ),
                'title-desc' => array(
                    'name' => 'title-desc',
                    'title' => 'Title Desc',
                    )
            )
        ),
        'terms_and_conditions' => array(
            'tab' => 'sell_media_general_settings',
            'name' => 'terms_and_conditions',
            'title' => __( 'Terms and Conditions','sell_media' ),
            'description' => __( 'These &quot;Terms and Conditions&quot; will show up on the checkout page. Users must agree to these terms before completing their purchase.', 'sell_media' ),
            'section' => 'general_plugin_section_1',
            'since' => '1.0',
            'id' => 'general_plugin_section_1',
            'sanitize' => 'html',
            'type' => 'textarea',
            'default' => ''
        ),
        'admin_columns' => array(
            'tab' => 'sell_media_general_settings',
            'name' => 'admin_columns',
            'title' => __( 'Show Columns','sell_media' ),
            'description' => __( 'Select the columns to show on the admin page &quot;All Products&quot; page.', 'sell_media' ),
            'section' => 'general_plugin_section_1',
            'since' => '1.0',
            'id' => 'general_plugin_section_1',
            'type' => 'checkbox',
            'default' => '',
            'valid_options' => array(
                'show_collection' => array(
                    'name' => 'show_collection',
                    'title' => 'Collections'
                    ),
                'show_license' => array(
                    'name' => 'show_license',
                    'title' => 'Licenses'
                    ),
                'show_keywords' => array(
                    'name' => 'show_keywords',
                    'title' => 'Keywords'
                    ),
                'show_creators' => array(
                    'name' => 'show_creators',
                    'title' => 'Creators'
                    )
                )
        ),

        // Size & Price
        'default_price' => array(
            'tab' => 'sell_media_size_settings',
            'name' => 'default_price',
            'title' => __( 'Original Price','sell_media' ),
            'description' => __( 'The original price of new items and bulk uploads. You can set unique prices by editing each individual item.', 'sell_media' ),
            'section' => 'size_price_plugin_section_1',
            'since' => '1.0',
            'id' => 'size_price_plugin_section_1',
            'default' => '1',
            'sanitize' => 'html',
            'type' => 'text'
            ),
        'hide_original_price' => array(
            'tab' => 'sell_media_size_settings',
            'name' => 'hide_original_price',
            'title' => __( 'Hide Original Price','sell_media' ),
            'description' => __( "Select 'Yes' to hide the original price above and rely solely on Price Groups (See below. Price Groups can only be used when selling images). Select 'No' if you're selling single file downloads or if you want to list the original price for each product. You can override this setting on a per-item basis.", 'sell_media' ),
            'section' => 'size_price_plugin_section_1',
            'since' => '1.0',
            'id' => 'size_price_plugin_section_1',
            'type' => 'select',
            'default' => 'no',
            'valid_options' => array(
                'no' => array(
                    'name' => 'no',
                    'title' => __( 'No','sell_media' )
                    ),
                'yes' => array(
                    'name' => 'yes',
                    'title' => __( 'Yes','sell_media' ),
                    )
            )
        ),
        'default_price_group' => array(
            'tab' => 'sell_media_size_settings',
            'name' => 'default_price_group',
            'title' => __( 'Default Image Price Group', 'sell_media' ),
            'description' => 'This is the default price group that will be assigned to all newly uploaded images for sale. You can override this setting on a per-item basis.',
            'id' => 'size_price_plugin_section_1',
            'section' => 'size_price_plugin_section_1',
            'type' => 'select',
            'default' => '',
            'valid_options' => sell_media_settings_price_group( 'price-group' )
            ),
        'price_group' => array(
            'tab' => 'sell_media_size_settings',
            'name' => 'price_group',
            'title' => __( 'Image Price Groups','sell_media' ),
            'default' => '',
            'description' => '',
            'id' => 'size_price_plugin_section_1',
            'section' => 'size_price_plugin_section_1',
            'type' => 'html',
            'valid_options' => sell_media_price_group_ui()
            ),

        // Payment Tab
        'paypal_email' => array(
            'tab' => 'sell_media_payment_settings',
            'name' => 'paypal_email',
            'title' => __( 'PayPal Email Address', 'sell_media' ),
            'description' => __( 'Add the email address associated with your PayPal account above.', 'sell_media' ),
            'default' => '',
            'section' => 'payment_section_1',
            'since' => '1.0',
            'default' => '',
            'id' => 'payment_section_1',
            'type' => 'text',
            'sanitize' => 'html'
            ),
        'currency' => array(
            'tab' => 'sell_media_payment_settings',
            'name' => 'currency',
            'title' => __( 'Currency','sell_media' ),
            'description' => __( 'The currency in which you accept payment.', 'sell_media' ),
            'section' => 'payment_section_1',
            'since' => '1.0.',
            'id' => 'payment_section_1',
            'type' => 'select',
            'default' => 'USD',
            'valid_options' => sell_media_currencies()
            ),
        'paypal_additional_test_email' => array(
            'tab' => 'sell_media_payment_settings',
            'name' => 'paypal_additional_test_email',
            'title' => __( 'PayPal Additional Test Emails','sell_media' ),
            'description' => __( 'This is useful when debugging PayPal. Enter a comma separated list of emails, and when a purchase is made the same email that is sent to the buyer will be sent to the recipients in the above list.','sell_media' ),
            'default' => '',
            'section' => 'payment_section_1',
            'since' => '1.0.',
            'id' => 'payment_section_1',
            'type' => 'text',
            'sanitize' => 'html'
        ),
        'tax' => array(
            'tab' => 'sell_media_payment_settings',
            'name' => 'tax',
            'title' => __( 'Tax','sell_media' ),
            'description' => 'Check to charge tax. You must set your tax rates below.',
            'section' => 'payment_section_1',
            'since' => '1.0',
            'id' => 'payment_section_1',
            'default' => '',
            'type' => 'checkbox',
            'valid_options' => array(
                'yes' => array(
                    'name' => 'yes',
                    'title' => __( 'Yes, charge tax','sell_media' )
                )
            )
        ),
        'tax_rate' => array(
            'tab' => 'sell_media_payment_settings',
            'name' => 'tax_rate',
            'title' => __( 'Tax Rate','sell_media' ),
            'description' => 'Set your tax rates. This tax rate will be applied to all cart orders (use .05 for 5 percent, .10 for 10 percent, etc)',
            'section' => 'payment_section_1',
            'since' => '1.0',
            'id' => 'payment_section_1',
            'default' => '',
            'type' => 'text',
            'sanitize' => 'html'
        ),

        // Email
        'from_name' => array(
            'tab' => 'email_plugin_tab',
            'name' => 'from_name',
            'title' => __( 'From Name','sell_media' ),
            'description' => __( 'The name associated with all outgoing email.','sell_media' ),
            'section' => 'email_section_1',
            'since' => '1.0.',
            'id' => 'email_section_1',
            'type' => 'text',
            'sanitize' => 'html',
            'default' => get_option( 'blogname' )
        ),
        'from_email' => array(
            'tab' => 'email_plugin_tab',
            'name' => 'from_email',
            'title' => __( 'From Email','sell_media' ),
            'description' => __( 'The email address used for all outgoing email.','sell_media' ),
            'section' => 'email_section_1',
            'since' => '1.0.',
            'id' => 'email_section_1',
            'type' => 'text',
            'sanitize' => 'html',
            'default' => get_option( 'admin_email' )
        ),
        'success_email_subject' => array(
            'tab' => 'email_plugin_tab',
            'name' => 'success_email_subject',
            'title' => __( 'Email Subject','sell_media' ),
            'description' => __( 'The email subject on successful purchase emails.','sell_media' ),
            'section' => 'email_section_1',
            'since' => '1.0.',
            'id' => 'email_section_1',
            'type' => 'text',
            'sanitize' => 'html',
            'default' => __( 'Your Purchase','sell_media' )
        ),
        'success_email_body' => array(
            'tab' => 'email_plugin_tab',
            'name' => 'success_email_body',
            'title' => __( 'Email Body','sell_media' ),
            'description' => __( "This e-mail message is sent to your customers in case of successful and cleared payment. You can use the following email tags: {first_name}, {last_name}, {payer_email}, {download_links}, {payment_id}. These tags are replaced with the real data associated with each payment. Be sure to include the {download_links} tag, otherwise your buyers won't receive their download purchases. The {payment_id} tag can be used to display an invoice number.",'sell_media' ),
            'section' => 'email_section_1',
            'since' => '1.0.',
            'id' => 'email_section_1',
            'type' => 'textarea',
            'sanitize' => 'html',
            'default' => "Hi {first_name} {last_name},\n\nThanks for purchasing from my site. Here are your download links:\n\n{download_links}\n\nThanks!"
        ),

        // Misc.
        'misc' => array(
            'tab' => 'misc_plugin_tab',
            'name' => 'misc',
            'title' => __( 'Settings for Extensions are shown below.','sell_media' ),
            'description' => '',
            'default' => '',
            'section' => 'misc_section_1',
            'since' => '1.0.',
            'id' => 'misc_section_1',
            'type' => 'html',
            'valid_options' => sprintf( "<a href='http://graphpaperpress.com/downloads/category/extensions/' class='button secondary' target='_blank'>%s</a>", __( 'Download Extensions for Sell Media','sell_media' ) )
        )
    );
    sell_media_register_plugin_options( apply_filters( 'sell_media_options', $options ) );

}
add_action( 'init', 'sell_media_init_settings' );