<?php


function sell_media_currencies(){
    $currencies = array(
    "USD" => array(
        'name' => 'USD',
        'title' => __('US Dollars ($)','sell_media')
        ),
    "EUR" => array(
        'name' => 'EUR',
        'title' => __('Euros (€)','sell_media')
        ),
    "GBP" => array(
        'name' => 'GBP',
        'title' => __('Pounds Sterling (£)','sell_media')
        ),
    "AUD" => array(
        'name' => 'AUD',
        'title' => __('Australian Dollars ($)','sell_media')
        ),
    "BRL" => array(
        'name' => 'BRL',
        'title' => __('Brazilian Real ($)','sell_media')
        ),
    "CAD" => array(
        'name' => 'CAD',
        'title' => __('Canadian Dollars ($)','sell_media')
        ),
    "CZK" => array(
        'name' => 'CZK',
        'title' => __('Czech Koruna (Kč)','sell_media')
        ),
    "DKK" => array(
        'name' => 'DKK',
        'title' => __('Danish Krone','sell_media')
        ),
    "HKD" => array(
        'name' => 'HKD',
        'title' => __('Hong Kong Dollar ($)','sell_media')
        ),
    "HUF" => array(
        'name' => 'HUF',
        'title' => __('Hungarian Forint','sell_media')
        ),
    "ILS" => array(
        'name' => 'ILS',
        'title' => __('Israeli Shekel','sell_media')
        ),
    "JPY" => array(
        'name' => 'JPY',
        'title' => __('Japanese Yen (¥)','sell_media')
        ),
    "MYR" => array(
        'name' => 'MYR',
        'title' => __('Malaysian Ringgits','sell_media')
        ),
    "MXN" => array(
        'name' => 'MXN',
        'title' => __('Mexican Peso ($)','sell_media')
        ),
    "NZD" => array(
        'name' => 'NZD',
        'title' => __('New Zealand Dollar ($)','sell_media')
        ),
    "NOK" => array(
        'name' => 'NOK',
        'title' => __('Norwegian Krone','sell_media')
        ),
    "PHP" => array(
        'name' => 'PHP',
        'title' => __('Philippine Pesos','sell_media')
        ),
    "PLN" => array(
        'name' => 'PLN',
        'title' => __('Polish Zloty','sell_media')
        ),
    "SGD" => array(
        'name' => 'SGD',
        'title' => __('Singapore Dollar ($)','sell_media')
        ),
    "SEK" => array(
        'name' => 'SEK',
        'title' => __('Swedish Krona','sell_media')
        ),
    "CHF" => array(
        'name' => 'CHF',
        'title' => __('Swiss Franc','sell_media')
        ),
    "TWD" => array(
        'name' => 'TWD',
        'title' => __('Taiwan New Dollars','sell_media')
        ),
    "THB" => array(
        'name' => 'THB',
        'title' => __('Thai Baht','sell_media')
        ),
    "TRY" => array(
        'name' => 'TRY',
        'title' => __('Turkish Lira (TL)','sell_media')
        ),
    "ZAR" => array(
        'name' => 'ZAR',
        'title' => __('South African rand (R)','sell_media')
        )
    );
    return $currencies;
}

/**
 * Define the Tabs appearing on the Theme Options page
 * Tabs contains sections
 * Options are assigned to both Tabs and Sections
 * See README.md for a full list of option types
 */

// General Tab
$general_settings_tab = array(
    "name" => "general_plugin_tab",
    "title" => __( "General", "sell_media" ),
    "sections" => array(
        "general_plugin_section_1" => array(
            "name" => "general_plugin_section_1",
            "title" => __( "General", "sell_media" ),
            "description" => __( "", "sell_media" )
        )
    )
);
sell_media_register_plugin_option_tab( $general_settings_tab );

// Size & Price Tab


// Payment Tab
$payment_tab = array(
    'name' => 'payment_plugin_tab',
    'title' => __('Payment','sell_media'),
    'sections' => array(
        'payment_section_1' => array(
            'name' => 'payment_section_1',
            'title' => __('Payment','sell_media'),
            'description' => __("", 'sell_media')
            )
        )
    );
sell_media_register_plugin_option_tab( $payment_tab );


// Email Tab
$email_tab = array(
    'name' => 'email_plugin_tab',
    'title' => __('Email','sell_media'),
    'sections' => array(
        'email_section_1' => array(
            'name' => 'email_section_1',
            'title' => __('Email Settings','sell_media'),
            'description' => __("", 'sell_media')
            )
        )
    );
