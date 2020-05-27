<?php
/**
 * Country Functions
 *
 * @package     Give
 * @subpackage  Functions
 * @copyright   Copyright (c) 2016, GiveWP
 * @license     https://opensource.org/licenses/gpl-license GNU Public License
 * @since       1.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Get Site Base Country
 *
 * @since 1.0
 * @return string $country The two letter country code for the site's base country
 */
function give_get_country() {
	$give_options = give_get_settings();
	$country      = isset( $give_options['base_country'] ) ? $give_options['base_country'] : 'US';

	return apply_filters( 'give_give_country', $country );
}

/**
 * Get Site Base State
 *
 * @since 1.0
 * @return string $state The site's base state name
 */
function give_get_state() {
	$give_options = give_get_settings();
	$state        = isset( $give_options['base_state'] ) ? $give_options['base_state'] : false;

	return apply_filters( 'give_give_state', $state );
}

/**
 * Get Site States
 *
 * @since 1.0
 *
 * @param null $country
 *
 * @return mixed  A list of states for the site's base country.
 */
function give_get_states( $country = null ) {
	// If Country have no states return empty array.
	$states = array();

	// Check if Country Code is empty or not.
	if ( empty( $country ) ) {
		// Get default country code that is being set by the admin.
		$country = give_get_country();
	}

	// Get all the list of the states in array key format where key is the country code and value is the states that it contain.
	$states_list = give_states_list();

	// Check if $country code exists in the array key.
	if ( array_key_exists( $country, $states_list ) ) {
		$states = $states_list[ $country ];
	}

	/**
	 * Filter the query in case tables are non-standard.
	 *
	 * @param string $query Database count query
	 */
	return (array) apply_filters( 'give_give_states', $states );
}

/**
 * Get Country List
 *
 * @since 1.0
 * @return array $countries A list of the available countries.
 */
function give_get_country_list() {
	$countries = array(
		''   => '',
		'US' => esc_html__( 'United States', 'give' ),
		'CA' => esc_html__( 'Canada', 'give' ),
		'GB' => esc_html__( 'United Kingdom', 'give' ),
		'AF' => esc_html__( 'Afghanistan', 'give' ),
		'AL' => esc_html__( 'Albania', 'give' ),
		'DZ' => esc_html__( 'Algeria', 'give' ),
		'AS' => esc_html__( 'American Samoa', 'give' ),
		'AD' => esc_html__( 'Andorra', 'give' ),
		'AO' => esc_html__( 'Angola', 'give' ),
		'AI' => esc_html__( 'Anguilla', 'give' ),
		'AQ' => esc_html__( 'Antarctica', 'give' ),
		'AG' => esc_html__( 'Antigua and Barbuda', 'give' ),
		'AR' => esc_html__( 'Argentina', 'give' ),
		'AM' => esc_html__( 'Armenia', 'give' ),
		'AW' => esc_html__( 'Aruba', 'give' ),
		'AU' => esc_html__( 'Australia', 'give' ),
		'AT' => esc_html__( 'Austria', 'give' ),
		'AZ' => esc_html__( 'Azerbaijan', 'give' ),
		'BS' => esc_html__( 'Bahamas', 'give' ),
		'BH' => esc_html__( 'Bahrain', 'give' ),
		'BD' => esc_html__( 'Bangladesh', 'give' ),
		'BB' => esc_html__( 'Barbados', 'give' ),
		'BY' => esc_html__( 'Belarus', 'give' ),
		'BE' => esc_html__( 'Belgium', 'give' ),
		'BZ' => esc_html__( 'Belize', 'give' ),
		'BJ' => esc_html__( 'Benin', 'give' ),
		'BM' => esc_html__( 'Bermuda', 'give' ),
		'BT' => esc_html__( 'Bhutan', 'give' ),
		'BO' => esc_html__( 'Bolivia', 'give' ),
		'BA' => esc_html__( 'Bosnia and Herzegovina', 'give' ),
		'BW' => esc_html__( 'Botswana', 'give' ),
		'BV' => esc_html__( 'Bouvet Island', 'give' ),
		'BR' => esc_html__( 'Brazil', 'give' ),
		'IO' => esc_html__( 'British Indian Ocean Territory', 'give' ),
		'BN' => esc_html__( 'Brunei Darrussalam', 'give' ),
		'BG' => esc_html__( 'Bulgaria', 'give' ),
		'BF' => esc_html__( 'Burkina Faso', 'give' ),
		'BI' => esc_html__( 'Burundi', 'give' ),
		'KH' => esc_html__( 'Cambodia', 'give' ),
		'CM' => esc_html__( 'Cameroon', 'give' ),
		'CV' => esc_html__( 'Cape Verde', 'give' ),
		'KY' => esc_html__( 'Cayman Islands', 'give' ),
		'CF' => esc_html__( 'Central African Republic', 'give' ),
		'TD' => esc_html__( 'Chad', 'give' ),
		'CL' => esc_html__( 'Chile', 'give' ),
		'CN' => esc_html__( 'China', 'give' ),
		'CX' => esc_html__( 'Christmas Island', 'give' ),
		'CC' => esc_html__( 'Cocos Islands', 'give' ),
		'CO' => esc_html__( 'Colombia', 'give' ),
		'KM' => esc_html__( 'Comoros', 'give' ),
		'CD' => esc_html__( 'Congo, Democratic People\'s Republic', 'give' ),
		'CG' => esc_html__( 'Congo, Republic of', 'give' ),
		'CK' => esc_html__( 'Cook Islands', 'give' ),
		'CR' => esc_html__( 'Costa Rica', 'give' ),
		'CI' => esc_html__( 'Cote d\'Ivoire', 'give' ),
		'HR' => esc_html__( 'Croatia/Hrvatska', 'give' ),
		'CU' => esc_html__( 'Cuba', 'give' ),
		'CY' => esc_html__( 'Cyprus Island', 'give' ),
		'CZ' => esc_html__( 'Czech Republic', 'give' ),
		'DK' => esc_html__( 'Denmark', 'give' ),
		'DJ' => esc_html__( 'Djibouti', 'give' ),
		'DM' => esc_html__( 'Dominica', 'give' ),
		'DO' => esc_html__( 'Dominican Republic', 'give' ),
		'TP' => esc_html__( 'East Timor', 'give' ),
		'EC' => esc_html__( 'Ecuador', 'give' ),
		'EG' => esc_html__( 'Egypt', 'give' ),
		'GQ' => esc_html__( 'Equatorial Guinea', 'give' ),
		'SV' => esc_html__( 'El Salvador', 'give' ),
		'ER' => esc_html__( 'Eritrea', 'give' ),
		'EE' => esc_html__( 'Estonia', 'give' ),
		'ET' => esc_html__( 'Ethiopia', 'give' ),
		'FK' => esc_html__( 'Falkland Islands', 'give' ),
		'FO' => esc_html__( 'Faroe Islands', 'give' ),
		'FJ' => esc_html__( 'Fiji', 'give' ),
		'FI' => esc_html__( 'Finland', 'give' ),
		'FR' => esc_html__( 'France', 'give' ),
		'GF' => esc_html__( 'French Guiana', 'give' ),
		'PF' => esc_html__( 'French Polynesia', 'give' ),
		'TF' => esc_html__( 'French Southern Territories', 'give' ),
		'GA' => esc_html__( 'Gabon', 'give' ),
		'GM' => esc_html__( 'Gambia', 'give' ),
		'GE' => esc_html__( 'Georgia', 'give' ),
		'DE' => esc_html__( 'Germany', 'give' ),
		'GR' => esc_html__( 'Greece', 'give' ),
		'GH' => esc_html__( 'Ghana', 'give' ),
		'GI' => esc_html__( 'Gibraltar', 'give' ),
		'GL' => esc_html__( 'Greenland', 'give' ),
		'GD' => esc_html__( 'Grenada', 'give' ),
		'GP' => esc_html__( 'Guadeloupe', 'give' ),
		'GU' => esc_html__( 'Guam', 'give' ),
		'GT' => esc_html__( 'Guatemala', 'give' ),
		'GG' => esc_html__( 'Guernsey', 'give' ),
		'GN' => esc_html__( 'Guinea', 'give' ),
		'GW' => esc_html__( 'Guinea-Bissau', 'give' ),
		'GY' => esc_html__( 'Guyana', 'give' ),
		'HT' => esc_html__( 'Haiti', 'give' ),
		'HM' => esc_html__( 'Heard and McDonald Islands', 'give' ),
		'VA' => esc_html__( 'Holy See (City Vatican State)', 'give' ),
		'HN' => esc_html__( 'Honduras', 'give' ),
		'HK' => esc_html__( 'Hong Kong', 'give' ),
		'HU' => esc_html__( 'Hungary', 'give' ),
		'IS' => esc_html__( 'Iceland', 'give' ),
		'IN' => esc_html__( 'India', 'give' ),
		'ID' => esc_html__( 'Indonesia', 'give' ),
		'IR' => esc_html__( 'Iran', 'give' ),
		'IQ' => esc_html__( 'Iraq', 'give' ),
		'IE' => esc_html__( 'Ireland', 'give' ),
		'IM' => esc_html__( 'Isle of Man', 'give' ),
		'IL' => esc_html__( 'Israel', 'give' ),
		'IT' => esc_html__( 'Italy', 'give' ),
		'JM' => esc_html__( 'Jamaica', 'give' ),
		'JP' => esc_html__( 'Japan', 'give' ),
		'JE' => esc_html__( 'Jersey', 'give' ),
		'JO' => esc_html__( 'Jordan', 'give' ),
		'KZ' => esc_html__( 'Kazakhstan', 'give' ),
		'KE' => esc_html__( 'Kenya', 'give' ),
		'KI' => esc_html__( 'Kiribati', 'give' ),
		'KW' => esc_html__( 'Kuwait', 'give' ),
		'KG' => esc_html__( 'Kyrgyzstan', 'give' ),
		'LA' => esc_html__( 'Lao People\'s Democratic Republic', 'give' ),
		'LV' => esc_html__( 'Latvia', 'give' ),
		'LB' => esc_html__( 'Lebanon', 'give' ),
		'LS' => esc_html__( 'Lesotho', 'give' ),
		'LR' => esc_html__( 'Liberia', 'give' ),
		'LY' => esc_html__( 'Libyan Arab Jamahiriya', 'give' ),
		'LI' => esc_html__( 'Liechtenstein', 'give' ),
		'LT' => esc_html__( 'Lithuania', 'give' ),
		'LU' => esc_html__( 'Luxembourg', 'give' ),
		'MO' => esc_html__( 'Macau', 'give' ),
		'MK' => esc_html__( 'Macedonia', 'give' ),
		'MG' => esc_html__( 'Madagascar', 'give' ),
		'MW' => esc_html__( 'Malawi', 'give' ),
		'MY' => esc_html__( 'Malaysia', 'give' ),
		'MV' => esc_html__( 'Maldives', 'give' ),
		'ML' => esc_html__( 'Mali', 'give' ),
		'MT' => esc_html__( 'Malta', 'give' ),
		'MH' => esc_html__( 'Marshall Islands', 'give' ),
		'MQ' => esc_html__( 'Martinique', 'give' ),
		'MR' => esc_html__( 'Mauritania', 'give' ),
		'MU' => esc_html__( 'Mauritius', 'give' ),
		'YT' => esc_html__( 'Mayotte', 'give' ),
		'MX' => esc_html__( 'Mexico', 'give' ),
		'FM' => esc_html__( 'Micronesia', 'give' ),
		'MD' => esc_html__( 'Moldova, Republic of', 'give' ),
		'MC' => esc_html__( 'Monaco', 'give' ),
		'MN' => esc_html__( 'Mongolia', 'give' ),
		'ME' => esc_html__( 'Montenegro', 'give' ),
		'MS' => esc_html__( 'Montserrat', 'give' ),
		'MA' => esc_html__( 'Morocco', 'give' ),
		'MZ' => esc_html__( 'Mozambique', 'give' ),
		'MM' => esc_html__( 'Myanmar', 'give' ),
		'NA' => esc_html__( 'Namibia', 'give' ),
		'NR' => esc_html__( 'Nauru', 'give' ),
		'NP' => esc_html__( 'Nepal', 'give' ),
		'NL' => esc_html__( 'Netherlands', 'give' ),
		'AN' => esc_html__( 'Netherlands Antilles', 'give' ),
		'NC' => esc_html__( 'New Caledonia', 'give' ),
		'NZ' => esc_html__( 'New Zealand', 'give' ),
		'NI' => esc_html__( 'Nicaragua', 'give' ),
		'NE' => esc_html__( 'Niger', 'give' ),
		'NG' => esc_html__( 'Nigeria', 'give' ),
		'NU' => esc_html__( 'Niue', 'give' ),
		'NF' => esc_html__( 'Norfolk Island', 'give' ),
		'KP' => esc_html__( 'North Korea', 'give' ),
		'MP' => esc_html__( 'Northern Mariana Islands', 'give' ),
		'NO' => esc_html__( 'Norway', 'give' ),
		'OM' => esc_html__( 'Oman', 'give' ),
		'PK' => esc_html__( 'Pakistan', 'give' ),
		'PW' => esc_html__( 'Palau', 'give' ),
		'PS' => esc_html__( 'Palestinian Territories', 'give' ),
		'PA' => esc_html__( 'Panama', 'give' ),
		'PG' => esc_html__( 'Papua New Guinea', 'give' ),
		'PY' => esc_html__( 'Paraguay', 'give' ),
		'PE' => esc_html__( 'Peru', 'give' ),
		'PH' => esc_html__( 'Philippines', 'give' ),
		'PN' => esc_html__( 'Pitcairn Island', 'give' ),
		'PL' => esc_html__( 'Poland', 'give' ),
		'PT' => esc_html__( 'Portugal', 'give' ),
		'PR' => esc_html__( 'Puerto Rico', 'give' ),
		'QA' => esc_html__( 'Qatar', 'give' ),
		'RE' => esc_html__( 'Reunion Island', 'give' ),
		'RO' => esc_html__( 'Romania', 'give' ),
		'RU' => esc_html__( 'Russian Federation', 'give' ),
		'RW' => esc_html__( 'Rwanda', 'give' ),
		'SH' => esc_html__( 'Saint Helena', 'give' ),
		'KN' => esc_html__( 'Saint Kitts and Nevis', 'give' ),
		'LC' => esc_html__( 'Saint Lucia', 'give' ),
		'PM' => esc_html__( 'Saint Pierre and Miquelon', 'give' ),
		'VC' => esc_html__( 'Saint Vincent and the Grenadines', 'give' ),
		'SM' => esc_html__( 'San Marino', 'give' ),
		'ST' => esc_html__( 'Sao Tome and Principe', 'give' ),
		'SA' => esc_html__( 'Saudi Arabia', 'give' ),
		'SN' => esc_html__( 'Senegal', 'give' ),
		'RS' => esc_html__( 'Serbia', 'give' ),
		'SC' => esc_html__( 'Seychelles', 'give' ),
		'SL' => esc_html__( 'Sierra Leone', 'give' ),
		'SG' => esc_html__( 'Singapore', 'give' ),
		'SK' => esc_html__( 'Slovak Republic', 'give' ),
		'SI' => esc_html__( 'Slovenia', 'give' ),
		'SB' => esc_html__( 'Solomon Islands', 'give' ),
		'SO' => esc_html__( 'Somalia', 'give' ),
		'ZA' => esc_html__( 'South Africa', 'give' ),
		'GS' => esc_html__( 'South Georgia', 'give' ),
		'KR' => esc_html__( 'South Korea', 'give' ),
		'ES' => esc_html__( 'Spain', 'give' ),
		'LK' => esc_html__( 'Sri Lanka', 'give' ),
		'SD' => esc_html__( 'Sudan', 'give' ),
		'SR' => esc_html__( 'Suriname', 'give' ),
		'SJ' => esc_html__( 'Svalbard and Jan Mayen Islands', 'give' ),
		'SZ' => esc_html__( 'Eswatini', 'give' ),
		'SE' => esc_html__( 'Sweden', 'give' ),
		'CH' => esc_html__( 'Switzerland', 'give' ),
		'SY' => esc_html__( 'Syrian Arab Republic', 'give' ),
		'TW' => esc_html__( 'Taiwan', 'give' ),
		'TJ' => esc_html__( 'Tajikistan', 'give' ),
		'TZ' => esc_html__( 'Tanzania', 'give' ),
		'TG' => esc_html__( 'Togo', 'give' ),
		'TK' => esc_html__( 'Tokelau', 'give' ),
		'TO' => esc_html__( 'Tonga', 'give' ),
		'TH' => esc_html__( 'Thailand', 'give' ),
		'TT' => esc_html__( 'Trinidad and Tobago', 'give' ),
		'TN' => esc_html__( 'Tunisia', 'give' ),
		'TR' => esc_html__( 'Turkey', 'give' ),
		'TM' => esc_html__( 'Turkmenistan', 'give' ),
		'TC' => esc_html__( 'Turks and Caicos Islands', 'give' ),
		'TV' => esc_html__( 'Tuvalu', 'give' ),
		'UG' => esc_html__( 'Uganda', 'give' ),
		'UA' => esc_html__( 'Ukraine', 'give' ),
		'AE' => esc_html__( 'United Arab Emirates', 'give' ),
		'UY' => esc_html__( 'Uruguay', 'give' ),
		'UM' => esc_html__( 'US Minor Outlying Islands', 'give' ),
		'UZ' => esc_html__( 'Uzbekistan', 'give' ),
		'VU' => esc_html__( 'Vanuatu', 'give' ),
		'VE' => esc_html__( 'Venezuela', 'give' ),
		'VN' => esc_html__( 'Vietnam', 'give' ),
		'VG' => esc_html__( 'Virgin Islands (British)', 'give' ),
		'VI' => esc_html__( 'Virgin Islands (USA)', 'give' ),
		'WF' => esc_html__( 'Wallis and Futuna Islands', 'give' ),
		'EH' => esc_html__( 'Western Sahara', 'give' ),
		'WS' => esc_html__( 'Western Samoa', 'give' ),
		'YE' => esc_html__( 'Yemen', 'give' ),
		'YU' => esc_html__( 'Yugoslavia', 'give' ),
		'ZM' => esc_html__( 'Zambia', 'give' ),
		'ZW' => esc_html__( 'Zimbabwe', 'give' ),
	);

	return (array) apply_filters( 'give_countries', $countries );
}

