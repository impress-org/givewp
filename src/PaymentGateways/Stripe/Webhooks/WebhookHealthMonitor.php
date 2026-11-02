<?php

/**
 * @since TBD
 */

namespace Give\PaymentGateways\Stripe\Webhooks;

use Give\PaymentGateways\Stripe\Models\AccountDetail;
use Give\PaymentGateways\Stripe\Webhooks\Actions\CheckWebhookEndpointHealth;
use Give\PaymentGateways\Stripe\Webhooks\Actions\GetStripeAccountsForMode;
use Give\PaymentGateways\Stripe\Webhooks\Exceptions\WebhookEndpointException;
use Give\PaymentGateways\Stripe\Webhooks\ValueObjects\WebhookHealthStatus;
use Give\PaymentGateways\Stripe\Webhooks\ValueObjects\WebhookSetupStatus;

/**
 * Checks every hour that each Stripe account sends events to this site, and emails the site administrator when one
 * does not. It only reads from Stripe.
 *
 * @since TBD
 */
final class WebhookHealthMonitor
{
    public const CRON_HOOK = 'givewp_stripe_webhook_health_check';

    public const EMAIL_SETTING = 'stripe_webhook_health_email';

    /**
     * Fingerprint of the problems in the last email, so the same problems are not emailed every hour.
     */
    private const NOTIFIED_OPTION = 'givewp_stripe_webhook_health_notified';

    /**
     * @var GetStripeAccountsForMode
     */
    private $getAccounts;

    /**
     * @var CheckWebhookEndpointHealth
     */
    private $checkHealth;

    /**
     * @var WebhookTarget
     */
    private $target;

    /**
     * @since TBD
     */
    public function __construct(
        GetStripeAccountsForMode $getAccounts,
        CheckWebhookEndpointHealth $checkHealth,
        WebhookTarget $target
    ) {
        $this->getAccounts = $getAccounts;
        $this->checkHealth = $checkHealth;
        $this->target = $target;
    }

    /**
     * @since TBD
     */
    public function schedule(): void
    {
        if (!wp_next_scheduled(self::CRON_HOOK)) {
            wp_schedule_event(time(), 'hourly', self::CRON_HOOK);
        }
    }

    /**
     * @since TBD
     */
    public function __invoke(): void
    {
        if (!give_is_setting_enabled(give_get_option(self::EMAIL_SETTING, 'enabled'))) {
            return;
        }

        $mode = give_is_test_mode() ? 'test' : 'live';
        $problems = [];

        foreach (($this->getAccounts)($mode) as $account) {
            $problem = $this->problemOf($account, $mode);

            if ($problem) {
                $problems[] = $problem;
            }
        }

        if (!$problems) {
            if (get_option(self::NOTIFIED_OPTION)) {
                WebhookLogger::info('Stripe webhook health problems are resolved.', ['mode' => $mode]);
            }

            delete_option(self::NOTIFIED_OPTION);

            return;
        }

        $fingerprint = md5(wp_json_encode([$mode, $problems]));

        if (get_option(self::NOTIFIED_OPTION) === $fingerprint) {
            return;
        }

        WebhookLogger::warning('Stripe webhook health problems found.', ['mode' => $mode, 'problems' => $problems]);

        if (!$this->sendEmail($problems, $mode)) {
            /* The fingerprint is not stored, so the next hourly run tries to send the email again. */
            WebhookLogger::error('The Stripe webhook health email could not be sent.', [
                'mode' => $mode,
                'recipient' => $this->recipient(),
            ]);

            return;
        }

        update_option(self::NOTIFIED_OPTION, $fingerprint, false);
        WebhookLogger::info('Stripe webhook health email sent.', ['mode' => $mode, 'recipient' => $this->recipient()]);
    }

    /**
     * A line describing what is wrong with the account, or an empty string when nothing is.
     *
     * @since TBD
     */
    private function problemOf(AccountDetail $account, string $mode): string
    {
        try {
            $status = ($this->checkHealth)($account, $mode);
        } catch (WebhookEndpointException $exception) {
            /* A network failure or a rate limit says nothing about the endpoint; the next run checks again. */
            if (WebhookSetupStatus::fromException($exception)->isOneOf(
                WebhookSetupStatus::CONNECTION(),
                WebhookSetupStatus::RATE_LIMITED()
            )) {
                WebhookLogger::debug(
                    'Stripe webhook health check skipped an account.',
                    array_merge(
                        ['account' => $account->accountSlug, 'account_type' => $account->type, 'mode' => $mode],
                        WebhookLogger::failureContext($exception)
                    )
                );

                return '';
            }

            return sprintf(
                /* translators: 1: Stripe account name. 2: Error message from Stripe. */
                __('%1$s: Stripe returned an error when GiveWP read the webhook endpoints: %2$s', 'give'),
                $account->accountName,
                $exception->getMessage()
            );
        }

        switch ($status->getValue()) {
            case WebhookHealthStatus::MISSING:
                /* translators: %s: Stripe account name. */
                return sprintf(__('%s: no webhook endpoint points at this site.', 'give'), $account->accountName);
            case WebhookHealthStatus::DISABLED:
                /* translators: %s: Stripe account name. */
                return sprintf(__('%s: the webhook endpoint for this site is disabled.', 'give'), $account->accountName);
            case WebhookHealthStatus::MISSING_EVENTS:
                return sprintf(
                    /* translators: %s: Stripe account name. */
                    __('%s: the webhook endpoint for this site does not send every event GiveWP needs.', 'give'),
                    $account->accountName
                );
            default:
                return '';
        }
    }

    /**
     * @since TBD
     *
     * @param string[] $problems
     */
    private function sendEmail(array $problems, string $mode): bool
    {
        $settingsUrl = admin_url(
            'edit.php?post_type=give_forms&page=give-settings&tab=gateways&section=stripe-settings&group=webhooks'
        );

        $message = implode(
            "\n\n",
            [
                sprintf(
                    /* translators: %s: "live" or "test". */
                    __('GiveWP checked the Stripe webhooks of your site in %s mode and found these problems:', 'give'),
                    $mode
                ),
                '- ' . implode("\n- ", $problems),
                sprintf(
                    /* translators: %s: Webhook endpoint URL. */
                    __('Without a working webhook, GiveWP does not learn about renewals, refunds and failed payments. Stripe must send events to %s', 'give'),
                    $this->target->url()
                ),
                sprintf(
                    /* translators: %s: Stripe Webhooks settings page URL. */
                    __('To fix this, open %s and click "Set up webhooks". You can turn these emails off on the same page.', 'give'),
                    $settingsUrl
                ),
            ]
        );

        return wp_mail(
            $this->recipient(),
            sprintf(
                /* translators: %s: Site name. */
                __('[%s] Your Stripe webhooks need attention', 'give'),
                wp_specialchars_decode(get_bloginfo('name'), ENT_QUOTES)
            ),
            $message
        );
    }

    /**
     * The address WordPress sends fatal error emails to.
     *
     * @since TBD
     */
    private function recipient(): string
    {
        if (defined('RECOVERY_MODE_EMAIL') && is_email(RECOVERY_MODE_EMAIL)) {
            return RECOVERY_MODE_EMAIL;
        }

        return (string)get_option('admin_email');
    }
}
