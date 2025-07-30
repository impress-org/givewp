<?php

namespace Give\ThirdPartySupport\Elementor\Widgets\V2\ElementorCampaignFormWidget;

use Elementor\Widget_Base;
use Give\Campaigns\Models\Campaign;

class ElementorCampaignFormWidget extends Widget_Base
{
    public function get_name(): string
    {
        return 'givewp_campaign_form';
    }

    public function get_title(): string
    {
        return __('GiveWP Campaign Form', 'give');
    }

    public function get_icon(): string
    {
        return 'give-icon';
    }

    public function get_categories(): array
    {
        return ['givewp-category'];
    }

    public function get_keywords(): array
    {
        return ['give', 'givewp', 'campaign', 'form'];
    }

    public function get_custom_help_url(): string
    {
        return 'https://givewp.com/documentation/';
    }

    protected function get_upsale_data(): array
    {
        return [];
    }

    public function get_script_depends(): array
    {
        return ['givewp-elementor-campaign-form-widget'];
    }

    public function get_style_depends(): array
    {
        return ['givewp-design-system-foundation', 'givewp-elementor-campaign-form-widget'];
    }

    public function has_widget_inner_wrapper(): bool
    {
        return false;
    }

    protected function is_dynamic_content(): bool
    {
        return true;
    }


    protected function register_controls(): void
    {
        $campaignOptions = $this->getCampaignOptions();

        $this->start_controls_section(
            'campaign_section',
            [
                'label' => __('Campaign', 'give'),
            ]
        );

        $this->add_control(
            'campaign_id',
            [
                'label' => __('Campaign', 'give'),
                'type' => \Elementor\Controls_Manager::SELECT,
                'options' => $campaignOptions,
                'default' => array_key_first($campaignOptions),
                'frontend_available' => true,
            ]
        );

        $this->add_control('use_default_form', [
            'label' => __('Use Default Form', 'give'),
            'type' => \Elementor\Controls_Manager::SWITCHER,
            'default' => 'yes',
            'frontend_available' => true,
        ]);

        //TODO: need to hook into js to get the form options based on the campaign id
        $this->add_control('form_id', [
            'label' => __('Form', 'give'),
            'type' => \Elementor\Controls_Manager::SELECT,
            'options' => [],
            'default' => '',
            'condition'  => [
                'use_default_form!' => 'yes',
            ],
            'frontend_available' => true,
        ]);

        $this->add_control(
            'display_style',
            [
                'label' => __('Display Style', 'give'),
                'type' => \Elementor\Controls_Manager::SELECT,
                'options' => ['onpage' => __('On Page', 'give'), 'modal' => __('Modal', 'give'), 'newTab' => __('New Tab', 'give')],
                'default' => 'onpage',
                'frontend_available' => true,
            ]
        );

        $this->add_control(
            'donate_button_text',
            [
                'label' => __('Donate Button Text', 'give'),
                'type' => \Elementor\Controls_Manager::TEXT,
                'default' => __('Continue to Donate', 'give'),
                'frontend_available' => true,
                'condition' => [
                    'display_style!' => 'onpage',
                ],
            ]
        );

        $this->end_controls_section();
    }

    protected function getCampaignOptions(): array
    {
        $campaigns = Campaign::query()->getAll();

        if (empty($campaigns)) {
            return [];
        }

        $options = [];

        foreach ($campaigns as $campaign) {
            $options[$campaign->id] = $campaign->title;
        }

        return $options;
    }

    protected function getFormOptions(int $campaignId): array
    {
        $campaign = Campaign::find($campaignId);

        if (!$campaign) {
            return [];
        }

        $forms = $campaign->forms()->getAll();

        if (empty($forms)) {
            return [];
        }

        $options = [];

        foreach ($forms as $form) {
            $options[$form->id] = $form->title;
        }

        return $options;
    }

    protected function render(): void
    {
        $campaignId = $this->get_settings('campaign_id');
        $displayStyle = $this->get_settings('display_style');
        $useDefaultForm = $this->get_settings('use_default_form');
        $donateButtonText = $this->get_settings('donate_button_text');

        if (empty($campaignId)) {
            return;
        }

        echo do_shortcode(sprintf('[givewp_campaign_form campaign_id="%s" display_style="%s" use_default_form="%s" continue_button_title="%s"]', $campaignId, $displayStyle, $useDefaultForm, $donateButtonText));
    }
}
