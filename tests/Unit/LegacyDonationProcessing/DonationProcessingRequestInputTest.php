<?php

namespace Give\Tests\Unit\LegacyDonationProcessing;

use Exception;
use Give\Tests\TestCase;
use Give\Tests\TestTraits\RefreshDatabase;
use WPDieException;

/**
 * Runs donations through the legacy processing code (give_process_donation_form() and its helpers)
 * to check that unslashing and sanitizing request input did not change how valid requests behave.
 *
 * @since TBD
 */
final class DonationProcessingRequestInputTest extends TestCase
{
    use RefreshDatabase;

    /**
     * The $_SERVER values from before the test, so changes made here do not reach later tests.
     *
     * @since TBD
     */
    private array $serverBackup = [];

    /**
     * @since TBD
     */
    public function setUp(): void
    {
        $this->serverBackup = $_SERVER;

        parent::setUp();

        if ( ! defined('GIVE_UNIT_TESTS')) {
            define('GIVE_UNIT_TESTS', true);
        }

        add_filter('wp_die_ajax_handler', '_give_die_handler', 10, 3);
        add_filter('wp_die_json_handler', '_give_die_handler', 10, 3);
        add_filter('wp_die_handler', '_give_die_handler', 10, 3);

        // Donation forms are submitted through admin-ajax.php.
        add_filter('wp_doing_ajax', '__return_true');

        wp_set_current_user(0);
        give_clear_errors();
    }

    /**
     * @since TBD
     */
    public function tearDown(): void
    {
        remove_filter('wp_doing_ajax', '__return_true');

        $_POST = [];
        $_REQUEST = [];

        give_clear_errors();

        parent::tearDown();

        $_SERVER = $this->serverBackup;
    }

    /**
     * @since TBD
     */
    private function createFormId(): int
    {
        $formId = wp_insert_post([
            'post_type' => 'give_forms',
            'post_status' => 'publish',
            'post_title' => 'Legacy Form',
        ]);

        give_update_meta($formId, '_give_price_option', 'set');
        give_update_meta($formId, '_give_set_price', '25.00');

        return $formId;
    }

    /**
     * Stops the legacy flow where it would normally end the request (give_die() returns in tests).
     *
     * @since TBD
     */
    private function stopAt(string $hook): void
    {
        // Priority 1 runs before the core listeners that print and clear the errors.
        add_action($hook, static function () {
            throw new Exception('stop');
        }, 1);
    }

    /**
     * Slashed the way WordPress slashes superglobals before plugins read them.
     *
     * @since TBD
     */
    private function validPost(int $formId, array $extra = []): array
    {
        return wp_slash(array_merge([
            'give-form-id' => $formId,
            'give-form-hash' => wp_create_nonce("give_donation_form_nonce_{$formId}"),
            'give-current-url' => home_url('/'),
            'give-gateway' => 'manual',
            'give-amount' => '25.00',
            'give_first' => "Jane O'Neil",
            'give_last' => 'Doe',
            'give_email' => 'jane@example.com',
        ], $extra));
    }

    /**
     * @since TBD
     */
    public function testAjaxValidationPassesWithExtraGatewayFields(): void
    {
        $formId = $this->createFormId();
        $_POST = $this->validPost($formId, [
            'give_ajax' => 'true',
            'gateway_token' => 'tok_123',
            'custom_gateway_field' => 'a "quoted" value',
        ]);

        $this->stopAt('give_process_donation_after_validation');

        ob_start();
        try {
            give_process_donation_form();
        } catch (Exception $e) {
            // Expected: stopped on purpose after validation passed.
        } catch (WPDieException $e) {
            // wp_die() is converted to an exception in the test environment.
        }
        $output = (string) ob_get_clean();

        $this->assertFalse(give_get_errors());
        $this->assertStringContainsString('success', $output);
    }

