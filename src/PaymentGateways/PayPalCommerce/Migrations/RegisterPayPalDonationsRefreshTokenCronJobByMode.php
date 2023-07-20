<?php
/**
 * Register PayPal Donations Refresh Token Cron Job By Mode
 *
 * This migration is used to register cron job for refresh token.
 * Cron job add for live and  sandbox mode if connected.
 */

namespace Give\PaymentGateways\PayPalCommerce\Migrations;

use Give\Framework\Migrations\Contracts\Migration;
use Give\PaymentGateways\PayPalCommerce\RefreshToken;
use Give\PaymentGateways\PayPalCommerce\Repositories\MerchantDetails;

/**
 * Class RegisterPayPalDonationsRefreshTokenCronJobByMode
 *
 * @since 2.30.0
 */
class RegisterPayPalDonationsRefreshTokenCronJobByMode extends Migration
{

    /**
     * @inerhitDoc
     * @since 2.30.0
     */
    public function run()
    {
        $liveRefreshToken = clone give(RefreshToken::class);
        $liveRefreshToken->setMode('live');

        $sandboxRefreshToken = clone give(RefreshToken::class);
        $sandboxRefreshToken->setMode('sandbox');

        // Clean up any existing cron jobs.
        wp_unschedule_hook('give_paypal_commerce_refresh_token'); // Legacy cron job.
        $liveRefreshToken->deleteRefreshTokenCronJob();
        $sandboxRefreshToken->deleteRefreshTokenCronJob();

        // Register cron job for live mode if connected.
        $liveMerchantDetailsRepository = clone give(MerchantDetails::class);
        $liveMerchantDetailsRepository->setMode('live');
        $liveMerchantDetails = $liveMerchantDetailsRepository->getDetails();

        if ($liveMerchantDetails->accountIsReady) {
            $liveRefreshToken->registerCronJobToRefreshToken($liveMerchantDetails->toArray()['token']['expiresIn']);
        }

        // Register cron job for sandbox mode if connected.
        $sandboxMerchantDetailsRepository = clone give(MerchantDetails::class);
        $sandboxMerchantDetailsRepository->setMode('sandbox');
        $sandboxMerchantDetails = $sandboxMerchantDetailsRepository->getDetails();

        if ($sandboxMerchantDetails->accountIsReady) {
            $sandboxRefreshToken->registerCronJobToRefreshToken($sandboxMerchantDetails->toArray()['token']['expiresIn']);
        }
    }

    /**
     * @inerhitDoc
     * @since 2.30.0
     */
    public static function id()
    {
        return 'register-paypal-donations-refresh-token-cron-job-by-mode';
    }

    /**
     * @inerhitDoc
     * @since 2.30.0
     */
    public static function timestamp()
    {
        return strtotime('2023-06-21');
    }

    /**
     * @inerhitDoc
     * @since 2.30.0
     */
    public static function title()
    {
        return 'Register PayPal Donations Refresh Token Cron Job By Mode';
    }
}
