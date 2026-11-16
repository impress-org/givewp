<?php

namespace Give\PaymentGateways\Stripe\Controllers;

use Give\PaymentGateways\Stripe\DataTransferObjects\DisconnectStripeAccountDto;
use Give\PaymentGateways\Stripe\Models\AccountDetail;
use Give\PaymentGateways\Stripe\Webhooks\Actions\DeleteSiteWebhookEndpoint;
use Give\PaymentGateways\Stripe\Webhooks\WebhookLogger;
use Give\PaymentGateways\Stripe\Webhooks\WebhookTarget;
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
     * @var WebhookTarget
     */
    private $target;

    /**
     * @since TBD
     */
    public function __construct(DeleteSiteWebhookEndpoint $deleteSiteWebhookEndpoint, WebhookTarget $target)
    {
        $this->deleteSiteWebhookEndpoint = $deleteSiteWebhookEndpoint;
        $this->target = $target;
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

        $accounts = give_stripe_get_all_accounts();
        $isSaved = array_key_exists($requestedData->accountSlug, $accounts);
        $account = $isSaved ?
            $this->accountFrom($accounts[$requestedData->accountSlug], $requestedData->accountSlug) :
            null;

        give_stripe_disconnect_account($requestedData->accountSlug);

        /* A saved record that cannot be read may still have an endpoint on Stripe, so it counts as a failed delete. */
        $deleted = !$isSaved || ($account && $this->deleteWebhookEndpoints($account, $this->remainingAccounts()));

        wp_send_json_success($deleted ? null : ['webhookWarning' => $this->cleanupWarning()]);
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
     * @param mixed  $record      The record saved under the slug.
     * @param string $accountSlug Slug of the saved Stripe account being disconnected.
     *
     * @return AccountDetail|null The account, or null when the record cannot be read.
     */
    private function accountFrom($record, string $accountSlug): ?AccountDetail
    {
        try {
            return AccountDetail::fromArray(is_array($record) ? $record : []);
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
     * @param AccountDetail   $account           The account that was just removed, with its key.
     * @param AccountDetail[] $remainingAccounts The accounts still saved; an endpoint one of them uses is kept.
     *
     * @return bool Whether nothing is left on the account that the admin has to delete.
     */
    private function deleteWebhookEndpoints(AccountDetail $account, array $remainingAccounts): bool
    {
        try {
            $results = ($this->deleteSiteWebhookEndpoint)($account, $remainingAccounts);
        } catch (Throwable $exception) {
            WebhookLogger::warning(
                'Stripe webhook endpoint was not deleted on disconnect.',
                array_merge(['account' => $account->accountSlug], WebhookLogger::failureContext($exception))
            );

            return false;
        }

        return !in_array(DeleteSiteWebhookEndpoint::FAILED, $results, true);
    }

    /**
     * The accounts still saved after the removal. A record that cannot be read holds no usable key, so it
     * is left out.
     *
     * @since TBD
     *
     * @return AccountDetail[]
     */
    private function remainingAccounts(): array
    {
        $accounts = [];

        foreach (give_stripe_get_all_accounts() as $record) {
            try {
                $accounts[] = AccountDetail::fromArray(is_array($record) ? $record : []);
            } catch (Throwable $exception) {
                continue;
            }
        }

        return $accounts;
    }

    /**
     * The message the settings page shows when the endpoint may still be on the account.
     *
     * @since TBD
     */
    private function cleanupWarning(): string
    {
        $urls = $this->target->urls();

        if (1 === count($urls)) {
            return sprintf(
                /* translators: %s: Webhook endpoint URL of this site. */
                __(
                    'The account is disconnected, but GiveWP could not delete its webhook endpoint for this site. In the Webhooks section of your Stripe Dashboard, delete every endpoint for %s.',
                    'give'
                ),
                $urls[0]
            );
        }

        return sprintf(
            /* translators: 1: Webhook endpoint URL from the home URL. 2: Webhook endpoint URL from the site URL. */
            __(
                'The account is disconnected, but GiveWP could not delete its webhook endpoint for this site. In the Webhooks section of your Stripe Dashboard, check both %1$s and %2$s, and delete every endpoint for either one.',
                'give'
            ),
            $urls[0],
            $urls[1]
        );
    }
}
