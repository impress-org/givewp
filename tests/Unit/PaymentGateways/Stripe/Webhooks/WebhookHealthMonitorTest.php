<?php

namespace Give\Tests\Unit\PaymentGateways\Stripe\Webhooks;

use Give\PaymentGateways\Stripe\Webhooks\WebhookHealthMonitor;
use Give\PaymentGateways\Stripe\Webhooks\WebhookTarget;
use Give\Tests\TestCase;
use Give\Tests\Unit\PaymentGateways\Stripe\Webhooks\Support\UsesFakeStripeApi;
use Stripe\Exception\ApiConnectionException;

/**
 * @since TBD
 */
final class WebhookHealthMonitorTest extends TestCase
{
    use UsesFakeStripeApi;

    /**
     * WebhookHealthMonitor::NOTIFIED_OPTION, which is private.
     */
    private const NOTIFIED_OPTION = 'givewp_stripe_webhook_health_notified';

    /**
     * @var array[] Arguments of each wp_mail() call.
     */
    private $emails = [];

    /**
     * @since TBD
     */
    public function setUp(): void
    {
        parent::setUp();

        $account = $this->makeAccount();
        give_update_option('_give_stripe_get_all_accounts', [$account->accountSlug => $account->toArray()]);
        give_delete_option(WebhookHealthMonitor::EMAIL_SETTING);
        add_filter('give_is_test_mode', '__return_true');
        add_filter('pre_wp_mail', function ($short, array $email) {
            $this->emails[] = $email;

            return true;
        }, 10, 2);
    }

    /**
     * @since TBD
     */
    public function testItEmailsTheFatalErrorAddressOnceForTheSameProblem(): void
    {
        $account = $this->makeAccount();
        $this->stripe->queue(200, $this->listBody([]));
        $this->stripe->queue(200, $this->listBody([]));

        give(WebhookHealthMonitor::class)();
        give(WebhookHealthMonitor::class)();

        $this->assertCount(1, $this->emails);
        $this->assertSame(get_option('admin_email'), $this->emails[0]['to']);
        $this->assertStringContainsString($account->accountName, $this->emails[0]['message']);
        $this->assertStringContainsString((new WebhookTarget())->url(), $this->emails[0]['message']);
        $this->assertSame(['get', 'get'], array_column($this->stripe->requests, 'method'));
    }

    /**
     * @since TBD
     */
    public function testItEmailsAgainWhenAProblemComesBackAfterAHealthyCheck(): void
    {
        $healthy = $this->endpointBody([
            'url' => home_url('/?give-listener=stripe'),
            'enabled_events' => WebhookTarget::REQUIRED_EVENTS,
        ]);
        $this->stripe->queue(200, $this->listBody([]));
        $this->stripe->queue(200, $this->listBody([$healthy]));
        $this->stripe->queue(200, $this->listBody([]));

        give(WebhookHealthMonitor::class)();
        give(WebhookHealthMonitor::class)();
        give(WebhookHealthMonitor::class)();

        $this->assertCount(2, $this->emails);
    }

    /**
     * @since TBD
     */
    public function testItDoesNothingWhenTheEmailSettingIsDisabled(): void
    {
        give_update_option(WebhookHealthMonitor::EMAIL_SETTING, 'disabled');

        give(WebhookHealthMonitor::class)();

        $this->assertSame([], $this->emails);
        $this->assertSame([], $this->stripe->requests);
    }

    /**
     * @since TBD
     *
     * @dataProvider transientFailureProvider
     */
    public function testItSendsNoEmailWhenStripeCannotBeAsked(callable $queueFailure): void
    {
        $queueFailure($this);

        give(WebhookHealthMonitor::class)();

        $this->assertSame([], $this->emails);
        $this->assertCount(1, $this->stripe->requests);
    }

    /**
     * @since TBD
     */
    public function transientFailureProvider(): array
    {
        return [
            'network failure' => [
                static function (self $test) {
                    $test->stripe->queueException(ApiConnectionException::factory('Could not connect to Stripe.'));
                },
            ],
            'rate limit' => [
                static function (self $test) {
                    $test->stripe->queue(429, $test->errorBody('invalid_request_error', 'Too many requests.'));
                },
            ],
        ];
    }

