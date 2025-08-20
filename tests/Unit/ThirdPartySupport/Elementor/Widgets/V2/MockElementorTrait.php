<?php

namespace Give\Tests\Unit\ThirdPartySupport\Elementor\Widgets\V2;

/**
 * Mock Elementor Widget_Base class for testing
 *
 * @since 4.7.0
 */
class MockWidgetBase
{
    protected function get_settings_for_display()
    {
        return [];
    }

    protected function render()
    {
        // Mock render method
    }
}

/**
 * Mock Elementor Controls_Manager class for testing
 *
 * @since 4.7.0
 */
class MockControlsManager
{
    const SELECT = 'select';
    const TEXT = 'text';
    const SWITCHER = 'switcher';
    const SELECT2 = 'select2';
    const NUMBER = 'number';
}

/**
 * Trait to provide mock Elementor classes for testing
 *
 * @since 4.7.0
 */
trait MockElementorTrait
{
    /**
     * Set up mock Elementor classes for testing
     * Call this in setUp() or at the beginning of test methods
     *
     * @since 4.7.0
     */
    protected function setUpMockElementorClasses(): void
    {
        // Create class aliases only if the original classes don't exist
        if (!class_exists('Elementor\Widget_Base')) {
            class_alias('Give\Tests\Unit\ThirdPartySupport\Elementor\Widgets\V2\MockWidgetBase', 'Elementor\Widget_Base');
        }

        if (!class_exists('Elementor\Controls_Manager')) {
            class_alias('Give\Tests\Unit\ThirdPartySupport\Elementor\Widgets\V2\MockControlsManager', 'Elementor\Controls_Manager');
        }
    }
}