/**
 * Get States List.
 *
 * @since 1.8.11
 *
 * @return array $states A list of the available states as in array key format.
 */
function give_states_list() {
	$states = array(
		'US' => give_get_states_list(),
		'CA' => give_get_provinces_list(),
		'AU' => give_get_australian_states_list(),
		'BR' => give_get_brazil_states_list(),
		'CN' => give_get_chinese_states_list(),
		'HK' => give_get_hong_kong_states_list(),
		'HU' => give_get_hungary_states_list(),
		'ID' => give_get_indonesian_states_list(),
		'IN' => give_get_indian_states_list(),
		'MY' => give_get_malaysian_states_list(),
		'NZ' => give_get_new_zealand_states_list(),
		'TH' => give_get_thailand_states_list(),
		'ZA' => give_get_south_african_states_list(),
		'ES' => give_get_spain_states_list(),
		'TR' => give_get_turkey_states_list(),
		'RO' => give_get_romania_states_list(),
		'PK' => give_get_pakistan_states_list(),
		'PH' => give_get_philippines_states_list(),
		'PE' => give_get_peru_states_list(),
		'NP' => give_get_nepal_states_list(),
		'NG' => give_get_nigerian_states_list(),
		'MX' => give_get_mexico_states_list(),
		'JP' => give_get_japan_states_list(),
		'IT' => give_get_italy_states_list(),
		'IR' => give_get_iran_states_list(),
		'IE' => give_get_ireland_states_list(),
		'GR' => give_get_greek_states_list(),
		'BO' => give_get_bolivian_states_list(),
		'BG' => give_get_bulgarian_states_list(),
		'BD' => give_get_bangladeshi_states_list(),
		'AR' => give_get_argentina_states_list(),
	);

	/**
	 * Filter can be used to add or remove the States from the Country.
	 *
	 * Filters can be use to add states inside the country all the states will be in array format ans the array key will be country code.
	 *
	 * @since 1.8.11
	 *
	 * @param array $states Contain the list of states in array key format where key of the array is there respected country code.
	 */
	return (array) apply_filters( 'give_states_list', $states );
}

/**
 * List of Country that have no states init.
 *
 * There are some country which does not have states init Example: germany.
 *
 * @since 1.8.11
 *
 * $$country array $country_code.
 */
function give_no_states_country_list() {
	$country_list = array();
	$locale       = give_get_country_locale();
	foreach ( $locale as $key => $value ) {
		if ( ! empty( $value['state'] ) && isset( $value['state']['hidden'] ) && true === $value['state']['hidden'] ) {
			$country_list[ $key ] = $value['state'];
		}
	}

	/**
	 * Filter can be used to add or remove the Country that does not have states init.
	 *
	 * @since 1.8.11
	 *
	 * @param array $country Contain key as there country code & value as there country name.
	 */
	return (array) apply_filters( 'give_no_states_country_list', $country_list );
}

/**
 * List of Country in which states fields is not required.
 *
 * There are some country in which states fields is not required Example: United Kingdom ( uk ).
 *
 * @since 1.8.11
 *
 * $country array $country_code.
 */
function give_states_not_required_country_list() {
	$country_list = array();
	$locale       = give_get_country_locale();
	foreach ( $locale as $key => $value ) {
		if ( ! empty( $value['state'] ) && isset( $value['state']['required'] ) && false === $value['state']['required'] ) {
			$country_list[ $key ] = $value['state'];
		}
	}

	/**
	 * Filter can be used to add or remove the Country in which states fields is not required.
	 *
	 * @since 1.8.11
	 *
	 * @param array $country Contain key as there country code & value as there country name.
	 */
	return (array) apply_filters( 'give_states_not_required_country_list', $country_list );
}

/**
 * List of Country in which city fields is not required.
 *
 * There are some country in which city fields is not required Example: Singapore ( sk ).
 *
 * @since 2.3.0
 *
 * $country array $country_list.
 */
function give_city_not_required_country_list() {
	$country_list = array();
	$locale       = give_get_country_locale();
	foreach ( $locale as $key => $value ) {
		if ( ! empty( $value['city'] ) && isset( $value['city']['required'] ) && false === $value['city']['required'] ) {
			$country_list[ $key ] = $value['city'];
		}
	}

	/**
	 * Filter can be used to add or remove the Country in which city fields is not required.
	 *
	 * @since 2.3.0
	 *
	 * @param array $country_list Contain key as there country code & value as there country name.
	 */
	return (array) apply_filters( 'give_city_not_required_country_list', $country_list );
}

/**
 * Get the country name by list key.
 *
 * @since 1.8.12
 *
 * @param string $key
 *
 * @return string|bool
 */
function give_get_country_name_by_key( $key ) {
	$country_list = give_get_country_list();

	if ( array_key_exists( $key, $country_list ) ) {
		return $country_list[ $key ];
	}

	return false;
}

/**
 * Get the label that need to show as an placeholder.
 *
 * @ since 1.8.12
 *
 * @return array $country_states_label
 */
function give_get_states_label() {
	$country_states_label = array();
	$default_label        = __( 'State', 'give' );
	$locale               = give_get_country_locale();
	foreach ( $locale as $key => $value ) {
		$label = $default_label;
		if ( ! empty( $value['state'] ) && ! empty( $value['state']['label'] ) ) {
			$label = $value['state']['label'];
		}
		$country_states_label[ $key ] = $label;
	}

	/**
	 * Filter can be used to add or remove the Country that does not have states init.
	 *
	 * @since 1.8.11
	 *
	 * @param array $country Contain key as there country code & value as there country name.
	 */
	return (array) apply_filters( 'give_get_states_label', $country_states_label );
}

/**
 * Get country locale settings.
 *
 * @since 1.8.12
 *
 * @return array
 */
