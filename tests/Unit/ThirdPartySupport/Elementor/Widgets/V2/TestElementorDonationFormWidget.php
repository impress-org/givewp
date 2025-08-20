<?php

namespace Give\ThirdPartySupport\Elementor\Widgets\V2\ElementorDonationFormWidget;

/**
 * Intercept do_shortcode calls from the widget's namespace to capture the built shortcode.
 */
function do_shortcode($shortcode)
{
    \Give\Tests\Unit\ThirdPartySupport\Elementor\Widgets\V2\TestElementorDonationFormWidget::$capturedShortcode = $shortcode;
    return '';
}

namespace Give\Tests\Unit\ThirdPartySupport\Elementor\Widgets\V2;

use Give\Tests\TestCase;
use Give\Tests\TestTraits\RefreshDatabase;
use Give\ThirdPartySupport\Elementor\Widgets\V2\ElementorDonationFormWidget\ElementorDonationFormWidget;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * @since 4.7.0
 * @covers \Give\ThirdPartySupport\Elementor\Widgets\V2\ElementorDonationFormWidget\ElementorDonationFormWidget
 */
class TestElementorDonationFormWidget extends TestCase
{
    use RefreshDatabase;
    use MockElementorTrait;

    /** @var string|null */
    public static $capturedShortcode;

    /**
     * @since 4.7.0
     */
    public function setUp(): void
    {
        parent::setUp();
        $this->setUpMockElementorClasses();
        self::$capturedShortcode = null;
    }

    /**
     * Test that render method outputs nothing when form_id is empty
     *
     * @since 4.7.0
     */
    public function testRenderOutputsNothingWhenFormIdIsEmpty(): void
    {
        $widget = $this->getMockBuilder(ElementorDonationFormWidget::class)
            ->onlyMethods(['get_settings_for_display'])
            ->getMock();

        $widget->method('get_settings_for_display')->willReturn([
            'form_id' => '',
            'display_style' => 'onpage',
            'donate_button_text' => 'Donate Now'
        ]);

        // Use reflection to call the protected render method
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
     * Test that render method processes shortcode when form_id is provided
     * Since form doesn't exist in test DB, should show error message
     *
     * @since 4.7.0
     */
    public function testRenderProcessesShortcodeWhenFormIdProvided(): void
    {
        $widget = $this->getMockBuilder(ElementorDonationFormWidget::class)
            ->onlyMethods(['get_settings_for_display'])
            ->getMock();

        $widget->method('get_settings_for_display')->willReturn([
            'form_id' => '123',
            'display_style' => 'modal',
            'donate_button_text' => 'Custom Donate Text'
        ]);

        // Use reflection to call the protected render method
        $reflection = new \ReflectionClass($widget);
        $method = $reflection->getMethod('render');
        $method->setAccessible(true);

        ob_start();
        $method->invoke($widget);
        $output = ob_get_clean();

        $this->assertIsString($output);
        $this->assertSame(
            '[give_form display_style="modal" continue_button_title="Custom Donate Text" id="123"]',
            self::$capturedShortcode
        );
    }

    /**
     * Test that render method processes shortcode with onpage display style
     *
     * @since 4.7.0
     */
    public function testRenderProcessesShortcodeWithOnpageDisplayStyle(): void
    {
        $widget = $this->getMockBuilder(ElementorDonationFormWidget::class)
            ->onlyMethods(['get_settings_for_display'])
            ->getMock();

        $widget->method('get_settings_for_display')->willReturn([
            'form_id' => '456',
            'display_style' => 'onpage',
            'donate_button_text' => 'Continue to Donate'
        ]);

        // Use reflection to call the protected render method
        $reflection = new \ReflectionClass($widget);
        $method = $reflection->getMethod('render');
        $method->setAccessible(true);

        ob_start();
        $method->invoke($widget);
        $output = ob_get_clean();

        $this->assertIsString($output);
        $this->assertSame(
            '[give_form display_style="onpage" continue_button_title="Continue to Donate" id="456"]',
            self::$capturedShortcode
        );
    }

    /**
     * Test that render method processes shortcode with newTab display style
     *
     * @since 4.7.0
     */
    public function testRenderProcessesShortcodeWithNewTabDisplayStyle(): void
    {
        $widget = $this->getMockBuilder(ElementorDonationFormWidget::class)
            ->onlyMethods(['get_settings_for_display'])
            ->getMock();

        $widget->method('get_settings_for_display')->willReturn([
            'form_id' => '789',
            'display_style' => 'newTab',
            'donate_button_text' => 'Donate in New Tab'
        ]);

        // Use reflection to call the protected render method
        $reflection = new \ReflectionClass($widget);
        $method = $reflection->getMethod('render');
        $method->setAccessible(true);

        ob_start();
        $method->invoke($widget);
        $output = ob_get_clean();

        $this->assertIsString($output);
        $this->assertSame(
            '[give_form display_style="newTab" continue_button_title="Donate in New Tab" id="789"]',
            self::$capturedShortcode
        );
    }
}
