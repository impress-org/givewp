<?php

namespace Give\ThirdPartySupport\Elementor\Widgets\V2\ElementorDonationFormWidget;

use Elementor\Widget_Base;
use Give\ThirdPartySupport\Elementor\Actions\RegisterWidgetEditorScripts;
use Give\ThirdPartySupport\Elementor\Traits\HasFormOptions;
use Give\Campaigns\ValueObjects\CampaignPageMetaKeys;

/**
 * @since 4.7.0
 */
class ElementorDonationFormWidget extends Widget_Base
{
    use HasFormOptions;

    /**
     * @since 4.7.0
     */
    public function get_name(): string
    {
        return 'givewp_donation_form';
    }

    /**
     * @since 4.7.0
     */
    public function get_title(): string
    {
        return __('GiveWP Donation Form', 'give');
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
        return ['give', 'givewp', 'donation', 'form'];
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
        return [RegisterWidgetEditorScripts::DONATION_FORM_WIDGET_SCRIPT_NAME];
    }

    /**
     * @since 4.7.0
     */
    public function get_style_depends(): array
    {
        return [RegisterWidgetEditorScripts::DONATION_FORM_WIDGET_SCRIPT_NAME];
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
            'default' => $this->getDefaultFormOption($formOptionsGroup),
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
     * @since 4.7.0
     */
	public function getDefaultFormOption(array $formOptionsGroup): string
	{
		$default = !empty($formOptionsGroup) ? array_key_first($formOptionsGroup[0]['options']) : '';

		$campaignId = get_post_meta(get_the_ID(), CampaignPageMetaKeys::CAMPAIGN_ID, true);

		if (!$campaignId) {
			return $default;
		}

		foreach ($formOptionsGroup as $group) {
			if (!empty($group['campaign_id']) && (string)$group['campaign_id'] === (string)$campaignId) {
				return !empty($group['options']) ? array_key_first($group['options']) : $default;
			}
		}

		return $default;
	}

    /**
     * @since 4.7.0
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
