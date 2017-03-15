<?php

/**
 * Get list of taxonomies
 */
function sell_media_plugin_get_taxonomy_list( $taxonomy = 'category', $firstblank = false ) {

    $args = array(
        'hide_empty' => 0
    );

    $terms_obj = get_terms( $taxonomy, $args );
    $terms = array();
    if( $firstblank ) {
        $terms['']['name'] = '';
        $terms['']['title'] = __( '-- Choose One --', 'sell_media' );
    }
    foreach ( $terms_obj as $tt ) {
        $terms[ $tt->slug ]['name'] = $tt->slug;
        $terms[ $tt->slug ]['title'] = $tt->name;
    }

    return $terms;
}


/**
 * Get current settings page tab
 */
function sell_media_plugin_get_current_tab() {

    global $sell_media_plugin_tabs;

    $first_tab = $sell_media_plugin_tabs[0]['name'];

    if ( isset( $_GET['tab'] ) ) {
        $current = esc_attr( $_GET['tab'] );
    } else {
        $current = $first_tab;
    }

    return $current;
}


/**
 * Get current settings page tab
 */
function sell_media_plugin_get_current_tab_title( $tabval ) {

    global $sell_media_plugin_tabs;

    $current = $sell_media_plugin_tabs[ $tabval ]['title'];

    return $current;
}


/**
 * Define sell_media Admin Page Tab Markup
 *
 * @uses    sell_media_plugin_get_current_tab() defined in \functions\options.php
 * @uses    sell_media_get_settings_page_tabs() defined in \functions\options.php
 *
 * @link    http://www.onedesigns.com/tutorials/separate-multiple-theme-options-pages-using-tabs    Daniel Tara
 */
function sell_media_plugin_get_page_tab_markup() {

    global $sell_media_plugin_tabs;

    $page = 'sell_media_plugin_options';

    if ( isset( $_GET['page'] ) && 'sell-media-reference' == $_GET['page'] ) {
        $page = 'sell-media-reference';
    } else {
        // do nothing
    }

    $current = sell_media_plugin_get_current_tab();

    if ( 'sell_media_plugin_options' == $page ) {
        $tabs = $sell_media_plugin_tabs;
    } else if ( 'sell-media-reference' == $page ) {
        $tabs = sell_media_get_reference_page_tabs();
    }

    $links = array();
    $i = 0;
    foreach( $tabs as $tab ) {
        if( isset( $tab['name'] ) )
            $tabname = $tab['name'];
        if( isset( $tab['title'] ) )
            $tabtitle = $tab['title'];
        if ( $tabname == $current ) {
            $links[] = "<a class='nav-tab nav-tab-active' href='?post_type=sell_media_item&page=$page&tab=$tabname&i=$i'>$tabtitle</a>";
        } else {
            $links[] = "<a class='nav-tab' href='?post_type=sell_media_item&page=$page&tab=$tabname&i=$i'>$tabtitle</a>";
        }
        $i++;
    }
    echo '<h2 class="nav-tab-wrapper">';
    foreach ( $links as $link )
        echo $link;
    echo '</h2>';
}


/**
 * Callback function to return valid pages for selection in Settings
 */
function sell_media_pages_options() {
    $final_pages['none'] = array(
        'name' => 0,
        'title' => 'None'
        );
    foreach( get_pages() as $page ){
        $final_pages[ $page->ID ] = array(
            'name' => $page->ID,
            'title' => $page->post_title
            );
    }
    return $final_pages;
}


/**
 * Returns a formatted list of currencies to be used with the Sell Media settings
 */
