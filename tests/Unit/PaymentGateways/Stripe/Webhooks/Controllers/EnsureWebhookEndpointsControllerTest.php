<?php

namespace Give\Tests\Unit\PaymentGateways\Stripe\Webhooks\Controllers;

use Give\Log\LogModel;
use Give\Log\LogRepository;
use Give\Log\ValueObjects\LogType;
use Give\PaymentGateways\Stripe\Models\AccountDetail;
use Give\PaymentGateways\Stripe\Webhooks\Controllers\EnsureWebhookEndpointsController;
use Give\PaymentGateways\Stripe\Webhooks\WebhookTarget;
use Give\Tests\TestCase;
use Give\Tests\Unit\PaymentGateways\Stripe\Webhooks\Support\UsesFakeStripeApi;
use WPDieException;

/**
 * @since TBD
 */
final class EnsureWebhookEndpointsControllerTest extends TestCase
{
    use UsesFakeStripeApi;

    /**
     * @since TBD
     */
    public function setUp(): void
    {
        parent::setUp();

        /* wp_send_json_* and check_ajax_referer() call wp_die() instead of exiting only when wp_doing_ajax() is true. */
        add_filter('wp_doing_ajax', '__return_true');

        /* Priority 11 so give_die()'s priority-10 handlers are overridden and wp_die() stays catchable. */
        add_filter('wp_die_ajax_handler', [$this, 'get_wp_die_handler'], 11);
        add_filter('wp_die_json_handler', [$this, 'get_wp_die_handler'], 11);
        add_filter('wp_die_handler', [$this, 'get_wp_die_handler'], 11);

        add_filter('give_is_test_mode', '__return_true');

        $user = self::factory()->user->create_and_get(['role' => 'administrator']);
        $user->add_cap('manage_give_settings');
        wp_set_current_user($user->ID);
    }

    /**
     * @since TBD
     */
    public function tearDown(): void
    {
        $_REQUEST = [];

        parent::tearDown();
    }

    /**
     * @since TBD
     */
    public function testItRejectsARequestWithoutAValidNonce(): void
    {
        $this->saveAccounts($this->makeAccount());
        $_REQUEST['_ajax_nonce'] = wp_create_nonce('another_action');

        try {
            $this->runController();
            $this->fail('Expected WPDieException.');
        } catch (WPDieException $exception) {
            /* check_ajax_referer() answers -1 to an AJAX request whose nonce does not verify. */
            $this->assertSame('-1', $exception->getMessage());
        }

        $this->assertSame([], $this->stripe->requests);
    }

    /**
     * @since TBD
     */
    public function testItRejectsAUserWhoCannotManageGiveSettings(): void
    {
        $this->saveAccounts($this->makeAccount());
        wp_set_current_user(self::factory()->user->create(['role' => 'subscriber']));
        $_REQUEST['_ajax_nonce'] = wp_create_nonce(EnsureWebhookEndpointsController::ACTION);

        $response = $this->callController();

        $this->assertSame(['success' => false], $response);
        $this->assertSame([], $this->stripe->requests);
    }

    /**
     * @since TBD
     */
    public function testItUsesTheTestKeyInTestMode(): void
    {
        $account = $this->makeAccount();
        $this->saveAccounts($account);
        $this->stripe->queue(200, $this->listBody([$this->siteEndpoint()]));

        $response = $this->callController();

        $this->assertTrue($response['success']);
        $this->assertSame('Bearer ' . $account->testSecretKey, $this->stripe->header(0, 'Authorization'));
        $this->assertStringContainsString('test mode', $response['data']['body']);
    }

    /**
     * @since TBD
     */
    public function testItUsesTheLiveKeyInLiveMode(): void
    {
        remove_filter('give_is_test_mode', '__return_true');
        add_filter('give_is_test_mode', '__return_false');
        $account = $this->makeAccount();
        $this->saveAccounts($account);
        $this->stripe->queue(200, $this->listBody([$this->siteEndpoint()]));

        $response = $this->callController();

        $this->assertTrue($response['success']);
        $this->assertSame('Bearer ' . $account->liveSecretKey, $this->stripe->header(0, 'Authorization'));
        $this->assertStringContainsString('live mode', $response['data']['body']);
    }

