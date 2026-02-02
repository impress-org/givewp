<?php

namespace Give\PaymentGateways\TheGivingBlock\Admin;

use Give\PaymentGateways\SettingPage;

/**
 * The Giving Block settings page under GiveWP > Payment Gateways.
 *
 * Registers a horizontal sub-tab "The Giving Block" and vertical tabs (Get Started, Organization, Options)
 * following the same pattern as the Stripe gateway settings.
 *
 * @unreleased
 */
class TheGivingBlockSettingPage implements SettingPage
{
    /**
     * Section id used in URLs and filters.
     */
    public const SECTION_ID = 'the-giving-block';

    /**
     * @inheritDoc
     *
     * @unreleased
     */
    public function getId(): string
    {
        return self::SECTION_ID;
    }

    /**
     * @inheritDoc
     *
     * @unreleased
     */
    public function getName(): string
    {
        return esc_html__('The Giving Block', 'give');
    }

    /**
     * @inheritDoc
     *
     * Returns settings keyed by group (vertical tab).
     *
     * @unreleased
     */
    public function getSettings(): array
    {
        $settings = [];

        $settings['get-started'] = [
            [
                'id' => 'give_title_tgb_get_started',
                'type' => 'title',
            ],
            [
                'name' => '',
                'desc' => '',
                'wrapper_class' => 'give-tgb-get-started-field-wrap',
                'id' => 'the_giving_block_get_started',
                'type' => 'the_giving_block_get_started',
            ],
            [
                'id' => 'give_title_tgb_get_started',
                'type' => 'sectionend',
            ],
        ];

        $settings['organization'] = [
            [
                'id' => 'give_title_tgb_organization',
                'type' => 'title',
            ],
            [
                'name' => '',
                'desc' => '',
                'wrapper_class' => 'give-tgb-organization-field-wrap',
                'id' => 'the_giving_block_organization',
                'type' => 'the_giving_block_organization',
            ],
            [
                'id' => 'give_title_tgb_organization',
                'type' => 'sectionend',
            ],
        ];

        $settings['options'] = [
            [
                'id' => 'give_title_tgb_options',
                'type' => 'title',
            ],
            [
                'name' => '',
                'desc' => '',
                'wrapper_class' => 'give-tgb-options-field-wrap',
                'id' => 'the_giving_block_options',
                'type' => 'the_giving_block_options',
            ],
            [
                'id' => 'give_title_tgb_options',
                'type' => 'sectionend',
            ],
        ];

        return $settings;
    }

    /**
     * @inheritDoc
     *
     * @unreleased
     */
    public function boot(): void
    {
        add_filter('give_get_sections_gateways', [$this, 'registerSection'], 10);
        add_filter('give_get_groups_' . self::SECTION_ID, [$this, 'registerGroups'], 10);
        add_filter('give_get_settings_gateways', [$this, 'registerSettings'], 10);
    }

    /**
     * Register the "The Giving Block" horizontal sub-tab under Payment Gateways.
     *
     * @unreleased
     *
     * @param array<string, string> $sections
     * @return array<string, string>
     */
    public function registerSection(array $sections): array
    {
        $sections[self::SECTION_ID] = $this->getName();

        return $sections;
    }

    /**
     * Register vertical tabs (groups) for the The Giving Block section.
     *
     * @unreleased
     *
     * @return array<string, string> Map of group slug => label
     */
    public function registerGroups(): array
    {
        return [
            'get-started' => __('Get Started', 'give'),
            'organization' => __('Organization', 'give'),
            'options' => __('Options', 'give'),
        ];
    }

    /**
     * Register settings for the The Giving Block section.
     *
     * @unreleased
     *
     * @param array $settings Existing gateway settings.
     * @return array
     */
    public function registerSettings(array $settings): array
    {
        $currentSection = give_get_current_setting_section();

        if ($currentSection !== self::SECTION_ID) {
            return $settings;
        }

        return $this->getSettings();
    }
}
