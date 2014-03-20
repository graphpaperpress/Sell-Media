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
 * @uses	sell_media_plugin_get_current_tab()	defined in \functions\options.php
 * @uses	sell_media_get_settings_page_tabs()	defined in \functions\options.php
 *
 * @link	http://www.onedesigns.com/tutorials/separate-multiple-theme-options-pages-using-tabs	Daniel Tara
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
    sell_media_plugin_utility_links();
    echo '<div id="icon-themes" class="icon32"><br /></div>';
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
        'title' => __('US Dollars (&#36;)','sell_media')
        ),
    "EUR" => array(
        'name' => 'EUR',
        'title' => __('Euros (&euro;)','sell_media')
        ),
    "GBP" => array(
        'name' => 'GBP',
        'title' => __('Pounds Sterling (&pound;)','sell_media')
        ),
    "AUD" => array(
        'name' => 'AUD',
        'title' => __('Australian Dollars (&#36;)','sell_media')
        ),
    "BRL" => array(
        'name' => 'BRL',
        'title' => __('Brazilian Real (R&#36;)','sell_media')
        ),
    "CAD" => array(
        'name' => 'CAD',
        'title' => __('Canadian Dollars (&#36;)','sell_media')
        ),
    "CZK" => array(
        'name' => 'CZK',
        'title' => __('Czech Koruna (&#75;&#269;)','sell_media')
        ),
    "DKK" => array(
        'name' => 'DKK',
        'title' => __('Danish Krone','sell_media')
        ),
    "HKD" => array(
        'name' => 'HKD',
        'title' => __('Hong Kong Dollar (&#36;)','sell_media')
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
        'title' => __('Japanese Yen (&yen;)','sell_media')
        ),
    "MYR" => array(
        'name' => 'MYR',
        'title' => __('Malaysian Ringgits','sell_media')
        ),
    "MXN" => array(
        'name' => 'MXN',
        'title' => __('Mexican Peso (&#36;)','sell_media')
        ),
    "NZD" => array(
        'name' => 'NZD',
        'title' => __('New Zealand Dollar (&#36;)','sell_media')
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
        'title' => __('Singapore Dollar (&#36;)','sell_media')
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

/**
 * Price Groups
 */
function sell_media_price_group_ui(){
    include_once( SELL_MEDIA_PLUGIN_DIR . '/inc/admin-price-groups.php' );
    // Since the nav style ui prints output we suppress it and
    // assign it to a variable.
    ob_start();
    $price_group = New SellMediaNavStyleUI();
    $price_group->taxonomy = 'price-group';
    $price_group->setting_ui();
    $price_group_ui = ob_get_contents();
    ob_end_clean();
    return $price_group_ui;
}