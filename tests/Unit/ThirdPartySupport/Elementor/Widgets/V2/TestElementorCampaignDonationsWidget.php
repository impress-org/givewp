<?php

namespace Give\ThirdPartySupport\Elementor\Widgets\V2\ElementorCampaignDonationsWidget;

function give($class)
{
    return new class {
        public function renderShortcode(array $attributes)
        {
            \Give\Tests\Unit\ThirdPartySupport\Elementor\Widgets\V2\TestElementorCampaignDonationsWidget::$capturedAttributes = $attributes;
            return '';
        }
    };
}

namespace Give\Tests\Unit\ThirdPartySupport\Elementor\Widgets\V2;

use Give\Tests\TestCase;
use Give\Tests\TestTraits\RefreshDatabase;
use Give\ThirdPartySupport\Elementor\Widgets\V2\ElementorCampaignDonationsWidget\ElementorCampaignDonationsWidget;

/**
 * @unreleased
 * @covers \Give\ThirdPartySupport\Elementor\Widgets\V2\ElementorCampaignDonationsWidget\ElementorCampaignDonationsWidget
 */
class TestElementorCampaignDonationsWidget extends TestCase
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
        $widget = $this->getMockBuilder(ElementorCampaignDonationsWidget::class)
            ->onlyMethods(['get_settings_for_display'])
            ->getMock();

        $widget->method('get_settings_for_display')->willReturn([
            'campaign_id' => '',
            'show_anonymous' => 'yes',
            'show_icon' => 'yes',
            'show_button' => 'yes',
            'donate_button_text' => 'Donate',
            'sort_by' => 'recent-donations',
            'donations_per_page' => 5,
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
	public function testRenderProcessesDonationsShortcode(): void
    {
        $widget = $this->getMockBuilder(ElementorCampaignDonationsWidget::class)
            ->onlyMethods(['get_settings_for_display'])
            ->getMock();

        $widget->method('get_settings_for_display')->willReturn([
            'campaign_id' => '77',
            'show_anonymous' => 'no',
            'show_icon' => 'yes',
            'show_button' => 'no',
            'donate_button_text' => 'Donate',
            'sort_by' => 'top-donations',
            'donations_per_page' => 10,
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
            'campaign_id' => '77',
            'show_anonymous' => false,
            'show_icon' => true,
            'show_button' => false,
            'donate_button_text' => 'Donate',
            'sort_by' => 'top-donations',
            'donations_per_page' => 10,
            'load_more_button_text' => 'More',
        ], self::$capturedAttributes);
    }
}

