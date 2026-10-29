<?php

namespace Give\Tests\Unit\PaymentGateways\Stripe\Webhooks\Controllers;

use Give\Log\ValueObjects\LogType;
use Give\PaymentGateways\Stripe\Models\AccountDetail;
use Give\PaymentGateways\Stripe\Webhooks\Actions\EnsureWebhookEndpoint;
use Give\PaymentGateways\Stripe\Webhooks\Controllers\ManageWebhookEndpointsController;
use Give\PaymentGateways\Stripe\Webhooks\ValueObjects\WebhookHealthStatus;
use Give\PaymentGateways\Stripe\Webhooks\WebhookTarget;
use Give\Tests\TestCase;
use Give\Tests\Unit\PaymentGateways\Stripe\Webhooks\Support\ReadsGiveLog;
use Give\Tests\Unit\PaymentGateways\Stripe\Webhooks\Support\UsesFakeStripeApi;
use WPDieException;

/*
 * The Log facade stores info entries only when GIVE_DEBUG or WP_DEBUG_LOG is set, and the controller logs the
 * created, updated and deleted entries this file asserts at info level. No other test asserts info entries, so
 * the constant is opened for the rest of the run once this file loads.
 */
if (!defined('GIVE_DEBUG')) {
    define('GIVE_DEBUG', true);
}

/**
 * @since TBD
 */
final class ManageWebhookEndpointsControllerTest extends TestCase
{
    use ReadsGiveLog;
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
        $_POST = [];

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

        $response = $this->callController('status');