function give_get_country_locale() {
	return (array) apply_filters(
		'give_get_country_locale',
		array(
			'AE' => array(
				'state' => array(
					'required' => false,
				),
			),
			'AF' => array(
				'state' => array(
					'required' => false,
					'hidden'   => true,
				),
			),
			'AT' => array(
				'state' => array(
					'required' => false,
					'hidden'   => true,
				),
			),
			'AU' => array(
				'state' => array(
					'label' => __( 'State', 'give' ),
				),
			),
			'AX' => array(
				'state' => array(
					'required' => false,
				),
			),
			'BD' => array(
				'state' => array(
					'label' => __( 'District', 'give' ),
				),
			),
			'BE' => array(
				'state' => array(
					'required' => false,
					'label'    => __( 'Province', 'give' ),
					'hidden'   => true,
				),
			),
			'BI' => array(
				'state' => array(
					'required' => false,
				),
			),
			'CA' => array(
				'state' => array(
					'label' => __( 'Province', 'give' ),
				),
			),
			'CH' => array(
				'state' => array(
					'label'    => __( 'Canton', 'give' ),
					'required' => false,
					'hidden'   => true,
				),
			),
			'CL' => array(
				'state' => array(
					'label' => __( 'Region', 'give' ),
				),
			),
			'CN' => array(
				'state' => array(
					'label' => __( 'Province', 'give' ),
				),
			),
			'CZ' => array(
				'state' => array(
					'required' => false,
					'hidden'   => true,
				),
			),
			'DE' => array(
				'state' => array(
					'required' => false,
					'hidden'   => true,
				),
			),
			'DK' => array(
				'state' => array(
					'required' => false,
					'hidden'   => true,
				),
			),
			'EE' => array(
				'state' => array(
					'required' => false,
					'hidden'   => true,
				),
			),
			'FI' => array(
				'state' => array(
					'required' => false,
					'hidden'   => true,
				),
			),
			'FR' => array(
				'state' => array(
					'required' => false,
					'hidden'   => true,
				),
			),
			'GP' => array(
				'state' => array(
					'required' => false,
				),
			),
			'GF' => array(
				'state' => array(
					'required' => false,
				),
			),
			'HK' => array(
				'state' => array(
					'label' => __( 'Region', 'give' ),
				),
			),
			'HU' => array(
				'state' => array(
					'label'  => __( 'County', 'give' ),
					'hidden' => true,
				),
			),
			'ID' => array(
				'state' => array(
					'label' => __( 'Province', 'give' ),
				),
			),
			'IE' => array(
				'state' => array(
					'label' => __( 'County', 'give' ),
				),
			),
			'IS' => array(
				'state' => array(
					'required' => false,
					'hidden'   => true,
				),
			),
			'IL' => array(
				'state' => array(
					'required' => false,
				),
			),
			'IT' => array(
				'state' => array(
					'required' => true,
					'label'    => __( 'Province', 'give' ),
				),
			),
			'JP' => array(
				'state' => array(
					'label' => __( 'Prefecture', 'give' ),
				),
			),
			'KR' => array(
				'state' => array(
					'required' => false,
				),
			),
			'KW' => array(
				'state' => array(
					'required' => false,
				),
			),
			'LB' => array(
				'state' => array(
					'required' => false,
				),
			),
			'MQ' => array(
				'state' => array(
					'required' => false,
				),
			),
			'NL' => array(
				'state' => array(
					'required' => false,
					'label'    => __( 'Province', 'give' ),
					'hidden'   => true,
				),
			),
			'NZ' => array(
				'state' => array(
					'label' => __( 'Region', 'give' ),
				),
			),
			'NO' => array(
				'state' => array(
					'required' => false,
					'hidden'   => true,
				),
			),
			'NP' => array(
				'state' => array(
					'label' => __( 'State / Zone', 'give' ),
				),
			),
			'PL' => array(
				'state' => array(
					'required' => false,
					'hidden'   => true,
				),
			),
			'PT' => array(
				'state' => array(
					'required' => false,
					'hidden'   => true,
				),
			),
			'RE' => array(
				'state' => array(
					'required' => false,
				),
			),
			'RO' => array(
				'state' => array(
					'required' => false,
				),
			),
			'SG' => array(
				'state' => array(
					'required' => false,
				),
				'city'  => array(
					'required' => false,
				),
			),
			'SK' => array(
				'state' => array(
					'required' => false,
					'hidden'   => true,
				),
			),
			'SI' => array(
				'state' => array(
					'required' => false,
					'hidden'   => true,
				),
			),
			'ES' => array(
				'state' => array(
					'label' => __( 'Province', 'give' ),
				),
			),
			'LI' => array(
				'state' => array(
					'label'    => __( 'Municipality', 'give' ),
					'required' => false,
					'hidden'   => true,
				),
			),
			'LK' => array(
				'state' => array(
					'required' => false,
				),
			),
			'SE' => array(
				'state' => array(
					'required' => false,
					'hidden'   => true,
				),
			),
			'TR' => array(
				'state' => array(
					'label' => __( 'Province', 'give' ),
				),
			),
			'US' => array(
				'state' => array(
					'label' => __( 'State', 'give' ),
				),
			),
			'GB' => array(
				'state' => array(
					'label'    => __( 'County', 'give' ),
					'required' => false,
				),
			),
			'VN' => array(
				'state' => array(
					'required' => false,
					'hidden'   => true,
				),
			),
			'YT' => array(
				'state' => array(
					'required' => false,
				),
			),
			'ZA' => array(
				'state' => array(
					'label' => __( 'Province', 'give' ),
				),
			),
			'PA' => array(
				'state' => array(
					'required' => true,
				),
			),
		)
	);
}

/**
 * Get Turkey States
 *
 * @since 1.8.12
 * @return array $states A list of states
 */
function give_get_turkey_states_list() {
	$states = array(
		''     => '',
		'TR01' => __( 'Adana', 'give' ),
		'TR02' => __( 'Ad&#305;yaman', 'give' ),
		'TR03' => __( 'Afyon', 'give' ),
		'TR04' => __( 'A&#287;r&#305;', 'give' ),
		'TR05' => __( 'Amasya', 'give' ),
		'TR06' => __( 'Ankara', 'give' ),
		'TR07' => __( 'Antalya', 'give' ),
		'TR08' => __( 'Artvin', 'give' ),
		'TR09' => __( 'Ayd&#305;n', 'give' ),
		'TR10' => __( 'Bal&#305;kesir', 'give' ),
		'TR11' => __( 'Bilecik', 'give' ),
		'TR12' => __( 'Bing&#246;l', 'give' ),
		'TR13' => __( 'Bitlis', 'give' ),
		'TR14' => __( 'Bolu', 'give' ),
		'TR15' => __( 'Burdur', 'give' ),
		'TR16' => __( 'Bursa', 'give' ),
		'TR17' => __( '&#199;anakkale', 'give' ),
		'TR18' => __( '&#199;ank&#305;r&#305;', 'give' ),
		'TR19' => __( '&#199;orum', 'give' ),
		'TR20' => __( 'Denizli', 'give' ),
		'TR21' => __( 'Diyarbak&#305;r', 'give' ),
		'TR22' => __( 'Edirne', 'give' ),
		'TR23' => __( 'Elaz&#305;&#287;', 'give' ),
		'TR24' => __( 'Erzincan', 'give' ),
		'TR25' => __( 'Erzurum', 'give' ),
		'TR26' => __( 'Eski&#351;ehir', 'give' ),
		'TR27' => __( 'Gaziantep', 'give' ),
		'TR28' => __( 'Giresun', 'give' ),
		'TR29' => __( 'G&#252;m&#252;&#351;hane', 'give' ),
		'TR30' => __( 'Hakkari', 'give' ),
		'TR31' => __( 'Hatay', 'give' ),
		'TR32' => __( 'Isparta', 'give' ),
		'TR33' => __( '&#304;&#231;el', 'give' ),
		'TR34' => __( '&#304;stanbul', 'give' ),
		'TR35' => __( '&#304;zmir', 'give' ),
		'TR36' => __( 'Kars', 'give' ),
		'TR37' => __( 'Kastamonu', 'give' ),
		'TR38' => __( 'Kayseri', 'give' ),
		'TR39' => __( 'K&#305;rklareli', 'give' ),
		'TR40' => __( 'K&#305;r&#351;ehir', 'give' ),
		'TR41' => __( 'Kocaeli', 'give' ),
		'TR42' => __( 'Konya', 'give' ),
		'TR43' => __( 'K&#252;tahya', 'give' ),
		'TR44' => __( 'Malatya', 'give' ),
		'TR45' => __( 'Manisa', 'give' ),
		'TR46' => __( 'Kahramanmara&#351;', 'give' ),
		'TR47' => __( 'Mardin', 'give' ),
		'TR48' => __( 'Mu&#287;la', 'give' ),
		'TR49' => __( 'Mu&#351;', 'give' ),
		'TR50' => __( 'Nev&#351;ehir', 'give' ),
		'TR51' => __( 'Ni&#287;de', 'give' ),
		'TR52' => __( 'Ordu', 'give' ),
		'TR53' => __( 'Rize', 'give' ),
		'TR54' => __( 'Sakarya', 'give' ),
		'TR55' => __( 'Samsun', 'give' ),
		'TR56' => __( 'Siirt', 'give' ),
		'TR57' => __( 'Sinop', 'give' ),
		'TR58' => __( 'Sivas', 'give' ),
		'TR59' => __( 'Tekirda&#287;', 'give' ),
		'TR60' => __( 'Tokat', 'give' ),
		'TR61' => __( 'Trabzon', 'give' ),
		'TR62' => __( 'Tunceli', 'give' ),
		'TR63' => __( '&#350;anl&#305;urfa', 'give' ),
		'TR64' => __( 'U&#351;ak', 'give' ),
		'TR65' => __( 'Van', 'give' ),
		'TR66' => __( 'Yozgat', 'give' ),
		'TR67' => __( 'Zonguldak', 'give' ),
		'TR68' => __( 'Aksaray', 'give' ),
		'TR69' => __( 'Bayburt', 'give' ),
		'TR70' => __( 'Karaman', 'give' ),
		'TR71' => __( 'K&#305;r&#305;kkale', 'give' ),
		'TR72' => __( 'Batman', 'give' ),
		'TR73' => __( '&#350;&#305;rnak', 'give' ),
		'TR74' => __( 'Bart&#305;n', 'give' ),
		'TR75' => __( 'Ardahan', 'give' ),
		'TR76' => __( 'I&#287;d&#305;r', 'give' ),
		'TR77' => __( 'Yalova', 'give' ),
		'TR78' => __( 'Karab&#252;k', 'give' ),
		'TR79' => __( 'Kilis', 'give' ),
		'TR80' => __( 'Osmaniye', 'give' ),
		'TR81' => __( 'D&#252;zce', 'give' ),
	);

	return apply_filters( 'give_turkey_states', $states );
}

/**
 * Get Romania States
 *
 * @since 1.8.12
 * @return array $states A list of states
 */
function give_get_romania_states_list() {
	$states = array(
		''   => '',
		'AB' => __( 'Alba', 'give' ),
		'AR' => __( 'Arad', 'give' ),
		'AG' => __( 'Arges', 'give' ),
		'BC' => __( 'Bacau', 'give' ),
		'BH' => __( 'Bihor', 'give' ),
		'BN' => __( 'Bistrita-Nasaud', 'give' ),
		'BT' => __( 'Botosani', 'give' ),
		'BR' => __( 'Braila', 'give' ),
		'BV' => __( 'Brasov', 'give' ),
		'B'  => __( 'Bucuresti', 'give' ),
		'BZ' => __( 'Buzau', 'give' ),
		'CL' => __( 'Calarasi', 'give' ),
		'CS' => __( 'Caras-Severin', 'give' ),
		'CJ' => __( 'Cluj', 'give' ),
		'CT' => __( 'Constanta', 'give' ),
		'CV' => __( 'Covasna', 'give' ),
		'DB' => __( 'Dambovita', 'give' ),
		'DJ' => __( 'Dolj', 'give' ),
		'GL' => __( 'Galati', 'give' ),
		'GR' => __( 'Giurgiu', 'give' ),
		'GJ' => __( 'Gorj', 'give' ),
		'HR' => __( 'Harghita', 'give' ),
		'HD' => __( 'Hunedoara', 'give' ),
		'IL' => __( 'Ialomita', 'give' ),
		'IS' => __( 'Iasi', 'give' ),
		'IF' => __( 'Ilfov', 'give' ),
		'MM' => __( 'Maramures', 'give' ),
		'MH' => __( 'Mehedinti', 'give' ),
		'MS' => __( 'Mures', 'give' ),
		'NT' => __( 'Neamt', 'give' ),
		'OT' => __( 'Olt', 'give' ),
		'PH' => __( 'Prahova', 'give' ),
		'SJ' => __( 'Salaj', 'give' ),
		'SM' => __( 'Satu Mare', 'give' ),
		'SB' => __( 'Sibiu', 'give' ),
		'SV' => __( 'Suceava', 'give' ),
		'TR' => __( 'Teleorman', 'give' ),
		'TM' => __( 'Timis', 'give' ),
		'TL' => __( 'Tulcea', 'give' ),
		'VL' => __( 'Valcea', 'give' ),
		'VS' => __( 'Vaslui', 'give' ),
		'VN' => __( 'Vrancea', 'give' ),
	);

	return apply_filters( 'give_romania_states', $states );
}

/**
 * Get Pakistan States
 *
 * @since 1.8.12
 * @return array $states A list of states
 */
function give_get_pakistan_states_list() {
	$states = array(
		''   => '',
		'JK' => __( 'Azad Kashmir', 'give' ),
		'BA' => __( 'Balochistan', 'give' ),
		'TA' => __( 'FATA', 'give' ),
		'GB' => __( 'Gilgit Baltistan', 'give' ),
		'IS' => __( 'Islamabad Capital Territory', 'give' ),
		'KP' => __( 'Khyber Pakhtunkhwa', 'give' ),
		'PB' => __( 'Punjab', 'give' ),
		'SD' => __( 'Sindh', 'give' ),
	);

	return apply_filters( 'give_pakistan_states', $states );
}

/**
 * Get Philippines States
 *
 * @since 1.8.12
 * @return array $states A list of states
 */
