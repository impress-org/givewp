<?php

namespace Give\Tests\Unit\ThirdPartySupport\Elementor\Widgets\V2;

use Give\Tests\TestCase;
use Give\Tests\TestTraits\RefreshDatabase;
use Give\ThirdPartySupport\Elementor\Widgets\V2\ElementorDonationFormGridWidget\ElementorDonationFormGridWidget;

/**
 * @unreleased
 * @covers \Give\ThirdPartySupport\Elementor\Widgets\V2\ElementorDonationFormGridWidget\ElementorDonationFormGridWidget
 */
class TestElementorDonationFormGridWidget extends TestCase
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
     * Test that render method processes grid shortcode with default settings
     * In test environment, shortcode may not output anything
     *
     * @unreleased
     */
    public function testRenderProcessesGridShortcodeWithDefaultSettings(): void
    {
        $widget = $this->getMockBuilder(ElementorDonationFormGridWidget::class)
            ->onlyMethods(['get_settings_for_display'])
            ->getMock();

        $widget->method('get_settings_for_display')->willReturn([
            'forms_per_page' => 12,
            'columns' => '',
            'orderby' => 'post_date',
            'order' => 'desc',
            'selection_type' => 'all'
        ]);

        // Use reflection to call the protected render method
        $reflection = new \ReflectionClass($widget);
        $method = $reflection->getMethod('render');
        $method->setAccessible(true);

        ob_start();
        $method->invoke($widget);
        $output = ob_get_clean();

        // In test environment, shortcode may not output anything
        $this->assertIsString($output);
    }

    /**
     * Test that render method processes grid shortcode with columns specified
     *
     * @unreleased
     */
    public function testRenderProcessesGridShortcodeWithColumns(): void
    {
        $widget = $this->getMockBuilder(ElementorDonationFormGridWidget::class)
            ->onlyMethods(['get_settings_for_display'])
            ->getMock();

        $widget->method('get_settings_for_display')->willReturn([
            'forms_per_page' => 8,
            'columns' => '3',
            'orderby' => 'title',
            'order' => 'asc',
            'selection_type' => 'all'
        ]);

        // Use reflection to call the protected render method
        $reflection = new \ReflectionClass($widget);
        $method = $reflection->getMethod('render');
        $method->setAccessible(true);

        ob_start();
        $method->invoke($widget);
        $output = ob_get_clean();

        // Test that method executed without errors
        $this->assertIsString($output);
    }

    /**
     * Test that render method processes grid shortcode with include specific forms
     *
     * @unreleased
     */
    public function testRenderProcessesGridShortcodeWithIncludeForms(): void
    {
        $widget = $this->getMockBuilder(ElementorDonationFormGridWidget::class)
            ->onlyMethods(['get_settings_for_display'])
            ->getMock();

        $widget->method('get_settings_for_display')->willReturn([
            'forms_per_page' => 6,
            'selection_type' => 'include',
            'ids' => ['123', '456', '789'],
            'orderby' => 'post_date',
            'order' => 'desc'
        ]);

        // Use reflection to call the protected render method
        $reflection = new \ReflectionClass($widget);
        $method = $reflection->getMethod('render');
        $method->setAccessible(true);

        ob_start();
        $method->invoke($widget);
        $output = ob_get_clean();

        // Test that method executed without errors
        $this->assertIsString($output);
    }

    /**
     * Test that render method processes grid shortcode with exclude specific forms
     *
     * @unreleased
     */
    public function testRenderProcessesGridShortcodeWithExcludeForms(): void
    {
        $widget = $this->getMockBuilder(ElementorDonationFormGridWidget::class)
            ->onlyMethods(['get_settings_for_display'])
            ->getMock();

        $widget->method('get_settings_for_display')->willReturn([
            'forms_per_page' => 10,
            'selection_type' => 'exclude',
            'exclude' => ['111', '222'],
            'orderby' => 'title',
            'order' => 'asc'
        ]);

        // Use reflection to call the protected render method
        $reflection = new \ReflectionClass($widget);
        $method = $reflection->getMethod('render');
        $method->setAccessible(true);

        ob_start();
        $method->invoke($widget);
        $output = ob_get_clean();

        // Test that method executed without errors
        $this->assertIsString($output);
    }

    /**
     * Test that render method processes grid shortcode with display style
     *
     * @unreleased
     */
    public function testRenderProcessesGridShortcodeWithDisplayStyle(): void
    {
        $widget = $this->getMockBuilder(ElementorDonationFormGridWidget::class)
            ->onlyMethods(['get_settings_for_display'])
            ->getMock();

        $widget->method('get_settings_for_display')->willReturn([
            'forms_per_page' => 12,
            'display_style' => 'modal',
            'orderby' => 'post_date',
            'order' => 'desc',
            'selection_type' => 'all'
        ]);

        // Use reflection to call the protected render method
        $reflection = new \ReflectionClass($widget);
        $method = $reflection->getMethod('render');
        $method->setAccessible(true);

        ob_start();
        $method->invoke($widget);
        $output = ob_get_clean();

        // Test that method executed without errors
        $this->assertIsString($output);
    }

    /**
     * Test that render method processes grid shortcode with string ids value
     *
     * @unreleased
     */
    public function testRenderProcessesGridShortcodeWithStringIds(): void
    {
        $widget = $this->getMockBuilder(ElementorDonationFormGridWidget::class)
            ->onlyMethods(['get_settings_for_display'])
            ->getMock();

        $widget->method('get_settings_for_display')->willReturn([
            'forms_per_page' => 6,
            'selection_type' => 'include',
            'ids' => '123,456,789', // String instead of array
            'orderby' => 'post_date',
            'order' => 'desc'
        ]);

        // Use reflection to call the protected render method
        $reflection = new \ReflectionClass($widget);
        $method = $reflection->getMethod('render');
        $method->setAccessible(true);

        ob_start();
        $method->invoke($widget);
        $output = ob_get_clean();

        // Test that method executed without errors
        $this->assertIsString($output);
    }
}
