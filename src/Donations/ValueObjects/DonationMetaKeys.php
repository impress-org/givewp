<?php

namespace Give\Donations\ValueObjects;

use MyCLabs\Enum\Enum;

/**
 * @unreleased
 */
class DonationMetaKeys extends Enum {
    const TOTAL = '_give_payment_total';
    const CURRENCY = '_give_payment_currency';
    const GATEWAY = '_give_payment_gateway';
    const DONOR_ID = '_give_payment_donor_id';
    const BILLING_FIRST_NAME = '_give_donor_billing_first_name';
    const BILLING_LAST_NAME = '_give_donor_billing_last_name';
    const DONOR_EMAIL = '_give_payment_donor_email';
    const SUBSCRIPTION_ID = 'subscription_id';
    const PAYMENT_MODE = '_give_payment_mode';
    const FORM_ID = '_give_payment_form_id';
    const FORM_TITLE = '_give_payment_form_title';
    const BILLING_COUNTRY = '_give_donor_billing_country';
    const BILLING_ADDRESS2 = '_give_donor_billing_address2';
    const BILLING_CITY = '_give_donor_billing_city';
    const BILLING_ADDRESS1 = '_give_donor_billing_address1';
    const BILLING_STATE = '_give_donor_billing_state';
    const BILLING_ZIP = '_give_donor_billing_zip';
    const PURCHASE_KEY = '_give_payment_purchase_key';
    const DONOR_IP = '_give_payment_donor_ip';
    const ANONYMOUS_DONATION = '_give_anonymous_donation';
    const PAYMENT_PRICE_ID = '_give_payment_price_id';
    const DONATION_COMPANY = '_give_donation_company';

    /**
     * @return array
     */
    public static function getAllKeys()
    {
        return array_values(self::toArray());
    }
}
