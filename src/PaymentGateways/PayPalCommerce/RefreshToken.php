<?php

namespace Give\PaymentGateways\PayPalCommerce;

use Give\Framework\Exceptions\Primitives\Exception;
use Give\Log\Log;
use Give\PaymentGateways\PayPalCommerce\Models\MerchantDetail;
use Give\PaymentGateways\PayPalCommerce\Repositories\MerchantDetails;
use Give\PaymentGateways\PayPalCommerce\Repositories\PayPalAuth;
use Give\PaymentGateways\PayPalCommerce\Repositories\Traits\HasMode;

/**
 * Class RefreshToken
 *
 * @since 2.30.0 Add support for mode.
 * @since 2.9.0
 */
class RefreshToken
{
    use HasMode;

    /* @var MerchantDetail */
    private $merchantDetail;

    /**
     * @since 2.9.0
     *
     * @var MerchantDetails
     */
    private $detailsRepository;

    /**
     * @since 2.9.0
     *
     * @var PayPalAuth
     */
    private $payPalAuth;

    /**
     * This time reduced from token expiration time to refresh token before it expires.
     *
     * @since 2.25.0
     *
     * @var int $expirationTimeOffset Expiration time offset in seconds.
     */
    private $expirationTimeOffset = 1800; // 30 minutes

    /**
     * RefreshToken constructor.
     *
     * @since 2.9.0
     * @since 2.9.6 Add MerchantDetail constructor param.
     *
     * @param MerchantDetails $detailsRepository
     * @param PayPalAuth $payPalAuth
     * @param MerchantDetail $merchantDetail
     */
    public function __construct(
        MerchantDetails $detailsRepository,
        PayPalAuth $payPalAuth,
        MerchantDetail $merchantDetail
    ) {
        $this->detailsRepository = $detailsRepository;
        $this->payPalAuth = $payPalAuth;
        $this->merchantDetail = $merchantDetail;

        $this->setMode(give_is_test_mode() ? 'sandbox' : 'live');
    }

    /**
     * Return cron json name which uses to refresh token.
     *
     * @since 2.9.0
     */
    private function getCronJobHookName(): string
    {
        return "give_paypal_commerce_refresh_{$this->mode}_token";
    }

    /**
     * Register cron job to refresh access token.
     * Note: only for internal use.
     *
     * @since 2.9.0
     *
     * @param string $tokenExpires
     *
     */
    public function registerCronJobToRefreshToken($tokenExpires)
    {
        // Refresh token before half hours of expires date.
        wp_schedule_single_event(
            time() + ($tokenExpires - $this->expirationTimeOffset),
            $this->getCronJobHookName()
        );
    }

    /**
     * Delete cron job which refresh access token.
     * Note: only for internal use.
     *
     * @since 2.9.0
     *
     */
    public function deleteRefreshTokenCronJob()
    {
        wp_clear_scheduled_hook($this->getCronJobHookName());
    }

    /**
     * Refresh token.
     * Note: only for internal use
     *
     * @since 2.25.0 Handle exception. Refresh access token every 5 minute on faliure.
     * @since 2.9.6 Refresh token only if paypal merchant id exist.
     * @since 2.9.0
     */
    public function refreshToken()
    {
        // Exit if account is not connected.
        if (! $this->detailsRepository->accountIsConnected()) {
            return;
        }

        // Default expiration date of access token.
        // This is used when we are unable to get access token from PayPal.
        $expiresIn = $this->expirationTimeOffset - 1500; // 5 minutes

        try {
            $tokenDetails = $this->payPalAuth->getTokenFromClientCredentials(
                $this->merchantDetail->clientId,
                $this->merchantDetail->clientSecret
            );

            $this->merchantDetail->setTokenDetails($tokenDetails);
            $this->detailsRepository->save($this->merchantDetail);

            $expiresIn = $tokenDetails['expiresIn'];
        } catch (Exception $exception) {
            give(Log::class)->warning(
                'PayPal Commerce: Error refresh access token',
                [
                    'category' => 'Payment Gateway',
                    'source' => 'Paypal Commerce',
                    'exception' => $exception,
                ]
            );
        }

        $this->deleteRefreshTokenCronJob();
        $this->registerCronJobToRefreshToken($expiresIn);
    }

    /**
     * This function handles cron job to refresh access token.
     *
     * Cron job action names:
     * - give_paypal_commerce_refresh_sandbox_token
     * - give_paypal_commerce_refresh_live_token
     *
     * @since 2.30.0
     * @return void
     */
    public function cronJobRefreshToken()
    {
        // Set mode.
        $mode = 'live';
        if (doing_action('give_paypal_commerce_refresh_sandbox_token')) {
            $mode = 'sandbox';
        }
        $this->setMode($mode);

        // Fetch merchant details.
        $this->detailsRepository->setMode($mode);
        $this->merchantDetail = $this->detailsRepository->getDetails();

        // Set mode to PayPalClient. PayPalAuth use it to make api request to paypal.
        $paypalClient = give(PayPalClient::class);
        $paypalClient->setMode($mode);

        $this->refreshToken();
    }
}
