<?php
/**
 * Example Implementation: GiveWP Campaign Elementor Template
 *
 * This file shows practical examples of how to use the Elementor campaign template
 * in different scenarios.
 *
 * NOTE: As of 4.0.0, campaign pages are automatically set up with Elementor template
 * data when Elementor is active. These examples show additional customization options.
 *
 * @since 4.0.0
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

// Include the template file
require_once __DIR__ . '/elementor-campaign-template.php';

/**
 * Example 0: Disable Automatic Elementor Template Setup
 *
 * If you want to disable the automatic setup of Elementor templates for campaign pages
 */
function disable_auto_elementor_campaign_setup() {
    add_filter('givewp_auto_setup_elementor_campaign_template', '__return_false');
}
// Uncomment the next line to disable automatic setup
// add_action('init', 'disable_auto_elementor_campaign_setup');

/**
 * Example 1: Simple Page Template Implementation
 *
 * Use this in a custom page template or functions.php to display a campaign
 */
function example_display_campaign_template() {
    // Example campaign ID - replace with actual campaign ID
    $campaignId = 123;
    $campaignDescription = 'Help us reach our goal to support local families in need. Every donation makes a difference!';

    // Display the template
    echo givewp_get_elementor_campaign_html_template($campaignId, $campaignDescription);
}

/**
 * Example 2: Shortcode for Easy Template Usage
 *
 * Register a shortcode to easily insert the campaign template anywhere
 */
function register_campaign_template_shortcode() {
    add_shortcode('givewp_campaign_template', 'render_campaign_template_shortcode');
}
add_action('init', 'register_campaign_template_shortcode');

function render_campaign_template_shortcode($atts) {
    // Parse shortcode attributes
    $atts = shortcode_atts([
        'campaign_id' => 0,
        'description' => ''
    ], $atts, 'givewp_campaign_template');

    // Validate campaign ID
    if (empty($atts['campaign_id'])) {
        return '<p>Error: Campaign ID is required.</p>';
    }

    return givewp_get_elementor_campaign_html_template(
        intval($atts['campaign_id']),
        sanitize_text_field($atts['description'])
    );
}

/**
 * Example 3: WordPress Hook Integration
 *
 * Automatically add the template to campaign pages
 */
function auto_add_campaign_template_to_pages() {
    // Only run on campaign pages (adjust the condition as needed)
    if (is_page() && get_post_meta(get_the_ID(), '_is_campaign_page', true)) {
        $campaignId = get_post_meta(get_the_ID(), '_campaign_id', true);
        $description = get_post_meta(get_the_ID(), '_campaign_description', true);

        if ($campaignId) {
            echo givewp_get_elementor_campaign_html_template($campaignId, $description);
        }
    }
}
add_action('the_content', 'auto_add_campaign_template_to_pages');

/**
 * Example 4: Custom Post Type Integration
 *
 * For sites using a custom post type for campaigns
 */
function display_campaign_template_for_cpt($content) {
    // Check if we're on a campaign post type
    if (is_singular('campaign')) {
        $campaignId = get_post_meta(get_the_ID(), 'givewp_campaign_id', true);
        $description = get_the_content();

        if ($campaignId) {
            // Prepend the campaign template to the content
            $campaignTemplate = givewp_get_elementor_campaign_html_template($campaignId, $description);
            return $campaignTemplate . $content;
        }
    }

    return $content;
}
add_filter('the_content', 'display_campaign_template_for_cpt');

/**
 * Example 5: AJAX Handler for Dynamic Campaign Stats
 *
 * Implement dynamic loading of campaign statistics
 */
function register_campaign_stats_ajax() {
    add_action('wp_ajax_get_campaign_stats', 'handle_campaign_stats_request');
    add_action('wp_ajax_nopriv_get_campaign_stats', 'handle_campaign_stats_request');
}
add_action('init', 'register_campaign_stats_ajax');

function handle_campaign_stats_request() {
    // Verify nonce for security
    if (!wp_verify_nonce($_POST['nonce'], 'campaign_stats_nonce')) {
        wp_die('Security check failed');
    }

    $campaignId = intval($_POST['campaign_id']);

    if (!$campaignId) {
        wp_die('Invalid campaign ID');
    }

    // Example: Fetch campaign statistics
    // Replace this with actual GiveWP API calls
    $stats = [
        'total_donations' => 150,
        'total_amount' => '$45,230',
        'average_donation' => '$301.53',
        'donor_count' => 89
    ];

    wp_send_json_success($stats);
}

/**
 * Example 6: Enqueue Scripts for Enhanced Functionality
 *
 * Add JavaScript to enhance the campaign template
 */
