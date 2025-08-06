<?php

namespace Give\ThirdPartySupport\Elementor\Widgets\V2\ElementorDonationFormWidget;

use Elementor\Widget_Base;
use Give\ThirdPartySupport\Elementor\Traits\HasFormOptions;

/**
 * @unreleased
 */
class ElementorDonationFormWidget extends Widget_Base
{
    use HasFormOptions;

    /**
     * @unreleased
     */
    public function get_name(): string
    {
        return 'givewp_donation_form';
    }

    /**
     * @unreleased
     */
    public function get_title(): string
    {
        return __('GiveWP Donation Form', 'give');
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
        return ['give', 'givewp', 'donation', 'form'];
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
        return ['givewp-elementor-donation-form-widget'];
    }

    /**
     * @unreleased
     */
    public function get_style_depends(): array
    {
        return ['givewp-design-system-foundation', 'givewp-elementor-donation-form-widget'];
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
        $formOptionsGroup = $this->getFormOptionsWithCampaigns();

        $this->start_controls_section(
            'donation_form_section',
            [
                'label' => __('Donation Form', 'give'),
            ]
        );

        $this->add_control('form_id', [
            'label' => __('Form', 'give'),
            'type' => \Elementor\Controls_Manager::SELECT,
            'options' => [],
            'default' => !empty($formOptionsGroup) ? array_key_first($formOptionsGroup[0]['options']) : '',
            'groups' => $formOptionsGroup,
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

    /**
     * @unreleased
     */
    protected function getFormOptionsWithCampaigns(): array
    {
        $campaignsWithForms = $this->getCampaignsWithForms();

        if (empty($campaignsWithForms)) {
            return [];
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

        return $formOptionsGroup;
    }

    /**
     * @unreleased
     */
    protected function render(): void
    {
        $settings = $this->get_settings_for_display();
        $displayStyle = $settings['display_style'];
        $donateButtonText = $settings['donate_button_text'];
        $formId = $settings['form_id'];

        if (empty($formId)) {
            return;
        }

        echo do_shortcode(sprintf('[give_form display_style="%s" continue_button_title="%s" id="%s"]', esc_attr($displayStyle), esc_attr($donateButtonText), esc_attr($formId)));
    }
}
