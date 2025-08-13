<?php

namespace Give\ThirdPartySupport\Elementor\Widgets\V2\ElementorCampaignDonorsWidget;

function give($class)
{
    return new class {
        public function renderShortcode(array $attributes)
        {
            \Give\Tests\Unit\ThirdPartySupport\Elementor\Widgets\V2\TestElementorCampaignDonorsWidget::$capturedAttributes = $attributes;
            return '';
        }
    };
}

namespace Give\Tests\Unit\ThirdPartySupport\Elementor\Widgets\V2;

use Give\Tests\TestCase;
use Give\Tests\TestTraits\RefreshDatabase;
use Give\ThirdPartySupport\Elementor\Widgets\V2\ElementorCampaignDonorsWidget\ElementorCampaignDonorsWidget;

/**
 * @unreleased
 * @covers \Give\ThirdPartySupport\Elementor\Widgets\V2\ElementorCampaignDonorsWidget\ElementorCampaignDonorsWidget
 */
class TestElementorCampaignDonorsWidget extends TestCase
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
    public function testRenderOutputsNothingWhenCampaignIdEmpty(): void
    {
        $widget = $this->getMockBuilder(ElementorCampaignDonorsWidget::class)
            ->onlyMethods(['get_settings_for_display'])
            ->getMock();

        $widget->method('get_settings_for_display')->willReturn([
            'campaign_id' => '',
            'show_anonymous' => 'yes',
            'show_company_name' => 'yes',
            'show_avatar' => 'yes',
            'show_button' => 'yes',
            'donate_button_text' => 'Join the list',
            'sort_by' => 'top-donors',
            'donors_per_page' => 5,
            'load_more_button_text' => 'Load more',
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
	public function testRenderProcessesDonorsShortcode(): void
    {
        $widget = $this->getMockBuilder(ElementorCampaignDonorsWidget::class)
            ->onlyMethods(['get_settings_for_display'])
            ->getMock();

        $widget->method('get_settings_for_display')->willReturn([
            'campaign_id' => '88',
            'show_anonymous' => 'no',
            'show_company_name' => 'no',
            'show_avatar' => 'yes',
            'show_button' => 'no',
            'donate_button_text' => 'Join the list',
            'sort_by' => 'recent-donors',
            'donors_per_page' => 10,
            'load_more_button_text' => 'More',
        ]);

        $reflection = new \ReflectionClass($widget);
        $method = $reflection->getMethod('render');
        $method->setAccessible(true);

        ob_start();
        $method->invoke($widget);
        $output = ob_get_clean();

        $this->assertIsString($output);
        $this->assertSame([
            'campaign_id' => '88',
            'show_anonymous' => false,
            'show_company_name' => false,
            'show_avatar' => true,
            'show_button' => false,
            'donate_button_text' => 'Join the list',
            'sort_by' => 'recent-donors',
            'donors_per_page' => 10,
            'load_more_button_text' => 'More',
        ], self::$capturedAttributes);
    }
}