    /**
     * @since TBD
     */
    public function testItEmailsTheErrorStripeReturnsForARejectedKey(): void
    {
        $account = $this->makeAccount();
        $error = $this->errorBody('authentication_error', 'Invalid API Key provided.');
        $this->stripe->queue(401, $error);

        give(WebhookHealthMonitor::class)();

        $this->assertCount(1, $this->emails);
        $this->assertMatchesRegularExpression(
            '/^- ' . preg_quote($account->accountName, '/') . ': .*' . preg_quote($error['error']['message'], '/') . '$/m',
            $this->emails[0]['message']
        );
    }

    /**
     * @since TBD
     */
    public function testItListsEveryAccountInOneEmail(): void
    {
        $first = $this->makeAccount(['account_slug' => 'acct_first', 'account_name' => 'First account']);
        $second = $this->makeAccount(['account_slug' => 'acct_second', 'account_name' => 'Second account']);
        give_update_option('_give_stripe_get_all_accounts', [
            $first->accountSlug => $first->toArray(),
            $second->accountSlug => $second->toArray(),
        ]);
        $this->stripe->queue(200, $this->listBody([]));
        $this->stripe->queue(200, $this->listBody([]));

        give(WebhookHealthMonitor::class)();

        $this->assertCount(1, $this->emails);
        $this->assertStringContainsString('- ' . $first->accountName . ':', $this->emails[0]['message']);
        $this->assertStringContainsString('- ' . $second->accountName . ':', $this->emails[0]['message']);
    }

    /**
     * @since TBD
     */
    public function testItTriesAgainOnTheNextRunWhenTheEmailFails(): void
    {
        $failNext = true;
        add_filter('pre_wp_mail', static function ($short) use (&$failNext) {
            $result = $failNext ? false : $short;
            $failNext = false;

            return $result;
        }, 20);
        $this->stripe->queue(200, $this->listBody([]));
        $this->stripe->queue(200, $this->listBody([]));

        give(WebhookHealthMonitor::class)();
        $notifiedAfterFailure = get_option(self::NOTIFIED_OPTION);
        give(WebhookHealthMonitor::class)();

        $this->assertFalse($notifiedAfterFailure);
        $this->assertCount(2, $this->emails);
        $this->assertIsString(get_option(self::NOTIFIED_OPTION));
    }

    /**
     * @since TBD
     */
    public function testItEmailsAgainWhenTheSameProblemShowsUpInTheOtherMode(): void
    {
        $this->stripe->queue(200, $this->listBody([]));
        $this->stripe->queue(200, $this->listBody([]));

        give(WebhookHealthMonitor::class)();
        remove_filter('give_is_test_mode', '__return_true');
        add_filter('give_is_test_mode', '__return_false');
        give(WebhookHealthMonitor::class)();

        $this->assertCount(2, $this->emails);
        $this->assertStringContainsString(' test mode ', $this->emails[0]['message']);
        $this->assertStringContainsString(' live mode ', $this->emails[1]['message']);
    }

    /**
     * @since TBD
     */
    public function testItDoesNotCallStripeWithoutAccounts(): void
    {
        give_update_option('_give_stripe_get_all_accounts', []);

        give(WebhookHealthMonitor::class)();

        $this->assertSame([], $this->stripe->requests);
        $this->assertSame([], $this->emails);
    }

    /**
     * @since TBD
     */
    public function testItSchedulesTheHourlyCheckOnce(): void
    {
        wp_clear_scheduled_hook(WebhookHealthMonitor::CRON_HOOK);

        give(WebhookHealthMonitor::class)->schedule();
        $scheduled = wp_next_scheduled(WebhookHealthMonitor::CRON_HOOK);
        give(WebhookHealthMonitor::class)->schedule();

        $this->assertNotFalse($scheduled);
        $this->assertSame($scheduled, wp_next_scheduled(WebhookHealthMonitor::CRON_HOOK));
        $this->assertSame('hourly', wp_get_schedule(WebhookHealthMonitor::CRON_HOOK));
    }
}
