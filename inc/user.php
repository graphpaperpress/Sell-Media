<?php
/**
 * User
 *
 * @package Sell Media
 * @author Thad Allender <support@graphpaperpress.com>
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Additional User Fields
 */
function sell_media_extra_user_fields() {

	$fields = array(
		array(
			'name'     => 'first name',
			'desc'     => '',
			'type'     => 'text',
			'required' => true
		),
		array(
			'name'     => 'last name',
			'desc'     => '',
			'type'     => 'text',
			'required' => true
		),
		array(
			'name'     => 'street',
			'desc'     => __( 'Example: 100 Main, Apt. 2B', 'sell_media' ),
			'type'     => 'text',
			'required' => true
		),
		array(
			'name'     => 'city',
			'desc'     => '',
			'type'     => 'text',
			'required' => true
		),
		array(
			'name'     => 'state',
			'desc'     => '',
			'type'     => 'text',
			'required' => true
		),
		array(
			'name'     => 'country',
			'desc'     => '',
			'type'     => 'select',
			'required' => true,
			'options'  => sell_media_get_countries()
		),
		array(
			'name'    => 'postal code',
			'desc'    => '',
			'type'    => 'text',
			'require' => true
		),
	);

	return apply_filters( 'sell_media_filter_registration_fields', $fields );
}

/**
 * Registration Form
 */
function sell_media_register_form() {

	$fields = sell_media_extra_user_fields();

	foreach ( $fields as $field ) {
		$id    = str_replace( ' ', '_', strtolower( $field['name'] ) );
		$value = isset( $_POST[$id] ) ? sanitize_text_field( $_POST[$id] ) : '';
		if ( 'select' === $field['type'] ) {
			?>
			<p>
				<label for="<?php echo esc_attr( $id ); ?>"><?php echo esc_html( ucwords( $field['name'] ) ); ?><br />
					<select name="<?php echo esc_attr( $id ); ?>" class="input">
						<?php
						if ( $field['options'] ) foreach ( $field['options'] as $key => $v ) {
							$selected = ( $value === $key ) ? 'selected' : '';
							?>
                            <option value="<?php echo esc_attr( $key ); ?>" <?php echo esc_attr( $selected );?>><?php echo esc_html( $v ); ?></option>
                            <?php
						}
						?>
					</select>
				</label>
			</p>
		<?php } else { ?>
			<p>
				<label for="<?php echo esc_attr( $id ); ?>"><?php echo esc_html( ucwords( $field['name'] ) ); ?><br />
				<input type="text" name="<?php echo esc_attr( $id ); ?>" id="<?php echo esc_attr( $id ); ?>" class="input" value="<?php echo esc_attr( $value ); ?>" size="25" /></label>
			</p>
		<?php }
	}
}
add_action( 'register_form', 'sell_media_register_form' );

/**
 * Registration form validation
 */
function sell_media_registration_errors( $errors, $sanitized_user_login, $user_email ) {

	$fields = sell_media_extra_user_fields();

	foreach ( $fields as $field ) {
		$id = str_replace( ' ', '_', strtolower( $field['name'] ) );
		if ( !isset( $_POST[$id] ) || sanitize_text_field( $_POST[$id] ) == '' ) {
			$errors->add( $id . '_error', sprintf('<strong>%s</strong>: %s',__( 'ERROR', 'sell_media' ), __( 'You must include a ' . $field['name'] . '.', 'sell_media' ) ) );
		}
	}

	return $errors;
}
add_filter( 'registration_errors', 'sell_media_registration_errors', 10, 3 );

/**
 * User registration meta update
 */
function sell_media_user_register( $user_id ) {

	$fields = sell_media_extra_user_fields();

	foreach ( $fields as $field ) {

		$id = str_replace( ' ', '_', strtolower( $field['name'] ) );
		if ( isset($_POST[$id]) && ! empty( $_POST[$id] ) ) {
			update_user_meta( $user_id, $id, sanitize_text_field( $_POST[$id] ) );
		}
	}
}
add_action( 'user_register', 'sell_media_user_register' );

/**
 * Get Countries
 */
