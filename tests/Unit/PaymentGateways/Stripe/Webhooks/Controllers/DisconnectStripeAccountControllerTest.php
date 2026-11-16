<?php

namespace Give\Tests\Unit\PaymentGateways\Stripe\Webhooks\Controllers;

use Give\PaymentGateways\Stripe\Controllers\DisconnectStripeAccountController;
use Give\PaymentGateways\Stripe\Models\AccountDetail;
use Give\PaymentGateways\Stripe\Webhooks\WebhookTarget;
use Give\Tests\TestCase;
use Give\Tests\Unit\PaymentGateways\Stripe\Webhooks\Support\UsesFakeStripeApi;
use RuntimeException;
use WPDieException;

/**
 * @since TBD
 */
final class DisconnectStripeAccountControllerTest extends TestCase
{
    use UsesFakeStripeApi;

    /**
     * @since TBD
     */
    public function setUp(): void
    {
        parent::setUp();

        add_filter('wp_doing_ajax', '__return_true');
        add_filter('wp_die_ajax_handler', [$this, 'get_wp_die_handler'], 11);
        add_filter('wp_die_json_handler', [$this, 'get_wp_die_handler'], 11);
        add_filter('wp_die_handler', [$this, 'get_wp_die_handler'], 11);

        $user = self::factory()->user->create_and_get(['role' => 'administrator']);
        $user->add_cap('manage_give_settings');
        wp_set_current_user($user->ID);
    }

    /**
     * @since TBD
     */
    public function tearDown(): void
    {
        $_GET = [];
        $_REQUEST = [];

        parent::tearDown();
    }

    /**
     * @since TBD
     */
    public function testItDeletesTheSiteEndpointInBothModesAfterRemovingTheAccount(): void
    {
        $account = $this->makeAccount();
        $other = $this->makeAccount(['account_slug' => 'acct_other', 'account_id' => 'acct_other']);
        $this->saveAccounts($account, $other);

        foreach (['test', 'live'] as $mode) {
            $this->stripe->queue(200, $this->listBody([$this->siteEndpoint()]));
            $this->stripe->queue(200, ['id' => 'we_123', 'object' => 'webhook_endpoint', 'deleted' => true]);
        }

        /* A slow Stripe request must not keep the account connected, so it is removed before Stripe is called. */
        $requestsWhenRemoved = null;
        add_action('update_option_give_settings', function () use (&$requestsWhenRemoved) {
            $requestsWhenRemoved = count($this->stripe->requests);
        });

        $response = $this->callController($account->accountSlug);

        $this->assertTrue($response['success']);
        $this->assertArrayNotHasKey('data', $response);
        $this->assertSame(0, $requestsWhenRemoved);
        $this->assertSame(['get', 'delete', 'get', 'delete'], array_column($this->stripe->requests, 'method'));
        $this->assertSame('Bearer ' . $account->testSecretKey, $this->stripe->header(1, 'Authorization'));
        $this->assertSame('Bearer ' . $account->liveSecretKey, $this->stripe->header(3, 'Authorization'));
        $this->assertSame(['acct_other'], array_keys(give_stripe_get_all_accounts()));
    }

    /**
     * @since TBD
     */
    public function testItRemovesTheAccountAndAnswersSuccessWhenStripeFails(): void
    {
        $account = $this->makeAccount();
        $this->saveAccounts($account);
        $this->stripe->queue(401, $this->errorBody('authentication_error', 'Invalid API Key provided.'));
        $this->stripe->queue(401, $this->errorBody('authentication_error', 'Invalid API Key provided.'));

        $response = $this->callController($account->accountSlug);

        $this->assertTrue($response['success']);
        $this->assertStringContainsString((new WebhookTarget())->url(), $response['data']['webhookWarning']);
        $this->assertCount(2, $this->stripe->requests);
        $this->assertSame([], give_stripe_get_all_accounts());
    }

    /**
     * @since TBD
     */
    public function testItRemovesTheAccountWhenStripeCannotBeReached(): void
    {
        $account = $this->makeAccount();
        $this->saveAccounts($account);
        $this->stripe->queueException(new RuntimeException('Connection reset'));
        $this->stripe->queueException(new RuntimeException('Connection reset'));

        $response = $this->callController($account->accountSlug);

        $this->assertTrue($response['success']);
        $this->assertStringContainsString((new WebhookTarget())->url(), $response['data']['webhookWarning']);
        $this->assertSame([], give_stripe_get_all_accounts());
    }

