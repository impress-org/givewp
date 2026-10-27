<?php

/**
 * @since TBD
 */

namespace Give\PaymentGateways\Stripe\Webhooks;

use Give\PaymentGateways\Stripe\Models\AccountDetail;
use Give\PaymentGateways\Stripe\Webhooks\ValueObjects\WebhookSetupStatus;

/**
 * Builds the content of the modal shown after a Stripe account is added, from the setup result of each account.
 *
 * @since TBD
 */
final class WebhookSetupNotice
{
    /**
     * @var WebhookTarget
     */
    private $target;

    /**
     * @since TBD
     */
    public function __construct(WebhookTarget $target)
    {
        $this->target = $target;
    }

    /**
     * @since TBD
     *
     * @param array<int, array{account: AccountDetail, status: WebhookSetupStatus, message: string}> $results
     * @param string $mode "live" or "test"
     *
     * @return array{level: string, title: string, body: string, retry: bool}
     */
    public function build(array $results, string $mode): array
    {
        $unknown = array_filter($results, static function (array $result) {
            return $result['status']->isUnknown();
        });

        if (!$results || $unknown) {
            return [
                'level' => 'error',
                'title' => esc_html__('We could not set up your Stripe webhook automatically.', 'give'),
                'body' => $this->manualInstructions(),
                'retry' => true,
            ];
        }

        $failed = array_filter($results, static function (array $result) {
            return !$result['status']->isSuccessful();
        });

        $items = '';

        foreach ($results as $result) {
            $items .= sprintf(
                '<li><strong>%1$s</strong> %2$s</li>',
                esc_html($result['account']->accountName),
                $this->describe($result['account'], $result['status'], $result['message'])
            );
        }

        $modeLabel = 'live' === $mode ? esc_html__('live mode', 'give') : esc_html__('test mode', 'give');

        return [
            'level' => $failed ? 'warning' : 'success',
            'title' => $failed ?
                esc_html__('You are connected, but the Stripe webhook needs your attention.', 'give') :
                esc_html__('You are connected! Your Stripe webhook is set up.', 'give'),
            'body' => sprintf(
                '<span>%1$s</span><ul class="give-stripe-webhook-results">%2$s</ul>',
                sprintf(
                    /* translators: 1: "live mode" or "test mode". 2: Webhook endpoint URL. */
                    esc_html__('Webhook endpoint for %1$s: %2$s', 'give'),
                    $modeLabel,
                    '<code>' . esc_html($this->target->url()) . '</code>'
                ),
                $items
            ),
            'retry' => (bool)$failed,
        ];
    }

    /**
     * @since TBD
     */
    private function describe(AccountDetail $account, WebhookSetupStatus $status, string $message): string
    {
        switch ($status->getValue()) {
            case WebhookSetupStatus::FOUND:
                return esc_html__('An endpoint for this site already exists and sends every event GiveWP needs.', 'give');
            case WebhookSetupStatus::CREATED:
                return esc_html__('GiveWP created the endpoint for this site.', 'give');
            case WebhookSetupStatus::UPDATED:
                return esc_html__('GiveWP enabled the endpoint for this site and added the events it was missing.', 'give');
            case WebhookSetupStatus::INCOMPLETE:
                return sprintf(
                    /* translators: 1: List of Stripe event names. 2: Stripe Dashboard webhooks URL. */
                    __('An endpoint points at this site, but it is disabled or does not send every event GiveWP needs, and GiveWP could not change it. Enable it and make sure it sends %1$s in the <a href="%2$s" target="_blank">Webhooks section of your Stripe Dashboard</a>, then try again.', 'give'),
                    '<code>' . esc_html(implode(', ', WebhookTarget::REQUIRED_EVENTS)) . '</code>',
                    esc_url('https://dashboard.stripe.com/webhooks')
                );
            case WebhookSetupStatus::LIMIT_REACHED:
                return sprintf(
                    /* translators: 1: Maximum number of webhook endpoints. 2: Stripe Dashboard webhooks URL. */
                    __('This Stripe account already has %1$d webhook endpoints, the most Stripe allows. Delete an endpoint you no longer use in the <a href="%2$s" target="_blank">Webhooks section of your Stripe Dashboard</a>, then try again.', 'give'),
                    WebhookTarget::MAX_ENDPOINTS_PER_ACCOUNT,
                    esc_url('https://dashboard.stripe.com/webhooks')
                );
            case WebhookSetupStatus::AUTHORIZATION:
                return 'connect' === $account->type ?
                    esc_html__('Stripe did not allow GiveWP to manage webhooks on this account. Disconnect the account and connect it again.', 'give') :
                    esc_html__('Stripe did not allow the saved API key to manage webhooks. Give the restricted key write access to Webhook Endpoints, or remove the account and add it again with a key that has it.', 'give');
            case WebhookSetupStatus::RATE_LIMITED:
                return esc_html__('Stripe received too many requests from this site. Wait a minute, then try again.', 'give');
            case WebhookSetupStatus::CONNECTION:
                return esc_html__('GiveWP could not reach Stripe. Check that this server can make outgoing HTTPS requests, then try again.', 'give');
            default:
                return sprintf(
                    /* translators: %s: Error message from Stripe. */
                    esc_html__('Stripe rejected the request: %s', 'give'),
                    esc_html($message)
                );
        }
    }

    /**
     * The manual setup steps that the modal showed before GiveWP set up endpoints itself.
     *
     * @since TBD
     */
    private function manualInstructions(): string
    {
        return sprintf(
            '<span>%1$s</span><span>%2$s</span><span class="give-field-description">%3$s</span>',
            sprintf(
                /* translators: 1: Stripe Dashboard webhooks URL. 2: Webhook endpoint URL. 3: Documentation URL. */
                __('Add the endpoint yourself: visit the <a href="%1$s" target="_blank">Webhooks section of your Stripe Dashboard</a>, click <strong>Add endpoint</strong> and paste %2$s. See our <a href="%3$s" target="_blank">documentation</a> for the events to select.', 'give'),
                esc_url('https://dashboard.stripe.com/webhooks'),
                '<code>' . esc_html($this->target->url()) . '</code>',
                esc_url('http://docs.givewp.com/stripe-webhooks')
            ),
            sprintf(
                /* translators: %s: Stripe settings page URL. */
                __('You can also click <strong>Try again</strong> now, or later click <strong>Set up webhooks</strong> on the <a href="%s">Stripe Webhooks settings page</a>.', 'give'),
                esc_url(
                    admin_url(
                        'edit.php?post_type=give_forms&page=give-settings&tab=gateways&section=stripe-settings&group=webhooks'
                    )
                )
            ),
            esc_html__(
                'Stripe webhooks are required so GiveWP can communicate properly with the payment gateway to confirm payment completion, renewals, and more.',
                'give'
            )
        );
    }
}
