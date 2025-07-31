<?php

namespace Give\ThirdPartySupport\Elementor\Widgets\V2\ElementorCampaignFormWidget;

use Elementor\Widget_Base;
use Exception;
use Give\Framework\Database\DB;

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
        [$campaignOptions, $formOptionsGroup] = $this->getCampaignAndFormOptions();

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
            'placeholder' => __('Select Form', 'give'),
            'frontend_available' => true,
            //'groups' => $formOptionsGroup,
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

        protected function getCampaignAndFormOptions(): array
    {
        $campaignsWithForms = $this->getCampaignsWithForms();

        if (empty($campaignsWithForms)) {
            return [[], []];
        }

        $campaignOptions = [];
        $formOptionsGroup = [];
        $campaignGroups = [];

        // Group forms by campaign
        foreach ($campaignsWithForms as $item) {
            // Skip items without campaign association
            if (empty($item->campaign_id) || empty($item->campaign_title)) {
                continue;
            }

            $campaignId = $item->campaign_id;
            $campaignTitle = $item->campaign_title;

            // Add to campaign options if not already added
            if (!isset($campaignOptions[$campaignId])) {
                $campaignOptions[$campaignId] = $campaignTitle;
                $campaignGroups[$campaignId] = [
                    'label' => $campaignTitle,
                    'options' => []
                ];
            }

            // Add form to the campaign group
            $campaignGroups[$campaignId]['options'][$item->id] = $item->title;
        }

        $formOptionsGroup = array_values($campaignGroups);

        return [$campaignOptions, $formOptionsGroup];
    }

    protected function render(): void
    {
        $settings = $this->get_settings_for_display();
        $campaignId = $settings['campaign_id'];
        $displayStyle = $settings['display_style'];
        $useDefaultForm = $settings['use_default_form'];
        $donateButtonText = $settings['donate_button_text'];
        $formId = $settings['form_id'];

        if (empty($campaignId)) {
            return;
        }

        echo do_shortcode(sprintf('[givewp_campaign_form campaign_id="%s" display_style="%s" use_default_form="%s" continue_button_title="%s" id="%s"]', $campaignId, $displayStyle, $useDefaultForm, $donateButtonText, $formId));
    }

    public function getCampaignsWithForms()
    {
        try {
            $query = DB::table('posts', 'forms')
                ->select(
                    ['forms.ID', 'id'],
                    ['forms.post_title', 'title'],
                    ['campaigns.campaign_title', 'campaign_title'],
                    ['campaigns.id', 'campaign_id']
                )
                ->innerJoin('give_campaign_forms', 'forms.ID', 'campaign_forms.form_id', 'campaign_forms')
                ->innerJoin('give_campaigns', 'campaign_forms.campaign_id', 'campaigns.id', 'campaigns')
                ->where('forms.post_status', 'publish');

            return $query->getAll();
        } catch (Exception $e) {
            error_log('getCampaignsWithForms error: ' . $e->getMessage());
            return [];
        }
    }
}
