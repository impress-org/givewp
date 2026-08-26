<?php

namespace Give\Tests\Unit\Form;

use Give\DonationForms\Models\DonationForm;
use Give\Helpers\Form\Utils as FormUtils;
use Give\Tests\TestCase;
use Give\Tests\TestTraits\RefreshDatabase;

/**
 * The legacy donation processor (give_process_donation) only supports option-based (v2)
 * forms. Visual Form Builder (v3) forms are processed through the givewp-donate route,
 * so the legacy endpoint must bail early for them.
 *
 * @since 4.16.6
 */
class LegacyDonationProcessorBailTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Prevent give_die() from stopping the tests (same pattern used by the legacy suite).
     *
     * @since 4.16.6
     */
    public function setUp(): void
    {
        parent::setUp();

        if ( ! defined('GIVE_UNIT_TESTS')) {
            define('GIVE_UNIT_TESTS', true);
        }

        // Prevent wp_die() (used by give_die() and wp_send_json_*()) from stopping
        // the tests: the _give_die_handler() callback returns early when
        // GIVE_UNIT_TESTS is defined.
        add_filter('wp_die_ajax_handler', '_give_die_handler', 10, 3);
        add_filter('wp_die_json_handler', '_give_die_handler', 10, 3);
        add_filter('wp_die_handler', '_give_die_handler', 10, 3);

        give_clear_errors();
    }

    /**
     * @since 4.16.6
     */
    public function tearDown(): void
    {
        $_POST = [];

        give_clear_errors();

        parent::tearDown();
    }

    /**
     * Option-based (v2) forms do not have the formBuilderSettings meta.
     *
     * @since 4.16.6
     */
    private function createV2Form(): int
    {
        return wp_insert_post([
            'post_type'   => 'give_forms',
            'post_status' => 'publish',
            'post_title'  => 'V2 Form',
        ]);
    }

    /**
     * The DonationForm model factory creates Visual Form Builder (v3) forms.
     *
     * @since 4.16.6
     */
    private function createV3Form(): int
    {
        return DonationForm::factory()->create()->id;
    }

    /**
     * @since 4.16.6
     */
    public function testProcessDonationBailsEarlyForInvalidFormId()
    {
        $invalidFormId = 999999;

        $proceeded = false;
        add_action('give_pre_process_donation', static function () use (&$proceeded) {
            $proceeded = true;
        });

        $_POST = [
            'give-form-id'     => $invalidFormId,
            'give-form-hash'   => wp_create_nonce("give_donation_form_nonce_{$invalidFormId}"),
            'give-current-url' => home_url('/'),
            'give_ajax'        => 'true',
        ];

        ob_start();
        try {
            give_process_donation_form();
        } catch (\WPDieException $e) {
            // wp_die() is converted to an exception in the test environment.
        }
        $output = (string) ob_get_clean();

        $this->assertStringContainsString('give_invalid_donation_form', $output);
        $this->assertFalse($proceeded, 'The donation flow must not proceed for an invalid form ID.');
    }

    /**
     * Draft give_forms posts remain valid for legacy donation processing.
     *
     * @since 4.16.6
     */
    public function testProcessDonationDoesNotBailForDraftV2Forms()
    {
        $formId = wp_insert_post([
            'post_type'   => 'give_forms',
            'post_status' => 'draft',
            'post_title'  => 'Draft V2 Form',
        ]);
        $this->assertFalse(FormUtils::isV3Form($formId));

        $proceeded = false;
        add_action('give_pre_process_donation', static function () use (&$proceeded) {
            $proceeded = true;
            throw new \Exception('stop-after-guard');
        });

        $_POST = [
            'give-form-id'     => $formId,
            'give-form-hash'   => wp_create_nonce("give_donation_form_nonce_{$formId}"),
            'give-current-url' => home_url('/'),
            'give_ajax'        => 'true',
        ];

        ob_start();
        try {
            give_process_donation_form();
        } catch (\Exception $e) {
            // Expected: the flow was intentionally stopped right after the guard.
        } catch (\WPDieException $e) {
            // wp_die() is converted to an exception in the test environment.
        }
        $output = (string) ob_get_clean();

        $this->assertStringNotContainsString('give_invalid_donation_form', $output);
        $this->assertTrue($proceeded, 'Draft v2 forms must still pass the form ID guard.');
    }

    /**
     * @since 4.16.6
     */
    public function testProcessDonationBailsEarlyForV3Forms()
    {
        $formId = $this->createV3Form();
        $this->assertTrue(FormUtils::isV3Form($formId));

        $proceeded = false;
        add_action('give_pre_process_donation', static function () use (&$proceeded) {
            $proceeded = true;
        });

        $_POST = [
            'give-form-id'     => $formId,
            'give-form-hash'   => wp_create_nonce("give_donation_form_nonce_{$formId}"),
            'give-current-url' => home_url('/'),
            'give_ajax'        => 'true', // Avoid wp_safe_redirect() in the non-ajax path.
        ];

        ob_start();
        try {
            give_process_donation_form();
        } catch (\WPDieException $e) {
            // wp_die() is converted to an exception in the test environment.
        }
        $output = (string) ob_get_clean();

        $this->assertStringContainsString('give_unsupported_form_version', $output);
        $this->assertFalse($proceeded, 'The donation flow must not proceed past the guard for v3 forms.');
    }

    /**
     * @since 4.16.6
     */
    public function testProcessDonationDoesNotBailForV2Forms()
    {
        $formId = $this->createV2Form();
        $this->assertFalse(FormUtils::isV3Form($formId));

        $proceeded = false;
        add_action('give_pre_process_donation', static function () use (&$proceeded) {
            $proceeded = true;
            // Stop the flow right after the guard: everything past this hook is
            // the regular v2 validation pipeline, out of scope for this test.
            throw new \Exception('stop-after-guard');
        });

        $_POST = [
            'give-form-id'     => $formId,
            'give-form-hash'   => wp_create_nonce("give_donation_form_nonce_{$formId}"),
            'give-current-url' => home_url('/'),
            'give_ajax'        => 'true', // Avoid wp_safe_redirect() in the non-ajax path.
        ];

        ob_start();
        try {
            give_process_donation_form();
        } catch (\Exception $e) {
            // Expected: the flow was intentionally stopped right after the guard.
        } catch (\WPDieException $e) {
            // wp_die() is converted to an exception in the test environment.
        }
        $output = (string) ob_get_clean();

        $this->assertStringNotContainsString('give_unsupported_form_version', $output);
        $this->assertTrue($proceeded, 'The donation flow must proceed past the guard for v2 forms.');
    }

    /**
     * @since 4.16.6
     */
    public function testDonationFormNonceRejectsInvalidFormId()
    {
        if ( ! defined('DOING_AJAX')) {
            define('DOING_AJAX', true);
        }

        $_POST = ['give_form_id' => 999999];

        ob_start();
        try {
            give_donation_form_nonce();
        } catch (\WPDieException $e) {
            // wp_send_json_*() is converted to an exception in the test environment.
        }
        $output = (string) ob_get_clean();

        $this->assertStringContainsString('give_invalid_donation_form', $output);
    }

    /**
     * @since 4.16.6
     */
    public function testDonationFormResetAllNonceRejectsInvalidFormId()
    {
        if ( ! defined('DOING_AJAX')) {
            define('DOING_AJAX', true);
        }

        $_POST = ['give_form_id' => 999999];

        ob_start();
        try {
            give_donation_form_reset_all_nonce();
        } catch (\WPDieException $e) {
            // wp_send_json_*() is converted to an exception in the test environment.
        }
        $output = (string) ob_get_clean();

        $this->assertStringContainsString('give_invalid_donation_form', $output);
    }

    /**
     * @since 4.16.6
     */
    public function testDonationFormNonceRejectsV3Forms()
    {
        if ( ! defined('DOING_AJAX')) {
            // wp_send_json_*() calls die() directly unless DOING_AJAX is defined.
            define('DOING_AJAX', true);
        }

        $formId = $this->createV3Form();

        $_POST = ['give_form_id' => $formId];

        ob_start();
        try {
            give_donation_form_nonce();
        } catch (\WPDieException $e) {
            // wp_send_json_*() is converted to an exception in the test environment.
        }
        $output = (string) ob_get_clean();

        $this->assertStringContainsString('give_unsupported_form_version', $output);
    }

    /**
     * @since 4.16.6
     */
    public function testDonationFormNonceStillWorksForV2Forms()
    {
        if ( ! defined('DOING_AJAX')) {
            // wp_send_json_*() calls die() directly unless DOING_AJAX is defined.
            define('DOING_AJAX', true);
        }

        $formId = $this->createV2Form();

        $_POST = ['give_form_id' => $formId];

        ob_start();
        try {
            give_donation_form_nonce();
        } catch (\WPDieException $e) {
            // wp_send_json_*() is converted to an exception in the test environment.
        }
        $output = (string) ob_get_clean();

        $json = json_decode($output, true);
        $this->assertIsArray($json);
        $this->assertNotEmpty($json['success']);
        $this->assertNotEmpty($json['data']);
    }
}
