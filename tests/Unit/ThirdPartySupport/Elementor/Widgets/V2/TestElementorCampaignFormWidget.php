<?php

namespace Give\Tests\Unit\ThirdPartySupport\Elementor\Widgets\V2;

use Give\Tests\TestCase;
use Give\Tests\TestTraits\RefreshDatabase;
use Give\ThirdPartySupport\Elementor\Widgets\V2\ElementorCampaignFormWidget\ElementorCampaignFormWidget;

/**
 * @unreleased
 * @covers \Give\ThirdPartySupport\Elementor\Widgets\V2\ElementorCampaignFormWidget\ElementorCampaignFormWidget
 */
class TestElementorCampaignFormWidget extends TestCase
{
    use RefreshDatabase;
    use MockElementorTrait;

    /**
     * @unreleased
     */
    public function setUp(): void
    {
        parent::setUp();
        $this->setUpMockElementorClasses();
    }

    /**
     * Test that render method processes campaign shortcode
     * In test environment, shortcode likely doesn't exist so outputs nothing
     *
     * @unreleased
     */
    public function testRenderProcessesCampaignShortcode(): void
    {
        $this->markTestIncomplete('This test is not implemented yet.');
    }
}