    /**
     * @since TBD
     */
    public function testDonationReachesTheGatewayWithUnslashedDonorData(): void
    {
        $formId = $this->createFormId();
        $_POST = $this->validPost($formId, ['custom_gateway_field' => 'a "quoted" value']);

        $postData = null;
        $userInfo = null;
        add_action('give_checkout_before_gateway', static function ($post, $info) use (&$postData, &$userInfo) {
            $postData = $post;
            $userInfo = $info;

            throw new Exception('stop-at-gateway');
        }, 10, 2);

        ob_start();
        try {
            give_process_donation_form();
        } catch (Exception $e) {
            // Expected: stopped on purpose once the gateway hook fires.
        } catch (WPDieException $e) {
            // wp_die() is converted to an exception in the test environment.
        }
        ob_end_clean();

        $this->assertFalse(give_get_errors());
        $this->assertIsArray($postData);
        $this->assertSame("Jane O'Neil", $userInfo['first_name']);
        $this->assertSame('jane@example.com', $userInfo['email']);
        $this->assertSame('a "quoted" value', $postData['custom_gateway_field']);
    }

    /**
     * @since TBD
     */
    public function testDonationWithABadNonceIsRejected(): void
    {
        $formId = $this->createFormId();
        $_POST = $this->validPost($formId, ['give_ajax' => 'true', 'give-form-hash' => 'bad-nonce']);

        $this->stopAt('give_ajax_donation_errors');

        ob_start();
        try {
            give_process_donation_form();
        } catch (Exception $e) {
            // Expected: stopped on purpose when the errors are sent back.
        } catch (WPDieException $e) {
            // wp_die() is converted to an exception in the test environment.
        }
        ob_end_clean();

        $this->assertArrayHasKey('donation_form_nonce', give_get_errors());
    }

    /**
     * @since TBD
     */
    public function testSpamCheckReadsTheUserAgent(): void
    {
        $_SERVER['HTTP_USER_AGENT'] = 'Mozilla\\/5.0';
        $this->assertFalse(give_is_spam_donation());

        $_SERVER['HTTP_USER_AGENT'] = '';
        $this->assertTrue(give_is_spam_donation());

        unset($_SERVER['HTTP_USER_AGENT']);
        $this->assertTrue(give_is_spam_donation());
    }

    /**
     * @since TBD
     */
    public function testChosenGatewayIsReadFromTheRequest(): void
    {
        $formId = $this->createFormId();

        $_REQUEST['give_form_id'] = (string) $formId;
        $_REQUEST['payment-mode'] = 'offline';

        $this->assertSame('offline', give_get_chosen_gateway($formId));

        $_REQUEST['payment-mode'] = 'not\\_a_gateway';
        $this->assertSame(give_get_default_gateway($formId), give_get_chosen_gateway($formId));
    }

    /**
     * @since TBD
     */
    public function testOfflineGatewayStaysAvailableOnTheNewFormScreen(): void
    {
        require_once GIVE_PLUGIN_DIR . 'includes/gateways/offline-donations.php';

        $_SERVER['REQUEST_URI'] = '/wp-admin/post-new.php?post_type=give_forms';

        $gateways = ['offline' => ['admin_label' => 'Offline']];
        $this->assertArrayHasKey('offline', give_filter_offline_gateway($gateways, 1));
    }

    /**
     * @since TBD
     */
    public function testDonationLevelsMinAndMaxAreSavedFromSlashedPostData(): void
    {
        $formId = $this->createFormId();

        $_POST = wp_slash([
            '_give_price_option' => 'multi',
            '_give_donation_levels' => [
                ['_give_id' => ['level_id' => '1'], '_give_amount' => '10.00', '_give_text' => 'Say "hi"'],
                ['_give_id' => ['level_id' => '2'], '_give_amount' => '50.00', '_give_text' => 'It\'s big'],
            ],
        ]);

        give_set_donation_levels_max_min_amount($formId);

        $this->assertEquals(10, give_get_meta($formId, '_give_levels_minimum_amount', true));
        $this->assertEquals(50, give_get_meta($formId, '_give_levels_maximum_amount', true));

        $_POST = ['_give_price_option' => 'set'];
        give_set_donation_levels_max_min_amount($formId);

        $this->assertEmpty(give_get_meta($formId, '_give_levels_minimum_amount', true));
    }
}
