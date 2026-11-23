<?php

namespace Give\Tests\Unit\Ajax;

use Give\Tests\TestCase;
use Give\Tests\TestTraits\RefreshDatabase;
use Give_Helper_Form;
use WPDieException;

/**
 * @since 4.19.2.2
 *
 * @covers ::give_check_for_form_price_variations
 */
final class FormPriceVariationsRequestTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @since 4.19.2.2
     */
    public function setUp(): void
    {
        parent::setUp();

        add_filter('wp_doing_ajax', '__return_true');

        // Priority 11 so give_die()'s priority-10 handler is overridden and wp_die() stays catchable.
        add_filter('wp_die_ajax_handler', [$this, 'get_wp_die_handler'], 11);

        wp_set_current_user($this->factory()->user->create(['role' => 'administrator']));
    }

    /**
     * @since 4.19.2.2
     */
    public function tearDown(): void
    {
        remove_filter('wp_doing_ajax', '__return_true');
        remove_filter('wp_die_ajax_handler', [$this, 'get_wp_die_handler'], 11);
        remove_filter('wp_die_ajax_handler', '_give_die_handler', 10);
        remove_filter('wp_die_json_handler', '_give_die_handler', 10);
        remove_filter('wp_die_handler', '_give_die_handler', 10);

        $_POST = [];
        $_REQUEST = [];

        parent::tearDown();
    }

    /**
     * @since 4.19.2.2
     */
    public function testRejectsRequestWithoutValidNonce(): void
    {
        $_POST['form_id'] = Give_Helper_Form::create_multilevel_form()->get_ID();

        [$output, $dieMessage] = $this->runHandler();

        $this->assertSame('-1', $dieMessage);
        $this->assertSame('', $output);
    }

    /**
     * @since 4.19.2.2
     */
    public function testReturnsPriceLevelsForRequestWithValidNonce(): void
    {
        $formId = Give_Helper_Form::create_multilevel_form()->get_ID();

        $_REQUEST['_wpnonce'] = $_POST['_wpnonce'] = wp_create_nonce('give_check_for_form_price_variations');
        $_POST['form_id'] = $formId;

        [$output] = $this->runHandler();

        foreach (give_get_variable_prices($formId) as $price) {
            $this->assertStringContainsString('<option value="' . $price['_give_id']['level_id'] . '">', $output);
        }
    }

    /**
     * Runs the handler until it ends the request with wp_die().
     *
     * @since 4.19.2.2
     *
     * @return array{0: string, 1: string} The printed output and the wp_die() message.
     */
    private function runHandler(): array
    {
        $dieMessage = null;

        ob_start();

        try {
            give_check_for_form_price_variations();
        } catch (WPDieException $exception) {
            $dieMessage = $exception->getMessage();
        }

        $output = ob_get_clean();

        $this->assertNotNull($dieMessage, 'The handler must end the request with wp_die().');

        return [$output, $dieMessage];
    }
}
