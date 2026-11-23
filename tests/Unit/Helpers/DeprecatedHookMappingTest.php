<?php

namespace Give\Tests\Unit\Helpers;

use Give\Tests\TestCase;

/**
 * Old hook names in the deprecated maps still run when Give fires the new names.
 *
 * @since TBD
 */
class DeprecatedHookMappingTest extends TestCase
{
    /**
     * @since TBD
     */
    public function setUp(): void
    {
        parent::setUp();

        add_filter('give_deprecated_function_trigger_error', '__return_false');
    }

    /**
     * @since TBD
     */
    public function tearDown(): void
    {
        remove_filter('give_deprecated_function_trigger_error', '__return_false');

        parent::tearDown();
    }

    /**
     * @since TBD
     */
    public function testMapsAreHookedOnTheNewNames(): void
    {
        $this->assertSame(10, has_filter('give_donation_data_before_gateway', 'give_deprecated_filter_mapping'));
        $this->assertSame(10, has_action('give_donation_form_before_cc_form', 'give_deprecated_action_mapping'));
        $this->assertSame(10, has_filter('give_stripe_get_connect_settings', 'give_stripe_deprecated_filter_mapping'));
    }

    /**
     * @since TBD
     */
    public function testOldFilterNameStillFiltersTheNewFilter(): void
    {
        $listener = static function ($data) {
            return $data . '-old';
        };
        add_filter('give_purchase_data_before_gateway', $listener);

        $result = apply_filters('give_donation_data_before_gateway', 'data');

        remove_filter('give_purchase_data_before_gateway', $listener);

        $this->assertSame('data-old', $result);
    }

    /**
     * @since TBD
     */
    public function testOldActionNameStillRunsWhenTheNewActionFires(): void
    {
        $calls = [];
        $listener = static function ($formId) use (&$calls) {
            $calls[] = $formId;
        };
        add_action('give_purchase_form_before_cc_form', $listener);

        do_action('give_donation_form_before_cc_form', 123);

        remove_action('give_purchase_form_before_cc_form', $listener);

        $this->assertSame([123], $calls);
    }

    /**
     * @since TBD
     */
    public function testOldStripeFilterNameStillFiltersTheNewFilter(): void
    {
        $listener = static function ($settings) {
            $settings['source'] = 'old';

            return $settings;
        };
        add_filter('get_give_stripe_connect_options', $listener);

        $result = apply_filters('give_stripe_get_connect_settings', []);

        remove_filter('get_give_stripe_connect_options', $listener);

        $this->assertSame(['source' => 'old'], $result);
    }
}