function give_get_philippines_states_list() {
	$states = array(
		''    => '',
		'ABR' => __( 'Abra', 'give' ),
		'AGN' => __( 'Agusan del Norte', 'give' ),
		'AGS' => __( 'Agusan del Sur', 'give' ),
		'AKL' => __( 'Aklan', 'give' ),
		'ALB' => __( 'Albay', 'give' ),
		'ANT' => __( 'Antique', 'give' ),
		'APA' => __( 'Apayao', 'give' ),
		'AUR' => __( 'Aurora', 'give' ),
		'BAS' => __( 'Basilan', 'give' ),
		'BAN' => __( 'Bataan', 'give' ),
		'BTN' => __( 'Batanes', 'give' ),
		'BTG' => __( 'Batangas', 'give' ),
		'BEN' => __( 'Benguet', 'give' ),
		'BIL' => __( 'Biliran', 'give' ),
		'BOH' => __( 'Bohol', 'give' ),
		'BUK' => __( 'Bukidnon', 'give' ),
		'BUL' => __( 'Bulacan', 'give' ),
		'CAG' => __( 'Cagayan', 'give' ),
		'CAN' => __( 'Camarines Norte', 'give' ),
		'CAS' => __( 'Camarines Sur', 'give' ),
		'CAM' => __( 'Camiguin', 'give' ),
		'CAP' => __( 'Capiz', 'give' ),
		'CAT' => __( 'Catanduanes', 'give' ),
		'CAV' => __( 'Cavite', 'give' ),
		'CEB' => __( 'Cebu', 'give' ),
		'COM' => __( 'Compostela Valley', 'give' ),
		'NCO' => __( 'Cotabato', 'give' ),
		'DAV' => __( 'Davao del Norte', 'give' ),
		'DAS' => __( 'Davao del Sur', 'give' ),
		'DAC' => __( 'Davao Occidental', 'give' ), // TODO: Needs to be updated when ISO code is assigned
		'DAO' => __( 'Davao Oriental', 'give' ),
		'DIN' => __( 'Dinagat Islands', 'give' ),
		'EAS' => __( 'Eastern Samar', 'give' ),
		'GUI' => __( 'Guimaras', 'give' ),
		'IFU' => __( 'Ifugao', 'give' ),
		'ILN' => __( 'Ilocos Norte', 'give' ),
		'ILS' => __( 'Ilocos Sur', 'give' ),
		'ILI' => __( 'Iloilo', 'give' ),
		'ISA' => __( 'Isabela', 'give' ),
		'KAL' => __( 'Kalinga', 'give' ),
		'LUN' => __( 'La Union', 'give' ),
		'LAG' => __( 'Laguna', 'give' ),
		'LAN' => __( 'Lanao del Norte', 'give' ),
		'LAS' => __( 'Lanao del Sur', 'give' ),
		'LEY' => __( 'Leyte', 'give' ),
		'MAG' => __( 'Maguindanao', 'give' ),
		'MAD' => __( 'Marinduque', 'give' ),
		'MAS' => __( 'Masbate', 'give' ),
		'MSC' => __( 'Misamis Occidental', 'give' ),
		'MSR' => __( 'Misamis Oriental', 'give' ),
		'MOU' => __( 'Mountain Province', 'give' ),
		'NEC' => __( 'Negros Occidental', 'give' ),
		'NER' => __( 'Negros Oriental', 'give' ),
		'NSA' => __( 'Northern Samar', 'give' ),
		'NUE' => __( 'Nueva Ecija', 'give' ),
		'NUV' => __( 'Nueva Vizcaya', 'give' ),
		'MDC' => __( 'Occidental Mindoro', 'give' ),
		'MDR' => __( 'Oriental Mindoro', 'give' ),
		'PLW' => __( 'Palawan', 'give' ),
		'PAM' => __( 'Pampanga', 'give' ),
		'PAN' => __( 'Pangasinan', 'give' ),
		'QUE' => __( 'Quezon', 'give' ),
		'QUI' => __( 'Quirino', 'give' ),
		'RIZ' => __( 'Rizal', 'give' ),
		'ROM' => __( 'Romblon', 'give' ),
		'WSA' => __( 'Samar', 'give' ),
		'SAR' => __( 'Sarangani', 'give' ),
		'SIQ' => __( 'Siquijor', 'give' ),
		'SOR' => __( 'Sorsogon', 'give' ),
		'SCO' => __( 'South Cotabato', 'give' ),
		'SLE' => __( 'Southern Leyte', 'give' ),
		'SUK' => __( 'Sultan Kudarat', 'give' ),
		'SLU' => __( 'Sulu', 'give' ),
		'SUN' => __( 'Surigao del Norte', 'give' ),
		'SUR' => __( 'Surigao del Sur', 'give' ),
		'TAR' => __( 'Tarlac', 'give' ),
		'TAW' => __( 'Tawi-Tawi', 'give' ),
		'ZMB' => __( 'Zambales', 'give' ),
		'ZAN' => __( 'Zamboanga del Norte', 'give' ),
		'ZAS' => __( 'Zamboanga del Sur', 'give' ),
		'ZSI' => __( 'Zamboanga Sibugay', 'give' ),
		'00'  => __( 'Metro Manila', 'give' ),
	);

	return apply_filters( 'give_philippines_states', $states );
}

/**
 * Get Peru States
 *
 * @since 1.8.12
 * @return array $states A list of states
 */
function give_get_peru_states_list() {
	$states = array(
		''    => '',
		'CAL' => __( 'El Callao', 'give' ),
		'LMA' => __( 'Municipalidad Metropolitana de Lima', 'give' ),
		'AMA' => __( 'Amazonas', 'give' ),
		'ANC' => __( 'Ancash', 'give' ),
		'APU' => __( 'Apur&iacute;mac', 'give' ),
		'ARE' => __( 'Arequipa', 'give' ),
		'AYA' => __( 'Ayacucho', 'give' ),
		'CAJ' => __( 'Cajamarca', 'give' ),
		'CUS' => __( 'Cusco', 'give' ),
		'HUV' => __( 'Huancavelica', 'give' ),
		'HUC' => __( 'Hu&aacute;nuco', 'give' ),
		'ICA' => __( 'Ica', 'give' ),
		'JUN' => __( 'Jun&iacute;n', 'give' ),
		'LAL' => __( 'La Libertad', 'give' ),
		'LAM' => __( 'Lambayeque', 'give' ),
		'LIM' => __( 'Lima', 'give' ),
		'LOR' => __( 'Loreto', 'give' ),
		'MDD' => __( 'Madre de Dios', 'give' ),
		'MOQ' => __( 'Moquegua', 'give' ),
		'PAS' => __( 'Pasco', 'give' ),
		'PIU' => __( 'Piura', 'give' ),
		'PUN' => __( 'Puno', 'give' ),
		'SAM' => __( 'San Mart&iacute;n', 'give' ),
		'TAC' => __( 'Tacna', 'give' ),
		'TUM' => __( 'Tumbes', 'give' ),
		'UCA' => __( 'Ucayali', 'give' ),
	);

	return apply_filters( 'give_peru_states', $states );
}

/**
 * Get Nepal States
 *
 * @since 1.8.12
 * @return array $states A list of states
 */
function give_get_nepal_states_list() {
	$states = array(
		''    => '',
		'BAG' => __( 'Bagmati', 'give' ),
		'BHE' => __( 'Bheri', 'give' ),
		'DHA' => __( 'Dhaulagiri', 'give' ),
		'GAN' => __( 'Gandaki', 'give' ),
		'JAN' => __( 'Janakpur', 'give' ),
		'KAR' => __( 'Karnali', 'give' ),
		'KOS' => __( 'Koshi', 'give' ),
		'LUM' => __( 'Lumbini', 'give' ),
		'MAH' => __( 'Mahakali', 'give' ),
		'MEC' => __( 'Mechi', 'give' ),
		'NAR' => __( 'Narayani', 'give' ),
		'RAP' => __( 'Rapti', 'give' ),
		'SAG' => __( 'Sagarmatha', 'give' ),
		'SET' => __( 'Seti', 'give' ),
	);

	return apply_filters( 'give_nepal_states', $states );
}

/**
 * Get Nigerian States
 *
 * @since 1.8.12
 * @return array $states A list of states
 */
function give_get_nigerian_states_list() {
	$states = array(
		''   => '',
		'AB' => __( 'Abia', 'give' ),
		'FC' => __( 'Abuja', 'give' ),
		'AD' => __( 'Adamawa', 'give' ),
		'AK' => __( 'Akwa Ibom', 'give' ),
		'AN' => __( 'Anambra', 'give' ),
		'BA' => __( 'Bauchi', 'give' ),
		'BY' => __( 'Bayelsa', 'give' ),
		'BE' => __( 'Benue', 'give' ),
		'BO' => __( 'Borno', 'give' ),
		'CR' => __( 'Cross River', 'give' ),
		'DE' => __( 'Delta', 'give' ),
		'EB' => __( 'Ebonyi', 'give' ),
		'ED' => __( 'Edo', 'give' ),
		'EK' => __( 'Ekiti', 'give' ),
		'EN' => __( 'Enugu', 'give' ),
		'GO' => __( 'Gombe', 'give' ),
		'IM' => __( 'Imo', 'give' ),
		'JI' => __( 'Jigawa', 'give' ),
		'KD' => __( 'Kaduna', 'give' ),
		'KN' => __( 'Kano', 'give' ),
		'KT' => __( 'Katsina', 'give' ),
		'KE' => __( 'Kebbi', 'give' ),
		'KO' => __( 'Kogi', 'give' ),
		'KW' => __( 'Kwara', 'give' ),
		'LA' => __( 'Lagos', 'give' ),
		'NA' => __( 'Nasarawa', 'give' ),
		'NI' => __( 'Niger', 'give' ),
		'OG' => __( 'Ogun', 'give' ),
		'ON' => __( 'Ondo', 'give' ),
		'OS' => __( 'Osun', 'give' ),
		'OY' => __( 'Oyo', 'give' ),
		'PL' => __( 'Plateau', 'give' ),
		'RI' => __( 'Rivers', 'give' ),
		'SO' => __( 'Sokoto', 'give' ),
		'TA' => __( 'Taraba', 'give' ),
		'YO' => __( 'Yobe', 'give' ),
		'ZA' => __( 'Zamfara', 'give' ),
	);

	return apply_filters( 'give_nigerian_states', $states );
}

/**
 * Get Mexico States
 *
 * @since 1.8.12
 * @return array $states A list of states
 */
function give_get_mexico_states_list() {
	$states = array(
		''                    => '',
		'Distrito Federal'    => __( 'Distrito Federal', 'give' ),
		'Jalisco'             => __( 'Jalisco', 'give' ),
		'Nuevo Leon'          => __( 'Nuevo León', 'give' ),
		'Aguascalientes'      => __( 'Aguascalientes', 'give' ),
		'Baja California'     => __( 'Baja California', 'give' ),
		'Baja California Sur' => __( 'Baja California Sur', 'give' ),
		'Campeche'            => __( 'Campeche', 'give' ),
		'Chiapas'             => __( 'Chiapas', 'give' ),
		'Chihuahua'           => __( 'Chihuahua', 'give' ),
		'Coahuila'            => __( 'Coahuila', 'give' ),
		'Colima'              => __( 'Colima', 'give' ),
		'Durango'             => __( 'Durango', 'give' ),
		'Guanajuato'          => __( 'Guanajuato', 'give' ),
		'Guerrero'            => __( 'Guerrero', 'give' ),
		'Hidalgo'             => __( 'Hidalgo', 'give' ),
		'Estado de Mexico'    => __( 'Edo. de México', 'give' ),
		'Michoacan'           => __( 'Michoacán', 'give' ),
		'Morelos'             => __( 'Morelos', 'give' ),
		'Nayarit'             => __( 'Nayarit', 'give' ),
		'Oaxaca'              => __( 'Oaxaca', 'give' ),
		'Puebla'              => __( 'Puebla', 'give' ),
		'Queretaro'           => __( 'Querétaro', 'give' ),
		'Quintana Roo'        => __( 'Quintana Roo', 'give' ),
		'San Luis Potosi'     => __( 'San Luis Potosí', 'give' ),
		'Sinaloa'             => __( 'Sinaloa', 'give' ),
		'Sonora'              => __( 'Sonora', 'give' ),
		'Tabasco'             => __( 'Tabasco', 'give' ),
		'Tamaulipas'          => __( 'Tamaulipas', 'give' ),
		'Tlaxcala'            => __( 'Tlaxcala', 'give' ),
		'Veracruz'            => __( 'Veracruz', 'give' ),
		'Yucatan'             => __( 'Yucatán', 'give' ),
		'Zacatecas'           => __( 'Zacatecas', 'give' ),
	);

	return apply_filters( 'give_mexico_states', $states );
}

/**
 * Get Japan States
 *
 * @since 1.8.12
 * @return array $states A list of states
 */
function give_get_japan_states_list() {
	$states = array(
		''     => '',
		'JP01' => __( 'Hokkaido', 'give' ),
		'JP02' => __( 'Aomori', 'give' ),
		'JP03' => __( 'Iwate', 'give' ),
		'JP04' => __( 'Miyagi', 'give' ),
		'JP05' => __( 'Akita', 'give' ),
		'JP06' => __( 'Yamagata', 'give' ),
		'JP07' => __( 'Fukushima', 'give' ),
		'JP08' => __( 'Ibaraki', 'give' ),
		'JP09' => __( 'Tochigi', 'give' ),
		'JP10' => __( 'Gunma', 'give' ),
		'JP11' => __( 'Saitama', 'give' ),
		'JP12' => __( 'Chiba', 'give' ),
		'JP13' => __( 'Tokyo', 'give' ),
		'JP14' => __( 'Kanagawa', 'give' ),
		'JP15' => __( 'Niigata', 'give' ),
		'JP16' => __( 'Toyama', 'give' ),
		'JP17' => __( 'Ishikawa', 'give' ),
		'JP18' => __( 'Fukui', 'give' ),
		'JP19' => __( 'Yamanashi', 'give' ),
		'JP20' => __( 'Nagano', 'give' ),
		'JP21' => __( 'Gifu', 'give' ),
		'JP22' => __( 'Shizuoka', 'give' ),
		'JP23' => __( 'Aichi', 'give' ),
		'JP24' => __( 'Mie', 'give' ),
		'JP25' => __( 'Shiga', 'give' ),
		'JP26' => __( 'Kyoto', 'give' ),
		'JP27' => __( 'Osaka', 'give' ),
		'JP28' => __( 'Hyogo', 'give' ),
		'JP29' => __( 'Nara', 'give' ),
		'JP30' => __( 'Wakayama', 'give' ),
		'JP31' => __( 'Tottori', 'give' ),
		'JP32' => __( 'Shimane', 'give' ),
		'JP33' => __( 'Okayama', 'give' ),
		'JP34' => __( 'Hiroshima', 'give' ),
		'JP35' => __( 'Yamaguchi', 'give' ),
		'JP36' => __( 'Tokushima', 'give' ),
		'JP37' => __( 'Kagawa', 'give' ),
		'JP38' => __( 'Ehime', 'give' ),
		'JP39' => __( 'Kochi', 'give' ),
		'JP40' => __( 'Fukuoka', 'give' ),
		'JP41' => __( 'Saga', 'give' ),
		'JP42' => __( 'Nagasaki', 'give' ),
		'JP43' => __( 'Kumamoto', 'give' ),
		'JP44' => __( 'Oita', 'give' ),
		'JP45' => __( 'Miyazaki', 'give' ),
		'JP46' => __( 'Kagoshima', 'give' ),
		'JP47' => __( 'Okinawa', 'give' ),
	);

	return apply_filters( 'give_japan_states', $states );
}

