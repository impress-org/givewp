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

    public function testShouldReturnDisabledWhenSetInGlobal()
    {
        $_POST['enable_my_feature'] = 'disabled';

        $this->assertFalse(FeatureFlag::enabled('my_feature'));
    }

    public function testShouldReturnEnabledWhenSetInGlobal()
    {
        $_POST['enable_my_feature'] = 'enabled';

        $this->assertTrue(FeatureFlag::enabled('my_feature'));
    }
}
