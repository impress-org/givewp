<?php
/**
 * Elementor Template for Campaign Pages
 *
 * This template provides a similar layout to the Gutenberg campaign page layout
 * but uses shortcodes instead of blocks for Elementor compatibility.
 *
 * Usage:
 * 1. Copy this template to your theme's elementor/templates/ directory
 * 2. Use the shortcodes in Elementor widgets (HTML widget or shortcode widget)
 * 3. Replace {campaign_id} with the actual campaign ID
 *
 * @since 4.0.0
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

/**
 * Get Elementor template content for campaign page
 *
 * @since 4.0.0
 * @param int $campaignId Campaign ID
 * @param string $shortDescription Campaign short description
 * @return array Elementor template data structure
 */
function givewp_get_elementor_campaign_template($campaignId, $shortDescription = '') {
    return [
        'version' => '0.4',
        'title' => 'GiveWP Campaign Template',
        'type' => 'page',
        'content' => [
            [
                'id' => 'campaign-main-section',
                'elType' => 'section',
                'settings' => [
                    'layout' => 'boxed',
                    'content_width' => [
                        'unit' => '%',
                        'size' => 100
                    ]
                ],
                'elements' => [
                    [
                        'id' => 'campaign-main-column',
                        'elType' => 'column',
                        'settings' => [
                            '_column_size' => 100,
                        ],
                        'elements' => [
                            // Two-column layout container
                            [
                                'id' => 'campaign-two-columns',
                                'elType' => 'section',
                                'settings' => [
                                    'structure' => '60-40',
                                    'gap' => 'default',
                                    'padding' => [
                                        'unit' => 'px',
                                        'top' => '0',
                                        'bottom' => '0'
                                    ]
                                ],
                                'elements' => [
                                    // Left column - Campaign Image
                                    [
                                        'id' => 'campaign-image-column',
                                        'elType' => 'column',
                                        'settings' => [
                                            '_column_size' => 60,
                                            'content_position' => 'stretch'
                                        ],
                                        'elements' => [
                                            [
                                                'id' => 'campaign-image',
                                                'elType' => 'widget',
                                                'widgetType' => 'shortcode',
                                                'settings' => [
                                                    'shortcode' => "[givewp_campaign campaign_id=\"{$campaignId}\" show_image=\"true\" show_description=\"false\" show_goal=\"false\"]",
                                                    'custom_css' => '.givewp-campaign img { width: 100%; height: 100%; object-fit: cover; aspect-ratio: 16/9; border-radius: 8px; }'
                                                ]
                                            ]
                                        ]
                                    ],
                                    // Right column - Goal, Stats, Donate Button
                                    [
                                        'id' => 'campaign-info-column',
                                        'elType' => 'column',
                                        'settings' => [
                                            '_column_size' => 40,
                                            'content_position' => 'stretch'
                                        ],
                                        'elements' => [
                                            // Campaign Goal
                                            [
                                                'id' => 'campaign-goal',
                                                'elType' => 'widget',
                                                'widgetType' => 'shortcode',
                                                'settings' => [
                                                    'shortcode' => "[givewp_campaign campaign_id=\"{$campaignId}\" show_image=\"false\" show_description=\"false\" show_goal=\"true\"]"
                                                ]
                                            ],
                                            // Campaign Stats Container
                                            [
                                                'id' => 'campaign-stats-container',
                                                'elType' => 'section',
                                                'settings' => [
                                                    'layout' => 'boxed',
                                                    'gap' => 'narrow'
                                                ],
                                                'elements' => [
                                                    [
                                                        'id' => 'campaign-stats-column',
                                                        'elType' => 'column',
                                                        'settings' => [
                                                            '_column_size' => 100
                                                        ],
                                                        'elements' => [
                                                            // Total Donations Stat
                                                            [
                                                                'id' => 'campaign-total-stat',
                                                                'elType' => 'widget',
                                                                'widgetType' => 'html',
                                                                'settings' => [
                                                                    'html' => '<div class="campaign-stat"><span class="stat-label">Total Donations</span><span class="stat-value" id="campaign-total-' . $campaignId . '">Loading...</span></div>',
                                                                    'custom_css' => '.campaign-stat { display: flex; justify-content: space-between; margin-bottom: 10px; } .stat-label { font-weight: normal; } .stat-value { font-weight: bold; }'
                                                                ]
                                                            ],
                                                            // Average Donation Stat
                                                            [
                                                                'id' => 'campaign-average-stat',
                                                                'elType' => 'widget',
                                                                'widgetType' => 'html',
                                                                'settings' => [
                                                                    'html' => '<div class="campaign-stat"><span class="stat-label">Average Donation</span><span class="stat-value" id="campaign-average-' . $campaignId . '">Loading...</span></div>',
                                                                    'custom_css' => '.campaign-stat { display: flex; justify-content: space-between; margin-bottom: 10px; } .stat-label { font-weight: normal; } .stat-value { font-weight: bold; }'
                                                                ]
                                                            ]
                                                        ]
                                                    ]
                                                ]
                                            ],
                                            // Donate Button
                                            [
                                                'id' => 'campaign-donate-button',
                                                'elType' => 'widget',
                                                'widgetType' => 'shortcode',
                                                'settings' => [
                                                    'shortcode' => "[givewp_campaign_form campaign_id=\"{$campaignId}\" display_style=\"button\" continue_button_title=\"Donate Now\" show_title=\"false\" show_goal=\"false\" show_content=\"false\"]"
                                                ]
                                            ]
                                        ]
                                    ]
                                ]
                            ]
                        ]
                    ]
                ]
            ],
            // Description Section
            [
                'id' => 'campaign-description-section',
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
                        'id' => 'campaign-description-column',
                        'elType' => 'column',
                        'settings' => [
                            '_column_size' => 100
                        ],
                        'elements' => [
                            [
                                'id' => 'campaign-description',
                                'elType' => 'widget',
                                'widgetType' => 'text-editor',
                                'settings' => [
                                    'editor' => $shortDescription ?: 'Campaign description goes here...'
                                ]
                            ]
                        ]
                    ]
                ]
            ],
            // Donations List Section
            [
                'id' => 'campaign-donations-section',
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
                        'id' => 'campaign-donations-column',
                        'elType' => 'column',
                        'settings' => [
                            '_column_size' => 100
                        ],
                        'elements' => [
                            [
                                'id' => 'campaign-donations',
                                'elType' => 'widget',
                                'widgetType' => 'shortcode',
                                'settings' => [
                                    'shortcode' => "[givewp_campaign_donations campaign_id=\"{$campaignId}\" show_anonymous=\"true\" show_icon=\"true\" show_button=\"true\" donations_per_page=\"5\"]"
                                ]
                            ]
                        ]
                    ]
                ]
            ],
            // Donors List Section
            [
                'id' => 'campaign-donors-section',
                'elType' => 'section',
                'settings' => [
                    'layout' => 'boxed'
                ],
                'elements' => [
                    [
                        'id' => 'campaign-donors-column',
                        'elType' => 'column',
                        'settings' => [
                            '_column_size' => 100
                        ],
                        'elements' => [
                            [
                                'id' => 'campaign-donors',
                                'elType' => 'widget',
                                'widgetType' => 'shortcode',
                                'settings' => [
                                    'shortcode' => "[givewp_campaign_donors campaign_id=\"{$campaignId}\" show_anonymous=\"true\" show_avatar=\"true\" show_button=\"true\" donors_per_page=\"5\"]"
                                ]
                            ]
                        ]
                    ]
                ]
            ]
        ]
    ];
}