function sell_media_currencies(){
    $currencies = array(
    "USD" => array(
        'name' => 'USD',
        'title' => __('US Dollars (&#36;)','sell_media'),
        'symbol' => "&#36;"
        ),
    "EUR" => array(
        'name' => 'EUR',
        'title' => __('Euros (&euro;)','sell_media'),
        'symbol' => "&euro;"
        ),
    "GBP" => array(
        'name' => 'GBP',
        'title' => __('Pounds Sterling (&pound;)','sell_media'),
        'symbol' => "&pound;"
        ),
    "AUD" => array(
        'name' => 'AUD',
        'title' => __('Australian Dollars (&#36;)','sell_media'),
        'symbol' => "&#36;"
        ),
    "BRL" => array(
        'name' => 'BRL',
        'title' => __('Brazilian Real (R&#36;)','sell_media'),
        'symbol' => "R&#36;"
        ),
    "CAD" => array(
        'name' => 'CAD',
        'title' => __('Canadian Dollars (&#36;)','sell_media'),
        'symbol' => "&#36;"
        ),
    "CZK" => array(
        'name' => 'CZK',
        'title' => __('Czech Koruna (&#75;&#269;)','sell_media'),
        'symbol' => "&#75;&#269;"
        ),
    "DKK" => array(
        'name' => 'DKK',
        'title' => __('Danish Krone','sell_media'),
        'symbol' => "DKK"
        ),
    "HKD" => array(
        'name' => 'HKD',
        'title' => __('Hong Kong Dollar (&#36;)','sell_media'),
        'symbol' => "&#36;"
        ),
    "HUF" => array(
        'name' => 'HUF',
        'title' => __('Hungarian Forint','sell_media'),
        'symbol' => "HUF"
        ),
    "ILS" => array(
        'name' => 'ILS',
        'title' => __('Israeli Shekel','sell_media'),
        'symbol' => "ILS"
        ),
    "JPY" => array(
        'name' => 'JPY',
        'title' => __('Japanese Yen (&yen;)','sell_media'),
        'symbol' => "&yen;"
        ),
    "MYR" => array(
        'name' => 'MYR',
        'title' => __('Malaysian Ringgits','sell_media'),
        'symbol' => "RM"
        ),
    "MXN" => array(
        'name' => 'MXN',
        'title' => __('Mexican Peso (&#36;)','sell_media'),
        'symbol' => "&#36;"
        ),
    "NZD" => array(
        'name' => 'NZD',
        'title' => __('New Zealand Dollar (&#36;)','sell_media'),
        'symbol' => "&#36;"
        ),
    "NOK" => array(
        'name' => 'NOK',
        'title' => __('Norwegian Krone','sell_media'),
        'symbol' => "kr"
        ),
    "PHP" => array(
        'name' => 'PHP',
        'title' => __('Philippine Pesos','sell_media'),
        'symbol' => "PHP"
        ),
    "PLN" => array(
        'name' => 'PLN',
        'title' => __('Polish Zloty','sell_media'),
        'symbol' => "PLN"
        ),
    "RUB" => array(
        'name' => 'RUB',
        'title' => __('Russian Ruble (&#x20bd;)','sell_media'),
        'symbol' => '&#x20bd;'
    ),
    "SGD" => array(
        'name' => 'SGD',
        'title' => __('Singapore Dollar (&#36;)','sell_media'),
        'symbol' => "&#36;"
        ),
    "SEK" => array(
        'name' => 'SEK',
        'title' => __('Swedish Krona','sell_media'),
        'symbol' => "SEK"
        ),
    "CHF" => array(
        'name' => 'CHF',
        'title' => __('Swiss Franc','sell_media'),
        'symbol' => "CHF"
        ),
    "TWD" => array(
        'name' => 'TWD',
        'title' => __('Taiwan New Dollars','sell_media'),
        'symbol' => "TWD"
        ),
    "THB" => array(
        'name' => 'THB',
        'title' => __('Thai Baht','sell_media'),
        'symbol' => "THB"
        ),
    "TRY" => array(
        'name' => 'TRY',
        'title' => __('Turkish Lira (TL)','sell_media'),
        'symbol' => "TL"
        ),
    "ZAR" => array(
        'name' => 'ZAR',
        'title' => __('South African rand (R)','sell_media'),
        'symbol' => "R"
        )
    );
    return apply_filters( 'sell_media_currencies', $currencies );
}


/**
 * Returns a formatted array of price groups for the sell media settings
 */
function sell_media_settings_price_group( $taxonomy=null ){
    $array[] = array(
        'name' => 0,
        'title' => __('None','sell_media')
        );

    $terms = get_terms( $taxonomy, array('hide_empty'=>false, 'parent'=>0) );

    if ( ! empty( $terms ) && ! is_wp_error( $terms ) ){
        foreach( $terms as $term ) {
            $array[ $term->term_id ] = array(
                'name' => $term->term_id,
                'title' => $term->name
                );
        }
    }

    return $array;
}

/**
 * Returns the payment gateways for the sell media settings
 */
function sell_media_settings_payment_gateway(){
    $gateways = array(
        array(
            'name' => 'PayPal',
            'title' => __('PayPal','sell_media')
            )
        );

    return apply_filters('sell_media_payment_gateway', $gateways);
}
