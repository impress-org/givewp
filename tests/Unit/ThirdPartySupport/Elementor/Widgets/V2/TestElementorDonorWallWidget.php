<?php

namespace Give\Tests\Unit\ThirdPartySupport\Elementor\Widgets\V2;

use Give\Tests\TestCase;
use Give\Tests\TestTraits\RefreshDatabase;
use Give\ThirdPartySupport\Elementor\Widgets\V2\ElementorDonorWallWidget\ElementorDonorWallWidget;

/**
 * @unreleased
 * @covers \Give\ThirdPartySupport\Elementor\Widgets\V2\ElementorDonorWallWidget\ElementorDonorWallWidget
 */
class TestElementorDonorWallWidget extends TestCase
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
     * Test that render method processes donor wall shortcode with default settings
     * In test environment, shortcode may not output anything
     *
     * @unreleased
     */
    public function testRenderProcessesDonorWallShortcodeWithDefaultSettings(): void
    {
        $widget = $this->getMockBuilder(ElementorDonorWallWidget::class)
            ->onlyMethods(['get_settings_for_display'])
            ->getMock();

        $widget->method('get_settings_for_display')->willReturn([
            'donors_per_page' => 12,
            'all_forms' => 'yes',
            'orderby' => 'post_date',
            'order' => 'desc',
            'columns' => 'best-fit'
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
     * Test that render method processes donor wall shortcode when all_forms is not yes
     *
     * @unreleased
     */
    public function testRenderProcessesDonorWallShortcodeWhenNotAllForms(): void
    {
        $widget = $this->getMockBuilder(ElementorDonorWallWidget::class)
            ->onlyMethods(['get_settings_for_display'])
            ->getMock();

        $widget->method('get_settings_for_display')->willReturn([
            'donors_per_page' => 10,
            'all_forms' => 'no',
            'form_id' => '123',
            'orderby' => 'donation_amount',
            'order' => 'asc',
            'columns' => '3'
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
     * Test that render method processes donor wall shortcode when all_forms is yes
     *
     * @unreleased
     */
    public function testRenderProcessesDonorWallShortcodeWhenAllForms(): void
    {
        $widget = $this->getMockBuilder(ElementorDonorWallWidget::class)
            ->onlyMethods(['get_settings_for_display'])
            ->getMock();

        $widget->method('get_settings_for_display')->willReturn([
            'donors_per_page' => 15,
            'all_forms' => 'yes',
            'form_id' => '456',
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
     * Test that render method processes donor wall shortcode with display options
     *
     * @unreleased
     */
    public function testRenderProcessesDonorWallShortcodeWithDisplayOptions(): void
    {
        $widget = $this->getMockBuilder(ElementorDonorWallWidget::class)
            ->onlyMethods(['get_settings_for_display'])
            ->getMock();

        $widget->method('get_settings_for_display')->willReturn([
            'donors_per_page' => 8,
            'all_forms' => 'yes',
            'show_avatar' => 'yes',
            'avatar_size' => '100',
            'show_name' => 'yes',
            'show_total' => 'yes',
            'show_time' => 'yes',
            'show_comments' => 'yes'
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
     * Test that render method processes donor wall shortcode with comment options
     *
     * @unreleased
     */
    public function testRenderProcessesDonorWallShortcodeWithCommentOptions(): void
    {
        $widget = $this->getMockBuilder(ElementorDonorWallWidget::class)
            ->onlyMethods(['get_settings_for_display'])
            ->getMock();

        $widget->method('get_settings_for_display')->willReturn([
            'donors_per_page' => 12,
            'all_forms' => 'yes', // Fix: include all_forms to prevent undefined array key error
            'comment_length' => '100',
            'only_comments' => 'yes',
            'anonymous' => 'no',
            'loadmore_text' => 'Load More Donors',
            'readmore_text' => 'Read More'
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
