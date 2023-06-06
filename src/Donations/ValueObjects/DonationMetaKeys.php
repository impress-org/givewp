<?php

namespace Give\Donations\ValueObjects;

use Give\Framework\Support\ValueObjects\Enum;
use Give\Framework\Support\ValueObjects\EnumInteractsWithQueryBuilder;

/**
 * @since 2.20.0 add fee amount recovered and exchange rate
 * @since 2.19.6
 *
 * @method static DonationMetaKeys AMOUNT()
 * @method static DonationMetaKeys CURRENCY()
 * @method static DonationMetaKeys GATEWAY()
 * @method static DonationMetaKeys DONOR_ID()
 * @method static DonationMetaKeys FIRST_NAME()
 * @method static DonationMetaKeys LAST_NAME()
 * @method static DonationMetaKeys EMAIL()
 * @method static DonationMetaKeys SUBSCRIPTION_ID()
 * @method static DonationMetaKeys MODE()
 * @method static DonationMetaKeys FORM_ID()
 * @method static DonationMetaKeys FORM_TITLE()
 * @method static DonationMetaKeys BILLING_COUNTRY()
 * @method static DonationMetaKeys BILLING_ADDRESS1()
 * @method static DonationMetaKeys BILLING_ADDRESS2()
 * @method static DonationMetaKeys BILLING_CITY()
 * @method static DonationMetaKeys BILLING_STATE()
 * @method static DonationMetaKeys BILLING_ZIP()
 * @method static DonationMetaKeys PURCHASE_KEY()
 * @method static DonationMetaKeys DONOR_IP()
 * @method static DonationMetaKeys ANONYMOUS()
 * @method static DonationMetaKeys LEVEL_ID()
 * @method static DonationMetaKeys COMPANY()
 * @method static DonationMetaKeys COMMENT()
 * @method static DonationMetaKeys GATEWAY_TRANSACTION_ID()
 * @method static DonationMetaKeys SUBSCRIPTION_INITIAL_DONATION()
 * @method static DonationMetaKeys IS_RECURRING()
 * @method static DonationMetaKeys FEE_AMOUNT_RECOVERED()
 * @method static DonationMetaKeys EXCHANGE_RATE()
 */
class DonationMetaKeys extends Enum
{
    use EnumInteractsWithQueryBuilder;

    const AMOUNT = '_give_payment_total';
    const BASE_AMOUNT = '_give_cs_base_amount';
    const CURRENCY = '_give_payment_currency';
    const EXCHANGE_RATE = '_give_cs_exchange_rate';
    const FEE_AMOUNT_RECOVERED = '_give_fee_amount';
    const GATEWAY = '_give_payment_gateway';
    const DONOR_ID = '_give_payment_donor_id';
    const FIRST_NAME = '_give_donor_billing_first_name';
    const LAST_NAME = '_give_donor_billing_last_name';
    const EMAIL = '_give_payment_donor_email';
    const SUBSCRIPTION_ID = 'subscription_id';
    const MODE = '_give_payment_mode';
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
    const ANONYMOUS = '_give_anonymous_donation';
    const LEVEL_ID = '_give_payment_price_id';
    const COMPANY = '_give_donation_company';
    const COMMENT = '_give_donation_comment';
    const GATEWAY_TRANSACTION_ID = '_give_payment_transaction_id';
    const SUBSCRIPTION_INITIAL_DONATION = '_give_subscription_payment';
    const IS_RECURRING = '_give_is_donation_recurring';
}
