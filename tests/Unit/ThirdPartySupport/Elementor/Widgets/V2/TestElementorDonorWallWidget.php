<?php

namespace Give\ThirdPartySupport\Elementor\Widgets\V2\ElementorDonorWallWidget;

/**
 * Intercept do_shortcode calls from the widget's namespace to capture the built shortcode.
 * This allows asserting the exact shortcode string structure in unit tests.
 */
function do_shortcode($shortcode)
{
    \Give\Tests\Unit\ThirdPartySupport\Elementor\Widgets\V2\TestElementorDonorWallWidget::$capturedShortcode = $shortcode;
    return '';
}

namespace Give\Tests\Unit\ThirdPartySupport\Elementor\Widgets\V2;

use Give\Tests\TestCase;
use Give\Tests\TestTraits\RefreshDatabase;
use Give\ThirdPartySupport\Elementor\Widgets\V2\ElementorDonorWallWidget\ElementorDonorWallWidget;

/**
 * @since 4.7.0
 * @covers \Give\ThirdPartySupport\Elementor\Widgets\V2\ElementorDonorWallWidget\ElementorDonorWallWidget
 */
class TestElementorDonorWallWidget extends TestCase
{
    use RefreshDatabase;
    use MockElementorTrait;

    /**
     * Captured shortcode string from the widget render method.
     * @var string|null
     */
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
     * Test that render method processes donor wall shortcode with default settings
     * In test environment, shortcode may not output anything
     *
     * @since 4.7.0
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

        $this->assertIsString($output);
        $this->assertSame(
            '[give_donor_wall donors_per_page="12" orderby="post_date" order="desc" columns="best-fit"]',
            self::$capturedShortcode
        );
    }

    /**
     * Test that render method processes donor wall shortcode when all_forms is not yes
     *
     * @since 4.7.0
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

        $this->assertIsString($output);
        $this->assertSame(
            '[give_donor_wall donors_per_page="10" form_id="123" orderby="donation_amount" order="asc" columns="3"]',
            self::$capturedShortcode
        );
    }

    /**
     * Test that render method processes donor wall shortcode when all_forms is yes
     *
     * @since 4.7.0
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

        $this->assertIsString($output);
        $this->assertSame(
            '[give_donor_wall donors_per_page="15" orderby="post_date" order="desc"]',
            self::$capturedShortcode
        );
    }

    /**
     * Test that render method processes donor wall shortcode with display options
     *
     * @since 4.7.0
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

        $this->assertIsString($output);
        $this->assertSame(
            '[give_donor_wall donors_per_page="8" show_avatar="yes" avatar_size="100" show_name="yes" show_total="yes" show_time="yes" show_comments="yes"]',
            self::$capturedShortcode
        );
    }

    /**
     * Test that render method processes donor wall shortcode with comment options
     *
     * @since 4.7.0
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

        $this->assertIsString($output);
        $this->assertSame(
            '[give_donor_wall donors_per_page="12" comment_length="100" only_comments="yes" anonymous="no" loadmore_text="Load More Donors" readmore_text="Read More"]',
            self::$capturedShortcode
        );
    }
}
