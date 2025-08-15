<?php

namespace Give\ThirdPartySupport\Elementor\Widgets\V2\ElementorCampaignCommentsWidget;

/**
 * Intercept give() calls from the widget's namespace to capture attributes passed to the shortcode renderer.
 */
function give($class)
{
    return new class {
        public function renderShortcode(array $attributes)
        {
            \Give\Tests\Unit\ThirdPartySupport\Elementor\Widgets\V2\TestElementorCampaignCommentsWidget::$capturedAttributes = $attributes;
            return '';
        }
    };
}

namespace Give\Tests\Unit\ThirdPartySupport\Elementor\Widgets\V2;

use Give\Tests\TestCase;
use Give\Tests\TestTraits\RefreshDatabase;
use Give\ThirdPartySupport\Elementor\Widgets\V2\ElementorCampaignCommentsWidget\ElementorCampaignCommentsWidget;

/**
 * @unreleased
 * @covers \Give\ThirdPartySupport\Elementor\Widgets\V2\ElementorCampaignCommentsWidget\ElementorCampaignCommentsWidget
 */
class TestElementorCampaignCommentsWidget extends TestCase
{
    use RefreshDatabase;
    use MockElementorTrait;

    /** @var array|null */
    public static $capturedAttributes;

    /**
     * @unreleased
     */
    public function setUp(): void
    {
        parent::setUp();
        $this->setUpMockElementorClasses();
        self::$capturedAttributes = null;
    }

    /**
     * @unreleased
     */
    public function testRenderOutputsNothingWhenCampaignIdEmpty(): void
    {
        $widget = $this->getMockBuilder(ElementorCampaignCommentsWidget::class)
            ->onlyMethods(['get_settings_for_display'])
            ->getMock();

        $widget->method('get_settings_for_display')->willReturn([
            'campaign_id' => '',
            'title' => '',
            'show_anonymous' => 'yes',
            'show_avatar' => 'yes',
            'show_date' => 'yes',
            'show_name' => 'yes',
            'comment_length' => 200,
            'read_more_text' => '',
            'comments_per_page' => 3,
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
     * @unreleased
     */
    public function testRenderProcessesCampaignCommentsShortcodeWithDefaults(): void
    {
        $widget = $this->getMockBuilder(ElementorCampaignCommentsWidget::class)
            ->onlyMethods(['get_settings_for_display'])
            ->getMock();

        $widget->method('get_settings_for_display')->willReturn([
            'campaign_id' => '42',
            'title' => '',
            'show_anonymous' => 'yes',
            'show_avatar' => 'yes',
            'show_date' => 'yes',
            'show_name' => 'yes',
            'comment_length' => 200,
            'read_more_text' => '',
            'comments_per_page' => 3,
        ]);

        $reflection = new \ReflectionClass($widget);
        $method = $reflection->getMethod('render');
        $method->setAccessible(true);

        ob_start();
        $method->invoke($widget);
        $output = ob_get_clean();

        $this->assertIsString($output);
        $this->assertSame([
            'campaign_id' => 42,
            'title' => '',
            'show_anonymous' => true,
            'show_avatar' => true,
            'show_date' => true,
            'show_name' => true,
            'comment_length' => 200,
            'read_more_text' => '',
            'comments_per_page' => 3,
        ], self::$capturedAttributes);
    }
}

