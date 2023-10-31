<?php

namespace Give\PaymentGateways\PayPalCommerce\Repositories;

use Give\PaymentGateways\PayPalCommerce\Repositories\Traits\HasMode;
use Give\PaymentGateways\PayPalCommerce\Utils;

class Settings
{
    use HasMode;

    /**
     * wp_options key for the account country
     *
     * @since 2.9.0
     */
    const COUNTRY_KEY = 'paypal_commerce_account_country';

    /**
     * wp_options key for the seller access token
     *
     * @since 2.9.0
     */
    const ACCESS_TOKEN_KEY = 'temp_give_paypal_commerce_seller_access_token';

    /**
     * wp_options key for the partner link details
     *
     * @since 2.9.0
     */
    const PARTNER_LINK_DETAIL_KEY = 'temp_give_paypal_commerce_partner_link';

    /**
     * give_settings key for the collect bulling details
     *
     * @since 2.11.1
     */
    const COLLECT_BILLING_DETAILS_KEY = 'paypal_commerce_collect_billing_details';

    /**
     * give_settings key for the collect bulling details
     *
     * @since 2.16.2
     */
    const TRANSACTION_TYPE = 'paypal_commerce_transaction_type';

    /**
     * Returns the country for the account
     *
     * @since 2.9.0
     */
    public function getAccountCountry(): string
    {
        return get_option(self::COUNTRY_KEY, give_get_country()) ?? '';
    }

    /**
     * Returns the PayPal merchant seller access token.
     *
     * @since 2.9.0
     *
     * @return array|null
     */
    public function getAccessToken()
    {
        return get_option(self::ACCESS_TOKEN_KEY, null);
    }

    /**
     * Returns the account access token
     *
     * @since 3.0.0 Set transaction type to "standard" if the country is not supported.
     * @since 2.9.0
     */
    public function getTransactionType(): string
    {
        return Utils::isDonationTransactionTypeSupported($this->getAccountCountry())
            ? give_get_option(self::TRANSACTION_TYPE, 'donation')
            : 'standard';
    }

    /**
     * Updates the country account
     *
     * @param string $country
     *
     * @return bool
     */
    public function updateAccountCountry($country)
    {
        return update_option(self::COUNTRY_KEY, $country);
    }

    /**
     * Updates the PayPal merchant seller access token.
     *
     * @param $token
     *
     * @return bool
     */
    public function updateAccessToken($token)
    {
        return update_option(self::ACCESS_TOKEN_KEY, $token);
    }

    /**
     * Deletes the PayPal seller access token.
     *
     * @since 2.9.0
     *
     * @return bool
     */
    public function deleteAccessToken()
    {
        return delete_option(self::ACCESS_TOKEN_KEY);
    }

    /**
     * Returns the partner link details
     *
     * @since 2.9.0
     *
     * @return string|null
     */
    public function getPartnerLinkDetails()
    {
        return get_option(self::PARTNER_LINK_DETAIL_KEY, null);
    }

    /**
     * Updates the partner link details
     *
     * @param $linkDetails
     *
     * @return bool
     */
    public function updatePartnerLinkDetails($linkDetails)
    {
        return update_option(self::PARTNER_LINK_DETAIL_KEY, $linkDetails);
    }

    /**
     * Deletes the partner link details
     *
     * @return bool
     */
    public function deletePartnerLinkDetails()
    {
        return delete_option(self::PARTNER_LINK_DETAIL_KEY);
    }

    /**
     * Updates the partner link details
     *
     * @since 2.25.0
     */
    public function updateSellerAccessToken(array $sellerAccessToken): bool
    {
        return update_option($this->getSellerAccessTokenOptionName(), $sellerAccessToken);
    }

    /**
     * Updates the partner link details
     *
     * @since 2.25.0
     */
    public function deleteSellerAccessToken(): bool
    {
        return delete_option($this->getSellerAccessTokenOptionName());
    }

    /**
     * Deletes the partner link details
     *
     * @since 2.11.1
     * @return bool
     */
    public function canCollectBillingInformation()
    {
        return give_is_setting_enabled(give_get_option(self::COLLECT_BILLING_DETAILS_KEY));
    }

    /**
     * @since 2.16.2
     */
    public function isTransactionTypeDonation()
    {
        return 'donation' === $this->getTransactionType();
    }

    /**
     * This function returns the seller access token option name
     *
     * @since 2.25.0
     */
    private function getSellerAccessTokenOptionName(): string
    {
        return sprintf(
            'give_paypal_commerce_%s_seller_access_token',
            $this->getMode()
        );
    }
}