/**
 * Generate simple HTML template for manual implementation
 *
 * @since 4.0.0
 * @param int $campaignId Campaign ID
 * @param string $shortDescription Campaign short description
 * @return string HTML template
 */
function givewp_get_elementor_campaign_html_template($campaignId, $shortDescription = '') {
    return '
<!-- Campaign Main Layout -->
<div class="givewp-campaign-elementor-template">

    <!-- Two Column Layout -->
    <div class="elementor-section elementor-top-section">
        <div class="elementor-container elementor-column-gap-default">
            <div class="elementor-row">

                <!-- Left Column: Campaign Image (60%) -->
                <div class="elementor-column elementor-col-60" style="flex-basis: 60%;">
                    <div class="elementor-column-wrap">
                        <div class="elementor-widget-wrap">
                            <!-- Campaign Image Shortcode -->
                            [givewp_campaign campaign_id="' . $campaignId . '" show_image="true" show_description="false" show_goal="false"]
                        </div>
                    </div>
                </div>

                <!-- Right Column: Goal, Stats, Donate Button (40%) -->
                <div class="elementor-column elementor-col-40" style="flex-basis: 40%;">
                    <div class="elementor-column-wrap">
                        <div class="elementor-widget-wrap">

                            <!-- Campaign Goal -->
                            <div class="elementor-widget">
                                [givewp_campaign campaign_id="' . $campaignId . '" show_image="false" show_description="false" show_goal="true"]
                            </div>

                            <!-- Campaign Stats Container -->
                            <div class="elementor-widget campaign-stats-container">
                                <div class="campaign-stat">
                                    <span class="stat-label">Total Donations</span>
                                    <span class="stat-value" id="campaign-total-' . $campaignId . '">Loading...</span>
                                </div>
                                <div class="campaign-stat">
                                    <span class="stat-label">Average Donation</span>
                                    <span class="stat-value" id="campaign-average-' . $campaignId . '">Loading...</span>
                                </div>
                            </div>

                            <!-- Donate Button -->
                            <div class="elementor-widget">
                                [givewp_campaign_form campaign_id="' . $campaignId . '" display_style="button" continue_button_title="Donate Now" show_title="false" show_goal="false" show_content="false"]
                            </div>

                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <!-- Description Section -->
    <div class="elementor-section elementor-top-section" style="margin-top: 40px; margin-bottom: 40px;">
        <div class="elementor-container">
            <div class="elementor-row">
                <div class="elementor-column elementor-col-100">
                    <div class="elementor-column-wrap">
                        <div class="elementor-widget-wrap">
                            <div class="elementor-widget elementor-widget-text-editor">
                                <p>' . esc_html($shortDescription ?: 'Campaign description goes here...') . '</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Campaign Donations Section -->
    <div class="elementor-section elementor-top-section" style="margin-bottom: 40px;">
        <div class="elementor-container">
            <div class="elementor-row">
                <div class="elementor-column elementor-col-100">
                    <div class="elementor-column-wrap">
                        <div class="elementor-widget-wrap">
                            <div class="elementor-widget">
                                [givewp_campaign_donations campaign_id="' . $campaignId . '" show_anonymous="true" show_icon="true" show_button="true" donations_per_page="5"]
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Campaign Donors Section -->
    <div class="elementor-section elementor-top-section">
        <div class="elementor-container">
            <div class="elementor-row">
                <div class="elementor-column elementor-col-100">
                    <div class="elementor-column-wrap">
                        <div class="elementor-widget-wrap">
                            <div class="elementor-widget">
                                [givewp_campaign_donors campaign_id="' . $campaignId . '" show_anonymous="true" show_avatar="true" show_button="true" donors_per_page="5"]
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>

<style>
/* Campaign Template Styles */
.givewp-campaign-elementor-template .campaign-stats-container {
    margin: 20px 0;
}

.givewp-campaign-elementor-template .campaign-stat {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 10px;
    padding: 5px 0;
}

.givewp-campaign-elementor-template .stat-label {
    font-weight: normal;
    color: #666;
}

.givewp-campaign-elementor-template .stat-value {
    font-weight: bold;
    color: #333;
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .givewp-campaign-elementor-template .elementor-col-60,
    .givewp-campaign-elementor-template .elementor-col-40 {
        flex-basis: 100% !important;
        margin-bottom: 20px;
    }
}

/* Campaign image styling */
.givewp-campaign-elementor-template .givewp-campaign img {
    width: 100%;
    height: auto;
    aspect-ratio: 16/9;
    object-fit: cover;
    border-radius: 8px;
}
</style>

<script>
// Load campaign stats dynamically (you may need to implement this via AJAX)
document.addEventListener("DOMContentLoaded", function() {
    // Example of how you might load dynamic stats
    // This would need to be implemented with actual GiveWP API calls

    const campaignId = ' . intval($campaignId) . ';

    // You can use wp_localize_script to pass data from PHP to JavaScript
    // or make AJAX calls to get campaign statistics

    // Example placeholders:
    const totalElement = document.getElementById("campaign-total-" + campaignId);
    const averageElement = document.getElementById("campaign-average-" + campaignId);

    if (totalElement) {
        // Replace with actual data fetching logic
        totalElement.textContent = "Loading...";
    }

    if (averageElement) {
        // Replace with actual data fetching logic
        averageElement.textContent = "Loading...";
    }
});
</script>
';
}
