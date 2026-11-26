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
    private const MODES = ['test', 'live'];

    /**
     * @var DeleteSiteWebhookEndpoint
     */
    private $deleteSiteWebhookEndpoint;

    /**
     * @var WebhookTarget
     */
    private $target;

    /**
     * @since 4.20.0
     */
    public function __construct(DeleteSiteWebhookEndpoint $deleteSiteWebhookEndpoint, WebhookTarget $target)
    {
        $this->deleteSiteWebhookEndpoint = $deleteSiteWebhookEndpoint;
        $this->target = $target;
    }

    /**
     * @since 4.20.0 Delete this site's webhook endpoint from the Stripe account after removing the account.
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
        $failedModes = [];

        if ($isSaved) {
            $failedModes = $account ?
                $this->deleteWebhookEndpoints($account, $this->remainingAccounts()) :
                self::MODES;
        }

        wp_send_json_success($failedModes ? ['webhookWarning' => $this->cleanupWarning($failedModes)] : null);
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
     * @since 4.20.0
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
     * @since 4.20.0
     *
     * @param AccountDetail   $account           The account that was just removed, with its key.
     * @param AccountDetail[] $remainingAccounts The accounts still saved; an endpoint one of them uses is kept.
     *
     * @return string[] The modes in which the endpoint may still be on the account, empty when nothing is left to delete.
     */
    private function deleteWebhookEndpoints(AccountDetail $account, array $remainingAccounts): array
    {
        try {
            $results = ($this->deleteSiteWebhookEndpoint)($account, $remainingAccounts);
        } catch (Throwable $exception) {
            WebhookLogger::warning(
                'Stripe webhook endpoint was not deleted on disconnect.',
                array_merge(['account' => $account->accountSlug], WebhookLogger::failureContext($exception))
            );

            return self::MODES;
        }

        return array_keys(array_filter($results, static function (string $result): bool {
            return DeleteSiteWebhookEndpoint::FAILED === $result;
        }));
    }

    /**
     * The accounts still saved after the removal. A record that cannot be read holds no usable key, so it
     * is left out.
     *
     * @since 4.20.0
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
     * @since 4.20.0
     *
     * @param string[] $failedModes The modes in which the endpoint may still be on the account.
     */
    private function cleanupWarning(array $failedModes): string
    {
        $urls = $this->target->urls();

        return $this->cleanupInstructions($urls) . $this->cleanupModeNote($failedModes);
    }

    /**
     * @since 4.20.0
     *
     * @param string[] $urls Webhook endpoint URLs of this site.
     */
    private function cleanupInstructions(array $urls): string
    {
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

    /**
     * Names the mode to clean up when only one failed, because the endpoint in the other mode was deleted or
     * kept for another saved account.
     *
     * @since 4.20.0
     *
     * @param string[] $failedModes The modes in which the endpoint may still be on the account.
     */
    private function cleanupModeNote(array $failedModes): string
    {
        if (count($failedModes) !== 1) {
            return '';
        }

        return ' ' . sprintf(
            /* translators: %s: "live mode" or "test mode". */
            __(
                'Only %s needs this. Do not delete an endpoint in the other mode, because another saved account may still use it.',
                'give'
            ),
            'live' === $failedModes[0] ? __('live mode', 'give') : __('test mode', 'give')
        );
    }
}