function sell_media_get_countries() {

	$countries = array(
		'AF' => __( 'Afghanistan', 'sell_media' ),
		'AL' => __( 'Albania', 'sell_media' ),
		'DZ' => __( 'Algeria', 'sell_media' ),
		'AS' => __( 'American Samoa', 'sell_media' ),
		'AD' => __( 'Andorra', 'sell_media' ),
		'AO' => __( 'Angola', 'sell_media' ),
		'AI' => __( 'Anguilla', 'sell_media' ),
		'AQ' => __( 'Antarctica', 'sell_media' ),
		'AG' => __( 'Antigua and Barbuda', 'sell_media' ),
		'AR' => __( 'Argentina', 'sell_media' ),
		'AM' => __( 'Armenia', 'sell_media' ),
		'AW' => __( 'Aruba', 'sell_media' ),
		'AU' => __( 'Australia', 'sell_media' ),
		'AT' => __( 'Austria', 'sell_media' ),
		'AZ' => __( 'Azerbaijan', 'sell_media' ),
		'BS' => __( 'Bahamas', 'sell_media' ),
		'BH' => __( 'Bahrain', 'sell_media' ),
		'BD' => __( 'Bangladesh', 'sell_media' ),
		'BB' => __( 'Barbados', 'sell_media' ),
		'BY' => __( 'Belarus', 'sell_media' ),
		'BE' => __( 'Belgium', 'sell_media' ),
		'BZ' => __( 'Belize', 'sell_media' ),
		'BJ' => __( 'Benin', 'sell_media' ),
		'BM' => __( 'Bermuda', 'sell_media' ),
		'BT' => __( 'Bhutan', 'sell_media' ),
		'BO' => __( 'Bolivia', 'sell_media' ),
		'BA' => __( 'Bosnia and Herzegovina', 'sell_media' ),
		'BW' => __( 'Botswana', 'sell_media' ),
		'BV' => __( 'Bouvet Island', 'sell_media' ),
		'BR' => __( 'Brazil', 'sell_media' ),
		'BQ' => __( 'British Antarctic Territory', 'sell_media' ),
		'IO' => __( 'British Indian Ocean Territory', 'sell_media' ),
		'VG' => __( 'British Virgin Islands', 'sell_media' ),
		'BN' => __( 'Brunei', 'sell_media' ),
		'BG' => __( 'Bulgaria', 'sell_media' ),
		'BF' => __( 'Burkina Faso', 'sell_media' ),
		'BI' => __( 'Burundi', 'sell_media' ),
		'KH' => __( 'Cambodia', 'sell_media' ),
		'CM' => __( 'Cameroon', 'sell_media' ),
		'CA' => __( 'Canada', 'sell_media' ),
		'CT' => __( 'Canton and Enderbury Islands', 'sell_media' ),
		'CV' => __( 'Cape Verde', 'sell_media' ),
		'KY' => __( 'Cayman Islands', 'sell_media' ),
		'CF' => __( 'Central African Republic', 'sell_media' ),
		'TD' => __( 'Chad', 'sell_media' ),
		'CL' => __( 'Chile', 'sell_media' ),
		'CN' => __( 'China', 'sell_media' ),
		'CX' => __( 'Christmas Island', 'sell_media' ),
		'CC' => __( 'Cocos [Keeling] Islands', 'sell_media' ),
		'CO' => __( 'Colombia', 'sell_media' ),
		'KM' => __( 'Comoros', 'sell_media' ),
		'CG' => __( 'Congo - Brazzaville', 'sell_media' ),
		'CD' => __( 'Congo - Kinshasa', 'sell_media' ),
		'CK' => __( 'Cook Islands', 'sell_media' ),
		'CR' => __( 'Costa Rica', 'sell_media' ),
		'HR' => __( 'Croatia', 'sell_media' ),
		'CU' => __( 'Cuba', 'sell_media' ),
		'CY' => __( 'Cyprus', 'sell_media' ),
		'CZ' => __( 'Czech Republic', 'sell_media' ),
		'CI' => __( 'Côte d’Ivoire', 'sell_media' ),
		'DK' => __( 'Denmark', 'sell_media' ),
		'DJ' => __( 'Djibouti', 'sell_media' ),
		'DM' => __( 'Dominica', 'sell_media' ),
		'DO' => __( 'Dominican Republic', 'sell_media' ),
		'NQ' => __( 'Dronning Maud Land', 'sell_media' ),
		'DD' => __( 'East Germany', 'sell_media' ),
		'EC' => __( 'Ecuador', 'sell_media' ),
		'EG' => __( 'Egypt', 'sell_media' ),
		'SV' => __( 'El Salvador', 'sell_media' ),
		'GQ' => __( 'Equatorial Guinea', 'sell_media' ),
		'ER' => __( 'Eritrea', 'sell_media' ),
		'EE' => __( 'Estonia', 'sell_media' ),
		'ET' => __( 'Ethiopia', 'sell_media' ),
		'FK' => __( 'Falkland Islands', 'sell_media' ),
		'FO' => __( 'Faroe Islands', 'sell_media' ),
		'FJ' => __( 'Fiji', 'sell_media' ),
		'FI' => __( 'Finland', 'sell_media' ),
		'FR' => __( 'France', 'sell_media' ),
		'GF' => __( 'French Guiana', 'sell_media' ),
		'PF' => __( 'French Polynesia', 'sell_media' ),
		'TF' => __( 'French Southern Territories', 'sell_media' ),
		'FQ' => __( 'French Southern and Antarctic Territories', 'sell_media' ),
		'GA' => __( 'Gabon', 'sell_media' ),
		'GM' => __( 'Gambia', 'sell_media' ),
		'GE' => __( 'Georgia', 'sell_media' ),
		'DE' => __( 'Germany', 'sell_media' ),
		'GH' => __( 'Ghana', 'sell_media' ),
		'GI' => __( 'Gibraltar', 'sell_media' ),
		'GR' => __( 'Greece', 'sell_media' ),
		'GL' => __( 'Greenland', 'sell_media' ),
		'GD' => __( 'Grenada', 'sell_media' ),
		'GP' => __( 'Guadeloupe', 'sell_media' ),
		'GU' => __( 'Guam', 'sell_media' ),
		'GT' => __( 'Guatemala', 'sell_media' ),
		'GG' => __( 'Guernsey', 'sell_media' ),
		'GN' => __( 'Guinea', 'sell_media' ),
		'GW' => __( 'Guinea-Bissau', 'sell_media' ),
		'GY' => __( 'Guyana', 'sell_media' ),
		'HT' => __( 'Haiti', 'sell_media' ),
		'HM' => __( 'Heard Island and McDonald Islands', 'sell_media' ),
		'HN' => __( 'Honduras', 'sell_media' ),
		'HK' => __( 'Hong Kong SAR China', 'sell_media' ),
		'HU' => __( 'Hungary', 'sell_media' ),
		'IS' => __( 'Iceland', 'sell_media' ),
		'IN' => __( 'India', 'sell_media' ),
		'ID' => __( 'Indonesia', 'sell_media' ),
		'IR' => __( 'Iran', 'sell_media' ),
		'IQ' => __( 'Iraq', 'sell_media' ),
		'IE' => __( 'Ireland', 'sell_media' ),
		'IM' => __( 'Isle of Man', 'sell_media' ),
		'IL' => __( 'Israel', 'sell_media' ),
		'IT' => __( 'Italy', 'sell_media' ),
		'JM' => __( 'Jamaica', 'sell_media' ),
		'JP' => __( 'Japan', 'sell_media' ),
		'JE' => __( 'Jersey', 'sell_media' ),
		'JT' => __( 'Johnston Island', 'sell_media' ),
		'JO' => __( 'Jordan', 'sell_media' ),
		'KZ' => __( 'Kazakhstan', 'sell_media' ),
		'KE' => __( 'Kenya', 'sell_media' ),
		'KI' => __( 'Kiribati', 'sell_media' ),
		'KW' => __( 'Kuwait', 'sell_media' ),
		'KG' => __( 'Kyrgyzstan', 'sell_media' ),
		'LA' => __( 'Laos', 'sell_media' ),
		'LV' => __( 'Latvia', 'sell_media' ),
		'LB' => __( 'Lebanon', 'sell_media' ),
		'LS' => __( 'Lesotho', 'sell_media' ),
		'LR' => __( 'Liberia', 'sell_media' ),
		'LY' => __( 'Libya', 'sell_media' ),
		'LI' => __( 'Liechtenstein', 'sell_media' ),
		'LT' => __( 'Lithuania', 'sell_media' ),
		'LU' => __( 'Luxembourg', 'sell_media' ),
		'MO' => __( 'Macau SAR China', 'sell_media' ),
		'MK' => __( 'Macedonia', 'sell_media' ),
		'MG' => __( 'Madagascar', 'sell_media' ),
		'MW' => __( 'Malawi', 'sell_media' ),
		'MY' => __( 'Malaysia', 'sell_media' ),
		'MV' => __( 'Maldives', 'sell_media' ),
		'ML' => __( 'Mali', 'sell_media' ),
		'MT' => __( 'Malta', 'sell_media' ),
		'MH' => __( 'Marshall Islands', 'sell_media' ),
		'MQ' => __( 'Martinique', 'sell_media' ),
		'MR' => __( 'Mauritania', 'sell_media' ),
		'MU' => __( 'Mauritius', 'sell_media' ),
		'YT' => __( 'Mayotte', 'sell_media' ),
		'FX' => __( 'Metropolitan France', 'sell_media' ),
		'MX' => __( 'Mexico', 'sell_media' ),
		'FM' => __( 'Micronesia', 'sell_media' ),
		'MI' => __( 'Midway Islands', 'sell_media' ),
		'MD' => __( 'Moldova', 'sell_media' ),
		'MC' => __( 'Monaco', 'sell_media' ),
		'MN' => __( 'Mongolia', 'sell_media' ),
		'ME' => __( 'Montenegro', 'sell_media' ),
		'MS' => __( 'Montserrat', 'sell_media' ),
		'MA' => __( 'Morocco', 'sell_media' ),
		'MZ' => __( 'Mozambique', 'sell_media' ),
		'MM' => __( 'Myanmar [Burma]', 'sell_media' ),
		'NA' => __( 'Namibia', 'sell_media' ),
		'NR' => __( 'Nauru', 'sell_media' ),
		'NP' => __( 'Nepal', 'sell_media' ),
		'NL' => __( 'Netherlands', 'sell_media' ),
		'AN' => __( 'Netherlands Antilles', 'sell_media' ),
		'NT' => __( 'Neutral Zone', 'sell_media' ),
		'NC' => __( 'New Caledonia', 'sell_media' ),
		'NZ' => __( 'New Zealand', 'sell_media' ),
		'NI' => __( 'Nicaragua', 'sell_media' ),
		'NE' => __( 'Niger', 'sell_media' ),
		'NG' => __( 'Nigeria', 'sell_media' ),
		'NU' => __( 'Niue', 'sell_media' ),
		'NF' => __( 'Norfolk Island', 'sell_media' ),
		'KP' => __( 'North Korea', 'sell_media' ),
		'VD' => __( 'North Vietnam', 'sell_media' ),
		'MP' => __( 'Northern Mariana Islands', 'sell_media' ),
		'NO' => __( 'Norway', 'sell_media' ),
		'OM' => __( 'Oman', 'sell_media' ),
		'PC' => __( 'Pacific Islands Trust Territory', 'sell_media' ),
		'PK' => __( 'Pakistan', 'sell_media' ),
		'PW' => __( 'Palau', 'sell_media' ),
		'PS' => __( 'Palestinian Territories', 'sell_media' ),
		'PA' => __( 'Panama', 'sell_media' ),
		'PZ' => __( 'Panama Canal Zone', 'sell_media' ),
		'PG' => __( 'Papua New Guinea', 'sell_media' ),
		'PY' => __( 'Paraguay', 'sell_media' ),
		'YD' => __( 'People\'s Democratic Republic of Yemen', 'sell_media' ),
		'PE' => __( 'Peru', 'sell_media' ),
		'PH' => __( 'Philippines', 'sell_media' ),
		'PN' => __( 'Pitcairn Islands', 'sell_media' ),
		'PL' => __( 'Poland', 'sell_media' ),
		'PT' => __( 'Portugal', 'sell_media' ),
		'PR' => __( 'Puerto Rico', 'sell_media' ),
		'QA' => __( 'Qatar', 'sell_media' ),
		'RO' => __( 'Romania', 'sell_media' ),
		'RU' => __( 'Russia', 'sell_media' ),
		'RW' => __( 'Rwanda', 'sell_media' ),
		'BL' => __( 'Saint Barthélemy', 'sell_media' ),
		'SH' => __( 'Saint Helena', 'sell_media' ),
		'KN' => __( 'Saint Kitts and Nevis', 'sell_media' ),
		'LC' => __( 'Saint Lucia', 'sell_media' ),
		'MF' => __( 'Saint Martin', 'sell_media' ),
		'PM' => __( 'Saint Pierre and Miquelon', 'sell_media' ),
		'VC' => __( 'Saint Vincent and the Grenadines', 'sell_media' ),
		'WS' => __( 'Samoa', 'sell_media' ),
		'SM' => __( 'San Marino', 'sell_media' ),
		'SA' => __( 'Saudi Arabia', 'sell_media' ),
		'SN' => __( 'Senegal', 'sell_media' ),
		'RS' => __( 'Serbia', 'sell_media' ),
		'CS' => __( 'Serbia and Montenegro', 'sell_media' ),
		'SC' => __( 'Seychelles', 'sell_media' ),
		'SL' => __( 'Sierra Leone', 'sell_media' ),
		'SG' => __( 'Singapore', 'sell_media' ),
		'SK' => __( 'Slovakia', 'sell_media' ),
		'SI' => __( 'Slovenia', 'sell_media' ),
		'SB' => __( 'Solomon Islands', 'sell_media' ),
		'SO' => __( 'Somalia', 'sell_media' ),
		'ZA' => __( 'South Africa', 'sell_media' ),
		'GS' => __( 'South Georgia and the South Sandwich Islands', 'sell_media' ),
		'KR' => __( 'South Korea', 'sell_media' ),
		'ES' => __( 'Spain', 'sell_media' ),
		'LK' => __( 'Sri Lanka', 'sell_media' ),
		'SD' => __( 'Sudan', 'sell_media' ),
		'SR' => __( 'Suriname', 'sell_media' ),
		'SJ' => __( 'Svalbard and Jan Mayen', 'sell_media' ),
		'SZ' => __( 'Swaziland', 'sell_media' ),
		'SE' => __( 'Sweden', 'sell_media' ),
		'CH' => __( 'Switzerland', 'sell_media' ),
		'SY' => __( 'Syria', 'sell_media' ),
		'ST' => __( 'São Tomé and Príncipe', 'sell_media' ),
		'TW' => __( 'Taiwan', 'sell_media' ),
		'TJ' => __( 'Tajikistan', 'sell_media' ),
		'TZ' => __( 'Tanzania', 'sell_media' ),
		'TH' => __( 'Thailand', 'sell_media' ),
		'TL' => __( 'Timor-Leste', 'sell_media' ),
		'TG' => __( 'Togo', 'sell_media' ),
		'TK' => __( 'Tokelau', 'sell_media' ),
		'TO' => __( 'Tonga', 'sell_media' ),
		'TT' => __( 'Trinidad and Tobago', 'sell_media' ),
		'TN' => __( 'Tunisia', 'sell_media' ),
		'TR' => __( 'Turkey', 'sell_media' ),
		'TM' => __( 'Turkmenistan', 'sell_media' ),
		'TC' => __( 'Turks and Caicos Islands', 'sell_media' ),
		'TV' => __( 'Tuvalu', 'sell_media' ),
		'UM' => __( 'U.S. Minor Ouexampleying Islands', 'sell_media' ),
		'PU' => __( 'U.S. Miscellaneous Pacific Islands', 'sell_media' ),
		'VI' => __( 'U.S. Virgin Islands', 'sell_media' ),
		'UG' => __( 'Uganda', 'sell_media' ),
		'UA' => __( 'Ukraine', 'sell_media' ),
		'SU' => __( 'Union of Soviet Socialist Republics', 'sell_media' ),
		'AE' => __( 'United Arab Emirates', 'sell_media' ),
		'GB' => __( 'United Kingdom', 'sell_media' ),
		'US' => __( 'United States', 'sell_media' ),
		'ZZ' => __( 'Unknown or Invalid Region', 'sell_media' ),
		'UY' => __( 'Uruguay', 'sell_media' ),
		'UZ' => __( 'Uzbekistan', 'sell_media' ),
		'VU' => __( 'Vanuatu', 'sell_media' ),
		'VA' => __( 'Vatican City', 'sell_media' ),
		'VE' => __( 'Venezuela', 'sell_media' ),
		'VN' => __( 'Vietnam', 'sell_media' ),
		'WK' => __( 'Wake Island', 'sell_media' ),
		'WF' => __( 'Wallis and Futuna', 'sell_media' ),
		'EH' => __( 'Western Sahara', 'sell_media' ),
		'YE' => __( 'Yemen', 'sell_media' ),
		'ZM' => __( 'Zambia', 'sell_media' ),
		'ZW' => __( 'Zimbabwe', 'sell_media' ),
		'AX' => __( 'Åland Islands', 'sell_media' ),
	);
	
	return apply_filters( 'sell_media_filter_countries', $countries );
}

