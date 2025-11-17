<?php

namespace Give\ThirdPartySupport\Elementor\Widgets\V2\ElementorCampaignDonationsWidget;

use Elementor\Widget_Base;
use Give\Campaigns\Shortcodes\CampaignDonationsShortcode;
use Give\ThirdPartySupport\Elementor\Traits\HasCampaignOptions;

/**
 * @since 4.7.0
 */
class ElementorCampaignDonationsWidget extends Widget_Base
{
    use HasCampaignOptions;

    /**
     * @since 4.7.0
     */
    public function get_name(): string
    {
        return 'givewp_campaign_donations';
    }

    /**
     * @since 4.7.0
     */
    public function get_title(): string
    {
        return __('GiveWP Campaign Donations', 'give');
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
        return ['give', 'givewp', 'campaign', 'donations', 'contributions'];
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
        return [];
    }

    /**
     * @since 4.7.0
     */
    public function get_style_depends(): array
    {
        return [];
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
            'campaign_donations_section',
            [
                'label' => __('Campaign Donations', 'give'),
            ]
        );

        $this->add_control('campaign_id', [
            'label' => __('Campaign', 'give'),
            'type' => \Elementor\Controls_Manager::SELECT,
            'options' => $campaignOptions,
            'default' => $this->getDefaultCampaignOption($campaignOptions),
        ]);

        $this->add_control(
            'show_anonymous',
            [
                'label' => __('Show Anonymous Donations', 'give'),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'label_on' => __('Show', 'give'),
                'label_off' => __('Hide', 'give'),
                'return_value' => 'yes',
                'default' => 'yes',
            ]
        );

        $this->add_control(
            'show_icon',
            [
                'label' => __('Show Icon', 'give'),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'label_on' => __('Show', 'give'),
                'label_off' => __('Hide', 'give'),
                'return_value' => 'yes',
                'default' => 'yes',
            ]
        );

        $this->add_control(
            'show_button',
            [
                'label' => __('Show Donate Button', 'give'),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'label_on' => __('Show', 'give'),
                'label_off' => __('Hide', 'give'),
                'return_value' => 'yes',
                'default' => 'yes',
            ]
        );

        $this->add_control(
            'donate_button_text',
            [
                'label' => __('Donate Button Text', 'give'),
                'type' => \Elementor\Controls_Manager::TEXT,
                'default' => __('Donate', 'give'),
                'condition' => [
                    'show_button' => 'yes',
                ],
            ]
        );

        $this->add_control(
            'sort_by',
            [
                'label' => __('Sort By', 'give'),
                'type' => \Elementor\Controls_Manager::SELECT,
                'options' => [
                    'recent-donations' => __('Recent Donations', 'give'),
                    'top-donations' => __('Top Donations', 'give'),
                ],
                'default' => 'recent-donations',
            ]
        );

        $this->add_control(
            'donations_per_page',
            [
                'label' => __('Donations Per Page', 'give'),
                'type' => \Elementor\Controls_Manager::NUMBER,
                'min' => 1,
                'max' => 50,
                'step' => 1,
                'default' => 5,
            ]
        );

        $this->add_control(
            'load_more_button_text',
            [
                'label' => __('Load More Button Text', 'give'),
                'type' => \Elementor\Controls_Manager::TEXT,
                'default' => __('Load more', 'give'),
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

        if (empty($campaignId)) {
            return;
        }

        $attributes = [
            'campaign_id' => $campaignId,
            'show_anonymous' => $settings['show_anonymous'] === 'yes',
            'show_icon' => $settings['show_icon'] === 'yes',
            'show_button' => $settings['show_button'] === 'yes',
            'donate_button_text' => $settings['donate_button_text'],
            'sort_by' => $settings['sort_by'],
            'donations_per_page' => $settings['donations_per_page'],
            'load_more_button_text' => $settings['load_more_button_text'],
        ];

        $shortcode = give(CampaignDonationsShortcode::class);
        echo $shortcode->renderShortcode($attributes);
    }
}
