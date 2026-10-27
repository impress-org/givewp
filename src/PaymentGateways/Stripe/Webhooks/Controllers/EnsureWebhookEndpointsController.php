<?php

/**
 * @since TBD
 */

namespace Give\PaymentGateways\Stripe\Webhooks\Controllers;

use Give\PaymentGateways\Stripe\Models\AccountDetail;
use Give\PaymentGateways\Stripe\Webhooks\Actions\EnsureWebhookEndpoint;
use Give\PaymentGateways\Stripe\Webhooks\Actions\GetStripeAccountsForMode;
use Give\PaymentGateways\Stripe\Webhooks\ValueObjects\WebhookSetupStatus;
use Give\PaymentGateways\Stripe\Webhooks\WebhookLogger;
use Give\PaymentGateways\Stripe\Webhooks\WebhookSetupNotice;
use Throwable;

/**
 * Sets up the webhook endpoint of every Stripe account for the current mode and answers with the modal content.
 *
 * @since TBD
 */
final class EnsureWebhookEndpointsController
{
    public const ACTION = 'givewp_stripe_ensure_webhook_endpoints';

    /**
     * @var GetStripeAccountsForMode
     */
    private $getAccounts;

    /**
     * @var EnsureWebhookEndpoint
     */
    private $ensureEndpoint;

    /**
     * @var WebhookSetupNotice
     */
    private $notice;

    /**
     * @since TBD
     */
    public function __construct(
        GetStripeAccountsForMode $getAccounts,
        EnsureWebhookEndpoint $ensureEndpoint,
        WebhookSetupNotice $notice
    ) {
        $this->getAccounts = $getAccounts;
        $this->ensureEndpoint = $ensureEndpoint;
        $this->notice = $notice;
    }

    /**
     * @since TBD
     */
    public function __invoke(): void
    {
        check_ajax_referer(self::ACTION);

        if (!current_user_can('manage_give_settings')) {
            wp_send_json_error(null, 403);
        }

        $mode = give_is_test_mode() ? 'test' : 'live';
        $results = [];

        foreach (($this->getAccounts)($mode) as $account) {
            try {
                $status = ($this->ensureEndpoint)($account, $mode);
                $message = '';
            } catch (Throwable $exception) {
                $status = WebhookSetupStatus::fromException($exception);
                $message = $exception->getMessage();

                $this->logFailure($account, $mode, $status, $exception);
            }

            $results[] = ['account' => $account, 'status' => $status, 'message' => $message];
        }

        wp_send_json_success($this->notice->build($results, $mode));
    }

    /**
     * @since TBD
     *
     * @param string $mode "live" or "test"
     */
    private function logFailure(
        AccountDetail $account,
        string $mode,
        WebhookSetupStatus $status,
        Throwable $exception
    ): void {
        $context = array_merge(
            [
                'account' => $account->accountSlug,
                'account_type' => $account->type,
                'mode' => $mode,
                'status' => $status->getValue(),
            ],
            WebhookLogger::failureContext($exception)
        );

        /* An unclassified failure is the one support has to investigate, so it is the only error level. */
        if ($status->isUnknown()) {
            WebhookLogger::error('Stripe webhook setup failed.', $context);

            return;
        }

        WebhookLogger::warning('Stripe webhook setup did not complete.', $context);
    }
}