/**
 * Get Italy States
 *
 * @since 1.8.12
 * @return array $states A list of states
 */
function give_get_italy_states_list() {
	$states = array(
		''   => '',
		'AG' => __( 'Agrigento', 'give' ),
		'AL' => __( 'Alessandria', 'give' ),
		'AN' => __( 'Ancona', 'give' ),
		'AO' => __( 'Aosta', 'give' ),
		'AR' => __( 'Arezzo', 'give' ),
		'AP' => __( 'Ascoli Piceno', 'give' ),
		'AT' => __( 'Asti', 'give' ),
		'AV' => __( 'Avellino', 'give' ),
		'BA' => __( 'Bari', 'give' ),
		'BT' => __( 'Barletta-Andria-Trani', 'give' ),
		'BL' => __( 'Belluno', 'give' ),
		'BN' => __( 'Benevento', 'give' ),
		'BG' => __( 'Bergamo', 'give' ),
		'BI' => __( 'Biella', 'give' ),
		'BO' => __( 'Bologna', 'give' ),
		'BZ' => __( 'Bolzano', 'give' ),
		'BS' => __( 'Brescia', 'give' ),
		'BR' => __( 'Brindisi', 'give' ),
		'CA' => __( 'Cagliari', 'give' ),
		'CL' => __( 'Caltanissetta', 'give' ),
		'CB' => __( 'Campobasso', 'give' ),
		'CI' => __( 'Carbonia-Iglesias', 'give' ),
		'CE' => __( 'Caserta', 'give' ),
		'CT' => __( 'Catania', 'give' ),
		'CZ' => __( 'Catanzaro', 'give' ),
		'CH' => __( 'Chieti', 'give' ),
		'CO' => __( 'Como', 'give' ),
		'CS' => __( 'Cosenza', 'give' ),
		'CR' => __( 'Cremona', 'give' ),
		'KR' => __( 'Crotone', 'give' ),
		'CN' => __( 'Cuneo', 'give' ),
		'EN' => __( 'Enna', 'give' ),
		'FM' => __( 'Fermo', 'give' ),
		'FE' => __( 'Ferrara', 'give' ),
		'FI' => __( 'Firenze', 'give' ),
		'FG' => __( 'Foggia', 'give' ),
		'FC' => __( 'Forlì-Cesena', 'give' ),
		'FR' => __( 'Frosinone', 'give' ),
		'GE' => __( 'Genova', 'give' ),
		'GO' => __( 'Gorizia', 'give' ),
		'GR' => __( 'Grosseto', 'give' ),
		'IM' => __( 'Imperia', 'give' ),
		'IS' => __( 'Isernia', 'give' ),
		'SP' => __( 'La Spezia', 'give' ),
		'AQ' => __( "L'Aquila", 'give' ),
		'LT' => __( 'Latina', 'give' ),
		'LE' => __( 'Lecce', 'give' ),
		'LC' => __( 'Lecco', 'give' ),
		'LI' => __( 'Livorno', 'give' ),
		'LO' => __( 'Lodi', 'give' ),
		'LU' => __( 'Lucca', 'give' ),
		'MC' => __( 'Macerata', 'give' ),
		'MN' => __( 'Mantova', 'give' ),
		'MS' => __( 'Massa-Carrara', 'give' ),
		'MT' => __( 'Matera', 'give' ),
		'ME' => __( 'Messina', 'give' ),
		'MI' => __( 'Milano', 'give' ),
		'MO' => __( 'Modena', 'give' ),
		'MB' => __( 'Monza e della Brianza', 'give' ),
		'NA' => __( 'Napoli', 'give' ),
		'NO' => __( 'Novara', 'give' ),
		'NU' => __( 'Nuoro', 'give' ),
		'OT' => __( 'Olbia-Tempio', 'give' ),
		'OR' => __( 'Oristano', 'give' ),
		'PD' => __( 'Padova', 'give' ),
		'PA' => __( 'Palermo', 'give' ),
		'PR' => __( 'Parma', 'give' ),
		'PV' => __( 'Pavia', 'give' ),
		'PG' => __( 'Perugia', 'give' ),
		'PU' => __( 'Pesaro e Urbino', 'give' ),
		'PE' => __( 'Pescara', 'give' ),
		'PC' => __( 'Piacenza', 'give' ),
		'PI' => __( 'Pisa', 'give' ),
		'PT' => __( 'Pistoia', 'give' ),
		'PN' => __( 'Pordenone', 'give' ),
		'PZ' => __( 'Potenza', 'give' ),
		'PO' => __( 'Prato', 'give' ),
		'RG' => __( 'Ragusa', 'give' ),
		'RA' => __( 'Ravenna', 'give' ),
		'RC' => __( 'Reggio Calabria', 'give' ),
		'RE' => __( 'Reggio Emilia', 'give' ),
		'RI' => __( 'Rieti', 'give' ),
		'RN' => __( 'Rimini', 'give' ),
		'RM' => __( 'Roma', 'give' ),
		'RO' => __( 'Rovigo', 'give' ),
		'SA' => __( 'Salerno', 'give' ),
		'VS' => __( 'Medio Campidano', 'give' ),
		'SS' => __( 'Sassari', 'give' ),
		'SV' => __( 'Savona', 'give' ),
		'SI' => __( 'Siena', 'give' ),
		'SR' => __( 'Siracusa', 'give' ),
		'SO' => __( 'Sondrio', 'give' ),
		'TA' => __( 'Taranto', 'give' ),
		'TE' => __( 'Teramo', 'give' ),
		'TR' => __( 'Terni', 'give' ),
		'TO' => __( 'Torino', 'give' ),
		'OG' => __( 'Ogliastra', 'give' ),
		'TP' => __( 'Trapani', 'give' ),
		'TN' => __( 'Trento', 'give' ),
		'TV' => __( 'Treviso', 'give' ),
		'TS' => __( 'Trieste', 'give' ),
		'UD' => __( 'Udine', 'give' ),
		'VA' => __( 'Varese', 'give' ),
		'VE' => __( 'Venezia', 'give' ),
		'VB' => __( 'Verbano-Cusio-Ossola', 'give' ),
		'VC' => __( 'Vercelli', 'give' ),
		'VR' => __( 'Verona', 'give' ),
		'VV' => __( 'Vibo Valentia', 'give' ),
		'VI' => __( 'Vicenza', 'give' ),
		'VT' => __( 'Viterbo', 'give' ),
	);

	return apply_filters( 'give_italy_states', $states );
}

/**
 * Get Iran States
 *
 * @since 1.8.12
 * @return array $states A list of states
 */
function give_get_iran_states_list() {
	$states = array(
		''    => '',
		'KHZ' => __( 'Khuzestan  (خوزستان)', 'give' ),
		'THR' => __( 'Tehran  (تهران)', 'give' ),
		'ILM' => __( 'Ilaam (ایلام)', 'give' ),
		'BHR' => __( 'Bushehr (بوشهر)', 'give' ),
		'ADL' => __( 'Ardabil (اردبیل)', 'give' ),
		'ESF' => __( 'Isfahan (اصفهان)', 'give' ),
		'YZD' => __( 'Yazd (یزد)', 'give' ),
		'KRH' => __( 'Kermanshah (کرمانشاه)', 'give' ),
		'KRN' => __( 'Kerman (کرمان)', 'give' ),
		'HDN' => __( 'Hamadan (همدان)', 'give' ),
		'GZN' => __( 'Ghazvin (قزوین)', 'give' ),
		'ZJN' => __( 'Zanjan (زنجان)', 'give' ),
		'LRS' => __( 'Luristan (لرستان)', 'give' ),
		'ABZ' => __( 'Alborz (البرز)', 'give' ),
		'EAZ' => __( 'East Azarbaijan (آذربایجان شرقی)', 'give' ),
		'WAZ' => __( 'West Azarbaijan (آذربایجان غربی)', 'give' ),
		'CHB' => __( 'Chaharmahal and Bakhtiari (چهارمحال و بختیاری)', 'give' ),
		'SKH' => __( 'South Khorasan (خراسان جنوبی)', 'give' ),
		'RKH' => __( 'Razavi Khorasan (خراسان رضوی)', 'give' ),
		'NKH' => __( 'North Khorasan (خراسان جنوبی)', 'give' ),
		'SMN' => __( 'Semnan (سمنان)', 'give' ),
		'FRS' => __( 'Fars (فارس)', 'give' ),
		'QHM' => __( 'Qom (قم)', 'give' ),
		'KRD' => __( 'Kurdistan / کردستان)', 'give' ),
		'KBD' => __( 'Kohgiluyeh and BoyerAhmad (کهگیلوییه و بویراحمد)', 'give' ),
		'GLS' => __( 'Golestan (گلستان)', 'give' ),
		'GIL' => __( 'Gilan (گیلان)', 'give' ),
		'MZN' => __( 'Mazandaran (مازندران)', 'give' ),
		'MKZ' => __( 'Markazi (مرکزی)', 'give' ),
		'HRZ' => __( 'Hormozgan (هرمزگان)', 'give' ),
		'SBN' => __( 'Sistan and Baluchestan (سیستان و بلوچستان)', 'give' ),
	);

	return apply_filters( 'give_iran_states', $states );
}

/**
 * Get Ireland States
 *
 * @since 1.8.12
 * @return array $states A list of states
 */
function give_get_ireland_states_list() {
	$states = array(
		''   => '',
		'AN' => __( 'Antrim', 'give' ),
		'AR' => __( 'Armagh', 'give' ),
		'CE' => __( 'Clare', 'give' ),
		'CK' => __( 'Cork', 'give' ),
		'CN' => __( 'Cavan', 'give' ),
		'CW' => __( 'Carlow', 'give' ),
		'DL' => __( 'Donegal', 'give' ),
		'DN' => __( 'Dublin', 'give' ),
		'DO' => __( 'Down', 'give' ),
		'DY' => __( 'Derry', 'give' ),
		'FM' => __( 'Fermanagh', 'give' ),
		'GY' => __( 'Galway', 'give' ),
		'KE' => __( 'Kildare', 'give' ),
		'KK' => __( 'Kilkenny', 'give' ),
		'KY' => __( 'Kerry', 'give' ),
		'LD' => __( 'Longford', 'give' ),
		'LH' => __( 'Louth', 'give' ),
		'LK' => __( 'Limerick', 'give' ),
		'LM' => __( 'Leitrim', 'give' ),
		'LS' => __( 'Laois', 'give' ),
		'MH' => __( 'Meath', 'give' ),
		'MN' => __( 'Monaghan', 'give' ),
		'MO' => __( 'Mayo', 'give' ),
		'OY' => __( 'Offaly', 'give' ),
		'RN' => __( 'Roscommon', 'give' ),
		'SO' => __( 'Sligo', 'give' ),
		'TR' => __( 'Tyrone', 'give' ),
		'TY' => __( 'Tipperary', 'give' ),
		'WD' => __( 'Waterford', 'give' ),
		'WH' => __( 'Westmeath', 'give' ),
		'WW' => __( 'Wicklow', 'give' ),
		'WX' => __( 'Wexford', 'give' ),
	);

	return apply_filters( 'give_ireland_states', $states );
}

/**
 * Get Greek States
 *
 * @since 1.8.12
 * @return array $states A list of states
 */
function give_get_greek_states_list() {
	$states = array(
		''  => '',
		'I' => __( 'Αττική', 'give' ),
		'A' => __( 'Ανατολική Μακεδονία και Θράκη', 'give' ),
		'B' => __( 'Κεντρική Μακεδονία', 'give' ),
		'C' => __( 'Δυτική Μακεδονία', 'give' ),
		'D' => __( 'Ήπειρος', 'give' ),
		'E' => __( 'Θεσσαλία', 'give' ),
		'F' => __( 'Ιόνιοι Νήσοι', 'give' ),
		'G' => __( 'Δυτική Ελλάδα', 'give' ),
		'H' => __( 'Στερεά Ελλάδα', 'give' ),
		'J' => __( 'Πελοπόννησος', 'give' ),
		'K' => __( 'Βόρειο Αιγαίο', 'give' ),
		'L' => __( 'Νότιο Αιγαίο', 'give' ),
		'M' => __( 'Κρήτη', 'give' ),
	);

	return apply_filters( 'give_greek_states', $states );
}

