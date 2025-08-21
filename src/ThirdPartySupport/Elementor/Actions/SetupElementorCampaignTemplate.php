<?php

namespace Give\ThirdPartySupport\Elementor\Actions;

use Give\Campaigns\Models\Campaign;
use Give\Campaigns\Models\CampaignPage;
use Give\ThirdPartySupport\Elementor\Widgets\V2\ElementorCampaignDonationsWidget\ElementorCampaignDonationsWidget;
use Give\ThirdPartySupport\Elementor\Widgets\V2\ElementorCampaignDonorsWidget\ElementorCampaignDonorsWidget;
use Give\ThirdPartySupport\Elementor\Widgets\V2\ElementorCampaignGoalWidget\ElementorCampaignGoalWidget;
use Give\ThirdPartySupport\Elementor\Widgets\V2\ElementorDonationFormWidget\ElementorDonationFormWidget;
use Give\ThirdPartySupport\Elementor\Widgets\V2\ElementorCampaignStatsWidget\ElementorCampaignStatsWidget;

/**
 * Automatically setup Elementor template data for campaign pages
 *
 * This class automatically populates campaign pages with our custom Elementor
 * template layout when Elementor is active, providing a seamless experience
 * for users creating campaign pages.
 *
 * @since 4.7.0
 */
class SetupElementorCampaignTemplate
{
    /**
     * Setup Elementor template data for campaign page
     *
     * @since 4.7.0
     */
    public function __invoke(CampaignPage $campaignPage): void
    {
        // Allow users to disable auto-setup via filter
        if (!apply_filters('givewp_auto_setup_elementor_campaign_template', true)) {
            return;
        }

        // Only proceed if Elementor is active
        if (!$this->isElementorActive()) {
            return;
        }

        // Only setup for new campaign pages that don't already have Elementor data
        if ($this->campaignPageHasElementorData($campaignPage->id)) {
            return;
        }

        $campaign = $campaignPage->campaign();
        if (!$campaign) {
            return;
        }

        $this->setupElementorData($campaignPage->id, $campaign->id, $campaign->shortDescription);
    }

    /**
     * Check if Elementor is active and available
     *
     * @since 4.7.0
     */
    private function isElementorActive(): bool
    {
        return class_exists('\Elementor\Plugin') && defined('ELEMENTOR_VERSION');
    }

    /**
     * Check if campaign page already has Elementor data
     *
     * @since 4.7.0
     */
    private function campaignPageHasElementorData(int $pageId): bool
    {
        $elementorData = get_post_meta($pageId, '_elementor_data', true);
        return !empty($elementorData) && $elementorData !== '[]';
    }

    /**
     * Setup Elementor data and meta keys for the campaign page
     *
     * @since 4.7.0
     */
    private function setupElementorData(int $pageId, int $campaignId, string $shortDescription): void
    {
        // Get the template data structure
        $templateData = $this->getElementorTemplateData($campaignId, $shortDescription);

        // Convert to JSON and add proper slashing for WordPress
        $jsonData = wp_slash(wp_json_encode($templateData));

        // Set the main Elementor data
        update_post_meta($pageId, '_elementor_data', $jsonData);

        // Set Elementor edit mode to enable editing
        update_post_meta($pageId, '_elementor_edit_mode', 'builder');

        // Set template type
        update_post_meta($pageId, '_elementor_template_type', 'page');

        // Set Elementor version
        if (defined('ELEMENTOR_VERSION')) {
            update_post_meta($pageId, '_elementor_version', ELEMENTOR_VERSION);
        }

        // Add our custom meta to track this as an auto-generated template
        update_post_meta($pageId, '_givewp_elementor_auto_template', true);
        update_post_meta($pageId, '_givewp_elementor_template_version', '1.0.0');
    }

