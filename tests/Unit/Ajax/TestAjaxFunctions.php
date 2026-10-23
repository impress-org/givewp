<?php

namespace Give\Tests\Unit\Ajax;

use Give\Tests\TestCase;
use WPDieException;

/**
 * @since TBD
 */
class TestAjaxFunctions extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();

        add_filter('wp_doing_ajax', '__return_true');
        add_filter('wp_die_ajax_handler', [$this, 'get_wp_die_handler']);
    }

    public function tearDown(): void
    {
        remove_filter('wp_doing_ajax', '__return_true');
        remove_filter('wp_die_ajax_handler', [$this, 'get_wp_die_handler']);
        unset($_POST['country'], $_POST['field_name'], $_POST['s'], $_POST['form_id']);

        parent::tearDown();
    }

    /**
     * @since TBD
     */
    public function testStatesFieldIgnoresMissingRequestValues()
    {
        $response = $this->runHandler('give_ajax_get_states_field');

        $this->assertTrue($response['success']);
    }

    /**
     * @since TBD
     */
    public function testStatesFieldUsesTheUnslashedFieldName()
    {
        $_POST['country'] = wp_slash('US');
        $_POST['field_name'] = wp_slash('card_state');

        $response = $this->runHandler('give_ajax_get_states_field');

        $this->assertTrue($response['states_found']);
        $this->assertStringContainsString('name="card_state"', $response['data']);
    }

    /**
     * @since TBD
     */
    public function testFormSearchWorksWithoutSearchTerm()
    {
        $formId = $this->factory()->post->create(['post_type' => 'give_forms', 'post_status' => 'publish']);

        $ids = wp_list_pluck($this->runHandler('give_ajax_form_search'), 'id');

        $this->assertContains($formId, $ids);
    }

    /**
     * @since TBD
     */
    public function testCheckoutFieldsCastTheFormIdToAnInteger()
    {
        $_POST['form_id'] = wp_slash('12abc');

        $captured = null;
        add_action('give_donation_form_register_login_fields', function ($formId) use (&$captured) {
            $captured = $formId;
        });

        $this->runHandler('give_load_checkout_fields');

        $this->assertSame(12, $captured);
    }

    /**
     * Runs an AJAX handler and returns its decoded JSON response.
     *
     * @since TBD
     */
    private function runHandler(string $handler): array
    {
        ob_start();

        try {
            $handler();
        } catch (WPDieException $exception) {
            // wp_send_json() ends the request with wp_die().
        }

        return json_decode(ob_get_clean(), true);
    }
}