/**
 * Get bolivian States
 *
 * @since 1.8.12
 * @return array $states A list of states
 */
function give_get_bolivian_states_list() {
	$states = array(
		''  => '',
		'B' => __( 'Chuquisaca', 'give' ),
		'H' => __( 'Beni', 'give' ),
		'C' => __( 'Cochabamba', 'give' ),
		'L' => __( 'La Paz', 'give' ),
		'O' => __( 'Oruro', 'give' ),
		'N' => __( 'Pando', 'give' ),
		'P' => __( 'Potosí', 'give' ),
		'S' => __( 'Santa Cruz', 'give' ),
		'T' => __( 'Tarija', 'give' ),
	);

	return apply_filters( 'give_bolivian_states', $states );
}

/**
 * Get Bulgarian States
 *
 * @since 1.8.12
 * @return array $states A list of states
 */
function give_get_bulgarian_states_list() {
	$states = array(
		''      => '',
		'BG-01' => __( 'Blagoevgrad', 'give' ),
		'BG-02' => __( 'Burgas', 'give' ),
		'BG-08' => __( 'Dobrich', 'give' ),
		'BG-07' => __( 'Gabrovo', 'give' ),
		'BG-26' => __( 'Haskovo', 'give' ),
		'BG-09' => __( 'Kardzhali', 'give' ),
		'BG-10' => __( 'Kyustendil', 'give' ),
		'BG-11' => __( 'Lovech', 'give' ),
		'BG-12' => __( 'Montana', 'give' ),
		'BG-13' => __( 'Pazardzhik', 'give' ),
		'BG-14' => __( 'Pernik', 'give' ),
		'BG-15' => __( 'Pleven', 'give' ),
		'BG-16' => __( 'Plovdiv', 'give' ),
		'BG-17' => __( 'Razgrad', 'give' ),
		'BG-18' => __( 'Ruse', 'give' ),
		'BG-27' => __( 'Shumen', 'give' ),
		'BG-19' => __( 'Silistra', 'give' ),
		'BG-20' => __( 'Sliven', 'give' ),
		'BG-21' => __( 'Smolyan', 'give' ),
		'BG-23' => __( 'Sofia', 'give' ),
		'BG-22' => __( 'Sofia-Grad', 'give' ),
		'BG-24' => __( 'Stara Zagora', 'give' ),
		'BG-25' => __( 'Targovishte', 'give' ),
		'BG-03' => __( 'Varna', 'give' ),
		'BG-04' => __( 'Veliko Tarnovo', 'give' ),
		'BG-05' => __( 'Vidin', 'give' ),
		'BG-06' => __( 'Vratsa', 'give' ),
		'BG-28' => __( 'Yambol', 'give' ),
	);

	return apply_filters( 'give_bulgarian_states', $states );
}

/**
 * Get Bangladeshi States
 *
 * @since 1.8.12.
 * @return array $states A list of states
 */
function give_get_bangladeshi_states_list() {
	$states = array(
		''     => '',
		'BAG'  => __( 'Bagerhat', 'give' ),
		'BAN'  => __( 'Bandarban', 'give' ),
		'BAR'  => __( 'Barguna', 'give' ),
		'BARI' => __( 'Barisal', 'give' ),
		'BHO'  => __( 'Bhola', 'give' ),
		'BOG'  => __( 'Bogra', 'give' ),
		'BRA'  => __( 'Brahmanbaria', 'give' ),
		'CHA'  => __( 'Chandpur', 'give' ),
		'CHI'  => __( 'Chittagong', 'give' ),
		'CHU'  => __( 'Chuadanga', 'give' ),
		'COM'  => __( 'Comilla', 'give' ),
		'COX'  => __( "Cox's Bazar", 'give' ),
		'DHA'  => __( 'Dhaka', 'give' ),
		'DIN'  => __( 'Dinajpur', 'give' ),
		'FAR'  => __( 'Faridpur ', 'give' ),
		'FEN'  => __( 'Feni', 'give' ),
		'GAI'  => __( 'Gaibandha', 'give' ),
		'GAZI' => __( 'Gazipur', 'give' ),
		'GOP'  => __( 'Gopalganj', 'give' ),
		'HAB'  => __( 'Habiganj', 'give' ),
		'JAM'  => __( 'Jamalpur', 'give' ),
		'JES'  => __( 'Jessore', 'give' ),
		'JHA'  => __( 'Jhalokati', 'give' ),
		'JHE'  => __( 'Jhenaidah', 'give' ),
		'JOY'  => __( 'Joypurhat', 'give' ),
		'KHA'  => __( 'Khagrachhari', 'give' ),
		'KHU'  => __( 'Khulna', 'give' ),
		'KIS'  => __( 'Kishoreganj', 'give' ),
		'KUR'  => __( 'Kurigram', 'give' ),
		'KUS'  => __( 'Kushtia', 'give' ),
		'LAK'  => __( 'Lakshmipur', 'give' ),
		'LAL'  => __( 'Lalmonirhat', 'give' ),
		'MAD'  => __( 'Madaripur', 'give' ),
		'MAG'  => __( 'Magura', 'give' ),
		'MAN'  => __( 'Manikganj ', 'give' ),
		'MEH'  => __( 'Meherpur', 'give' ),
		'MOU'  => __( 'Moulvibazar', 'give' ),
		'MUN'  => __( 'Munshiganj', 'give' ),
		'MYM'  => __( 'Mymensingh', 'give' ),
		'NAO'  => __( 'Naogaon', 'give' ),
		'NAR'  => __( 'Narail', 'give' ),
		'NARG' => __( 'Narayanganj', 'give' ),
		'NARD' => __( 'Narsingdi', 'give' ),
		'NAT'  => __( 'Natore', 'give' ),
		'NAW'  => __( 'Nawabganj', 'give' ),
		'NET'  => __( 'Netrakona', 'give' ),
		'NIL'  => __( 'Nilphamari', 'give' ),
		'NOA'  => __( 'Noakhali', 'give' ),
		'PAB'  => __( 'Pabna', 'give' ),
		'PAN'  => __( 'Panchagarh', 'give' ),
		'PAT'  => __( 'Patuakhali', 'give' ),
		'PIR'  => __( 'Pirojpur', 'give' ),
		'RAJB' => __( 'Rajbari', 'give' ),
		'RAJ'  => __( 'Rajshahi', 'give' ),
		'RAN'  => __( 'Rangamati', 'give' ),
		'RANP' => __( 'Rangpur', 'give' ),
		'SAT'  => __( 'Satkhira', 'give' ),
		'SHA'  => __( 'Shariatpur', 'give' ),
		'SHE'  => __( 'Sherpur', 'give' ),
		'SIR'  => __( 'Sirajganj', 'give' ),
		'SUN'  => __( 'Sunamganj', 'give' ),
		'SYL'  => __( 'Sylhet', 'give' ),
		'TAN'  => __( 'Tangail', 'give' ),
		'THA'  => __( 'Thakurgaon', 'give' ),
	);

	return apply_filters( 'give_bangladeshi_states', $states );
}

/**
 * Get Argentina States
 *
 * @since 1.8.12
 * @return array $states A list of states
 */
function give_get_argentina_states_list() {
	$states = array(
		''  => '',
		'C' => __( 'Ciudad Aut&oacute;noma de Buenos Aires', 'give' ),
		'B' => __( 'Buenos Aires', 'give' ),
		'K' => __( 'Catamarca', 'give' ),
		'H' => __( 'Chaco', 'give' ),
		'U' => __( 'Chubut', 'give' ),
		'X' => __( 'C&oacute;rdoba', 'give' ),
		'W' => __( 'Corrientes', 'give' ),
		'E' => __( 'Entre R&iacute;os', 'give' ),
		'P' => __( 'Formosa', 'give' ),
		'Y' => __( 'Jujuy', 'give' ),
		'L' => __( 'La Pampa', 'give' ),
		'F' => __( 'La Rioja', 'give' ),
		'M' => __( 'Mendoza', 'give' ),
		'N' => __( 'Misiones', 'give' ),
		'Q' => __( 'Neuqu&eacute;n', 'give' ),
		'R' => __( 'R&iacute;o Negro', 'give' ),
		'A' => __( 'Salta', 'give' ),
		'J' => __( 'San Juan', 'give' ),
		'D' => __( 'San Luis', 'give' ),
		'Z' => __( 'Santa Cruz', 'give' ),
		'S' => __( 'Santa Fe', 'give' ),
		'G' => __( 'Santiago del Estero', 'give' ),
		'V' => __( 'Tierra del Fuego', 'give' ),
		'T' => __( 'Tucum&aacute;n', 'give' ),
	);

	return apply_filters( 'give_argentina_states', $states );
}

/**
 * Get States List
 *
 * @access      public
 * @since       1.2
 * @return      array
 */
function give_get_states_list() {
	$states = array(
		''   => '',
		'AL' => 'Alabama',
		'AK' => 'Alaska',
		'AZ' => 'Arizona',
		'AR' => 'Arkansas',
		'CA' => 'California',
		'CO' => 'Colorado',
		'CT' => 'Connecticut',
		'DE' => 'Delaware',
		'DC' => 'District of Columbia',
		'FL' => 'Florida',
		'GA' => 'Georgia',
		'HI' => 'Hawaii',
		'ID' => 'Idaho',
		'IL' => 'Illinois',
		'IN' => 'Indiana',
		'IA' => 'Iowa',
		'KS' => 'Kansas',
		'KY' => 'Kentucky',
		'LA' => 'Louisiana',
		'ME' => 'Maine',
		'MD' => 'Maryland',
		'MA' => 'Massachusetts',
		'MI' => 'Michigan',
		'MN' => 'Minnesota',
		'MS' => 'Mississippi',
		'MO' => 'Missouri',
		'MT' => 'Montana',
		'NE' => 'Nebraska',
		'NV' => 'Nevada',
		'NH' => 'New Hampshire',
		'NJ' => 'New Jersey',
		'NM' => 'New Mexico',
		'NY' => 'New York',
		'NC' => 'North Carolina',
		'ND' => 'North Dakota',
		'OH' => 'Ohio',
		'OK' => 'Oklahoma',
		'OR' => 'Oregon',
		'PA' => 'Pennsylvania',
		'RI' => 'Rhode Island',
		'SC' => 'South Carolina',
		'SD' => 'South Dakota',
		'TN' => 'Tennessee',
		'TX' => 'Texas',
		'UT' => 'Utah',
		'VT' => 'Vermont',
		'VA' => 'Virginia',
		'WA' => 'Washington',
		'WV' => 'West Virginia',
		'WI' => 'Wisconsin',
		'WY' => 'Wyoming',
		'AS' => 'American Samoa',
		'CZ' => 'Canal Zone',
		'CM' => 'Commonwealth of the Northern Mariana Islands',
		'FM' => 'Federated States of Micronesia',
		'GU' => 'Guam',
		'MH' => 'Marshall Islands',
		'MP' => 'Northern Mariana Islands',
		'PW' => 'Palau',
		'PI' => 'Philippine Islands',
		'PR' => 'Puerto Rico',
		'TT' => 'Trust Territory of the Pacific Islands',
		'VI' => 'Virgin Islands',
		'AA' => 'Armed Forces - Americas',
		'AE' => 'Armed Forces - Europe, Canada, Middle East, Africa',
		'AP' => 'Armed Forces - Pacific',
	);

	return apply_filters( 'give_us_states', $states );
}

/**
 * Get Provinces List
 *
 * @access      public
 * @since       1.0
 * @return      array
 */
function give_get_provinces_list() {
	$provinces = array(
		''   => '',
		'AB' => esc_html__( 'Alberta', 'give' ),
		'BC' => esc_html__( 'British Columbia', 'give' ),
		'MB' => esc_html__( 'Manitoba', 'give' ),
		'NB' => esc_html__( 'New Brunswick', 'give' ),
		'NL' => esc_html__( 'Newfoundland and Labrador', 'give' ),
		'NS' => esc_html__( 'Nova Scotia', 'give' ),
		'NT' => esc_html__( 'Northwest Territories', 'give' ),
		'NU' => esc_html__( 'Nunavut', 'give' ),
		'ON' => esc_html__( 'Ontario', 'give' ),
		'PE' => esc_html__( 'Prince Edward Island', 'give' ),
		'QC' => esc_html__( 'Quebec', 'give' ),
		'SK' => esc_html__( 'Saskatchewan', 'give' ),
		'YT' => esc_html__( 'Yukon', 'give' ),
	);

	return apply_filters( 'give_canada_provinces', $provinces );
}

/**
 * Get Australian States
 *
 * @since 1.0
 * @return array $states A list of states
 */