    /**
     * @since TBD
     */
    public function testItSetsUpEveryAccountAndAnswersWithOneNotice(): void
    {
        $found = $this->makeAccount(['account_slug' => 'acct_found', 'account_name' => 'Found account']);
        $created = $this->makeAccount([
            'account_slug' => 'acct_created',
            'account_name' => 'Created account',
            'test_secret_key' => 'sk_test_created',
        ]);
        $this->saveAccounts($found, $created);
        $this->stripe->queue(200, $this->listBody([$this->siteEndpoint()]));
        $this->stripe->queue(200, $this->listBody([]));
        $this->stripe->queue(200, $this->endpointBody(['id' => 'we_new']));

        $response = $this->callController();

        $this->assertTrue($response['success']);
        $this->assertSame('success', $response['data']['level']);
        $this->assertFalse($response['data']['retry']);
        $this->assertStringContainsString($found->accountName, $response['data']['body']);
        $this->assertStringContainsString($created->accountName, $response['data']['body']);
        $this->assertSame(['get', 'get', 'post'], array_column($this->stripe->requests, 'method'));
        $this->assertSame('Bearer ' . $found->testSecretKey, $this->stripe->header(0, 'Authorization'));
        $this->assertSame('Bearer ' . $created->testSecretKey, $this->stripe->header(1, 'Authorization'));
    }

    /**
     * @since TBD
     */
    public function testItLogsAFailedAccountAndAsksForARetry(): void
    {
        $account = $this->makeAccount(['account_slug' => 'acct_failing']);
        $error = $this->errorBody('authentication_error', 'Invalid API Key provided.');
        $this->saveAccounts($account);
        $this->stripe->queue(401, $error);

        $response = $this->callController();

        $this->assertTrue($response['success']);
        $this->assertSame('warning', $response['data']['level']);
        $this->assertTrue($response['data']['retry']);

        $logged = array_filter(
            give(LogRepository::class)->getLogsByType(LogType::ERROR),
            static function (LogModel $log) use ($account) {
                return false !== strpos((string)($log->getContext()['post_content'] ?? ''), $account->accountSlug);
            }
        );

        $this->assertCount(1, $logged);
        $this->assertStringContainsString(
            $error['error']['message'],
            array_values($logged)[0]->getContext()['post_content']
        );
    }

    /**
     * @since TBD
     */
    public function testItAnswersWithTheModalFields(): void
    {
        $this->saveAccounts($this->makeAccount());
        $this->stripe->queue(200, $this->listBody([$this->siteEndpoint()]));

        $data = $this->callController()['data'];

        $this->assertSame(['level', 'title', 'body', 'retry'], array_keys($data));
        $this->assertIsString($data['level']);
        $this->assertIsString($data['title']);
        $this->assertIsString($data['body']);
        $this->assertIsBool($data['retry']);
    }

    /**
     * @since TBD
     */
    private function saveAccounts(AccountDetail ...$accounts): void
    {
        $saved = [];

        foreach ($accounts as $account) {
            $saved[$account->accountSlug] = $account->toArray();
        }

        give_update_option('_give_stripe_get_all_accounts', $saved);
    }

    /**
     * @since TBD
     */
    private function siteEndpoint(): array
    {
        return $this->endpointBody([
            'url' => (new WebhookTarget())->url(),
            'enabled_events' => WebhookTarget::REQUIRED_EVENTS,
        ]);
    }

    /**
     * Sends a request with a valid nonce, unless the test set one, and returns the decoded JSON response.
     *
     * @since TBD
     *
     * @return array{success: bool, data: mixed}
     */
    private function callController(): array
    {
        if (!isset($_REQUEST['_ajax_nonce'])) {
            $_REQUEST['_ajax_nonce'] = wp_create_nonce(EnsureWebhookEndpointsController::ACTION);
        }

        ob_start();

        try {
            $this->runController();
            $output = ob_get_clean();
            $this->fail("Expected WPDieException was not thrown. Output: $output");
        } catch (WPDieException $exception) {
            $output = ob_get_clean();
        }

        $response = json_decode($output, true);

        if (!is_array($response)) {
            $this->fail("The controller did not answer with JSON: \"$output\"");
        }

        return $response;
    }

    /**
     * @since TBD
     */
    private function runController(): void
    {
        give(EnsureWebhookEndpointsController::class)();
    }
}
