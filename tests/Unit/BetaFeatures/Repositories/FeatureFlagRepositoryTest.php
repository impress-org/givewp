<?php

namespace Give\Tests\Unit\BetaFeatures\Repositories;

use Give\BetaFeatures\Facades\FeatureFlag;
use Give\Tests\TestCase;
use Give\Tests\TestTraits\RefreshDatabase;

class FeatureFlagRepositoryTest extends TestCase
{
    use RefreshDatabase;

    public function testEventTicketsShouldNotBeEnabledByDefault()
    {
        if (defined('GIVE_FEATURE_ENABLE_EVENT_TICKETS')){
            $this->markTestSkipped();
        } else {
            $this->assertFalse(FeatureFlag::eventTickets());
        }
    }

    public function testShouldReturnDisabledWhenNotSet()
    {
        $this->assertFalse(FeatureFlag::enabled('my_feature'));
    }

    public function testShouldReturnDisabledWhenNotEnabled()
    {
        give_update_option('enable_my_feature', false);

        $this->assertFalse(FeatureFlag::enabled('my_feature'));
    }

    public function testShouldReturnEnabledWhenEnabled()
    {
        give_update_option('enable_my_feature', true);

        $this->assertTrue(FeatureFlag::enabled('my_feature'));
    }

    /**
     * @since TBD $_POST is no longer trusted outside a verified settings-save request.
     */
    public function testShouldIgnorePostOverrideWithoutAVerifiedSettingsSaveRequest()
    {
        give_update_option('enable_my_feature', true);
        $_POST['enable_my_feature'] = 'disabled';

        $this->assertTrue(FeatureFlag::enabled('my_feature'));
    }

    /**
     * @since TBD $_POST is no longer trusted outside a verified settings-save request.
     */
    public function testShouldIgnorePostOverrideEvenWhenItWouldEnableTheFeature()
    {
        give_update_option('enable_my_feature', false);
        $_POST['enable_my_feature'] = 'enabled';

        $this->assertFalse(FeatureFlag::enabled('my_feature'));
    }

    /**
     * @since TBD
     */
    public function testShouldTrustPostOverrideDuringAVerifiedSettingsSaveRequest()
    {
        $userId = self::factory()->user->create(['role' => 'administrator']);
        wp_set_current_user($userId);
        $_REQUEST['_give-save-settings'] = wp_create_nonce('give-save-settings');
        $_POST['enable_my_feature'] = 'enabled';

        give_update_option('enable_my_feature', false);

        $this->assertTrue(FeatureFlag::enabled('my_feature'));
    }

    /**
     * @since TBD
     */
    public function testShouldIgnorePostOverrideWhenNonceIsMissingEvenForAnAdministrator()
    {
        $userId = self::factory()->user->create(['role' => 'administrator']);
        wp_set_current_user($userId);
        $_POST['enable_my_feature'] = 'enabled';

        give_update_option('enable_my_feature', false);

        $this->assertFalse(FeatureFlag::enabled('my_feature'));
    }

    /**
     * @since TBD
     */
    public function testShouldIgnorePostOverrideWhenCapabilityIsMissingEvenWithAValidNonce()
    {
        $userId = self::factory()->user->create(['role' => 'subscriber']);
        wp_set_current_user($userId);
        $_REQUEST['_give-save-settings'] = wp_create_nonce('give-save-settings');
        $_POST['enable_my_feature'] = 'enabled';

        give_update_option('enable_my_feature', false);

        $this->assertFalse(FeatureFlag::enabled('my_feature'));
    }
}
