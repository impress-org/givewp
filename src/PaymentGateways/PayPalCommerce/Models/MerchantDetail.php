<?php

namespace Give\PaymentGateways\PayPalCommerce\Models;

use Give\Framework\Exceptions\Primitives\InvalidArgumentException;

/**
 * Class MerchantDetail
 * @package Give\PaymentGateways\PayPalCommerce
 *
 * @since 2.9.0
 */
class MerchantDetail
{
    /**
     * PayPal merchant Id  (email address)
     *
     * @since 2.9.0
     *
     * @var null|string
     */
    public $merchantId = null;

    /**
     * PayPal merchant id
     *
     * @since 2.9.0
     *
     * @var null|string
     */
    public $merchantIdInPayPal = null;

    /**
     * Client id.
     *
     * @since 2.9.0
     *
     * @var null |string
     */
    public $clientId = null;

    /**
     * Client Secret
     *
     * @since 2.9.0
     *
     * @var null|string
     */
    public $clientSecret = null;

    /**
     * Access token.
     *
     * @since 2.9.0
     *
     * @var null|string
     */
    public $accessToken = null;

    /**
     * Whether or not the connected account is ready to process donations.
     *
     * @since 2.9.0
     *
     * @var bool
     */
    public $accountIsReady = false;

    /**
     * Whether or not the account can make custom payments (i.e Advanced Fields & PPCP)
     *
     * @since 2.9.0
     *
     * @var bool
     */
    public $supportsCustomPayments;

    /**
     * PayPal account accountCountry.
     *
     * @since 2.9.0
     *
     * @var bool
     */
    public $accountCountry;

    /**
     * Access token.
     *
     * @since 2.9.0
     *
     * @var array
     */
    private $tokenDetails = null;

    /**
     * Return array of merchant details.
     *
     * @sicne 2.9.0
     *
     * @return array
     */
    public function toArray()
    {
        return [
            'merchantId' => $this->merchantId,
            'merchantIdInPayPal' => $this->merchantIdInPayPal,
            'clientId' => $this->clientId,
            'clientSecret' => $this->clientSecret,
            'token' => $this->tokenDetails,
            'accountIsReady' => $this->accountIsReady,
            'supportsCustomPayments' => $this->supportsCustomPayments,
            'accountCountry' => $this->accountCountry,
        ];
    }

    /**
     * Make MerchantDetail object from array.
     *
     * @since 2.9.0
     *
     * @param array $merchantDetails
     *
     * @return MerchantDetail
     */
    public static function fromArray($merchantDetails)
    {
        $obj = new static();

        if ( ! $merchantDetails) {
            return $obj;
        }

        $obj->validate($merchantDetails);
        $obj->setupProperties($merchantDetails);

        return $obj;
    }

    /**
     * Setup properties from array.
     *
     * @since 2.9.0
     *
     * @param $merchantDetails
     *
     */
    private function setupProperties($merchantDetails)
    {
        $this->merchantId = $merchantDetails['merchantId'];
        $this->merchantIdInPayPal = $merchantDetails['merchantIdInPayPal'];

        $this->clientId = $merchantDetails['clientId'];
        $this->clientSecret = $merchantDetails['clientSecret'];
        $this->tokenDetails = $merchantDetails['token'];
        $this->accountIsReady = $merchantDetails['accountIsReady'];
        $this->supportsCustomPayments = $merchantDetails['supportsCustomPayments'];
        $this->accountCountry = $merchantDetails['accountCountry'];
        $this->accessToken = $this->tokenDetails['accessToken'];
    }

    /**
     * Validate merchant details.
     *
     * @since 2.9.0
     *
     * @param array $merchantDetails
     */
    private function validate($merchantDetails)
    {
        $required = [
            'merchantId',
            'merchantIdInPayPal',
            'clientId',
            'clientSecret',
            'token',
            'accountIsReady',
            'supportsCustomPayments',
            'accountCountry',
        ];

        if (array_diff($required, array_keys($merchantDetails))) {
            throw new InvalidArgumentException(
                esc_html__(
                    'To create a MerchantDetail object, please provide the following: ' . implode(', ', $required),
                    'give'
                )
            );
        }
    }

    /**
     * Get refresh token code.
     *
     * @since 2.9.0
     *
     * @param array $tokenDetails
     *
     * @return mixed
     */
    public function setTokenDetails($tokenDetails)
    {
        $this->tokenDetails = array_merge($this->tokenDetails, $tokenDetails);
    }
}