function give_get_australian_states_list() {
	$states = array(
		''    => '',
		'ACT' => 'Australian Capital Territory',
		'NSW' => 'New South Wales',
		'NT'  => 'Northern Territory',
		'QLD' => 'Queensland',
		'SA'  => 'South Australia',
		'TAS' => 'Tasmania',
		'VIC' => 'Victoria',
		'WA'  => 'Western Australia',
	);

	return apply_filters( 'give_australian_states', $states );
}

/**
 * Get Brazil States
 *
 * @since 1.0
 * @return array $states A list of states
 */
function give_get_brazil_states_list() {
	$states = array(
		''   => '',
		'AC' => 'Acre',
		'AL' => 'Alagoas',
		'AP' => 'Amap&aacute;',
		'AM' => 'Amazonas',
		'BA' => 'Bahia',
		'CE' => 'Cear&aacute;',
		'DF' => 'Distrito Federal',
		'ES' => 'Esp&iacute;rito Santo',
		'GO' => 'Goi&aacute;s',
		'MA' => 'Maranh&atilde;o',
		'MT' => 'Mato Grosso',
		'MS' => 'Mato Grosso do Sul',
		'MG' => 'Minas Gerais',
		'PA' => 'Par&aacute;',
		'PB' => 'Para&iacute;ba',
		'PR' => 'Paran&aacute;',
		'PE' => 'Pernambuco',
		'PI' => 'Piau&iacute;',
		'RJ' => 'Rio de Janeiro',
		'RN' => 'Rio Grande do Norte',
		'RS' => 'Rio Grande do Sul',
		'RO' => 'Rond&ocirc;nia',
		'RR' => 'Roraima',
		'SC' => 'Santa Catarina',
		'SP' => 'S&atilde;o Paulo',
		'SE' => 'Sergipe',
		'TO' => 'Tocantins',
	);

	return apply_filters( 'give_brazil_states', $states );
}

/**
 * Get Hong Kong States
 *
 * @since 1.0
 * @return array $states A list of states
 */
function give_get_hong_kong_states_list() {
	$states = array(
		''                => '',
		'HONG KONG'       => 'Hong Kong Island',
		'KOWLOON'         => 'Kowloon',
		'NEW TERRITORIES' => 'New Territories',
	);

	return apply_filters( 'give_hong_kong_states', $states );
}

/**
 * Get Hungary States
 *
 * @since 1.0
 * @return array $states A list of states
 */
function give_get_hungary_states_list() {
	$states = array(
		''   => '',
		'BK' => 'Bács-Kiskun',
		'BE' => 'Békés',
		'BA' => 'Baranya',
		'BZ' => 'Borsod-Abaúj-Zemplén',
		'BU' => 'Budapest',
		'CS' => 'Csongrád',
		'FE' => 'Fejér',
		'GS' => 'Győr-Moson-Sopron',
		'HB' => 'Hajdú-Bihar',
		'HE' => 'Heves',
		'JN' => 'Jász-Nagykun-Szolnok',
		'KE' => 'Komárom-Esztergom',
		'NO' => 'Nógrád',
		'PE' => 'Pest',
		'SO' => 'Somogy',
		'SZ' => 'Szabolcs-Szatmár-Bereg',
		'TO' => 'Tolna',
		'VA' => 'Vas',
		'VE' => 'Veszprém',
		'ZA' => 'Zala',
	);

	return apply_filters( 'give_hungary_states', $states );
}

/**
 * Get Chinese States
 *
 * @since 1.0
 * @return array $states A list of states
 */
function give_get_chinese_states_list() {
	$states = array(
		''     => '',
		'CN1'  => 'Yunnan / &#20113;&#21335;',
		'CN2'  => 'Beijing / &#21271;&#20140;',
		'CN3'  => 'Tianjin / &#22825;&#27941;',
		'CN4'  => 'Hebei / &#27827;&#21271;',
		'CN5'  => 'Shanxi / &#23665;&#35199;',
		'CN6'  => 'Inner Mongolia / &#20839;&#33945;&#21476;',
		'CN7'  => 'Liaoning / &#36797;&#23425;',
		'CN8'  => 'Jilin / &#21513;&#26519;',
		'CN9'  => 'Heilongjiang / &#40657;&#40857;&#27743;',
		'CN10' => 'Shanghai / &#19978;&#28023;',
		'CN11' => 'Jiangsu / &#27743;&#33487;',
		'CN12' => 'Zhejiang / &#27993;&#27743;',
		'CN13' => 'Anhui / &#23433;&#24509;',
		'CN14' => 'Fujian / &#31119;&#24314;',
		'CN15' => 'Jiangxi / &#27743;&#35199;',
		'CN16' => 'Shandong / &#23665;&#19996;',
		'CN17' => 'Henan / &#27827;&#21335;',
		'CN18' => 'Hubei / &#28246;&#21271;',
		'CN19' => 'Hunan / &#28246;&#21335;',
		'CN20' => 'Guangdong / &#24191;&#19996;',
		'CN21' => 'Guangxi Zhuang / &#24191;&#35199;&#22766;&#26063;',
		'CN22' => 'Hainan / &#28023;&#21335;',
		'CN23' => 'Chongqing / &#37325;&#24198;',
		'CN24' => 'Sichuan / &#22235;&#24029;',
		'CN25' => 'Guizhou / &#36149;&#24030;',
		'CN26' => 'Shaanxi / &#38485;&#35199;',
		'CN27' => 'Gansu / &#29976;&#32899;',
		'CN28' => 'Qinghai / &#38738;&#28023;',
		'CN29' => 'Ningxia Hui / &#23425;&#22799;',
		'CN30' => 'Macau / &#28595;&#38376;',
		'CN31' => 'Tibet / &#35199;&#34255;',
		'CN32' => 'Xinjiang / &#26032;&#30086;',
	);

	return apply_filters( 'give_chinese_states', $states );
}

/**
 * Get New Zealand States
 *
 * @since 1.0
 * @return array $states A list of states
 */
function give_get_new_zealand_states_list() {
	$states = array(
		''   => '',
		'AK' => 'Auckland',
		'BP' => 'Bay of Plenty',
		'CT' => 'Canterbury',
		'HB' => 'Hawke&rsquo;s Bay',
		'MW' => 'Manawatu-Wanganui',
		'MB' => 'Marlborough',
		'NS' => 'Nelson',
		'NL' => 'Northland',
		'OT' => 'Otago',
		'SL' => 'Southland',
		'TK' => 'Taranaki',
		'TM' => 'Tasman',
		'WA' => 'Waikato',
		'WE' => 'Wellington',
		'WC' => 'West Coast',
	);

	return apply_filters( 'give_new_zealand_states', $states );
}

/**
 * Get Indonesian States
 *
 * @since 1.0
 * @return array $states A list of states
 */
function give_get_indonesian_states_list() {
	$states = array(
		''   => '',
		'AC' => 'Daerah Istimewa Aceh',
		'SU' => 'Sumatera Utara',
		'SB' => 'Sumatera Barat',
		'RI' => 'Riau',
		'KR' => 'Kepulauan Riau',
		'JA' => 'Jambi',
		'SS' => 'Sumatera Selatan',
		'BB' => 'Bangka Belitung',
		'BE' => 'Bengkulu',
		'LA' => 'Lampung',
		'JK' => 'DKI Jakarta',
		'JB' => 'Jawa Barat',
		'BT' => 'Banten',
		'JT' => 'Jawa Tengah',
		'JI' => 'Jawa Timur',
		'YO' => 'Daerah Istimewa Yogyakarta',
		'BA' => 'Bali',
		'NB' => 'Nusa Tenggara Barat',
		'NT' => 'Nusa Tenggara Timur',
		'KB' => 'Kalimantan Barat',
		'KT' => 'Kalimantan Tengah',
		'KI' => 'Kalimantan Timur',
		'KS' => 'Kalimantan Selatan',
		'KU' => 'Kalimantan Utara',
		'SA' => 'Sulawesi Utara',
		'ST' => 'Sulawesi Tengah',
		'SG' => 'Sulawesi Tenggara',
		'SR' => 'Sulawesi Barat',
		'SN' => 'Sulawesi Selatan',
		'GO' => 'Gorontalo',
		'MA' => 'Maluku',
		'MU' => 'Maluku Utara',
		'PA' => 'Papua',
		'PB' => 'Papua Barat',
	);

	return apply_filters( 'give_indonesia_states', $states );
}

/**
 * Get Indian States
 *
 * @since 1.0
 * @return array $states A list of states
 */
function give_get_indian_states_list() {
	$states = array(
		''   => '',
		'AP' => 'Andhra Pradesh',
		'AR' => 'Arunachal Pradesh',
		'AS' => 'Assam',
		'BR' => 'Bihar',
		'CT' => 'Chhattisgarh',
		'GA' => 'Goa',
		'GJ' => 'Gujarat',
		'HR' => 'Haryana',
		'HP' => 'Himachal Pradesh',
		'JK' => 'Jammu and Kashmir',
		'JH' => 'Jharkhand',
		'KA' => 'Karnataka',
		'KL' => 'Kerala',
		'MP' => 'Madhya Pradesh',
		'MH' => 'Maharashtra',
		'MN' => 'Manipur',
		'ML' => 'Meghalaya',
		'MZ' => 'Mizoram',
		'NL' => 'Nagaland',
		'OR' => 'Orissa',
		'PB' => 'Punjab',
		'RJ' => 'Rajasthan',
		'SK' => 'Sikkim',
		'TN' => 'Tamil Nadu',
		'TG' => 'Telangana',
		'TR' => 'Tripura',
		'UT' => 'Uttarakhand',
		'UP' => 'Uttar Pradesh',
		'WB' => 'West Bengal',
		'AN' => 'Andaman and Nicobar Islands',
		'CH' => 'Chandigarh',
		'DN' => 'Dadar and Nagar Haveli',
		'DD' => 'Daman and Diu',
		'DL' => 'Delhi',
		'LD' => 'Lakshadweep',
		'PY' => 'Pondicherry (Puducherry)',
	);

	return apply_filters( 'give_indian_states', $states );
}

/**
 * Get Malaysian States
 *
 * @since 1.6
 * @return array $states A list of states
 */
function give_get_malaysian_states_list() {
	$states = array(
		''    => '',
		'JHR' => 'Johor',
		'KDH' => 'Kedah',
		'KTN' => 'Kelantan',
		'MLK' => 'Melaka',
		'NSN' => 'Negeri Sembilan',
		'PHG' => 'Pahang',
		'PRK' => 'Perak',
		'PLS' => 'Perlis',
		'PNG' => 'Pulau Pinang',
		'SBH' => 'Sabah',
		'SWK' => 'Sarawak',
		'SGR' => 'Selangor',
		'TRG' => 'Terengganu',
		'KUL' => 'W.P. Kuala Lumpur',
		'LBN' => 'W.P. Labuan',
		'PJY' => 'W.P. Putrajaya',
	);

	return apply_filters( 'give_malaysian_states', $states );
}

/**
 * Get South African States
 *
 * @since 1.6
 * @return array $states A list of states
 */
function give_get_south_african_states_list() {
	$states = array(
		''    => '',
		'EC'  => 'Eastern Cape',
		'FS'  => 'Free State',
		'GP'  => 'Gauteng',
		'KZN' => 'KwaZulu-Natal',
		'LP'  => 'Limpopo',
		'MP'  => 'Mpumalanga',
		'NC'  => 'Northern Cape',
		'NW'  => 'North West',
		'WC'  => 'Western Cape',
	);

	return apply_filters( 'give_south_african_states', $states );
}

/**
 * Get Thailand States
 *
 * @since 1.6
 * @return array $states A list of states
 */
