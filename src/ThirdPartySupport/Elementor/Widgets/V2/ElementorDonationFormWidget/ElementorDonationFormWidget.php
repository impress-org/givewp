<?php

namespace Give\ThirdPartySupport\Elementor\Widgets\V2\ElementorDonationFormWidget;

use Elementor\Widget_Base;
use Give\ThirdPartySupport\Elementor\Actions\RegisterWidgetEditorScripts;
use Give\ThirdPartySupport\Elementor\Traits\HasFormOptions;
use Give\Campaigns\ValueObjects\CampaignPageMetaKeys;

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
        return [RegisterWidgetEditorScripts::DONATION_FORM_WIDGET_SCRIPT_NAME];
    }

    /**
     * @unreleased
     */
    public function get_style_depends(): array
    {
        return [RegisterWidgetEditorScripts::DONATION_FORM_WIDGET_SCRIPT_NAME];
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
					'options' => [],
					'campaign_id' => $campaignId,
					'defaultFormId' => $item->default_form_id ?? null,
				];
			}

			// Add form to the campaign group
			$campaignGroups[$campaignId]['options'][$item->id] = $item->title;
        }

		// Ensure default form shows first in each campaign group
		foreach ($campaignGroups as $id => $group) {
			$defaultFormId = isset($group['defaultFormId']) ? (int)$group['defaultFormId'] : null;
			$defaultKey = $defaultFormId ?: null;
			if ($defaultKey !== null && isset($group['options'][$defaultKey])) {
				$orderedOptions = [];
				$orderedOptions[$defaultKey] = $group['options'][$defaultKey];
				foreach ($group['options'] as $formKey => $label) {
					if ((string)$formKey === (string)$defaultKey) {
						continue;
					}
					$orderedOptions[$formKey] = $label;
				}
				$campaignGroups[$id]['options'] = $orderedOptions;
			}
			unset($campaignGroups[$id]['defaultFormId']);
		}

		$formOptionsGroup = array_values($campaignGroups);

        return $formOptionsGroup;
    }

    /**
     * @unreleased
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
