<?php

namespace Give\ThirdPartySupport\Elementor\Widgets\V2\ElementorCampaignWidget;

use Elementor\Widget_Base;
use Give\Campaigns\Shortcodes\CampaignShortcode;
use Give\ThirdPartySupport\Elementor\Traits\HasCampaignOptions;
use Give\ThirdPartySupport\Elementor\Actions\RegisterWidgetEditorScripts;

/**
 * @unreleased
 */
class ElementorCampaignWidget extends Widget_Base
{
    use HasCampaignOptions;

    /**
     * @unreleased
     */
    public function get_name(): string
    {
        return 'givewp_campaign';
    }

    /**
     * @unreleased
     */
    public function get_title(): string
    {
        return __('GiveWP Campaign', 'give');
    }

    /**
     * @unreleased
     */
    public function get_icon(): string
    {
        return 'give-icon';
    }

    /**
     * @unreleased
     */
    public function get_categories(): array
    {
        return ['givewp-category'];
    }

    /**
     * @unreleased
     */
    public function get_keywords(): array
    {
        return ['give', 'givewp', 'campaign', 'single'];
    }

    /**
     * @unreleased
     */
    public function get_custom_help_url(): string
    {
        return 'https://givewp.com/documentation/';
    }

    /**
     * @unreleased
     */
    protected function get_upsale_data(): array
    {
        return [];
    }

    /**
     * @unreleased
     */
    public function get_script_depends(): array
    {
        return [RegisterWidgetEditorScripts::CAMPAIGN_WIDGET_SCRIPT_NAME];
    }

    /**
     * @unreleased
     */
    public function get_style_depends(): array
    {
        return [RegisterWidgetEditorScripts::CAMPAIGN_WIDGET_SCRIPT_NAME];
    }

    /**
     * @unreleased
     */
    public function has_widget_inner_wrapper(): bool
    {
        return false;
    }

    /**
     * @unreleased
     */
    protected function is_dynamic_content(): bool
    {
        return true;
    }

    /**
     * @unreleased
     */
    protected function register_controls(): void
    {
        $campaignOptions = $this->getCampaignOptions();

        $this->start_controls_section(
            'campaign_section',
            [
                'label' => __('Campaign', 'give'),
            ]
        );

        $this->add_control('campaign_id', [
            'label' => __('Campaign', 'give'),
            'type' => \Elementor\Controls_Manager::SELECT,
            'options' => $campaignOptions,
            'default' => $this->getDefaultCampaignOption($campaignOptions),
        ]);

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

        $this->end_controls_section();
    }

    /**
     * @unreleased
     */
    protected function render(): void
    {
        $settings = $this->get_settings_for_display();
        $campaignId = $settings['campaign_id'];

        if (empty($campaignId)) {
            return;
        }

        $attributes = [
            'campaign_id' => $campaignId,
            'show_image' => $settings['show_image'] === 'yes',
            'show_description' => $settings['show_description'] === 'yes',
            'show_goal' => $settings['show_goal'] === 'yes',
        ];

        $shortcode = give(CampaignShortcode::class);
        echo $shortcode->renderShortcode($attributes);
    }
}