sell_media_register_plugin_option_tab( $email_tab );


// Misc Tab
$misc_tab = array(
    'name' => 'misc_plugin_tab',
    'title' => __('Misc','sell_media'),
    'sections' => array(
        'misc_section_1' => array(
            'name' => 'misc_section_1',
            'title' => __('Misc','sell_media'),
            'description' => __("", 'sell_media')
            )
        )
    );
sell_media_register_plugin_option_tab( $misc_tab );


/**
 * The following example shows you how to register theme options and assign them to tabs and sections:
 */
$options = array(

    // General Tab
    'test_mode' => array(
        "tab" => "general_plugin_tab",
        "name" => "test_mode",
        "title" => __("Test Mode","sell_media"),
        "description" => sprintf( __('To accept real payments, select No. To fully use test mode, you must have %1$s.'), '<a href="https://developer.paypal.com/" target="_blank">Paypal sandbox (test) account</a>' ),
        "section" => "general_plugin_section_1",
        "since" => "1.0",
        "id" => "general_plugin_section_1",
        "type" => "select",
        "default" => 1,
        "valid_options" => array(
            1 => array(
                'name' => 1,
                'title' => 'Yes',
                ),
            0 => array(
                'name' => 0,
                'title' => 'No'
                )
        )
    ),
    'checkout_page' => array(
        "tab" => "general_plugin_tab",
        "name" => "checkout_page",
        "title" => __("Checkout Page","sell_media"),
        "description" => __( 'What page contains the <code>[sell_media_checkout]</code> shortcode? This shortcode generates the checkout cart.', 'sell_media' ),
        "section" => "general_plugin_section_1",
        "since" => "1.0",
        "id" => "general_plugin_section_1",
        "type" => "select",
        "default" => "none",
        "valid_options" => sell_media_pages_options()
    ),
    'thanks_page' => array(
        "tab" => "general_plugin_tab",
        "name" => "thanks_page",
        "title" => __("Thanks Page","sell_media"),
        "description" => __( 'What page contains the <code>[sell_media_thanks]</code> shortcode?', 'sell_media' ),
        "section" => "general_plugin_section_1",
        "since" => "1.0",
        "id" => "general_plugin_section_1",
        "type" => "select",
        "default" => "none",
        "valid_options" => sell_media_pages_options()
    ),
    'dashboard_page' => array(
        "tab" => "general_plugin_tab",
        "name" => "dashboard_page",
        "title" => __("Dashboard Page","sell_media"),
        "description" => __( 'Where is your customer Dashboard page? This page will contain the <code>[sell_media_download_list]</code> shortcode.', 'sell_media' ),
        "section" => "general_plugin_section_1",
        "since" => "1.0",
        "id" => "general_plugin_section_1",
        "type" => "select",
        "default" => "none",
        "valid_options" => sell_media_pages_options()
    ),
    'login_page' => array(
        "tab" => "general_plugin_tab",
        "name" => "login_page",
        "title" => __("Login Page","sell_media"),
        "description" => __( 'Where is your customer login page? This page will contain the <code>[sell_media_login_form]</code> shortcode.', 'sell_media' ),
        "section" => "general_plugin_section_1",
        "since" => "1.0",
        "id" => "general_plugin_section_1",
        "type" => "select",
        "default" => "none",
        "valid_options" => sell_media_pages_options()
    ),
    'customer_notification' => array(
        "tab" => "general_plugin_tab",
        "name" => "customer_notification",
        "title" => __("Customer Notification","sell_media"),
        "description" => __( 'Notify the customer of their site registration.', 'sell_media' ),
        "section" => "general_plugin_section_1",
        "since" => "1.0",
        "id" => "general_plugin_section_1",
        "type" => "select",
        "default" => 1,
        "valid_options" => array(
            0 => array(
                'name' => 0,
                'title' => 'No'
                ),
            1 => array(
                'name' => 1,
                'title' => 'Yes',
                )
        )
    ),
    'style' => array(
        "tab" => "general_plugin_tab",
        "name" => "style",
        "title" => __("Style","sell_media"),
        "description" => __( 'Choose the style of your theme. Sell Media will load styles to match your theme.', 'sell_media' ),
        "section" => "general_plugin_section_1",
        "since" => "1.0",
        "id" => "general_plugin_section_1",
        "type" => "select",
        "default" => "none",
        "valid_options" => array(
            'dark' => array(
                'name' => 'dark',
                'title' => 'Dark'
                ),
            'light' => array(
                'name' => 'light',
                'title' => 'Light',
                )
        )
    ),
    'plugin_credit' => array(
        "tab" => "general_plugin_tab",
        "name" => "plugin_credit",
        "title" => __("Plugin Credit","sell_media"),
        "description" => __( 'Let your site visitors know you are using the Sell Media plugin?', 'sell_media' ),
        "section" => "general_plugin_section_1",
        "since" => "1.0",
        "id" => "general_plugin_section_1",
        "type" => "select",
        "default" => 1,
        "valid_options" => array(
            0 => array(
                'name' => 0,
                'title' => 'No'
                ),
            1 => array(
                'name' => 1,
                'title' => 'Yes',
                )
        )
    ),
    'post_type_slug' => array(
        "tab" => "general_plugin_tab",
        "name" => "post_type_slug",
        "title" => __("Post Type Slug","sell_media"),
        "description" => __( 'You can change the post type slug to: &quot;photos&quot; or &quot;downloads&quot;. The default slug is &quot;items&quot;.', 'sell_media' ),
        "section" => "general_plugin_section_1",
        "since" => "1.0",
        "id" => "general_plugin_section_1",
        "type" => "text",
        "default" => "",
        "sanitize" => "html"
    ),
    'order_by' => array(
        "tab" => "general_plugin_tab",
        "name" => "order_by",
        "title" => __("Order By","sell_media"),
        "description" => __( 'Choose the order of items for the archive pages.', 'sell_media' ),
        "section" => "general_plugin_section_1",
        "since" => "1.0",
        "id" => "general_plugin_section_1",
        "type" => "select",
        "default" => "none",
        "valid_options" => array(
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
            'title-asc' => array(
                'name' => 'title-asc',
                'title' => 'Title ASC',
                )
        )
    ),
    'terms_and_conditions' => array(
        "tab" => "general_plugin_tab",
        "name" => "terms_and_conditions",
        "title" => __("Terms and Conditions","sell_media"),
        "description" => __( 'These "Terms and Conditions" will show up on the checkout page. Users must agree to these terms before completing their purchase.', 'sell_media' ),
        "section" => "general_plugin_section_1",
        "since" => "1.0",
        "id" => "general_plugin_section_1",
        "sanitize" => "html",
        "type" => "textarea",
        "default" => ""
    ),
    'disable_search' => array(
        "tab" => "general_plugin_tab",
        "name" => "disable_search",
        "title" => __("Disable Sell Media Search","sell_media"),
        "description" => __( 'Set this to "no" if you do not want to use the built in Sell Media search.', 'sell_media' ),
        "section" => "general_plugin_section_1",
        "since" => "1.0",
        "id" => "general_plugin_section_1",
        "type" => "select",
        "default" => 0,
        "valid_options" => array(
            0 => array(
                'name' => 0,
                'title' => 'No'
                ),
            1 => array(
                'name' => 1,
                'title' => 'Yes',
                )
        )
    ),
    'hide_original_price' => array(
        "tab" => "general_plugin_tab",
        "name" => "hide_original_price",
        "title" => __("Hide Original Price","sell_media"),
        "description" => __( 'You can also hide the original price by editing each individual item.', 'sell_media' ),
        "section" => "general_plugin_section_1",
        "since" => "1.0",
        "id" => "general_plugin_section_1",
        "type" => "select",
        "default" => 0,
        "valid_options" => array(
            0 => array(
                'name' => 0,
                'title' => 'No'
                ),
            1 => array(
                'name' => 1,
                'title' => 'Yes',
                )
        )
    ),
    'admin_columns' => array(
        "tab" => "general_plugin_tab",
        "name" => "admin_columns",
        "title" => __("Hide Original Price","sell_media"),
        "description" => __( 'Select the columns to show on the admin page "All Items" page.', 'sell_media' ),
        "section" => "general_plugin_section_1",
        "since" => "1.0",
        "id" => "general_plugin_section_1",
        "type" => "checkbox",
        "default" => 0,
        "valid_options" => array(
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


    // Payment Tab
    'default_payment' => array(
        'tab' => 'payment_plugin_tab',
        'name' => 'default_payment',
        'title' => __('Default Payment','sell_media'),
        'description' => '',
        'section' => 'payment_section_1',
        'since' => '1.0',
        'id' => 'payment_section_1',
        'type' => 'select',
        'valid_options' => array(
            array(
                'name' => 'paypal',
                'title' => __('PayPal','sell_media')
                )
            )
        // $gateways = array(
        //     array(
        //         'id' => 'paypal',
        //         'name' => __('Paypal','sell_media')
        //         )
        //     );
        // $gateways = apply_filters('sell_media_payment_gateway', $gateways);
        ),
    'paypal_email' => array(
        'tab' => 'payment_plugin_tab',
        'name' => 'paypal_email',
        'title' => __('PayPal Email Address', 'sell_media'),
        'description' => sprintf( __('The email address used to collect Paypal payments. %1$s: You must setup IPN Notifications in Paypal to process transactions. %2$s. Here is the listener URL you need to add in Paypal: %3$s'), '<strong>'.__('IMPORTANT', 'sell_media').'</strong>', '<a href="https://cms.paypal.com/us/cgi-bin/?cmd=_render-content&content_ID=developer/e_howto_admin_IPNSetup#id089EG030E5Z" target="_blank">Read Paypal instructions</a>', '<code>' . site_url( '?sell_media-listener=IPN' ) . '</code>'),
        'section' => 'payment_section_1',
        'since' => '1.0',
        'id' => 'payment_section_1',
        'type' => 'text'
        ),
    'currency' => array(
        'tab' => 'payment_plugin_tab',
        'name' => 'currency',
        'title' => __('Currency','sell_media'),
        'description' => __('The currency in which you accept payment.', 'sell_media'),
        'section' => 'payment_section_1',
        'since' => '1.0.',
        'id' => 'payment_section_1',
        'type' => 'select',
        'default' => 'USA',
        'valid_options' => sell_media_currencies()
        ),
    'paypal_additional_test_email' => array(
        'tab' => 'payment_plugin_tab',
        'name' => 'paypal_additional_test_email',
        'title' => __('Paypal Additional Test Emails','sell_media'),
        'description' => __('This is useful when debugging Paypal. Enter a comma separeted list of emails, and when a purchase is made the same email that is sent to the buyer will be sent to the recipients in the above list.','sell_media'),
        'section' => 'payment_section_1',
        'since' => '1.0.',
        'id' => 'payment_section_1',
        'type' => 'text'
    ),


    // Email
    'from_name' => array(
        'tab' => 'email_plugin_tab',
        'name' => 'from_name',
        'title' => __('From Name','sell_media'),
        'description' => __('The name associated with all outgoing email.','sell_media'),
        'section' => 'email_section_1',
        'since' => '1.0.',
        'id' => 'email_section_1',
        'type' => 'text'
    ),
    'from_email' => array(
        'tab' => 'email_plugin_tab',
        'name' => 'from_email',
        'title' => __('From Email','sell_media'),
        'description' => __('The email address used for all outgoing email.','sell_media'),
        'section' => 'email_section_1',
        'since' => '1.0.',
        'id' => 'email_section_1',
        'type' => 'text'
    ),
    'email_subject' => array(
        'tab' => 'email_plugin_tab',
        'name' => 'email_subject',
        'title' => __('Email Subject','sell_media'),
        'description' => __('The email subject on successful purchase emails.','sell_media'),
        'section' => 'email_section_1',
        'since' => '1.0.',
        'id' => 'email_section_1',
        'type' => 'text'
    ),
    'email_body' => array(
        'tab' => 'email_plugin_tab',
        'name' => 'email_body',
        'title' => __('Email Body','sell_media'),
        'description' => __("This e-mail message is sent to your customers in case of successful and cleared payment. You can use the following keywords: {first_name}, {last_name}, {payer_email}, {download_links}. Be sure to include the {download_links} tag, otherwise your buyers won't receive their download purchases.",'sell_media'),
        'section' => 'email_section_1',
        'since' => '1.0.',
        'id' => 'email_section_1',
        'type' => 'textarea',
        'sanitize' => 'html'
    ),


    // Misc.
    'misc' => array(
        'tab' => 'misc_plugin_tab',
        'name' => 'misc',
        'title' => __('Settings for Extensions are shown below.','sell_media'),
        'description' => '',
        'section' => 'misc_section_1',
        'since' => '1.0.',
        'id' => 'misc_section_1',
        'type' => 'html',
        'valid_options' => sprintf( '<a href="http://graphpaperpress.com/plugins/sell-media/downloads/category/extensions/" class="button secondary" target="_blank">%s</a>', __( 'Download Extensions for Sell Media','sell_media' ) )
    )
);

sell_media_register_plugin_options( $options );