        $this->assertSame(['success' => false], $response);
        $this->assertSame([], $this->stripe->requests);
    }

    /**
     * @since TBD
     *
     * @dataProvider unknownOperationProvider
     */
    public function testItRejectsAnUnknownOrMissingOperation(string $operation): void
    {
        $this->saveAccounts($this->makeAccount());

        $response = $this->callController($operation);

        $this->assertSame(['success' => false], $response);
        $this->assertSame([], $this->stripe->requests);
    }

    /**
     * @since TBD
     */
    public function unknownOperationProvider(): array
    {
        return [
            'unknown' => ['purge'],
            'missing' => [''],
        ];
    }

    /**
     * @since TBD
     *
     * @dataProvider accountOperationProvider
     */
    public function testItRejectsAnAccountThatIsNotSavedForTheMode(string $operation): void
    {
        $account = $this->makeAccount();
        $noTestKey = $this->makeAccount([
            'type' => 'manual',
            'account_slug' => 'acct_live_only',
            'test_secret_key' => '',
            'test_publishable_key' => '',
        ]);
        $this->saveAccounts($account, $noTestKey);

        foreach (['acct_unknown', $noTestKey->accountSlug, ''] as $slug) {
            $response = $this->callController($operation, ['account' => $slug, 'endpoint_id' => 'we_123']);

            $this->assertSame(['success' => false], $response);
        }

        $this->assertSame([], $this->stripe->requests);
    }

    /**
     * @since TBD
     */
    public function accountOperationProvider(): array
    {
        return [
            'list' => ['list'],
            'create' => ['create'],
            'update' => ['update'],
            'delete' => ['delete'],
        ];
    }

    /**
     * @since TBD
     */
    public function testStatusReportsEachAccountAndSkipsAccountsWithoutAKeyForTheMode(): void
    {
        $failing = $this->makeAccount(['account_slug' => 'acct_failing', 'test_secret_key' => 'sk_test_failing']);
        $healthy = $this->makeAccount(['account_slug' => 'acct_healthy', 'test_secret_key' => 'sk_test_healthy']);
        $missing = $this->makeAccount(['account_slug' => 'acct_missing', 'test_secret_key' => 'sk_test_missing']);
        $liveOnly = $this->makeAccount([
            'type' => 'manual',
            'account_slug' => 'acct_live_only',
            'test_secret_key' => '',
            'test_publishable_key' => '',
        ]);
        $this->saveAccounts($failing, $healthy, $missing, $liveOnly);
        $this->stripe->queue(401, $this->errorBody('authentication_error', 'Invalid API Key provided.'));
        $this->stripe->queue(200, $this->listBody([$this->siteEndpoint()]));
        $this->stripe->queue(200, $this->listBody([]));

        $response = $this->callController('status');

        $this->assertTrue($response['success']);
        $this->assertSame(
            [
                $failing->accountSlug => 'error',
                $healthy->accountSlug => WebhookHealthStatus::HEALTHY,
                $missing->accountSlug => WebhookHealthStatus::MISSING,
            ],
            $response['data']['states']
        );
        $this->assertCount(3, $this->stripe->requests);
        $this->assertSame(['get', 'get', 'get'], array_column($this->stripe->requests, 'method'));
        $this->assertSame('Bearer ' . $failing->testSecretKey, $this->stripe->header(0, 'Authorization'));
        $this->assertSame('Bearer ' . $healthy->testSecretKey, $this->stripe->header(1, 'Authorization'));
        $this->assertSame('Bearer ' . $missing->testSecretKey, $this->stripe->header(2, 'Authorization'));
    }

    /**
     * @since TBD
     */
    public function testStatusUsesTheLiveKeysInLiveMode(): void
    {
        remove_filter('give_is_test_mode', '__return_true');
        add_filter('give_is_test_mode', '__return_false');
        $account = $this->makeAccount();
        $testOnly = $this->makeAccount([
            'type' => 'manual',
            'account_slug' => 'acct_test_only',
            'live_secret_key' => '',
            'live_publishable_key' => '',
        ]);
        $this->saveAccounts($account, $testOnly);
        $this->stripe->queue(200, $this->listBody([$this->siteEndpoint()]));

        $response = $this->callController('status');

        $this->assertSame([$account->accountSlug => WebhookHealthStatus::HEALTHY], $response['data']['states']);
        $this->assertCount(1, $this->stripe->requests);
        $this->assertSame('Bearer ' . $account->liveSecretKey, $this->stripe->header(0, 'Authorization'));
    }

    /**
     * @since TBD
     */
    public function testListReturnsTheModalHtmlAndTheHealthState(): void
    {
        $account = $this->makeAccount();
        $other = $this->endpointBody(['id' => 'we_other', 'url' => 'https://other.example.com/?give-listener=stripe']);
        $this->saveAccounts($account);
        $this->stripe->queue(200, $this->listBody([$this->siteEndpoint(), $other]));

        $response = $this->callController('list', ['account' => $account->accountSlug]);

        $this->assertTrue($response['success']);
        $this->assertSame(WebhookHealthStatus::HEALTHY, $response['data']['state']);
        $this->assertStringContainsString('give-stripe-webhooks', $response['data']['html']);
        $this->assertStringContainsString('we_other', $response['data']['html']);
        $this->assertStringContainsString('https://other.example.com/?give-listener=stripe', $response['data']['html']);
        $this->assertSame(['get'], array_column($this->stripe->requests, 'method'));
    }

    /**
     * @since TBD
     */
    public function testListReportsAnErrorStateAndLogsAWarningWhenStripeCannotBeRead(): void
    {
        $account = $this->makeAccount();
        $error = $this->errorBody('authentication_error', 'Invalid API Key provided.');
        $this->saveAccounts($account);
        $this->stripe->queue(401, $error);

        $response = $this->callController('list', ['account' => $account->accountSlug]);

        $this->assertTrue($response['success']);
        $this->assertSame('error', $response['data']['state']);
        $this->assertStringContainsString($error['error']['message'], $response['data']['html']);

        $logged = $this->webhookLogs(LogType::WARNING, $account->accountSlug);

        $this->assertCount(1, $logged);
        $this->assertSame('list', $logged[0]->getContext()['operation']);
    }

    /**
     * @since TBD
     */
    public function testCreateSendsOnlyTheRequiredEventsTheApiVersionAndTheDescription(): void
    {
        $account = $this->makeAccount();
        $this->saveAccounts($account);
        $url = 'https://example.com/?give-listener=stripe';
        $posted = ['charge.refunded', 'not.a.required.event', 'payment_intent.succeeded', '*'];
        $expectedEvents = array_values(array_intersect(WebhookTarget::REQUIRED_EVENTS, $posted));
        $this->stripe->queue(200, $this->listBody([]));
        $this->stripe->queue(200, $this->endpointBody(['id' => 'we_new', 'url' => $url]));
        $this->stripe->queue(200, $this->listBody([$this->endpointBody(['id' => 'we_new', 'url' => $url])]));

        $response = $this->callController('create', [
            'account' => $account->accountSlug,
            'url' => $url,
            'events' => $posted,
        ]);

        $this->assertTrue($response['success']);
        $this->assertSame(['get', 'post', 'get'], array_column($this->stripe->requests, 'method'));
        $this->assertSame(
            [
                'url' => $url,
                'enabled_events' => $expectedEvents,
                'api_version' => EnsureWebhookEndpoint::API_VERSION,
                'description' => 'GiveWP',
            ],
            $this->stripe->requests[1]['params']
        );
        $this->assertStringContainsString('Webhook endpoint added.', $response['data']['html']);
        $this->assertStringContainsString('we_new', $response['data']['html']);

        $logged = $this->webhookLogs(LogType::INFO, $account->accountSlug);

        $this->assertCount(1, $logged);
        $this->assertSame('we_new', $logged[0]->getContext()['endpoint_id']);
        $this->assertSame('test', $logged[0]->getContext()['mode']);
        $this->assertSame([], $this->webhookLogs(LogType::WARNING, $account->accountSlug));
    }

    /**
     * @since TBD
     *
     * @dataProvider incompleteCreateProvider
     */
    public function testCreateRejectsAnEmptyUrlOrEventListWithoutCreatingAnEndpoint(array $fields): void
    {
        $account = $this->makeAccount();
        $this->saveAccounts($account);
        $this->stripe->queue(200, $this->listBody([]));

        $response = $this->callController('create', ['account' => $account->accountSlug] + $fields);

        $this->assertTrue($response['success']);
        $this->assertStringContainsString('Enter a URL and choose at least one event.', $response['data']['html']);
        /* Only the read that refreshes the list reaches Stripe. */
        $this->assertSame(['get'], array_column($this->stripe->requests, 'method'));
        $this->assertSame([], $this->webhookLogs(LogType::INFO, $account->accountSlug));
    }

    /**
     * @since TBD
     */
    public function incompleteCreateProvider(): array
    {
        $url = 'https://example.com/?give-listener=stripe';

        return [
            'no events' => [['url' => $url]],
            'only events outside the required list' => [['url' => $url, 'events' => ['not.a.required.event']]],
            'no url' => [['events' => ['payment_intent.succeeded']]],
            'empty url' => [['url' => '', 'events' => ['payment_intent.succeeded']]],
        ];
    }

    /**
     * @since TBD
     */
    public function testCreateIsRefusedWhenTheAccountHasTheMaximumNumberOfEndpoints(): void
    {
        $account = $this->makeAccount();
        $this->saveAccounts($account);
        $endpoints = [];

        for ($index = 1; $index <= WebhookTarget::MAX_ENDPOINTS_PER_ACCOUNT; $index++) {
            $endpoints[] = $this->endpointBody([
                'id' => "we_$index",
                'url' => "https://example$index.com/?give-listener=stripe",
            ]);
        }

        $this->stripe->queue(200, $this->listBody($endpoints));
        $this->stripe->queue(200, $this->listBody($endpoints));

        $response = $this->callController('create', [
            'account' => $account->accountSlug,
            'url' => 'https://example.com/?give-listener=stripe',
            'events' => ['payment_intent.succeeded'],
        ]);

        $this->assertTrue($response['success']);
        $this->assertSame(['get', 'get'], array_column($this->stripe->requests, 'method'));
        $this->assertStringContainsString('maximum of 16 webhook endpoints', $response['data']['html']);
        $this->assertStringNotContainsString('data-webhook-operation="create"', $response['data']['html']);
    }

    /**
     * @since TBD
     */
    public function testUpdateKeepsEventsOutsideTheRequiredListAndDisablesTheEndpoint(): void
    {
        $account = $this->makeAccount();
        $this->saveAccounts($account);
        $existing = $this->endpointBody([
            'id' => 'we_site',
            'url' => (new WebhookTarget())->url(),
            'enabled_events' => ['payment_intent.succeeded', 'charge.refunded', 'account.updated', 'payout.paid'],
        ]);
        $url = 'https://changed.example.com/?give-listener=stripe';
        $posted = ['payment_intent.succeeded', 'invoice.payment_failed'];
        $expectedEvents = array_merge(['account.updated', 'payout.paid'], $posted);
        $this->stripe->queue(200, $this->listBody([$existing]));
        $this->stripe->queue(200, $this->endpointBody(['id' => 'we_site', 'status' => 'disabled']));
        $this->stripe->queue(200, $this->listBody([$existing]));

        $response = $this->callController('update', [
            'account' => $account->accountSlug,
            'endpoint_id' => 'we_site',
            'url' => $url,
            'status' => 'disabled',
            'events' => $posted,
        ]);

        $this->assertTrue($response['success']);
        $this->assertSame(['get', 'post', 'get'], array_column($this->stripe->requests, 'method'));
        $this->assertStringEndsWith('/v1/webhook_endpoints/we_site', $this->stripe->requests[1]['absUrl']);
        $this->assertSame(
            ['url' => $url, 'disabled' => 'true', 'enabled_events' => $expectedEvents],
            $this->stripe->requests[1]['params']
        );
        $this->assertStringContainsString('Webhook endpoint saved.', $response['data']['html']);
        $this->assertSame(
            'updated',
            explode(' ', $this->webhookLogs(LogType::INFO, $account->accountSlug)[0]->getMessage())[3]
        );
    }

    /**
     * @since TBD
     */
    public function testUpdateEnablesTheEndpointWhenTheStatusIsNotDisabled(): void
    {
        $account = $this->makeAccount();
        $this->saveAccounts($account);
        $existing = $this->endpointBody(['id' => 'we_site', 'status' => 'disabled']);
        $this->stripe->queue(200, $this->listBody([$existing]));
        $this->stripe->queue(200, $this->endpointBody(['id' => 'we_site']));
        $this->stripe->queue(200, $this->listBody([$existing]));

        $this->callController('update', [
            'account' => $account->accountSlug,
            'endpoint_id' => 'we_site',
            'url' => 'https://example.com/?give-listener=stripe',
            'status' => 'enabled',
            'events' => ['charge.refunded'],
        ]);

        $this->assertSame('false', $this->stripe->requests[1]['params']['disabled']);
    }

    /**
     * @since TBD
     */
    public function testUpdateKeepsTheAllEventsWildcardWhateverTheFormSends(): void
    {
        $account = $this->makeAccount();
        $this->saveAccounts($account);
        $existing = $this->endpointBody(['id' => 'we_all', 'enabled_events' => ['*']]);
        $this->stripe->queue(200, $this->listBody([$existing]));
        $this->stripe->queue(200, $this->endpointBody(['id' => 'we_all', 'enabled_events' => ['*']]));
        $this->stripe->queue(200, $this->listBody([$existing]));

        $this->callController('update', [
            'account' => $account->accountSlug,
            'endpoint_id' => 'we_all',
            'url' => 'https://example.com/?give-listener=stripe',
            'status' => 'enabled',
            'events' => ['charge.refunded'],
        ]);

        $this->assertSame(['*'], $this->stripe->requests[1]['params']['enabled_events']);
    }

    /**
     * @since TBD
     */
    public function testUpdateAnswersWithAnErrorNoticeForAnEndpointThatIsNotOnTheAccount(): void
    {
        $account = $this->makeAccount();
        $this->saveAccounts($account);
        $this->stripe->queue(200, $this->listBody([$this->endpointBody(['id' => 'we_other'])]));
        $this->stripe->queue(200, $this->listBody([$this->endpointBody(['id' => 'we_other'])]));

        $response = $this->callController('update', [
            'account' => $account->accountSlug,
            'endpoint_id' => 'we_missing',
            'url' => 'https://example.com/?give-listener=stripe',
            'events' => ['charge.refunded'],
        ]);

        $this->assertTrue($response['success']);
        $this->assertSame(['get', 'get'], array_column($this->stripe->requests, 'method'));
        $this->assertStringContainsString('The webhook endpoint was not found.', $response['data']['html']);
        $this->assertSame([], $this->webhookLogs(LogType::INFO, $account->accountSlug));
    }

    /**
     * @since TBD
     */
    public function testDeleteRemovesTheEndpointAndAnswersWithTheRefreshedList(): void
    {
        $account = $this->makeAccount();
        $this->saveAccounts($account);
        $this->stripe->queue(200, ['id' => 'we_123', 'object' => 'webhook_endpoint', 'deleted' => true]);
        $this->stripe->queue(200, $this->listBody([]));

        $response = $this->callController('delete', [
            'account' => $account->accountSlug,
            'endpoint_id' => 'we_123',
        ]);

        $this->assertTrue($response['success']);
        $this->assertSame(['delete', 'get'], array_column($this->stripe->requests, 'method'));
        $this->assertStringEndsWith('/v1/webhook_endpoints/we_123', $this->stripe->requests[0]['absUrl']);
        $this->assertSame(WebhookHealthStatus::MISSING, $response['data']['state']);
        $this->assertStringContainsString('Webhook endpoint deleted.', $response['data']['html']);
        $this->assertStringContainsString('This account has no webhook endpoints.', $response['data']['html']);

        $logged = $this->webhookLogs(LogType::INFO, $account->accountSlug);

        $this->assertCount(1, $logged);
        $this->assertSame('we_123', $logged[0]->getContext()['endpoint_id']);
    }

    /**
     * @since TBD
     *
     * @dataProvider mutationProvider
     */
    public function testAStripeFailureOnAMutationAnswersWithAnErrorNoticeAndLogsAWarning(
        string $operation,
        array $fields,
        array $queuedBefore
    ): void {
        $account = $this->makeAccount();
        $error = $this->errorBody('invalid_request_error', 'The endpoint could not be changed.');
        $this->saveAccounts($account);

        foreach ($queuedBefore as $body) {
            $this->stripe->queue(200, $body);
        }

        $this->stripe->queue(400, $error);
        $this->stripe->queue(200, $this->listBody([]));

        $response = $this->callController($operation, ['account' => $account->accountSlug] + $fields);

        $this->assertTrue($response['success']);
        $this->assertStringContainsString(
            'Stripe returned an error: ' . $error['error']['message'],
            $response['data']['html']
        );
        $this->assertStringContainsString('give-stripe-webhooks__notice--error', $response['data']['html']);

        $logged = $this->webhookLogs(LogType::WARNING, $account->accountSlug);

        $this->assertCount(1, $logged);
        $this->assertSame($operation, $logged[0]->getContext()['operation']);
        $this->assertSame(400, $logged[0]->getContext()['stripe_http_status']);
        $this->assertSame($error['error']['message'], $logged[0]->getContext()['error']);
        $this->assertSame([], $this->webhookLogs(LogType::INFO, $account->accountSlug));
    }

    /**
     * @since TBD
     */
    public function mutationProvider(): array
    {
        $existing = $this->endpointBody(['id' => 'we_123']);
        $url = 'https://example.com/?give-listener=stripe';

        return [
            'create' => [
                'create',
                ['url' => $url, 'events' => ['charge.refunded']],
                [$this->listBody([])],
            ],
            'update' => [
                'update',
                ['endpoint_id' => 'we_123', 'url' => $url, 'events' => ['charge.refunded']],
                [$this->listBody([$existing])],
            ],
            'delete' => [
                'delete',
                ['endpoint_id' => 'we_123'],
                [],
            ],
        ];
    }

    /**
     * @since TBD
     */
    public function testDeleteWithAMalformedEndpointIdAnswersWithAnErrorNoticeWithoutCallingStripeToDelete(): void
    {
        $account = $this->makeAccount();
        $this->saveAccounts($account);
        $this->stripe->queue(200, $this->listBody([]));

        $response = $this->callController('delete', [
            'account' => $account->accountSlug,
            'endpoint_id' => '../we_123',
        ]);

        $this->assertTrue($response['success']);
        $this->assertSame(['get'], array_column($this->stripe->requests, 'method'));
        $this->assertStringContainsString('give-stripe-webhooks__notice--error', $response['data']['html']);
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
     * @param array<string, string|string[]> $fields
     *
     * @return array{success: bool, data: mixed}
     */
    private function callController(string $operation, array $fields = []): array
    {
        if (!isset($_REQUEST['_ajax_nonce'])) {
            $_REQUEST['_ajax_nonce'] = wp_create_nonce(ManageWebhookEndpointsController::ACTION);
        }

        /* The controller reads $_POST, which is slashed the way WordPress slashes it before a handler runs. */
        $_POST = wp_slash(['operation' => $operation] + $fields);

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
        give(ManageWebhookEndpointsController::class)();
    }
}
