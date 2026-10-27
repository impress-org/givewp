<?php

namespace Give\PaymentGateways\Stripe\Controllers;

use Give\PaymentGateways\Stripe\DataTransferObjects\DisconnectStripeAccountDto;
use Give\PaymentGateways\Stripe\Models\AccountDetail;
use Give\PaymentGateways\Stripe\Webhooks\Actions\DeleteSiteWebhookEndpoint;
use Give\PaymentGateways\Stripe\Webhooks\WebhookLogger;
use Throwable;

/**
 * Class DisconnectStripeAccountController
 * @package Give\PaymentGateways\Stripe\Controllers
 *
 * @since 2.13.0
 */
class DisconnectStripeAccountController
{
    /**
     * @var DeleteSiteWebhookEndpoint
     */
    private $deleteSiteWebhookEndpoint;

    /**
     * @since TBD
     */
    public function __construct(DeleteSiteWebhookEndpoint $deleteSiteWebhookEndpoint)
    {
        $this->deleteSiteWebhookEndpoint = $deleteSiteWebhookEndpoint;
    }

    /**
     * @since TBD Delete this site's webhook endpoint from the Stripe account before removing the account.
     * @since 2.13.0
     */
    public function __invoke()
    {
        $this->validateRequest();

        // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- the account slug is needed to build the nonce action, and securityCheck() verifies the nonce with check_admin_referer() right after.
        $requestedData = DisconnectStripeAccountDto::fromArray(give_clean($_GET));

        $this->securityCheck($requestedData->accountSlug);

        $this->deleteWebhookEndpoints($requestedData->accountSlug);

        give_stripe_disconnect_account($requestedData->accountSlug);

        wp_send_json_success();
    }

    /**
     * @since 2.13.0
     */
    private function validateRequest()
    {
        if ( ! current_user_can('manage_give_settings')) {
            die();
        }
    }

    /**
     * @since 2.13.0
     *
     * @param $accountSlug
     */
    private function securityCheck($accountSlug)
    {
        if ( ! check_admin_referer('give_disconnect_connected_stripe_account_' . $accountSlug)) {
            die();
        }
    }

    /**
     * Runs before the account is removed because the request needs the account's key. A failure is logged and
     * does not stop the disconnect.
     *
     * @since TBD
     *
     * @param string $accountSlug
     */
    private function deleteWebhookEndpoints($accountSlug)
    {
        $accounts = give_stripe_get_all_accounts();

        if (!isset($accounts[$accountSlug]) || !is_array($accounts[$accountSlug])) {
            return;
        }

        try {
            ($this->deleteSiteWebhookEndpoint)(AccountDetail::fromArray($accounts[$accountSlug]));
        } catch (Throwable $exception) {
            WebhookLogger::warning(
                'Stripe webhook endpoint was not deleted on disconnect.',
                array_merge(['account' => $accountSlug], WebhookLogger::failureContext($exception))
            );
        }
    }
}