    /**
     * Get Elementor template data structure
     *
     * @since 4.7.0
     * @return array
     */
    private function getElementorTemplateData(int $campaignId, string $shortDescription): array
    {
        $campaign = Campaign::find($campaignId);
        if (!$campaign) {
            return [];
        }

        $campaignDefaultFormId = $campaign->defaultFormId;
        $campaignImageUrl = $campaign->image;
        $campaignImageId = null;
        if ($campaign->image && $imageId = attachment_url_to_postid($campaign->image)) {
            $campaignImageId = $imageId;
        }

        return [
            [
                'id' => $this->generateElementorId(),
                'elType' => 'section',
                'settings' => [
                    'layout' => 'boxed',
                    'gap' => 'default',
                    'content_width' => [
                        'unit' => '%',
                        'size' => 100
                    ],
                    'margin' => [
                        'unit' => 'px',
                        'top' => '0',
                        'right' => '0',
                        'bottom' => '40',
                        'left' => '0'
                    ]
                ],
                'elements' => [
                    // Left Column (60% - Post Featured Image)
                    [
                        'id' => $this->generateElementorId(),
                        'elType' => 'column',
                        'settings' => [
                            '_column_size' => 60,
                            'space_between_widgets' => '0'
                        ],
                        'elements' => [
                            [
                                'id' => $this->generateElementorId(),
                                'elType' => 'widget',
                                'widgetType' => 'image',
                                'settings' => [
                                    'image' => [
                                        'id' => $campaignImageId,
                                        'url' => $campaignImageUrl
                                    ],
                                    'image_size' => 'full',
                                    'width' => [
                                        'unit' => '%',
                                        'size' => 100
                                    ],
                                    'height' => [
                                        'unit' => '%',
                                        'size' => 100
                                    ],
                                    'object-fit' => 'cover',
                                    'image_border_border' => 'default',
                                    'image_border_radius' => [
                                        'unit' => 'px',
                                        'top' => 8,
                                        'right' => 8,
                                        'bottom' => 8,
                                        'left' => 8,
                                        'isLinked' => true
                                    ],
                                    'height_inner' => [
                                        'unit' => 'px',
                                        'size' => 0
                                    ],
                                    'aspect_ratio' => '16:9'
                                ]
                            ]
                        ]
                    ],
                    // Right Column (40% - Goal, Stats, Donate Button)
                    [
                        'id' => $this->generateElementorId(),
                        'elType' => 'column',
                        'settings' => [
                            '_column_size' => 40,
                            'space_between_widgets' => '20'
                        ],
                        'elements' => [
                            // Campaign Goal
                            [
                                'id' => $this->generateElementorId(),
                                'elType' => 'widget',
                                'widgetType' => give(ElementorCampaignGoalWidget::class)->get_name(),
                                'settings' => [
                                    'campaign_id' => $campaignId
                                ]
                            ],
                            // Campaign Stats
                            [
                                'id' => $this->generateElementorId(),
                                'elType' => 'widget',
                                'widgetType' => give(ElementorCampaignStatsWidget::class)->get_name(),
                                'settings' => [
                                    'campaign_id' => $campaignId,
                                    'statistic' => 'top-donation'
                                ]
                            ],
                            [
                                'id' => $this->generateElementorId(),
                                'elType' => 'widget',
                                'widgetType' => give(ElementorCampaignStatsWidget::class)->get_name(),
                                'settings' => [
                                    'campaign_id' => $campaignId,
                                    'statistic' => 'average-donation'
                                ]
                            ],
                            // Donate Button
                            [
                                'id' => $this->generateElementorId(),
                                'elType' => 'widget',
                                'widgetType' => give(ElementorDonationFormWidget::class)->get_name(),
                                'settings' => [
                                    'form_id' => $campaignDefaultFormId,
                                    'display_style' => 'modal'
                                ]
                            ]
                        ]
                    ]
                ]
            ],
            // Description Section
            [
                'id' => $this->generateElementorId(),
                'elType' => 'section',
                'settings' => [
                    'layout' => 'boxed',
                    'margin' => [
                        'unit' => 'px',
                        'top' => '40',
                        'bottom' => '40'
                    ]
                ],
                'elements' => [
                    [
                        'id' => $this->generateElementorId(),
                        'elType' => 'column',
                        'settings' => [
                            '_column_size' => 100
                        ],
                        'elements' => [
                            [
                                'id' => $this->generateElementorId(),
                                'elType' => 'widget',
                                'widgetType' => 'text-editor',
                                'settings' => [
                                    'editor' => $shortDescription ?: ''
                                ]
                            ]
                        ]
                    ]
                ]
            ],
            // Donations Section
            [
                'id' => $this->generateElementorId(),
                'elType' => 'section',
                'settings' => [
                    'layout' => 'boxed',
                    'margin' => [
                        'unit' => 'px',
                        'bottom' => '40'
                    ]
                ],
                'elements' => [
                    [
                        'id' => $this->generateElementorId(),
                        'elType' => 'column',
                        'settings' => [
                            '_column_size' => 100
                        ],
                        'elements' => [
                            [
                                'id' => $this->generateElementorId(),
                                'elType' => 'widget',
                                'widgetType' => give(ElementorCampaignDonationsWidget::class)->get_name(),
                                'settings' => [
                                    'campaign_id' => $campaignId,
                                    'show_anonymous' => 'yes',
                                    'show_icon' => 'yes',
                                    'show_button' => 'yes',
                                    'donate_button_text' => 'Donate',
                                    'sort_by' => 'recent-donations',
                                    'donations_per_page' => 5,
                                    'load_more_button_text' => 'Load more'
                                ]
                            ]
                        ]
                    ]
                ]
            ],
            // Donors Section
            [
                'id' => $this->generateElementorId(),
                'elType' => 'section',
                'settings' => [
                    'layout' => 'boxed'
                ],
                'elements' => [
                    [
                        'id' => $this->generateElementorId(),
                        'elType' => 'column',
                        'settings' => [
                            '_column_size' => 100
                        ],
                        'elements' => [
                            [
                                'id' => $this->generateElementorId(),
                                'elType' => 'widget',
                                'widgetType' => give(ElementorCampaignDonorsWidget::class)->get_name(),
                                'settings' => [
                                    'campaign_id' => $campaignId,
                                    'show_anonymous' => 'yes',
                                    'show_company_name' => 'yes',
                                    'show_avatar' => 'yes',
                                    'show_button' => 'yes',
                                    'donate_button_text' => 'Join the list',
                                    'sort_by' => 'top-donors',
                                    'donors_per_page' => 5,
                                    'load_more_button_text' => 'Load more'
                                ]
                            ]
                        ]
                    ]
                ]
            ]
        ];
    }

    /**
     * Generate a random Elementor-style ID
     *
     * @since 4.7.0
     */
    private function generateElementorId(): string
    {
        return substr(md5(uniqid('', true)), 0, 7);
    }
}
