<?php

namespace Give\ThirdPartySupport\Elementor\Widgets\V2\ElementorCampaignGridWidget;

function give($class)
{
    return new class {
        public function renderShortcode(array $attributes)
        {
            \Give\Tests\Unit\ThirdPartySupport\Elementor\Widgets\V2\TestElementorCampaignGridWidget::$capturedAttributes = $attributes;
            return '';
        }
    };
}

namespace Give\Tests\Unit\ThirdPartySupport\Elementor\Widgets\V2;

use Give\Tests\TestCase;
use Give\Tests\TestTraits\RefreshDatabase;
use Give\ThirdPartySupport\Elementor\Widgets\V2\ElementorCampaignGridWidget\ElementorCampaignGridWidget;

/**
 * @unreleased
 * @covers \Give\ThirdPartySupport\Elementor\Widgets\V2\ElementorCampaignGridWidget\ElementorCampaignGridWidget
 */
class TestElementorCampaignGridWidget extends TestCase
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
	 * @unreleased
	 */
    public function testRenderProcessesGridWithDefaults(): void
    {
        $widget = $this->getMockBuilder(ElementorCampaignGridWidget::class)
            ->onlyMethods(['get_settings_for_display'])
            ->getMock();

        $widget->method('get_settings_for_display')->willReturn([
            'layout' => 'full',
            'show_image' => 'yes',
            'show_description' => 'yes',
            'show_goal' => 'yes',
            'sort_by' => 'date',
            'order_by' => 'desc',
            'per_page' => 6,
            'show_pagination' => 'yes',
        ]);

        $reflection = new \ReflectionClass($widget);
        $method = $reflection->getMethod('render');
        $method->setAccessible(true);

        ob_start();
        $method->invoke($widget);
        $output = ob_get_clean();

        $this->assertIsString($output);
        $this->assertSame([
            'layout' => 'full',
            'show_image' => true,
            'show_description' => true,
            'show_goal' => true,
            'sort_by' => 'date',
            'order_by' => 'desc',
            'per_page' => 6,
            'show_pagination' => true,
            'filter_by' => null,
        ], self::$capturedAttributes);
    }
}

