<?php

namespace Give\ThirdPartySupport\Elementor\Widgets\V2\ElementorCampaignGridWidget;

use Elementor\Widget_Base;
use Give\Campaigns\Shortcodes\CampaignGridShortcode;
use Give\ThirdPartySupport\Elementor\Actions\RegisterWidgetEditorScripts;

/**
 * @since 4.7.0
 */
class ElementorCampaignGridWidget extends Widget_Base
{
    /**
     * @since 4.7.0
     */
    public function get_name(): string
    {
        return 'givewp_campaign_grid';
    }

    /**
     * @since 4.7.0
     */
    public function get_title(): string
    {
        return __('GiveWP Campaign Grid', 'give');
    }

    /**
     * @since 4.7.0
     */
    public function get_icon(): string
    {
        return 'give-icon';
    }

    /**
     * @since 4.7.0
     */
    public function get_categories(): array
    {
        return ['givewp-category'];
    }

    /**
     * @since 4.7.0
     */
    public function get_keywords(): array
    {
        return ['give', 'givewp', 'campaign', 'grid', 'list'];
    }

    /**
     * @since 4.7.0
     */
    public function get_custom_help_url(): string
    {
        return 'https://givewp.com/documentation/';
    }

    /**
     * @since 4.7.0
     */
    protected function get_upsale_data(): array
    {
        return [];
    }

    /**
     * @since 4.7.0
     */
    public function get_script_depends(): array
    {
        return [RegisterWidgetEditorScripts::CAMPAIGN_GRID_WIDGET_SCRIPT_NAME];
    }

    /**
     * @since 4.7.0
     */
    public function get_style_depends(): array
    {
        return [
            'givewp-design-system-foundation',
            RegisterWidgetEditorScripts::CAMPAIGN_GRID_WIDGET_SCRIPT_NAME,
        ];
    }

    /**
     * @since 4.7.0
     */
    public function has_widget_inner_wrapper(): bool
    {
        return false;
    }

    /**
     * @since 4.7.0
     */
    protected function is_dynamic_content(): bool
    {
        return true;
    }

    /**
     * @since 4.7.0
     */
    protected function register_controls(): void
    {
        $this->start_controls_section(
            'campaign_grid_section',
            [
                'label' => __('Campaign Grid', 'give'),
            ]
        );

        $this->add_control(
            'layout',
            [
                'label' => __('Layout', 'give'),
                'type' => \Elementor\Controls_Manager::SELECT,
                'options' => [
                    'full' => __('Full', 'give'),
                    'double' => __('Double', 'give'),
                    'triple' => __('Triple', 'give'),
                ],
                'default' => 'full',
            ]
        );

        $this->add_control(
            'show_image',
            [
                'label' => __('Show Image', 'give'),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'label_on' => __('Show', 'give'),
                'label_off' => __('Hide', 'give'),
                'return_value' => 'yes',
                'default' => 'yes',
            ]
        );

        $this->add_control(
            'show_description',
            [
                'label' => __('Show Description', 'give'),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'label_on' => __('Show', 'give'),
                'label_off' => __('Hide', 'give'),
                'return_value' => 'yes',
                'default' => 'yes',
            ]
        );

        $this->add_control(
            'show_goal',
            [
                'label' => __('Show Goal', 'give'),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'label_on' => __('Show', 'give'),
                'label_off' => __('Hide', 'give'),
                'return_value' => 'yes',
                'default' => 'yes',
            ]
        );

        $this->add_control(
            'sort_by',
            [
                'label' => __('Sort By', 'give'),
                'type' => \Elementor\Controls_Manager::SELECT,
                'options' => [
                    'date' => __('Date Created', 'give'),
                ],
                'default' => 'date',
            ]
        );

        $this->add_control(
            'order_by',
            [
                'label' => __('Order', 'give'),
                'type' => \Elementor\Controls_Manager::SELECT,
                'options' => [
                    'asc' => __('Ascending', 'give'),
                    'desc' => __('Descending', 'give'),
                ],
                'default' => 'desc',
            ]
        );

        $this->add_control(
            'per_page',
            [
                'label' => __('Per Page', 'give'),
                'type' => \Elementor\Controls_Manager::NUMBER,
                'min' => 1,
                'max' => 100,
                'step' => 1,
                'default' => 6,
            ]
        );

        $this->add_control(
            'show_pagination',
            [
                'label' => __('Show Pagination', 'give'),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'label_on' => __('Show', 'give'),
                'label_off' => __('Hide', 'give'),
                'return_value' => 'yes',
                'default' => 'yes',
            ]
        );

        $this->end_controls_section();
    }

    /**
     * @since 4.7.0
     */
    protected function render(): void
    {
        $settings = $this->get_settings_for_display();

        $attributes = [
            'layout' => $settings['layout'],
            'show_image' => $settings['show_image'] === 'yes',
            'show_description' => $settings['show_description'] === 'yes',
            'show_goal' => $settings['show_goal'] === 'yes',
            'sort_by' => $settings['sort_by'],
            'order_by' => $settings['order_by'],
            'per_page' => (int)$settings['per_page'],
            'show_pagination' => $settings['show_pagination'] === 'yes',
            'filter_by' => null,
        ];

        $shortcode = give(CampaignGridShortcode::class);
        echo $shortcode->renderShortcode($attributes);
    }
}