function give_get_thailand_states_list() {
	$states = array(
		''      => '',
		'TH-37' => 'Amnat Charoen (&#3629;&#3635;&#3609;&#3634;&#3592;&#3648;&#3592;&#3619;&#3636;&#3597;)',
		'TH-15' => 'Ang Thong (&#3629;&#3656;&#3634;&#3591;&#3607;&#3629;&#3591;)',
		'TH-14' => 'Ayutthaya (&#3614;&#3619;&#3632;&#3609;&#3588;&#3619;&#3624;&#3619;&#3637;&#3629;&#3618;&#3640;&#3608;&#3618;&#3634;)',
		'TH-10' => 'Bangkok (&#3585;&#3619;&#3640;&#3591;&#3648;&#3607;&#3614;&#3617;&#3627;&#3634;&#3609;&#3588;&#3619;)',
		'TH-38' => 'Bueng Kan (&#3610;&#3638;&#3591;&#3585;&#3634;&#3628;)',
		'TH-31' => 'Buri Ram (&#3610;&#3640;&#3619;&#3637;&#3619;&#3633;&#3617;&#3618;&#3660;)',
		'TH-24' => 'Chachoengsao (&#3593;&#3632;&#3648;&#3594;&#3636;&#3591;&#3648;&#3607;&#3619;&#3634;)',
		'TH-18' => 'Chai Nat (&#3594;&#3633;&#3618;&#3609;&#3634;&#3607;)',
		'TH-36' => 'Chaiyaphum (&#3594;&#3633;&#3618;&#3616;&#3641;&#3617;&#3636;)',
		'TH-22' => 'Chanthaburi (&#3592;&#3633;&#3609;&#3607;&#3610;&#3640;&#3619;&#3637;)',
		'TH-50' => 'Chiang Mai (&#3648;&#3594;&#3637;&#3618;&#3591;&#3651;&#3627;&#3617;&#3656;)',
		'TH-57' => 'Chiang Rai (&#3648;&#3594;&#3637;&#3618;&#3591;&#3619;&#3634;&#3618;)',
		'TH-20' => 'Chonburi (&#3594;&#3621;&#3610;&#3640;&#3619;&#3637;)',
		'TH-86' => 'Chumphon (&#3594;&#3640;&#3617;&#3614;&#3619;)',
		'TH-46' => 'Kalasin (&#3585;&#3634;&#3628;&#3626;&#3636;&#3609;&#3608;&#3640;&#3660;)',
		'TH-62' => 'Kamphaeng Phet (&#3585;&#3635;&#3649;&#3614;&#3591;&#3648;&#3614;&#3594;&#3619;)',
		'TH-71' => 'Kanchanaburi (&#3585;&#3634;&#3597;&#3592;&#3609;&#3610;&#3640;&#3619;&#3637;)',
		'TH-40' => 'Khon Kaen (&#3586;&#3629;&#3609;&#3649;&#3585;&#3656;&#3609;)',
		'TH-81' => 'Krabi (&#3585;&#3619;&#3632;&#3610;&#3637;&#3656;)',
		'TH-52' => 'Lampang (&#3621;&#3635;&#3611;&#3634;&#3591;)',
		'TH-51' => 'Lamphun (&#3621;&#3635;&#3614;&#3641;&#3609;)',
		'TH-42' => 'Loei (&#3648;&#3621;&#3618;)',
		'TH-16' => 'Lopburi (&#3621;&#3614;&#3610;&#3640;&#3619;&#3637;)',
		'TH-58' => 'Mae Hong Son (&#3649;&#3617;&#3656;&#3630;&#3656;&#3629;&#3591;&#3626;&#3629;&#3609;)',
		'TH-44' => 'Maha Sarakham (&#3617;&#3627;&#3634;&#3626;&#3634;&#3619;&#3588;&#3634;&#3617;)',
		'TH-49' => 'Mukdahan (&#3617;&#3640;&#3585;&#3604;&#3634;&#3627;&#3634;&#3619;)',
		'TH-26' => 'Nakhon Nayok (&#3609;&#3588;&#3619;&#3609;&#3634;&#3618;&#3585;)',
		'TH-73' => 'Nakhon Pathom (&#3609;&#3588;&#3619;&#3611;&#3600;&#3617;)',
		'TH-48' => 'Nakhon Phanom (&#3609;&#3588;&#3619;&#3614;&#3609;&#3617;)',
		'TH-30' => 'Nakhon Ratchasima (&#3609;&#3588;&#3619;&#3619;&#3634;&#3594;&#3626;&#3637;&#3617;&#3634;)',
		'TH-60' => 'Nakhon Sawan (&#3609;&#3588;&#3619;&#3626;&#3623;&#3619;&#3619;&#3588;&#3660;)',
		'TH-80' => 'Nakhon Si Thammarat (&#3609;&#3588;&#3619;&#3624;&#3619;&#3637;&#3608;&#3619;&#3619;&#3617;&#3619;&#3634;&#3594;)',
		'TH-55' => 'Nan (&#3609;&#3656;&#3634;&#3609;)',
		'TH-96' => 'Narathiwat (&#3609;&#3619;&#3634;&#3608;&#3636;&#3623;&#3634;&#3626;)',
		'TH-39' => 'Nong Bua Lam Phu (&#3627;&#3609;&#3629;&#3591;&#3610;&#3633;&#3623;&#3621;&#3635;&#3616;&#3641;)',
		'TH-43' => 'Nong Khai (&#3627;&#3609;&#3629;&#3591;&#3588;&#3634;&#3618;)',
		'TH-12' => 'Nonthaburi (&#3609;&#3609;&#3607;&#3610;&#3640;&#3619;&#3637;)',
		'TH-13' => 'Pathum Thani (&#3611;&#3607;&#3640;&#3617;&#3608;&#3634;&#3609;&#3637;)',
		'TH-94' => 'Pattani (&#3611;&#3633;&#3605;&#3605;&#3634;&#3609;&#3637;)',
		'TH-82' => 'Phang Nga (&#3614;&#3633;&#3591;&#3591;&#3634;)',
		'TH-93' => 'Phatthalung (&#3614;&#3633;&#3607;&#3621;&#3640;&#3591;)',
		'TH-56' => 'Phayao (&#3614;&#3632;&#3648;&#3618;&#3634;)',
		'TH-67' => 'Phetchabun (&#3648;&#3614;&#3594;&#3619;&#3610;&#3641;&#3619;&#3603;&#3660;)',
		'TH-76' => 'Phetchaburi (&#3648;&#3614;&#3594;&#3619;&#3610;&#3640;&#3619;&#3637;)',
		'TH-66' => 'Phichit (&#3614;&#3636;&#3592;&#3636;&#3605;&#3619;)',
		'TH-65' => 'Phitsanulok (&#3614;&#3636;&#3625;&#3603;&#3640;&#3650;&#3621;&#3585;)',
		'TH-54' => 'Phrae (&#3649;&#3614;&#3619;&#3656;)',
		'TH-83' => 'Phuket (&#3616;&#3641;&#3648;&#3585;&#3655;&#3605;)',
		'TH-25' => 'Prachin Buri (&#3611;&#3619;&#3634;&#3592;&#3637;&#3609;&#3610;&#3640;&#3619;&#3637;)',
		'TH-77' => 'Prachuap Khiri Khan (&#3611;&#3619;&#3632;&#3592;&#3623;&#3610;&#3588;&#3637;&#3619;&#3637;&#3586;&#3633;&#3609;&#3608;&#3660;)',
		'TH-85' => 'Ranong (&#3619;&#3632;&#3609;&#3629;&#3591;)',
		'TH-70' => 'Ratchaburi (&#3619;&#3634;&#3594;&#3610;&#3640;&#3619;&#3637;)',
		'TH-21' => 'Rayong (&#3619;&#3632;&#3618;&#3629;&#3591;)',
		'TH-45' => 'Roi Et (&#3619;&#3657;&#3629;&#3618;&#3648;&#3629;&#3655;&#3604;)',
		'TH-27' => 'Sa Kaeo (&#3626;&#3619;&#3632;&#3649;&#3585;&#3657;&#3623;)',
		'TH-47' => 'Sakon Nakhon (&#3626;&#3585;&#3621;&#3609;&#3588;&#3619;)',
		'TH-11' => 'Samut Prakan (&#3626;&#3617;&#3640;&#3607;&#3619;&#3611;&#3619;&#3634;&#3585;&#3634;&#3619;)',
		'TH-74' => 'Samut Sakhon (&#3626;&#3617;&#3640;&#3607;&#3619;&#3626;&#3634;&#3588;&#3619;)',
		'TH-75' => 'Samut Songkhram (&#3626;&#3617;&#3640;&#3607;&#3619;&#3626;&#3591;&#3588;&#3619;&#3634;&#3617;)',
		'TH-19' => 'Saraburi (&#3626;&#3619;&#3632;&#3610;&#3640;&#3619;&#3637;)',
		'TH-91' => 'Satun (&#3626;&#3605;&#3641;&#3621;)',
		'TH-17' => 'Sing Buri (&#3626;&#3636;&#3591;&#3627;&#3660;&#3610;&#3640;&#3619;&#3637;)',
		'TH-33' => 'Sisaket (&#3624;&#3619;&#3637;&#3626;&#3632;&#3648;&#3585;&#3625;)',
		'TH-90' => 'Songkhla (&#3626;&#3591;&#3586;&#3621;&#3634;)',
		'TH-64' => 'Sukhothai (&#3626;&#3640;&#3650;&#3586;&#3607;&#3633;&#3618;)',
		'TH-72' => 'Suphan Buri (&#3626;&#3640;&#3614;&#3619;&#3619;&#3603;&#3610;&#3640;&#3619;&#3637;)',
		'TH-84' => 'Surat Thani (&#3626;&#3640;&#3619;&#3634;&#3625;&#3598;&#3619;&#3660;&#3608;&#3634;&#3609;&#3637;)',
		'TH-32' => 'Surin (&#3626;&#3640;&#3619;&#3636;&#3609;&#3607;&#3619;&#3660;)',
		'TH-63' => 'Tak (&#3605;&#3634;&#3585;)',
		'TH-92' => 'Trang (&#3605;&#3619;&#3633;&#3591;)',
		'TH-23' => 'Trat (&#3605;&#3619;&#3634;&#3604;)',
		'TH-34' => 'Ubon Ratchathani (&#3629;&#3640;&#3610;&#3621;&#3619;&#3634;&#3594;&#3608;&#3634;&#3609;&#3637;)',
		'TH-41' => 'Udon Thani (&#3629;&#3640;&#3604;&#3619;&#3608;&#3634;&#3609;&#3637;)',
		'TH-61' => 'Uthai Thani (&#3629;&#3640;&#3607;&#3633;&#3618;&#3608;&#3634;&#3609;&#3637;)',
		'TH-53' => 'Uttaradit (&#3629;&#3640;&#3605;&#3619;&#3604;&#3636;&#3605;&#3606;&#3660;)',
		'TH-95' => 'Yala (&#3618;&#3632;&#3621;&#3634;)',
		'TH-35' => 'Yasothon (&#3618;&#3650;&#3626;&#3608;&#3619;)',
	);

	return apply_filters( 'give_thailand_states', $states );
}

/**
 * Get Spain States
 *
 * @since 1.0
 * @return array $states A list of states
 */
function give_get_spain_states_list() {
	$states = array(
		''   => '',
		'C'  => esc_html__( 'A Coru&ntilde;a', 'give' ),
		'VI' => esc_html__( 'Álava', 'give' ),
		'AB' => esc_html__( 'Albacete', 'give' ),
		'A'  => esc_html__( 'Alicante', 'give' ),
		'AL' => esc_html__( 'Almer&iacute;a', 'give' ),
		'O'  => esc_html__( 'Asturias', 'give' ),
		'AV' => esc_html__( '&Aacute;vila', 'give' ),
		'BA' => esc_html__( 'Badajoz', 'give' ),
		'PM' => esc_html__( 'Baleares', 'give' ),
		'B'  => esc_html__( 'Barcelona', 'give' ),
		'BU' => esc_html__( 'Burgos', 'give' ),
		'CC' => esc_html__( 'C&aacute;ceres', 'give' ),
		'CA' => esc_html__( 'C&aacute;diz', 'give' ),
		'S'  => esc_html__( 'Cantabria', 'give' ),
		'CS' => esc_html__( 'Castell&oacute;n', 'give' ),
		'CE' => esc_html__( 'Ceuta', 'give' ),
		'CR' => esc_html__( 'Ciudad Real', 'give' ),
		'CO' => esc_html__( 'C&oacute;rdoba', 'give' ),
		'CU' => esc_html__( 'Cuenca', 'give' ),
		'GI' => esc_html__( 'Girona', 'give' ),
		'GR' => esc_html__( 'Granada', 'give' ),
		'GU' => esc_html__( 'Guadalajara', 'give' ),
		'SS' => esc_html__( 'Gipuzkoa', 'give' ),
		'H'  => esc_html__( 'Huelva', 'give' ),
		'HU' => esc_html__( 'Huesca', 'give' ),
		'J'  => esc_html__( 'Ja&eacute;n', 'give' ),
		'LO' => esc_html__( 'La Rioja', 'give' ),
		'GC' => esc_html__( 'Las Palmas', 'give' ),
		'LE' => esc_html__( 'Le&oacute;n', 'give' ),
		'L'  => esc_html__( 'Lleida', 'give' ),
		'LU' => esc_html__( 'Lugo', 'give' ),
		'M'  => esc_html__( 'Madrid', 'give' ),
		'MA' => esc_html__( 'M&aacute;laga', 'give' ),
		'ML' => esc_html__( 'Melilla', 'give' ),
		'MU' => esc_html__( 'Murcia', 'give' ),
		'NA' => esc_html__( 'Navarra', 'give' ),
		'OR' => esc_html__( 'Ourense', 'give' ),
		'P'  => esc_html__( 'Palencia', 'give' ),
		'PO' => esc_html__( 'Pontevedra', 'give' ),
		'SA' => esc_html__( 'Salamanca', 'give' ),
		'TF' => esc_html__( 'Santa Cruz de Tenerife', 'give' ),
		'SG' => esc_html__( 'Segovia', 'give' ),
		'SE' => esc_html__( 'Sevilla', 'give' ),
		'SO' => esc_html__( 'Soria', 'give' ),
		'T'  => esc_html__( 'Tarragona', 'give' ),
		'TE' => esc_html__( 'Teruel', 'give' ),
		'TO' => esc_html__( 'Toledo', 'give' ),
		'V'  => esc_html__( 'Valencia', 'give' ),
		'VA' => esc_html__( 'Valladolid', 'give' ),
		'BI' => esc_html__( 'Bizkaia', 'give' ),
		'ZA' => esc_html__( 'Zamora', 'give' ),
		'Z'  => esc_html__( 'Zaragoza', 'give' ),
	);

	return apply_filters( 'give_spain_states', $states );
}
