<?php

namespace Give\Tests\Unit\ThirdPartySupport\Elementor\Widgets\V2;

use Give\Tests\TestCase;
use Give\Tests\TestTraits\RefreshDatabase;
use Give\ThirdPartySupport\Elementor\Widgets\V2\ElementorDonationFormWidget\ElementorDonationFormWidget;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * @unreleased
 * @covers \Give\ThirdPartySupport\Elementor\Widgets\V2\ElementorDonationFormWidget\ElementorDonationFormWidget
 */
class TestElementorDonationFormWidget extends TestCase
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
     * Test that render method outputs nothing when form_id is empty
     *
     * @unreleased
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
    }

    /**
     * Test that render method processes shortcode when form_id is provided
     * Since form doesn't exist in test DB, should show error message
     *
     * @unreleased
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

        // In test environment, non-existent form ID should produce error notice
        $this->assertStringContainsString('give_notice', $output);
        $this->assertStringContainsString('valid Donation Form ID', $output);
    }

    /**
     * Test that render method processes shortcode with onpage display style
     *
     * @unreleased
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

        // Should process shortcode and show error for non-existent form
        $this->assertStringContainsString('give_notice', $output);
        $this->assertStringContainsString('valid Donation Form ID', $output);
    }

    /**
     * Test that render method processes shortcode with newTab display style
     *
     * @unreleased
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

        // Should process shortcode and show error for non-existent form
        $this->assertStringContainsString('give_notice', $output);
        $this->assertStringContainsString('valid Donation Form ID', $output);
    }
}
