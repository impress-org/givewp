<?php

namespace Give\ThirdPartySupport\Elementor\Widgets\V2\ElementorCampaignCommentsWidget;

use Elementor\Widget_Base;
use Give\Campaigns\Shortcodes\CampaignCommentsShortcode;
use Give\ThirdPartySupport\Elementor\Traits\HasCampaignOptions;
use Give\ThirdPartySupport\Elementor\Actions\RegisterWidgetEditorScripts;

/**
 * @since 4.7.0
 */
class ElementorCampaignCommentsWidget extends Widget_Base
{
    use HasCampaignOptions;

    /**
     * @since 4.7.0
     */
    public function get_name(): string
    {
        return 'givewp_campaign_comments';
    }

    /**
     * @since 4.7.0
     */
    public function get_title(): string
    {
        return __('GiveWP Campaign Comments', 'give');
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
        return ['give', 'givewp', 'campaign', 'comments', 'messages'];
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
        return [RegisterWidgetEditorScripts::CAMPAIGN_COMMENTS_WIDGET_SCRIPT_NAME];
    }

    /**
     * @since 4.7.0
     */
    public function get_style_depends(): array
    {
        return [RegisterWidgetEditorScripts::CAMPAIGN_COMMENTS_WIDGET_SCRIPT_NAME];
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
            'campaign_comments_section',
            [
                'label' => __('Campaign Comments', 'give'),
            ]
        );

        $this->add_control('campaign_id', [
            'label' => __('Campaign', 'give'),
            'type' => \Elementor\Controls_Manager::SELECT,
            'options' => $campaignOptions,
            'default' => $this->getDefaultCampaignOption($campaignOptions),
        ]);

        $this->add_control(
            'title',
            [
                'label' => __('Title', 'give'),
                'type' => \Elementor\Controls_Manager::TEXT,
                'default' => '',
            ]
        );

        $this->add_control(
            'show_anonymous',
            [
                'label' => __('Show Anonymous', 'give'),
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
            'show_date',
            [
                'label' => __('Show Date', 'give'),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'label_on' => __('Show', 'give'),
                'label_off' => __('Hide', 'give'),
                'return_value' => 'yes',
                'default' => 'yes',
            ]
        );

        $this->add_control(
            'show_name',
            [
                'label' => __('Show Name', 'give'),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'label_on' => __('Show', 'give'),
                'label_off' => __('Hide', 'give'),
                'return_value' => 'yes',
                'default' => 'yes',
            ]
        );

        $this->add_control(
            'comment_length',
            [
                'label' => __('Comment Length', 'give'),
                'type' => \Elementor\Controls_Manager::NUMBER,
                'min' => 0,
                'step' => 1,
                'default' => 200,
            ]
        );

        $this->add_control(
            'read_more_text',
            [
                'label' => __('Read More Text', 'give'),
                'type' => \Elementor\Controls_Manager::TEXT,
                'default' => '',
            ]
        );

        $this->add_control(
            'comments_per_page',
            [
                'label' => __('Comments Per Page', 'give'),
                'type' => \Elementor\Controls_Manager::NUMBER,
                'min' => 1,
                'max' => 50,
                'step' => 1,
                'default' => 3,
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
            'campaign_id' => (int) $campaignId,
            'title' => (string) ($settings['title'] ?? ''),
            'show_anonymous' => $settings['show_anonymous'] === 'yes',
            'show_avatar' => $settings['show_avatar'] === 'yes',
            'show_date' => $settings['show_date'] === 'yes',
            'show_name' => $settings['show_name'] === 'yes',
            'comment_length' => (int) ($settings['comment_length'] ?? 200),
            'read_more_text' => (string) ($settings['read_more_text'] ?? ''),
            'comments_per_page' => (int) ($settings['comments_per_page'] ?? 3),
        ];

        $shortcode = give(CampaignCommentsShortcode::class);
        echo $shortcode->renderShortcode($attributes);
    }
}

