<?php

namespace Give\ThirdPartySupport\Elementor\Widgets\V2\ElementorCampaignWidget;

/**
 * Intercept give() calls from the widget's namespace to capture attributes passed to the shortcode renderer.
 */
function give($class)
{
    return new class {
        public function renderShortcode(array $attributes)
        {
            \Give\Tests\Unit\ThirdPartySupport\Elementor\Widgets\V2\TestElementorCampaignWidget::$capturedAttributes = $attributes;
            return '';
        }
    };
}

namespace Give\Tests\Unit\ThirdPartySupport\Elementor\Widgets\V2;

use Give\Tests\TestCase;
use Give\Tests\TestTraits\RefreshDatabase;
use Give\ThirdPartySupport\Elementor\Widgets\V2\ElementorCampaignWidget\ElementorCampaignWidget;

/**
 * @since 4.7.0
 * @covers \Give\ThirdPartySupport\Elementor\Widgets\V2\ElementorCampaignWidget\ElementorCampaignWidget
 */
class TestElementorCampaignWidget extends TestCase
{
    use RefreshDatabase;
    use MockElementorTrait;

    /** @var array|null */
    public static $capturedAttributes;

    /**
     * @since 4.7.0
     */
    public function setUp(): void
    {
        parent::setUp();
        $this->setUpMockElementorClasses();
        self::$capturedAttributes = null;
    }

    /**
     * @since 4.7.0
     */
    /**
     * @since 4.7.0
     */
    public function testRenderOutputsNothingWhenCampaignIdEmpty(): void
    {
        $widget = $this->getMockBuilder(ElementorCampaignWidget::class)
            ->onlyMethods(['get_settings_for_display'])
            ->getMock();

        $widget->method('get_settings_for_display')->willReturn([
            'campaign_id' => '',
            'show_image' => 'yes',
            'show_description' => 'yes',
            'show_goal' => 'yes',
        ]);

        $reflection = new \ReflectionClass($widget);
        $method = $reflection->getMethod('render');
        $method->setAccessible(true);

        ob_start();
        $method->invoke($widget);
        $output = ob_get_clean();

        $this->assertEmpty($output);
        $this->assertNull(self::$capturedAttributes);
    }

    /**
     * @since 4.7.0
     */
    /**
     * @since 4.7.0
     */
    public function testRenderProcessesCampaignShortcodeWithDefaults(): void
    {
        $widget = $this->getMockBuilder(ElementorCampaignWidget::class)
            ->onlyMethods(['get_settings_for_display'])
            ->getMock();

        $widget->method('get_settings_for_display')->willReturn([
            'campaign_id' => '42',
            'show_image' => 'yes',
            'show_description' => 'yes',
            'show_goal' => 'yes',
        ]);

        $reflection = new \ReflectionClass($widget);
        $method = $reflection->getMethod('render');
        $method->setAccessible(true);

        ob_start();
        $method->invoke($widget);
        $output = ob_get_clean();

        $this->assertIsString($output);
        $this->assertSame([
            'campaign_id' => '42',
            'show_image' => true,
            'show_description' => true,
            'show_goal' => true,
        ], self::$capturedAttributes);
    }
}

