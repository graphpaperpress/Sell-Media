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
 * Returns a formatted array of price groups for the sell media settings
 */
function sell_media_settings_price_group( $taxonomy=null ){
    $array[] = array(
        'name' => 0,
        'title' => __('None','sell_media')
        );
    foreach( get_terms( $taxonomy ,array('hide_empty'=>false, 'parent'=>0)) as $term ) {
        $array[ $term->term_id ] = array(
            'name' => $term->term_id,
            'title' => $term->name
            );
    }

    return $array;
}


function sell_media_settings_payment_gateway(){
    $gateways = array(
        array(
            'name' => 'paypal',
            'title' => __('PayPal','sell_media')
            )
        );

    return apply_filters('sell_media_payment_gateway', $gateways);
}


function sell_media_price_group_ui(){
    include_once(plugin_dir_path( dirname( dirname( __FILE__ ) ) ).'inc/admin-price-groups.php');
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


function sell_media_countries_list(){
    $items = array(
        "US" => array(
            "name" => "US",
            "title" => __("United States","sell_media")
            ),
        "AF" => array(
            "name" => "AF",
            "title" => __("Afghanistan","sell_media")
            ),
        "AX" => array(
            "name" => "AX",
            "title" => __("Åland Islands","sell_media")
            ),
        "AL" => array(
            "name" => "AL",
            "title" => __("Albania","sell_media")
            ),
        "DZ" => array(
            "name" => "DZ",
            "title" => __("Algeria","sell_media")
            ),
        "AS" => array(
            "name" => "AS",
            "title" => __("American Samoa","sell_media")
            ),
        "AD" => array(
            "name" => "AD",
            "title" => __("Andorra","sell_media")
            ),
        "AO" => array(
            "name" => "AO",
            "title" => __("Angola","sell_media")
            ),
        "AI" => array(
            "name" => "AI",
            "title" => __("Anguilla","sell_media")
            ),
        "AQ" => array(
            "name" => "AQ",
            "title" => __("Antarctica","sell_media")
            ),
        "AG" => array(
            "name" => "AG",
            "title" => __("Antigua and Barbuda","sell_media")
            ),
        "AR" => array(
            "name" => "AR",
            "title" => __("Argentina","sell_media")
            ),
        "AM" => array(
            "name" => "AM",
            "title" => __("Armenia","sell_media")
            ),
        "AW" => array(
            "name" => "AW",
            "title" => __("Aruba","sell_media")
            ),
        "AU" => array(
            "name" => "AU",
            "title" => __("Australia","sell_media")
            ),
        "AT" => array(
            "name" => "AT",
            "title" => __("Austria","sell_media")
            ),
        "AZ" => array(
            "name" => "AZ",
            "title" => __("Azerbaijan, Republic of","sell_media")
            ),
        "BS" => array(
            "name" => "BS",
            "title" => __("Bahamas","sell_media")
            ),
        "BH" => array(
            "name" => "BH",
            "title" => __("Bahrain","sell_media")
            ),
        "BD" => array(
            "name" => "BD",
            "title" => __("Bangladesh","sell_media")
            ),
        "BB" => array(
            "name" => "BB",
            "title" => __("Barbados","sell_media")
            ),
        "BY" => array(
            "name" => "BY",
            "title" => __("Belarus","sell_media")
            ),
        "BE" => array(
            "name" => "BE",
            "title" => __("Belgium","sell_media")
            ),
        "BZ" => array(
            "name" => "BZ",
            "title" => __("Belize","sell_media")
            ),
        "BJ" => array(
            "name" => "BJ",
            "title" => __("Benin","sell_media")
            ),
        "BM" => array(
            "name" => "BM",
            "title" => __("Bermuda","sell_media")
            ),
        "BT" => array(
            "name" => "BT",
            "title" => __("Bhutan","sell_media")
            ),
        "BO" => array(
            "name" => "BO",
            "title" => __("Bolivia, Plurinational State of","sell_media")
            ),
        "BQ" => array(
            "name" => "BQ",
            "title" => __("Bonaire, Sint Eustatius and Saba","sell_media")
            ),
        "BA" => array(
            "name" => "BA",
            "title" => __("Bosnia and Herzegovina","sell_media")
            ),
        "BW" => array(
            "name" => "BW",
            "title" => __("Botswana","sell_media")
            ),
        "BV" => array(
            "name" => "BV",
            "title" => __("Bouvet Island","sell_media")
            ),
        "BR" => array(
            "name" => "BR",
            "title" => __("Brazil","sell_media")
            ),
        "IO" => array(
            "name" => "IO",
            "title" => __("British Indian Ocean Territory","sell_media")
            ),
        "BN" => array(
            "name" => "BN",
            "title" => __("Brunei Darussalam","sell_media")
            ),
        "BG" => array(
            "name" => "BG",
            "title" => __("Bulgaria","sell_media")
            ),
        "BF" => array(
            "name" => "BF",
            "title" => __("Burkina Faso","sell_media")
            ),
        "BI" => array(
            "name" => "BI",
            "title" => __("Burundi","sell_media")
            ),
        "KH" => array(
            "name" => "KH",
            "title" => __("Cambodia","sell_media")
            ),
        "CM" => array(
            "name" => "CM",
            "title" => __("Cameroon","sell_media")
            ),
        "CA" => array(
            "name" => "CA",
            "title" => __("Canada","sell_media")
            ),
        "CV" => array(
            "name" => "CV",
            "title" => __("Cape Verde","sell_media")
            ),
        "KY" => array(
            "name" => "KY",
            "title" => __("Cayman Islands","sell_media")
            ),
        "CF" => array(
            "name" => "CF",
            "title" => __("Central African Republic","sell_media")
            ),
        "TD" => array(
            "name" => "TD",
            "title" => __("Chad","sell_media")
            ),
        "CL" => array(
            "name" => "CL",
            "title" => __("Chile","sell_media")
            ),
        "CN" => array(
            "name" => "CN",
            "title" => __("China","sell_media")
            ),
        "CX" => array(
            "name" => "CX",
            "title" => __("Christmas Island","sell_media")
            ),
        "CC" => array(
            "name" => "CC",
            "title" => __("Cocos (Keeling) Islands","sell_media")
            ),
        "CO" => array(
            "name" => "CO",
            "title" => __("Colombia","sell_media")
            ),
        "KM" => array(
            "name" => "KM",
            "title" => __("Comoros","sell_media")
            ),
        "CG" => array(
            "name" => "CG",
            "title" => __("Congo","sell_media")
            ),
        "CD" => array(
            "name" => "CD",
            "title" => __("Congo, the Democratic Republic of the","sell_media")
            ),
        "CK" => array(
            "name" => "CK",
            "title" => __("Cook Islands","sell_media")
            ),
        "CR" => array(
            "name" => "CR",
            "title" => __("Costa Rica","sell_media")
            ),
        "CI" => array(
            "name" => "CI",
            "title" => __("Côte d'Ivoire","sell_media")
            ),
        "HR" => array(
            "name" => "HR",
            "title" => __("Croatia","sell_media")
            ),
        "CU" => array(
            "name" => "CU",
            "title" => __("Cuba","sell_media")
            ),
        "CW" => array(
            "name" => "CW",
            "title" => __("Curaçao","sell_media")
            ),
        "CY" => array(
            "name" => "CY",
            "title" => __("Cyprus","sell_media")
            ),
        "CZ" => array(
            "name" => "CZ",
            "title" => __("Czech Republic","sell_media")
            ),
        "DK" => array(
            "name" => "DK",
            "title" => __("Denmark","sell_media")
            ),
        "DJ" => array(
            "name" => "DJ",
            "title" => __("Djibouti","sell_media")
            ),
        "DM" => array(
            "name" => "DM",
            "title" => __("Dominica","sell_media")
            ),
        "DO" => array(
            "name" => "DO",
            "title" => __("Dominican Republic","sell_media")
            ),
        "EC" => array(
            "name" => "EC",
            "title" => __("Ecuador","sell_media")
            ),
        "EG" => array(
            "name" => "EG",
            "title" => __("Egypt","sell_media")
            ),
        "SV" => array(
            "name" => "SV",
            "title" => __("El Salvador","sell_media")
            ),
        "GQ" => array(
            "name" => "GQ",
            "title" => __("Equatorial Guinea","sell_media")
            ),
        "ER" => array(
            "name" => "ER",
            "title" => __("Eritrea","sell_media")
            ),
        "EE" => array(
            "name" => "EE",
            "title" => __("Estonia","sell_media")
            ),
        "ET" => array(
            "name" => "ET",
            "title" => __("Ethiopia","sell_media")
            ),
        "FK" => array(
            "name" => "FK",
            "title" => __("Falkland Islands (Malvinas)","sell_media")
            ),
        "FO" => array(
            "name" => "FO",
            "title" => __("Faroe Islands","sell_media")
            ),
        "FJ" => array(
            "name" => "FJ",
            "title" => __("Fiji","sell_media")
            ),
        "FI" => array(
            "name" => "FI",
            "title" => __("Finland","sell_media")
            ),
        "FR" => array(
            "name" => "FR",
            "title" => __("France","sell_media")
            ),
        "GF" => array(
            "name" => "GF",
            "title" => __("French Guiana","sell_media")
            ),
        "PF" => array(
            "name" => "PF",
            "title" => __("French Polynesia","sell_media")
            ),
        "TF" => array(
            "name" => "TF",
            "title" => __("French Southern Territories","sell_media")
            ),
        "GA" => array(
            "name" => "GA",
            "title" => __("Gabon","sell_media")
            ),
        "GM" => array(
            "name" => "GM",
            "title" => __("Gambia","sell_media")
            ),
        "GE" => array(
            "name" => "GE",
            "title" => __("Georgia","sell_media")
            ),
        "DE" => array(
            "name" => "DE",
            "title" => __("Germany","sell_media")
            ),
        "GH" => array(
            "name" => "GH",
            "title" => __("Ghana","sell_media")
            ),
        "GI" => array(
            "name" => "GI",
            "title" => __("Gibraltar","sell_media")
            ),
        "GR" => array(
            "name" => "GR",
            "title" => __("Greece","sell_media")
            ),
        "GL" => array(
            "name" => "GL",
            "title" => __("Greenland","sell_media")
            ),
        "GD" => array(
            "name" => "GD",
            "title" => __("Grenada","sell_media")
            ),
        "GP" => array(
            "name" => "GP",
            "title" => __("Guadeloupe","sell_media")
            ),
        "GU" => array(
            "name" => "GU",
            "title" => __("Guam","sell_media")
            ),
        "GT" => array(
            "name" => "GT",
            "title" => __("Guatemala","sell_media")
            ),
        "GG" => array(
            "name" => "GG",
            "title" => __("Guernsey","sell_media")
            ),
        "GN" => array(
            "name" => "GN",
            "title" => __("Guinea","sell_media")
            ),
        "GW" => array(
            "name" => "GW",
            "title" => __("Guinea-Bissau","sell_media")
            ),
        "GY" => array(
            "name" => "GY",
            "title" => __("Guyana","sell_media")
            ),
        "HT" => array(
            "name" => "HT",
            "title" => __("Haiti","sell_media")
            ),
        "HM" => array(
            "name" => "HM",
            "title" => __("Heard Island and McDonald Islands","sell_media")
            ),
        "VA" => array(
            "name" => "VA",
            "title" => __("Holy See (Vatican City State)","sell_media")
            ),
        "HN" => array(
            "name" => "HN",
            "title" => __("Honduras","sell_media")
            ),
        "HK" => array(
            "name" => "HK",
            "title" => __("Hong Kong","sell_media")
            ),
        "HU" => array(
            "name" => "HU",
            "title" => __("Hungary","sell_media")
            ),
        "IS" => array(
            "name" => "IS",
            "title" => __("Iceland","sell_media")
            ),
        "IN" => array(
            "name" => "IN",
            "title" => __("India","sell_media")
            ),
        "ID" => array(
            "name" => "ID",
            "title" => __("Indonesia","sell_media")
            ),
        "IR" => array(
            "name" => "IR",
            "title" => __("Iran, Islamic Republic of","sell_media")
            ),
        "IQ" => array(
            "name" => "IQ",
            "title" => __("Iraq","sell_media")
            ),
        "IE" => array(
            "name" => "IE",
            "title" => __("Ireland","sell_media")
            ),
        "IM" => array(
            "name" => "IM",
            "title" => __("Isle of Man","sell_media")
            ),
        "IL" => array(
            "name" => "IL",
            "title" => __("Israel","sell_media")
            ),
        "IT" => array(
            "name" => "IT",
            "title" => __("Italy","sell_media")
            ),
        "JM" => array(
            "name" => "JM",
            "title" => __("Jamaica","sell_media")
            ),
        "JP" => array(
            "name" => "JP",
            "title" => __("Japan","sell_media")
            ),
        "JE" => array(
            "name" => "JE",
            "title" => __("Jersey","sell_media")
            ),
        "JO" => array(
            "name" => "JO",
            "title" => __("Jordan","sell_media")
            ),
        "KZ" => array(
            "name" => "KZ",
            "title" => __("Kazakhstan","sell_media")
            ),
        "KE" => array(
            "name" => "KE",
            "title" => __("Kenya","sell_media")
            ),
        "KI" => array(
            "name" => "KI",
            "title" => __("Kiribati","sell_media")
            ),
        "KP" => array(
            "name" => "KP",
            "title" => __("Korea, Democratic People's Republic of","sell_media")
            ),
        "KR" => array(
            "name" => "KR",
            "title" => __("Korea, Republic of","sell_media")
            ),
        "KW" => array(
            "name" => "KW",
            "title" => __("Kuwait","sell_media")
            ),
        "KG" => array(
            "name" => "KG",
            "title" => __("Kyrgyzstan","sell_media")
            ),
        "LA" => array(
            "name" => "LA",
            "title" => __("Lao People's Democratic Republic","sell_media")
            ),
        "LV" => array(
            "name" => "LV",
            "title" => __("Latvia","sell_media")
            ),
        "LB" => array(
            "name" => "LB",
            "title" => __("Lebanon","sell_media")
        ),
        "LS" => array(
            "name" => "LS",
            "title" => __("Lesotho","sell_media")
            ),
        "LR" => array(
            "name" => "LR",
            "title" => __("Liberia","sell_media")
        ),
        "LY" => array(
            "name" => "LY",
            "title" => __("Libyan Arab Jamahiriya","sell_media")
            ),
        "LI" => array(
            "name" => "LI",
            "title" => __("Liechtenstein","sell_media")
        ),
        "LT" => array(
            "name" => "LT",
            "title" => __("Lithuania","sell_media")
            ),
        "LU" => array(
            "name" => "LU",
            "title" => __("Luxembourg","sell_media")
        ),
        "MO" => array(
            "name" => "MO",
            "title" => __("Macao","sell_media")
        ),
        "MK" => array(
            "name" => "MK",
            "title" => __("Macedonia, the former Yugoslav Republic of","sell_media")
        ),
        "MG" => array(
            "name" => "MG",
            "title" => __("Madagascar","sell_media")
        ),
        "MW" => array(
            "name" => "MW",
            "title" => __("Malawi","sell_media")
        ),
        "MY" => array(
            "name" => "MY",
            "title" => __("Malaysia","sell_media")
        ),
        "MV" => array(
            "name" => "MV",
            "title" => __("Maldives","sell_media")
        ),
        "ML" => array(
            "name" => "ML",
            "title" => __("Mali","sell_media")
        ),
        "MT" => array(
            "name" => "MT",
            "title" => __("Malta","sell_media")
        ),
        "MH" => array(
            "name" => "MH",
            "title" => __("Marshall Islands","sell_media")
        ),
        "MQ" => array(
            "name" => "MQ",
            "title" => __("Martinique","sell_media")
        ),
        "MR" => array(
            "name" => "MR",
            "title" => __("Mauritania","sell_media")
        ),
        "MU" => array(
            "name" => "MU",
            "title" => __("Mauritius","sell_media")
        ),
        "YT" => array(
            "name" => "YT",
            "title" => __("Mayotte","sell_media")
        ),
        "MX" => array(
            "name" => "MX",
            "title" => __("Mexico","sell_media")
        ),
        "FM" => array(
            "name" => "FM",
            "title" => __("Micronesia, Federated States of","sell_media")
        ),
        "MD" => array(
            "name" => "MD",
            "title" => __("Moldova, Republic of","sell_media")
        ),
        "MC" => array(
            "name" => "MC",
            "title" => __("Monaco","sell_media")
        ),
        "MN" => array(
            "name" => "MN",
            "title" => __("Mongolia","sell_media")
        ),
        "ME" => array(
            "name" => "ME",
            "title" => __("Montenegro","sell_media")
        ),
        "MS" => array(
            "name" => "MS",
            "title" => __("Montserrat","sell_media")
        ),
        "MA" => array(
            "name" => "MA",
            "title" => __("Morocco","sell_media")
        ),
        "MZ" => array(
            "name" => "MZ",
            "title" => __("Mozambique","sell_media")
        ),
        "MM" => array(
            "name" => "MM",
            "title" => __("Myanmar","sell_media")
        ),
        "NA" => array(
            "name" => "NA",
            "title" => __("Namibia","sell_media")
        ),
        "NR" => array(
            "name" => "NR",
            "title" => __("Nauru","sell_media")
        ),
        "NP" => array(
            "name" => "NP",
            "title" => __("Nepal","sell_media")
        ),
        "NL" => array(
            "name" => "NL",
            "title" => __("Netherlands","sell_media")
        ),
        "NC" => array(
            "name" => "NC",
            "title" => __("New Caledonia","sell_media")
        ),
        "NZ" => array(
            "name" => "NZ",
            "title" => __("New Zealand","sell_media")
        ),
        "NI" => array(
            "name" => "NI",
            "title" => __("Nicaragua","sell_media")
        ),
        "NE" => array(
            "name" => "NE",
            "title" => __("Niger","sell_media")
        ),
        "NG" => array(
            "name" => "NG",
            "title" => __("Nigeria","sell_media")
        ),
        "NU" => array(
            "name" => "NU",
            "title" => __("Niue","sell_media")
        ),
        "NF" => array(
            "name" => "NF",
            "title" => __("Norfolk Island","sell_media")
        ),
        "MP" => array(
            "name" => "MP",
            "title" => __("Northern Mariana Islands","sell_media")
        ),
        "NO" => array(
            "name" => "NO",
            "title" => __("Norway","sell_media")
        ),
        "OM" => array(
            "name" => "OM",
            "title" => __("Oman","sell_media")
        ),
        "PK" => array(
            "name" => "PK",
            "title" => __("Pakistan","sell_media")
        ),
        "PW" => array(
            "name" => "PW",
            "title" => __("Palau","sell_media")
        ),
        "PS" => array(
            "name" => "PS",
            "title" => __("Palestinian Territory, Occupied","sell_media")
        ),
        "PA" => array(
            "name" => "PA",
            "title" => __("Panama","sell_media")
        ),
        "PG" => array(
            "name" => "PG",
            "title" => __("Papua New Guinea","sell_media")
        ),
        "PY" => array(
            "name" => "PY",
            "title" => __("Paraguay","sell_media")
        ),
        "PE" => array(
            "name" => "PE",
            "title" => __("Peru","sell_media")
        ),
        "PH" => array(
            "name" => "PH",
            "title" => __("Philippines","sell_media")
        ),
        "PN" => array(
            "name" => "PN",
            "title" => __("Pitcairn","sell_media")
        ),
        "PL" => array(
            "name" => "PL",
            "title" => __("Poland","sell_media")
        ),
        "PT" => array(
            "name" => "PT",
            "title" => __("Portugal","sell_media")
        ),
        "PR" => array(
            "name" => "PR",
            "title" => __("Puerto Rico","sell_media")
        ),
        "QA" => array(
            "name" => "QA",
            "title" => __("Qatar","sell_media")
        ),
        "RE" => array(
            "name" => "RE",
            "title" => __("Réunion","sell_media")
        ),
        "RO" => array(
            "name" => "RO",
            "title" => __("Romania","sell_media")
        ),
        "RU" => array(
            "name" => "RU",
            "title" => __("Russian Federation","sell_media")
        ),
        "RW" => array(
            "name" => "RW",
            "title" => __("Rwanda","sell_media")
        ),
        "BL" => array(
            "name" => "BL",
            "title" => __("Saint Barthélemy","sell_media")
        ),
        "SH" => array(
            "name" => "SH",
            "title" => __("Saint Helena, Ascension and Tristan da Cunha","sell_media")
        ),
        "KN" => array(
            "name" => "KN",
            "title" => __("Saint Kitts and Nevis","sell_media")
        ),
        "LC" => array(
            "name" => "LC",
            "title" => __("Saint Lucia","sell_media")
        ),
        "MF" => array(
            "name" => "MF",
            "title" => __("Saint Martin (French part)","sell_media")
        ),
        "PM" => array(
            "name" => "PM",
            "title" => __("Saint Pierre and Miquelon","sell_media")
        ),
        "VC" => array(
            "name" => "VC",
            "title" => __("Saint Vincent and the Grenadines","sell_media")
        ),
        "WS" => array(
            "name" => "WS",
            "title" => __("Samoa","sell_media")
        ),
        "SM" => array(
            "name" => "SM",
            "title" => __("San Marino","sell_media")
        ),
        "ST" => array(
            "name" => "ST",
            "title" => __("Sao Tome and Principe","sell_media")
        ),
        "SA" => array(
            "name" => "SA",
            "title" => __("Saudi Arabia","sell_media")
        ),
        "SN" => array(
            "name" => "SN",
            "title" => __("Senegal","sell_media")
        ),
        "RS" => array(
            "name" => "RS",
            "title" => __("Serbia","sell_media")
        ),
        "SC" => array(
            "name" => "SC",
            "title" => __("Seychelles","sell_media")
        ),
        "SL" => array(
            "name" => "SL",
            "title" => __("Sierra Leone","sell_media")
        ),
        "SG" => array(
            "name" => "SG",
            "title" => __("Singapore","sell_media")
        ),
        "SX" => array(
            "name" => "SX",
            "title" => __("Sint Maarten (Dutch part)","sell_media")
        ),
        "SK" => array(
            "name" => "SK",
            "title" => __("Slovakia","sell_media")
        ),
        "SI" => array(
            "name" => "SI",
            "title" => __("Slovenia","sell_media")
        ),
        "SB" => array(
            "name" => "SB",
            "title" => __("Solomon Islands","sell_media")
        ),
        "SO" => array(
            "name" => "SO",
            "title" => __("Somalia","sell_media")
        ),
        "ZA" => array(
            "name" => "ZA",
            "title" => __("South Africa","sell_media")
        ),
        "GS" => array(
            "name" => "GS",
            "title" => __("South Georgia and the South Sandwich Islands","sell_media")
        ),
        "SS" => array(
            "name" => "SS",
            "title" => __("South Sudan","sell_media")
        ),
        "ES" => array(
            "name" => "ES",
            "title" => __("Spain","sell_media")
        ),
        "LK" => array(
            "name" => "LK",
            "title" => __("Sri Lanka","sell_media")
        ),
        "SD" => array(
            "name" => "SD",
            "title" => __("Sudan","sell_media")
        ),
        "SR" => array(
            "name" => "SR",
            "title" => __("Suriname","sell_media")
        ),
        "SJ" => array(
            "name" => "SJ",
            "title" => __("Svalbard and Jan Mayen","sell_media")
        ),
        "SZ" => array(
            "name" => "SZ",
            "title" => __("Swaziland","sell_media")
        ),
        "SE" => array(
            "name" => "SE",
            "title" => __("Sweden","sell_media")
        ),
        "CH" => array(
            "name" => "CH",
            "title" => __("Switzerland","sell_media")
        ),
        "SY" => array(
            "name" => "SY",
            "title" => __("Syrian Arab Republic","sell_media")
        ),
        "TW" => array(
            "name" => "TW",
            "title" => __("Taiwan, Province of China","sell_media")
        ),
        "TJ" => array(
            "name" => "TJ",
            "title" => __("Tajikistan","sell_media")
        ),
        "TZ" => array(
            "name" => "TZ",
            "title" => __("Tanzania, United Republic of","sell_media")
        ),
        "TH" => array(
            "name" => "TH",
            "title" => __("Thailand","sell_media")
        ),
        "TL" => array(
            "name" => "TL",
            "title" => __("Timor-Leste","sell_media")
        ),
        "TG" => array(
            "name" => "TG",
            "title" => __("Togo","sell_media")
        ),
        "TK" => array(
            "name" => "TK",
            "title" => __("Tokelau","sell_media")
        ),
        "TO" => array(
            "name" => "TO",
            "title" => __("Tonga","sell_media")
        ),
        "TT" => array(
            "name" => "TT",
            "title" => __("Trinidad and Tobago","sell_media")
        ),
        "TN" => array(
            "name" => "TN",
            "title" => __("Tunisia","sell_media")
        ),
        "TR" => array(
            "name" => "TR",
            "title" => __("Turkey","sell_media")
        ),
        "TM" => array(
            "name" => "TM",
            "title" => __("Turkmenistan","sell_media")
        ),
        "TC" => array(
            "name" => "TC",
            "title" => __("Turks and Caicos Islands","sell_media")
        ),
        "TV" => array(
            "name" => "TV",
            "title" => __("Tuvalu","sell_media")
        ),
        "UG" => array(
            "name" => "UG",
            "title" => __("Uganda","sell_media")
        ),
        "UA" => array(
            "name" => "UA",
            "title" => __("Ukraine","sell_media")
        ),
        "AE" => array(
            "name" => "AE",
            "title" => __("United Arab Emirates","sell_media")
        ),
        "GB" => array(
            "name" => "GB",
            "title" => __("United Kingdom","sell_media")
        ),
        "UM" => array(
            "name" => "UM",
            "title" => __("United States Minor Outlying Islands","sell_media")
        ),
        "UY" => array(
            "name" => "UY",
            "title" => __("Uruguay","sell_media")
        ),
        "UZ" => array(
            "name" => "UZ",
            "title" => __("Uzbekistan","sell_media")
        ),
        "VU" => array(
            "name" => "VU",
            "title" => __("Vanuatu","sell_media")
        ),
        "VE" => array(
            "name" => "VE",
            "title" => __("Venezuela, Bolivarian Republic of","sell_media")
        ),
        "VN" => array(
            "name" => "VN",
            "title" => __("Viet Nam","sell_media")
        ),
        "VG" => array(
            "name" => "VG",
            "title" => __("Virgin Islands, British","sell_media")
        ),
        "VI" => array(
            "name" => "VI",
            "title" => __("Virgin Islands, U.S.","sell_media")
        ),
        "WF" => array(
            "name" => "WF",
            "title" => __("Wallis and Futuna","sell_media")
        ),
        "EH" => array(
            "name" => "EH",
            "title" => __("Western Sahara","sell_media")
        ),
        "YE" => array(
            "name" => "YE",
            "title" => __("Yemen","sell_media")
        ),
        "ZM" => array(
            "name" => "ZM",
            "title" => __("Zambia","sell_media")
        ),
        "ZW" => array(
            "name" => "ZW",
            "title" => __("Zimbabwe","sell_media")
        )
    );
    return $items;
}
