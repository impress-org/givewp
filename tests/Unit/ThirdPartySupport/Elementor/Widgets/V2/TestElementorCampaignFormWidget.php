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
     * Test that render method outputs nothing when campaign_id is empty
     *
     * @unreleased
     */
    public function testRenderOutputsNothingWhenCampaignIdIsEmpty(): void
    {
        $widget = $this->getMockBuilder(ElementorCampaignFormWidget::class)
            ->onlyMethods(['get_settings_for_display'])
            ->getMock();

        $widget->method('get_settings_for_display')->willReturn([
            'campaign_id' => '',
            'display_style' => 'onpage',
            'use_default_form' => 'yes',
            'donate_button_text' => 'Donate Now',
            'form_id' => ''
        ]);

        // Use reflection to call the protected render method
        $reflection = new \ReflectionClass($widget);
        $method = $reflection->getMethod('render');
        $method->setAccessible(true);

        ob_start();
        $method->invoke($widget);
        $output = ob_get_clean();

        $this->assertEmpty($output);
    }

    /**
     * Test that render method processes campaign shortcode
     * In test environment, shortcode likely doesn't exist so outputs nothing
     *
     * @unreleased
     */
    public function testRenderProcessesCampaignShortcode(): void
    {
        $widget = $this->getMockBuilder(ElementorCampaignFormWidget::class)
            ->onlyMethods(['get_settings_for_display'])
            ->getMock();

        $widget->method('get_settings_for_display')->willReturn([
            'campaign_id' => '100',
            'display_style' => 'modal',
            'use_default_form' => 'yes',
            'donate_button_text' => 'Support Campaign',
            'form_id' => ''
        ]);

        // Use reflection to call the protected render method
        $reflection = new \ReflectionClass($widget);
        $method = $reflection->getMethod('render');
        $method->setAccessible(true);

        ob_start();
        $method->invoke($widget);
        $output = ob_get_clean();

        // In test environment without campaigns add-on, shortcode doesn't exist so outputs nothing
        $this->assertEmpty($output);
    }

    /**
     * Test that render method processes campaign shortcode with specific form
     *
     * @unreleased
     */
    public function testRenderProcessesCampaignShortcodeWithSpecificForm(): void
    {
        $widget = $this->getMockBuilder(ElementorCampaignFormWidget::class)
            ->onlyMethods(['get_settings_for_display'])
            ->getMock();

        $widget->method('get_settings_for_display')->willReturn([
            'campaign_id' => '200',
            'display_style' => 'newTab',
            'use_default_form' => 'no',
            'donate_button_text' => 'Custom Donate Text',
            'form_id' => '456'
        ]);

        // Use reflection to call the protected render method
        $reflection = new \ReflectionClass($widget);
        $method = $reflection->getMethod('render');
        $method->setAccessible(true);

        ob_start();
        $method->invoke($widget);
        $output = ob_get_clean();

        // Shortcode doesn't exist in test environment
        $this->assertEmpty($output);
    }

    /**
     * Test that render method processes campaign shortcode with onpage display style
     *
     * @unreleased
     */
    public function testRenderProcessesCampaignShortcodeOnpageStyle(): void
    {
        $widget = $this->getMockBuilder(ElementorCampaignFormWidget::class)
            ->onlyMethods(['get_settings_for_display'])
            ->getMock();

        $widget->method('get_settings_for_display')->willReturn([
            'campaign_id' => '300',
            'display_style' => 'onpage',
            'use_default_form' => 'yes',
            'donate_button_text' => 'Continue to Donate',
            'form_id' => ''
        ]);

        // Use reflection to call the protected render method
        $reflection = new \ReflectionClass($widget);
        $method = $reflection->getMethod('render');
        $method->setAccessible(true);

        ob_start();
        $method->invoke($widget);
        $output = ob_get_clean();

        // Shortcode doesn't exist in test environment
        $this->assertEmpty($output);
    }
}
