<?php

namespace Give\PaymentGateways\PayPalCommerce;

use Give\PaymentGateways\PayPalCommerce\Models\MerchantDetail;

/**
 * Class Utils
 *
 * @since 2.9.0
 */
class Utils
{
    /**
     * Returns whether or not the PayPal Commerce gateway is active
     *
     * @since 2.9.0
     *
     * @return bool
     */
    public static function gatewayIsActive()
    {
        return give_is_gateway_active(PayPalCommerce::GATEWAY_ID);
    }

    /**
     * Return whether or not payment gateway accept payment.
     *
     * @since 2.9.6
     * @return bool
     */
    public static function isAccountReadyToAcceptPayment()
    {
        /* @var MerchantDetail $merchantDetail */
        $merchantDetail = give(MerchantDetail::class);

        return (bool)$merchantDetail->accountIsReady;
    }

    /**
     * this function should return true if country supports "Donation" transaction type.
     *
     * @since 3.0.0
     */
    public static function isDonationTransactionTypeSupported(string $country): bool
    {
        // Set fallback country.
        $country = $country ?: give_get_country();

        $countries = [
            'AD', // Andorra
            'AR', // Argentina
            'AW', // Aruba
            'AU', // Australia
            'AT', // Austria
            'AZ', // Azerbaijan
            'BY', // Belarus
            'BE', // Belgium
            'BM', // Bermuda
            'BT', // Bhutan
            'BR', // Brazil
            'BN', // Brunei
            'BG', // Bulgaria
            'BI', // Burundi
            'KH', // Cambodia
            'CM', // Cameroon
            'CA', // Canada
            'KY', // Cayman Islands
            'TD', // Chad
            'CL', // Chile
            'CO', // Colombia
            'KM', // Comoros
            'CK', // Cook Islands
            'CR', // Costa Rica
            'CI', // Cote D'Ivoire
            'CY', // Cyprus
            'CZ', // Czech Republic
            'CD', // Democratic Republic of the Congo
            'DK', // Denmark
            'DJ', // Djibouti
            'DO', // Dominican Republic
            'SV', // El Salvador
            'ER', // Eritrea
            'EE', // Estonia
            'FK', // Falkland Islands
            'FO', // Faroe Islands
            'FI', // Finland
            'FR', // France
            'GF', // French Guiana
            'GA', // Gabon Republic
            'DE', // Germany
            'GI', // Gibraltar
            'GR', // Greece
            'GL', // Greenland
            'GP', // Guadeloupe
            'GT', // Guatemala
            'GW', // Guinea Bissau
            'HU', // Hungary
            'IS', // Iceland
            'IE', // Ireland
            'IL', // Israel
            'IT', // Italy
            'JM', // Jamaica
            'KE', // Kenya
            'KI', // Kiribati
            'KW', // Kuwait
            'LA', // Laos
            'LV', // Latvia
            'LI', // Liechtenstein
            'LT', // Lithuania
            'LU', // Luxembourg
            'MK', // Macedonia
            'MV', // Maldives
            'MT', // Malta
            'MH', // Marshall Islands
            'MQ', // Martinique
            'MR', // Mauritania
            'YT', // Mayotte
            'MX', // Mexico
            'FM', // Micronesia
            'MN', // Mongolia
            'ME', // Montenegro
            'MS', // Montserrat
            'NR', // Nauru
            'NP', // Nepal
            'NL', // Netherlands
            'NZ', // New Zealand
            'NE', // Niger
            'NG', // Nigeria
            'NU', // Niue
            'NF', // Norfolk Island
            'NO', // Norway
            'PA', // Panama
            'PE', // Peru
            'PH', // Philippines
            'PN', // Pitcairn Islands
            'PL', // Poland
            'PT', // Portugal
            'QA', // Qatar
            'CG', // Republic of the Congo
            'RE', // Reunion
            'RO', // Romania
            'RW', // Rwanda
            'PM', // Saint Pierre and Miquelon
            'VC', // Saint Vincent and Grenadines
            'WS', // Samoa
            'SM', // San Marino
            'ST', // São Tomé and Príncipe
            'SL', // Sierra Leone
            'SG', // Singapore
            'SK', // Slovakia
            'SI', // Slovenia
            'SB', // Solomon Islands
            'SO', // Somalia
            'ZA', // South Africa
            'ES', // Spain
            'LK', // Sri Lanka
            'SH', // St Helena
            'SJ', // Svalbard and Jan Mayen Islands
            'SE', // Sweden
            'CH', // Switzerland
            'TO', // Tonga
            'TV', // Tuvalu
            'UA', // Ukraine
            'AE', // United Arab Emirates
            'GB', // United Kingdom (Great Britain)
            'US', // United States
            'UY', // Uruguay
            'VU', // Vanuatu
            'VA', // Vatican City
            'VG', // Virgin Islands British
            'WF', // Wallis and Futuna Islands
            'YE', // Yemen
            'ZW' // Zimbabwe
        ];

        return in_array($country, $countries, true);
    }
}