function enqueue_campaign_template_scripts() {
    if (is_page() || is_singular('campaign')) {
        wp_enqueue_script(
            'givewp-campaign-template',
            plugin_dir_url(__FILE__) . 'assets/campaign-template.js',
            ['jquery'],
            '1.0.0',
            true
        );

        // Localize script with AJAX URL and nonce
        wp_localize_script('givewp-campaign-template', 'campaignAjax', [
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('campaign_stats_nonce')
        ]);
    }
}
add_action('wp_enqueue_scripts', 'enqueue_campaign_template_scripts');

/**
 * Example 7: Widget Implementation
 *
 * Create a widget for displaying campaign templates
 */
class GiveWP_Campaign_Template_Widget extends WP_Widget {

    public function __construct() {
        parent::__construct(
            'givewp_campaign_template_widget',
            'GiveWP Campaign Template',
            ['description' => 'Display a campaign using the Elementor-style template']
        );
    }

    public function widget($args, $instance) {
        $campaignId = !empty($instance['campaign_id']) ? intval($instance['campaign_id']) : 0;
        $description = !empty($instance['description']) ? $instance['description'] : '';

        if ($campaignId) {
            echo $args['before_widget'];

            if (!empty($instance['title'])) {
                echo $args['before_title'] . apply_filters('widget_title', $instance['title']) . $args['after_title'];
            }

            echo givewp_get_elementor_campaign_html_template($campaignId, $description);

            echo $args['after_widget'];
        }
    }

    public function form($instance) {
        $title = !empty($instance['title']) ? $instance['title'] : '';
        $campaignId = !empty($instance['campaign_id']) ? $instance['campaign_id'] : '';
        $description = !empty($instance['description']) ? $instance['description'] : '';
        ?>
        <p>
            <label for="<?php echo $this->get_field_id('title'); ?>">Title:</label>
            <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>"
                   name="<?php echo $this->get_field_name('title'); ?>" type="text"
                   value="<?php echo esc_attr($title); ?>">
        </p>
        <p>
            <label for="<?php echo $this->get_field_id('campaign_id'); ?>">Campaign ID:</label>
            <input class="widefat" id="<?php echo $this->get_field_id('campaign_id'); ?>"
                   name="<?php echo $this->get_field_name('campaign_id'); ?>" type="number"
                   value="<?php echo esc_attr($campaignId); ?>">
        </p>
        <p>
            <label for="<?php echo $this->get_field_id('description'); ?>">Description:</label>
            <textarea class="widefat" id="<?php echo $this->get_field_id('description'); ?>"
                      name="<?php echo $this->get_field_name('description'); ?>" rows="4"><?php echo esc_textarea($description); ?></textarea>
        </p>
        <?php
    }

    public function update($new_instance, $old_instance) {
        $instance = [];
        $instance['title'] = (!empty($new_instance['title'])) ? sanitize_text_field($new_instance['title']) : '';
        $instance['campaign_id'] = (!empty($new_instance['campaign_id'])) ? intval($new_instance['campaign_id']) : 0;
        $instance['description'] = (!empty($new_instance['description'])) ? sanitize_textarea_field($new_instance['description']) : '';

        return $instance;
    }
}

// Register the widget
function register_campaign_template_widget() {
    register_widget('GiveWP_Campaign_Template_Widget');
}
add_action('widgets_init', 'register_campaign_template_widget');

/**
 * Example 8: Gutenberg Block Integration
 *
 * Create a Gutenberg block that uses the Elementor template
 */
function register_campaign_template_block() {
    if (function_exists('register_block_type')) {
        register_block_type('givewp/campaign-elementor-template', [
            'editor_script' => 'givewp-campaign-template-block',
            'render_callback' => 'render_campaign_template_block'
        ]);
    }
}
add_action('init', 'register_campaign_template_block');

function render_campaign_template_block($attributes) {
    $campaignId = isset($attributes['campaignId']) ? intval($attributes['campaignId']) : 0;
    $description = isset($attributes['description']) ? $attributes['description'] : '';

    if (!$campaignId) {
        return '<p>Please select a campaign ID in the block settings.</p>';
    }

    return givewp_get_elementor_campaign_html_template($campaignId, $description);
}

/**
 * Usage Examples in Templates:
 *
 * 1. In a page template:
 *    example_display_campaign_template();
 *
 * 2. As a shortcode:
 *    [givewp_campaign_template campaign_id="123" description="Your description here"]
 *
 * 3. In PHP with custom data:
 *    echo givewp_get_elementor_campaign_html_template(123, 'Custom description');
 *
 * 4. With the widget:
 *    Add the "GiveWP Campaign Template" widget to any sidebar
 *
 * 5. Individual shortcodes for manual implementation:
 *    [givewp_campaign campaign_id="123" show_image="true" show_goal="false"]
 *    [givewp_campaign_form campaign_id="123" display_style="button"]
 *    [givewp_campaign_donations campaign_id="123" donations_per_page="5"]
 *    [givewp_campaign_donors campaign_id="123" donors_per_page="5"]
 */
