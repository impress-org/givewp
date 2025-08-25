<?php

namespace Give\ThirdPartySupport\Elementor\Widgets\V2\ElementorCampaignGoalWidget;

function give($class)
{
    return new class {
        public function renderShortcode(array $attributes)
        {
            \Give\Tests\Unit\ThirdPartySupport\Elementor\Widgets\V2\TestElementorCampaignGoalWidget::$capturedAttributes = $attributes;
            return '';
        }
    };
}

namespace Give\Tests\Unit\ThirdPartySupport\Elementor\Widgets\V2;

use Give\Tests\TestCase;
use Give\Tests\TestTraits\RefreshDatabase;
use Give\ThirdPartySupport\Elementor\Widgets\V2\ElementorCampaignGoalWidget\ElementorCampaignGoalWidget;

/**
 * @since 4.7.0
 * @covers \Give\ThirdPartySupport\Elementor\Widgets\V2\ElementorCampaignGoalWidget\ElementorCampaignGoalWidget
 */
class TestElementorCampaignGoalWidget extends TestCase
{
    use RefreshDatabase;
    use MockElementorTrait;

    /** @var array|null */
    public static $capturedAttributes;

    public function setUp(): void
    {
        parent::setUp();
        $this->setUpMockElementorClasses();
        self::$capturedAttributes = null;
    }

	/**
	 * @since 4.7.0
	 */
    public function testRenderOutputsNothingWhenCampaignIdEmpty(): void
    {
        $widget = $this->getMockBuilder(ElementorCampaignGoalWidget::class)
            ->onlyMethods(['get_settings_for_display'])
            ->getMock();

        $widget->method('get_settings_for_display')->willReturn([
            'campaign_id' => '',
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
	public function testRenderProcessesCampaignGoalShortcode(): void
    {
        $widget = $this->getMockBuilder(ElementorCampaignGoalWidget::class)
            ->onlyMethods(['get_settings_for_display'])
            ->getMock();

        $widget->method('get_settings_for_display')->willReturn([
            'campaign_id' => '101',
        ]);

        $reflection = new \ReflectionClass($widget);
        $method = $reflection->getMethod('render');
        $method->setAccessible(true);

        ob_start();
        $method->invoke($widget);
        $output = ob_get_clean();

        $this->assertIsString($output);
        $this->assertSame([
            'campaign_id' => '101',
        ], self::$capturedAttributes);
    }
}

