<?php

namespace Give\ThirdPartySupport\Elementor\Widgets\V2\ElementorCampaignFormWidget;

/**
 * Intercept do_shortcode calls from the widget's namespace to capture the built shortcode.
 */
function do_shortcode($shortcode)
{
    \Give\Tests\Unit\ThirdPartySupport\Elementor\Widgets\V2\TestElementorCampaignFormWidget::$capturedShortcode = $shortcode;
    return '';
}

namespace Give\Tests\Unit\ThirdPartySupport\Elementor\Widgets\V2;

use Give\Tests\TestCase;
use Give\Tests\TestTraits\RefreshDatabase;

/**
 * @unreleased
 * @covers \Give\ThirdPartySupport\Elementor\Widgets\V2\ElementorCampaignFormWidget\ElementorCampaignFormWidget
 */
class TestElementorCampaignFormWidget extends TestCase
{
    use RefreshDatabase;
    use MockElementorTrait;

    /** @var string|null */
    public static $capturedShortcode;

    /**
     * @unreleased
     */
    public function setUp(): void
    {
        parent::setUp();
        $this->setUpMockElementorClasses();
        self::$capturedShortcode = null;
    }

    /**
     * Test that render method processes campaign shortcode
     * In test environment, shortcode likely doesn't exist so outputs nothing
     *
     * @unreleased
     */
    public function testRenderOutputsNothingWhenCampaignIdEmpty(): void
    {
        $widget = $this->getMockBuilder(\Give\ThirdPartySupport\Elementor\Widgets\V2\ElementorCampaignFormWidget\ElementorCampaignFormWidget::class)
            ->onlyMethods(['get_settings_for_display'])
            ->getMock();

        $widget->method('get_settings_for_display')->willReturn([
            'campaign_id' => '',
            'display_style' => 'onpage',
            'use_default_form' => 'yes',
            'donate_button_text' => 'Continue to Donate',
            'form_id' => '',
        ]);

        $reflection = new \ReflectionClass($widget);
        $method = $reflection->getMethod('render');
        $method->setAccessible(true);

        ob_start();
        $method->invoke($widget);
        $output = ob_get_clean();

        $this->assertEmpty($output);
        $this->assertNull(self::$capturedShortcode);
    }

    /**
     * @unreleased
     */
    public function testRenderProcessesCampaignShortcodeWithDefaultForm(): void
    {
        $widget = $this->getMockBuilder(\Give\ThirdPartySupport\Elementor\Widgets\V2\ElementorCampaignFormWidget\ElementorCampaignFormWidget::class)
            ->onlyMethods(['get_settings_for_display'])
            ->getMock();

        $widget->method('get_settings_for_display')->willReturn([
            'campaign_id' => '42',
            'display_style' => 'modal',
            'use_default_form' => 'yes',
            'donate_button_text' => 'Donate Now',
            'form_id' => '',
        ]);

        $reflection = new \ReflectionClass($widget);
        $method = $reflection->getMethod('render');
        $method->setAccessible(true);

        ob_start();
        $method->invoke($widget);
        $output = ob_get_clean();

        $this->assertIsString($output);
        $this->assertSame(
            '[givewp_campaign_form campaign_id="42" display_style="modal" use_default_form="yes" continue_button_title="Donate Now" id=""]',
            self::$capturedShortcode
        );
    }

    /**
     * @unreleased
     */
    public function testRenderProcessesCampaignShortcodeWithSpecificForm(): void
    {
        $widget = $this->getMockBuilder(\Give\ThirdPartySupport\Elementor\Widgets\V2\ElementorCampaignFormWidget\ElementorCampaignFormWidget::class)
            ->onlyMethods(['get_settings_for_display'])
            ->getMock();

        $widget->method('get_settings_for_display')->willReturn([
            'campaign_id' => '5',
            'display_style' => 'newTab',
            'use_default_form' => 'no',
            'donate_button_text' => 'Contribute',
            'form_id' => '123',
        ]);

        $reflection = new \ReflectionClass($widget);
        $method = $reflection->getMethod('render');
        $method->setAccessible(true);

        ob_start();
        $method->invoke($widget);
        $output = ob_get_clean();

        $this->assertIsString($output);
        $this->assertSame(
            '[givewp_campaign_form campaign_id="5" display_style="newTab" use_default_form="no" continue_button_title="Contribute" id="123"]',
            self::$capturedShortcode
        );
    }
}