/**
 * Login URL
 */
function sell_media_login_logo_url() {
	return home_url();
}
add_filter( 'login_headerurl', 'sell_media_login_logo_url' );

/**
 * Login URL title
 */
function sell_media_login_logo_url_title() {
	return get_bloginfo( 'name' );
}
add_filter( 'login_headertext', 'sell_media_login_logo_url_title' );

/**
 * Custom CSS for login/registration page
 */
function sell_media_login_css() { ?>
    <style type="text/css">
        .login-action-register #login h1 {
			display: none;
        }
    </style>
<?php }
add_action( 'login_enqueue_scripts', 'sell_media_login_css' );

/**
 * Show extra profile fields in admin
 */
function sell_media_show_extra_profile_fields( $user ) {
	?><h2><?php echo esc_html__( 'Address', 'sell_media' ); ?></h2>
	<table class="form-table">
	<?php
	$fields = sell_media_extra_user_fields();
	foreach ( $fields as $field ) {
		$id = str_replace( ' ', '_', strtolower( $field['name'] ) );
		if ( 'first_name' !== $id && 'last_name' !== $id ) {
			$value = get_the_author_meta( $id, $user->ID );
			?><tr><?php
			?><th><label for="<?php echo esc_attr( $id ); ?>"><?php echo esc_html( ucwords( $field['name'] ) ); ?></label></th><?php
			?><td><?php
			if ( 'select' === $field['type'] ) {
				?><select name="<?php echo esc_attr( $id ); ?>" class="input"><?php
					if ( $field['options'] ) foreach ( $field['options'] as $key => $v ) {
						$selected = (( $value === $key ) ? 'selected' : '');
						?><option value="<?php echo esc_attr( $key ); ?>" <?php echo esc_attr( $selected ); ?>><?php echo esc_html( $v ); ?></option><?php
					}
					?></select><?php
			} else {
				?><input type="text" name="<?php echo esc_attr( $id ); ?>" id="<?php echo esc_attr( $id ); ?>" value="<?php echo esc_attr( $value ); ?>" class="regular-text" /><br />
			<?php }
			if(isset($field['desc']) && !empty($field['desc'])) {
				?><span class="description"><?php echo esc_html__($field['desc'], 'sell_media'); ?></span><?php
			}
			?></td><?php
			?></tr><?php
		}
	}

	?></table><?php
}
add_action( 'show_user_profile', 'sell_media_show_extra_profile_fields', 10, 1 );
add_action( 'edit_user_profile', 'sell_media_show_extra_profile_fields', 10, 1 );

/**
 * Save profile fields
 */
function sell_media_save_extra_profile_fields( $user_id ) {

	if ( ! current_user_can( 'edit_user', $user_id ) ) {
		return false;
	}

	$fields = sell_media_extra_user_fields();
	foreach ( $fields as $field ) {
		$id = str_replace( ' ', '_', strtolower( $field['name'] ) );
		update_user_meta( $user_id, $id, sanitize_text_field( $_POST[$id] ) );
	}
}
add_action( 'personal_options_update', 'sell_media_save_extra_profile_fields' );
add_action( 'edit_user_profile_update', 'sell_media_save_extra_profile_fields' );
