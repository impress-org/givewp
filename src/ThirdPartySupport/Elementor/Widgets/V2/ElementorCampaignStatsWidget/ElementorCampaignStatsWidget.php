<?php

namespace Give\ThirdPartySupport\Elementor\Widgets\V2\ElementorCampaignStatsWidget;

use Elementor\Widget_Base;
use Give\Campaigns\Shortcodes\CampaignStatsShortcode;
use Give\ThirdPartySupport\Elementor\Traits\HasCampaignOptions;

/**
 * @since 4.7.0
 */
class ElementorCampaignStatsWidget extends Widget_Base
{
    use HasCampaignOptions;

    /**
     * @since 4.7.0
     */
    public function get_name(): string
    {
        return 'givewp_campaign_stats';
    }

    /**
     * @since 4.7.0
     */
    public function get_title(): string
    {
        return __('GiveWP Campaign Stats', 'give');
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
        return ['give', 'givewp', 'campaign', 'stats', 'statistics'];
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
        return ['givewp-elementor-campaign-stats-widget'];
    }

    /**
     * @since 4.7.0
     */
    public function get_style_depends(): array
    {
        return ['givewp-design-system-foundation', 'givewp-elementor-campaign-stats-widget'];
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
            'campaign_stats_section',
            [
                'label' => __('Campaign Statistics', 'give'),
            ]
        );

        $this->add_control('campaign_id', [
            'label' => __('Campaign', 'give'),
            'type' => \Elementor\Controls_Manager::SELECT,
            'options' => $campaignOptions,
            'default' => !empty($campaignOptions) ? array_key_first($campaignOptions) : '',
        ]);

        $this->add_control(
            'statistic',
            [
                'label' => __('Statistic Type', 'give'),
                'type' => \Elementor\Controls_Manager::SELECT,
                'options' => [
                    'top-donation' => __('Top Donation', 'give'),
                    'average-donation' => __('Average Donation', 'give'),
                ],
                'default' => 'top-donation',
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
        $campaignId = $settings['campaign_id'];
        $statistic = $settings['statistic'];

        if (empty($campaignId)) {
            return;
        }

        $shortcode = give(CampaignStatsShortcode::class);
        echo $shortcode->renderShortcode([
            'campaign_id' => $campaignId,
            'statistic' => $statistic,
        ]);
    }
}
