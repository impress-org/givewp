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
     * @since TBD Delete this site's webhook endpoint from the Stripe account after removing the account.
     * @since 2.13.0
     */
    public function __invoke()
    {
        $this->validateRequest();

        // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- the account slug is needed to build the nonce action, and securityCheck() verifies the nonce with check_admin_referer() right after.
        $requestedData = DisconnectStripeAccountDto::fromArray(give_clean($_GET));

        $this->securityCheck($requestedData->accountSlug);

        $account = $this->savedAccount($requestedData->accountSlug);

        give_stripe_disconnect_account($requestedData->accountSlug);

        if ($account) {
            $this->deleteWebhookEndpoints($account);
        }

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
     * The saved account, read before it is removed because deleting its webhook endpoint needs its key.
     *
     * @since TBD
     *
     * @param string $accountSlug Slug of the saved Stripe account being disconnected.
     *
     * @return AccountDetail|null The account, or null when no account is saved under the slug.
     */
    private function savedAccount(string $accountSlug): ?AccountDetail
    {
        $accounts = give_stripe_get_all_accounts();

        if (!isset($accounts[$accountSlug]) || !is_array($accounts[$accountSlug])) {
            return null;
        }

        try {
            return AccountDetail::fromArray($accounts[$accountSlug]);
        } catch (Throwable $exception) {
            WebhookLogger::warning(
                'Stripe webhook endpoint was not deleted on disconnect.',
                array_merge(['account' => $accountSlug], WebhookLogger::failureContext($exception))
            );

            return null;
        }
    }

    /**
     * Runs after the account is removed, so a slow or failing Stripe request cannot leave the account
     * connected. A failure is logged and the disconnect still answers with success.
     *
     * @since TBD
     *
     * @param AccountDetail $account The account that was just removed, with its key.
     */
    private function deleteWebhookEndpoints(AccountDetail $account): void
    {
        try {
            ($this->deleteSiteWebhookEndpoint)($account);
        } catch (Throwable $exception) {
            WebhookLogger::warning(
                'Stripe webhook endpoint was not deleted on disconnect.',
                array_merge(['account' => $account->accountSlug], WebhookLogger::failureContext($exception))
            );
        }
    }
}
