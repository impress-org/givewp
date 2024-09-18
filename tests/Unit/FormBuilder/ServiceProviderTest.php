<?php

namespace Give\Tests\Unit\FormBuilder;

use Give\Tests\TestCase;
use Give\Tests\TestTraits\RefreshDatabase;

/**
 * @unreleased
 */
class ServiceProviderTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @unreleased
     */
    public function testItDismissesTheAdditionalPaymentGatewaysNotice() {

        $userId = $this->factory()->user->create();
        wp_set_current_user($userId);

        do_action('wp_ajax_givewp_additional_payment_gateways_hide_notice');

        $this->assertTrue(
            (bool) get_user_meta($userId, 'givewp-additional-payment-gateways-notice-dismissed', true)
        );
    }
}
