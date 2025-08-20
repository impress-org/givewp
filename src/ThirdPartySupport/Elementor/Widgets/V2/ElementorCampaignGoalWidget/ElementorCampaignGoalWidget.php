<?php

namespace Give\ThirdPartySupport\Elementor\Widgets\V2\ElementorCampaignGoalWidget;

use Elementor\Widget_Base;
use Give\Campaigns\Shortcodes\CampaignGoalShortcode;
use Give\ThirdPartySupport\Elementor\Traits\HasCampaignOptions;
use Give\ThirdPartySupport\Elementor\Actions\RegisterWidgetEditorScripts;

/**
 * @since 4.7.0
 */
class ElementorCampaignGoalWidget extends Widget_Base
{
    use HasCampaignOptions;

    /**
     * @since 4.7.0
     */
    public function get_name(): string
    {
        return 'givewp_campaign_goal';
    }

    /**
     * @since 4.7.0
     */
    public function get_title(): string
    {
        return __('GiveWP Campaign Goal', 'give');
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
        return ['give', 'givewp', 'campaign', 'goal', 'progress', 'target'];
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
        return [RegisterWidgetEditorScripts::CAMPAIGN_GOAL_WIDGET_SCRIPT_NAME];
    }

    /**
     * @since 4.7.0
     */
    public function get_style_depends(): array
    {
        return [RegisterWidgetEditorScripts::CAMPAIGN_GOAL_WIDGET_SCRIPT_NAME];
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
        $campaignOptions = $this->getCampaignOptions();

        $this->start_controls_section(
            'campaign_goal_section',
            [
                'label' => __('Campaign Goal', 'give'),
            ]
        );

        $this->add_control('campaign_id', [
            'label' => __('Campaign', 'give'),
            'type' => \Elementor\Controls_Manager::SELECT,
            'options' => $campaignOptions,
            'default' => $this->getDefaultCampaignOption($campaignOptions),
        ]);

        $this->end_controls_section();
    }

    /**
     * @since 4.7.0
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
        ];

        $shortcode = give(CampaignGoalShortcode::class);
        echo $shortcode->renderShortcode($attributes);
    }
}
