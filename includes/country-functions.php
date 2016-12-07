<?php
/**
 * Country Functions
 *
 * @package     Give
 * @subpackage  Functions
 * @copyright   Copyright (c) 2016, WordImpress
 * @license     https://opensource.org/licenses/gpl-license GNU Public License
 * @since       1.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


/**
 * Get Shop Base Country
 *
 * @since 1.0
 * @return string $country The two letter country code for the shop's base country
 */
function give_get_country() {
	$give_options = give_get_settings();
	$country = isset( $give_options['base_country'] ) ? $give_options['base_country'] : 'US';

	return apply_filters( 'give_give_country', $country );
}

/**
 * Get Shop Base State
 *
 * @since 1.0
 * @return string $state The shop's base state name
 */
function give_get_state() {
	$give_options = give_get_settings();
	$state = isset( $give_options['base_state'] ) ? $give_options['base_state'] : false;

	return apply_filters( 'give_give_state', $state );
}

/**
 * Get Shop States
 *
 * @since 1.0
 *
 * @param null $country
 *
 * @return mixed|void  A list of states for the shop's base country
 */
function give_get_states( $country = null ) {

	if ( empty( $country ) ) {
		$country = give_get_country();
	}

	switch ( $country ) :

		case 'US' :
			$states = give_get_states_list();
			break;
		case 'CA' :
			$states = give_get_provinces_list();
			break;
		case 'AU' :
			$states = give_get_australian_states_list();
			break;
		case 'BR' :
			$states = give_get_brazil_states_list();
			break;
		case 'CN' :
			$states = give_get_chinese_states_list();
			break;
		case 'HK' :
			$states = give_get_hong_kong_states_list();
			break;
		case 'HU' :
			$states = give_get_hungary_states_list();
			break;
		case 'ID' :
			$states = give_get_indonesian_states_list();
			break;
		case 'IN' :
			$states = give_get_indian_states_list();
			break;
		case 'MY' :
			$states = give_get_malaysian_states_list();
			break;
		case 'NZ' :
			$states = give_get_new_zealand_states_list();
			break;
		case 'TH' :
			$states = give_get_thailand_states_list();
			break;
		case 'ZA' :
			$states = give_get_south_african_states_list();
			break;
		case 'ES' :
			$states = give_get_spain_states_list();
			break;
		default :
			$states = array();
			break;

	endswitch;

	return apply_filters( 'give_give_states', $states );
}


/**
 * Get Country List
 *
 * @since 1.0
 * @return array $countries A list of the available countries
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
		'BS' => esc_html__( 'Bahamas', 'give'),
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
		'PH' => esc_html__( 'Phillipines', 'give' ),
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
		'SZ' => esc_html__( 'Swaziland', 'give' ),
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
		'ZW' => esc_html__( 'Zimbabwe', 'give' )
	);

	return apply_filters( 'give_countries', $countries );
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
		'AP' => 'Armed Forces - Pacific'
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
		'AB' => esc_html__('Alberta', 'give' ),
		'BC' => esc_html__('British Columbia', 'give' ),
		'MB' => esc_html__('Manitoba', 'give' ),
		'NB' => esc_html__('New Brunswick', 'give' ),
		'NL' => esc_html__('Newfoundland and Labrador', 'give' ),
		'NS' => esc_html__('Nova Scotia', 'give' ),
		'NT' => esc_html__('Northwest Territories', 'give' ),
		'NU' => esc_html__('Nunavut', 'give' ),
		'ON' => esc_html__('Ontario', 'give' ),
		'PE' => esc_html__('Prince Edward Island', 'give' ),
		'QC' => esc_html__('Quebec', 'give' ),
		'SK' => esc_html__('Saskatchewan', 'give' ),
		'YT' => esc_html__('Yukon', 'give' )
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
		'WA'  => 'Western Australia'
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
		'TO' => 'Tocantins'
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
		'NEW TERRITORIES' => 'New Territories'
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
		'ZA' => 'Zala'
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
		'CN32' => 'Xinjiang / &#26032;&#30086;'
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
		'WC' => 'West Coast'
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
		'PB' => 'Papua Barat'
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
		'PY' => 'Pondicherry (Puducherry)'
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
		'PJY' => 'W.P. Putrajaya'
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
		'WC'  => 'Western Cape'
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
		'TH-35' => 'Yasothon (&#3618;&#3650;&#3626;&#3608;&#3619;)'
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
		'Z'  => esc_html__( 'Zaragoza', 'give' )
	);

	return apply_filters( 'give_spain_states', $states );
}
