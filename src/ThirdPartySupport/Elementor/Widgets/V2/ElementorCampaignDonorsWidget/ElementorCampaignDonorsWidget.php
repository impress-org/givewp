<?php

namespace Give\ThirdPartySupport\Elementor\Widgets\V2\ElementorCampaignDonorsWidget;

use Elementor\Widget_Base;
use Give\Campaigns\Shortcodes\CampaignDonorsShortcode;
use Give\ThirdPartySupport\Elementor\Traits\HasCampaignOptions;

/**
 * @since 4.7.0
 */
class ElementorCampaignDonorsWidget extends Widget_Base
{
    use HasCampaignOptions;

    /**
     * @since 4.7.0
     */
    public function get_name(): string
    {
        return 'givewp_campaign_donors';
    }

    /**
     * @since 4.7.0
     */
    public function get_title(): string
    {
        return __('GiveWP Campaign Donors', 'give');
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
        return ['give', 'givewp', 'campaign', 'donors', 'supporters'];
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
            'campaign_donors_section',
            [
                'label' => __('Campaign Donors', 'give'),
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
                'label' => __('Show Anonymous Donors', 'give'),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'label_on' => __('Show', 'give'),
                'label_off' => __('Hide', 'give'),
                'return_value' => 'yes',
                'default' => 'yes',
            ]
        );

        $this->add_control(
            'show_company_name',
            [
                'label' => __('Show Company Name', 'give'),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'label_on' => __('Show', 'give'),
                'label_off' => __('Hide', 'give'),
                'return_value' => 'yes',
                'default' => 'yes',
            ]
        );

        $this->add_control(
            'show_avatar',
            [
                'label' => __('Show Avatar', 'give'),
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
                'default' => __('Join the list', 'give'),
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
                    'top-donors' => __('Top Donors', 'give'),
                    'recent-donors' => __('Recent Donors', 'give'),
                ],
                'default' => 'top-donors',
            ]
        );

        $this->add_control(
            'donors_per_page',
            [
                'label' => __('Donors Per Page', 'give'),
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
            'show_company_name' => $settings['show_company_name'] === 'yes',
            'show_avatar' => $settings['show_avatar'] === 'yes',
            'show_button' => $settings['show_button'] === 'yes',
            'donate_button_text' => $settings['donate_button_text'],
            'sort_by' => $settings['sort_by'],
            'donors_per_page' => $settings['donors_per_page'],
            'load_more_button_text' => $settings['load_more_button_text'],
        ];

        $shortcode = give(CampaignDonorsShortcode::class);
        echo $shortcode->renderShortcode($attributes);
    }
}