    /**
     * @since TBD
     */
    public function testItWarnsWhenTheSavedAccountCannotBeRead(): void
    {
        $slug = 'acct_broken';
        give_update_option('_give_stripe_get_all_accounts', [$slug => ['account_slug' => $slug]]);

        $response = $this->callController($slug);

        $this->assertTrue($response['success']);
        $this->assertStringContainsString((new WebhookTarget())->url(), $response['data']['webhookWarning']);
        $this->assertSame([], $this->stripe->requests);
        $this->assertSame([], give_stripe_get_all_accounts());
    }

    /**
     * @since TBD
     */
    public function testItWarnsWhenTheSavedRecordIsNull(): void
    {
        $slug = 'acct_null';
        give_update_option('_give_stripe_get_all_accounts', [$slug => null]);

        $response = $this->callController($slug);

        $this->assertTrue($response['success']);
        $this->assertStringContainsString((new WebhookTarget())->url(), $response['data']['webhookWarning']);
        $this->assertSame([], $this->stripe->requests);
    }

    /**
     * @since TBD
     */
    public function testItKeepsTheEndpointWhenAnotherSavedRecordOfTheSameStripeAccountUsesIt(): void
    {
        $manual = $this->makeAccount(['account_slug' => 'account_1', 'account_id' => '', 'live_secret_key' => '']);
        $connect = $this->makeAccount(['account_slug' => 'acct_shared', 'account_id' => 'acct_shared']);
        $this->saveAccounts($manual, $connect);
        $this->stripe->queue(200, $this->listBody([$this->siteEndpoint()]));
        $this->stripe->queue(200, $this->listBody([$this->siteEndpoint()]));

        $response = $this->callController($manual->accountSlug);

        $this->assertTrue($response['success']);
        $this->assertArrayNotHasKey('data', $response);
        $this->assertSame(['get', 'get'], array_column($this->stripe->requests, 'method'));
        $this->assertSame([$connect->accountSlug], array_keys(give_stripe_get_all_accounts()));
    }

    /**
     * @since TBD
     */
    public function testItDoesNotWarnWhenNoAccountIsSavedUnderTheSlug(): void
    {
        $other = $this->makeAccount(['account_slug' => 'acct_other', 'account_id' => 'acct_other']);
        $this->saveAccounts($other);

        $response = $this->callController('acct_missing');

        $this->assertTrue($response['success']);
        $this->assertArrayNotHasKey('data', $response);
        $this->assertSame([], $this->stripe->requests);
    }

    /**
     * @since TBD
     */
    public function testItNamesBothListenerUrlsWhenTheSiteUrlDiffers(): void
    {
        update_option('siteurl', untrailingslashit(home_url()) . '/wp');
        $account = $this->makeAccount();
        $this->saveAccounts($account);
        $this->stripe->queue(401, $this->errorBody('authentication_error', 'Invalid API Key provided.'));
        $this->stripe->queue(401, $this->errorBody('authentication_error', 'Invalid API Key provided.'));

        $response = $this->callController($account->accountSlug);

        $urls = (new WebhookTarget())->urls();
        $this->assertCount(2, $urls);
        foreach ($urls as $url) {
            $this->assertStringContainsString($url, $response['data']['webhookWarning']);
        }
    }

    /**
     * @since TBD
     */
    public function testItDoesNotCallStripeWhenTheNonceIsInvalid(): void
    {
        $account = $this->makeAccount();
        $this->saveAccounts($account);
        $_GET = ['account_slug' => $account->accountSlug, '_wpnonce' => wp_create_nonce('another_action')];
        $_REQUEST = $_GET;

        try {
            give(DisconnectStripeAccountController::class)();
            $this->fail('Expected WPDieException.');
        } catch (WPDieException $exception) {
            $this->assertSame([], $this->stripe->requests);
            $this->assertSame([$account->accountSlug], array_keys(give_stripe_get_all_accounts()));
        }
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
     * Sends a request with a valid nonce and returns the decoded JSON response.
     *
     * @since TBD
     *
     * @return array{success: bool, data: mixed}
     */
    private function callController(string $accountSlug): array
    {
        $_GET = [
            'account_slug' => $accountSlug,
            '_wpnonce' => wp_create_nonce('give_disconnect_connected_stripe_account_' . $accountSlug),
        ];
        $_REQUEST = $_GET;

        ob_start();

        try {
            give(DisconnectStripeAccountController::class)();
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
}
