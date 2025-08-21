<?php

namespace Give\ThirdPartySupport\Elementor\Widgets\V2\ElementorDonorWallWidget;

use Elementor\Widget_Base;
use Exception;
use Give\ThirdPartySupport\Elementor\Traits\HasFormOptions;

/**
 *
 * @since 4.7.0
 */
class ElementorDonorWallWidget extends Widget_Base
{
    use HasFormOptions;

    /**
     * @since 4.7.0
     */
    public function get_name(): string
    {
        return 'givewp_donor_wall';
    }

    /**
     * @since 4.7.0
     */
    public function get_title(): string
    {
        return __('GiveWP Donor Wall', 'give');
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
        return ['give', 'givewp', 'donor', 'wall', 'donors'];
    }

    /**
     * @since 4.7.0
     */
    public function get_custom_help_url(): string
    {
        return 'https://givewp.com/documentation/core/shortcodes/give_donor_wall/';
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
        return ['givewp-elementor-donor-wall-widget'];
    }

    /**
     * @since 4.7.0
     */
    public function get_style_depends(): array
    {
        return ['givewp-design-system-foundation', 'givewp-elementor-donor-wall-widget'];
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
     * Register Elementor controls for the Donor Wall widget.
     *
     * @since 4.7.0
     */
    protected function register_controls(): void
    {
        $this->start_controls_section(
            'donor_wall_settings',
            [
                'label' => __('Donor Wall Settings', 'give'),
            ]
        );

        $this->add_control(
            'donors_per_page',
            [
                'label' => __('Donors per Page', 'give'),
                'type' => \Elementor\Controls_Manager::NUMBER,
                'description' => __('Set how many donors will show before the "Show More" button appears.', 'give'),
                'min' => 1,
                'max' => 30,
                'default' => 12
            ]
        );

        $this->add_control(
            'all_forms',
            [
                'label' => __('Show All Donors?', 'give'),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'description' => __('Do you want to show all donors here, or choose one form?', 'give'),
                'label_on' => __('Yes', 'give'),
                'label_off' => __('No', 'give'),
                'return_value' => 'yes',
                'default' => 'yes',
            ]
        );

        $this->add_control(
            'form_id',
            [
                'label' => __('Form', 'give'),
                'type' => \Elementor\Controls_Manager::SELECT,
                'description' => __('Select the form to pull donors from.', 'give'),
                'options' => [],
                'groups' => $this->getFormOptionsWithCampaigns(),
                'condition' => [
                    'all_forms!' => 'yes'
                ],
            ]
        );

        $this->add_control(
            'orderby',
            [
                'label' => __('Order By', 'give'),
                'type' => \Elementor\Controls_Manager::SELECT,
                'description' => __('The element which the order will be determined by.', 'give'),
                'options' => [
                    'post_date' => __('Donation Date', 'give'),
                    'donation_amount' => __('Donation Amount', 'give')
                ],
                'default' => 'post_date'
            ]
        );

        $this->add_control(
            'order',
            [
                'label' => __('Order', 'give'),
                'type' => \Elementor\Controls_Manager::SELECT,
                'description' => __('The order the donors will be displayed in according to the "Order By" field chosen.', 'give'),
                'options' => [
                    'desc' => __('Descending', 'give'),
                    'asc' => __('Ascending', 'give')
                ],
                'default' => 'desc'
            ]
        );

        $this->add_control(
            'columns',
            [
                'label' => __('Number of Columns', 'give'),
                'type' => \Elementor\Controls_Manager::SELECT,
                'description' => __('The number of columns to display. Note that "Best Fit" will always stretch to fill the available width.', 'give'),
                'options' => [
                    'best-fit' => __('Best Fit', 'give'),
                    '1' => '1',
                    '2' => '2',
                    '3' => '3',
                    '4' => '4',
                ],
                'default' => 'best-fit'
            ]
        );

        $this->end_controls_section();

        $this->start_controls_section(
            'donor_display_settings',
            [
                'label' => __('Display Settings', 'give'),
            ]
        );

        $this->add_control(
            'show_avatar',
            [
                'label' => __('Show Avatar', 'give'),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'description' => __('Show/Hide the donor avatar. Will display the donor\'s initials if no avatar is supported for their email address.', 'give'),
                'label_on' => __('Show', 'give'),
                'label_off' => __('Hide', 'give'),
                'return_value' => 'yes',
                'default' => 'yes',
            ]
        );

        $this->add_control(
            'avatar_size',
            [
                'label' => __('Avatar Size', 'give'),
                'type' => \Elementor\Controls_Manager::NUMBER,
                'description' => __('Set the size of the Avatar in pixels.', 'give'),
                'default' => 60,
                'condition' => [
                    'show_avatar' => 'yes'
                ],
            ]
        );

        $this->add_control(
            'show_name',
            [
                'label' => __('Show Name', 'give'),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'description' => __('Show/Hide the donor\'s name.', 'give'),
                'label_on' => __('Show', 'give'),
                'label_off' => __('Hide', 'give'),
                'return_value' => 'yes',
                'default' => 'yes',
            ]
        );

        $this->add_control(
            'show_total',
            [
                'label' => __('Show Total', 'give'),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'description' => __('Show/Hide the donation amount.', 'give'),
                'label_on' => __('Show', 'give'),
                'label_off' => __('Hide', 'give'),
                'return_value' => 'yes',
                'default' => 'yes',
            ]
        );

        $this->add_control(
            'show_time',
            [
                'label' => __('Show Date', 'give'),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'description' => __('Show/Hide the date the donation was made.', 'give'),
                'label_on' => __('Show', 'give'),
                'label_off' => __('Hide', 'give'),
                'return_value' => 'yes',
                'default' => 'yes',
            ]
        );

        $this->add_control(
            'show_comments',
            [
                'label' => __('Show Comments', 'give'),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'description' => __('Show/Hide the donor comment if any was provided by the donor.', 'give'),
                'label_on' => __('Show', 'give'),
                'label_off' => __('Hide', 'give'),
                'return_value' => 'yes',
                'default' => 'yes',
            ]
        );

        $this->add_control(
            'comment_length',
            [
                'label' => __('Comment Length (characters)', 'give'),
                'type' => \Elementor\Controls_Manager::NUMBER,
                'description' => __('Number of characters to allow to be shown in the donor comments. This can help make each donor card a more consistent height.', 'give'),
                'default' => 140,
                'condition' => [
                    'show_comments' => 'yes'
                ]
            ]
        );

        $this->add_control(
            'only_comments',
            [
                'label' => __('Only Show Donations with Comments', 'give'),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'description' => __('Only show donations that have comments.', 'give'),
                'label_on' => __('Yes', 'give'),
                'label_off' => __('No', 'give'),
                'return_value' => 'yes',
                'default' => '',
                'condition' => [
                    'show_comments' => 'yes'
                ]
            ]
        );

        $this->add_control(
            'anonymous',
            [
                'label' => __('Show Anonymous Donations', 'give'),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'description' => __('Include/Exclude donations made that selected to be "Anonymous". They will be shown without the name or comments.', 'give'),
                'label_on' => __('Show', 'give'),
                'label_off' => __('Hide', 'give'),
                'return_value' => 'yes',
                'default' => 'yes',
            ]
        );

        $this->end_controls_section();

        $this->start_controls_section(
            'button_text_settings',
            [
                'label' => __('Button Text Settings', 'give'),
            ]
        );

        $this->add_control(
            'loadmore_text',
            [
                'label' => __('Load More Text', 'give'),
                'type' => \Elementor\Controls_Manager::TEXT,
                'description' => __('The text that appears on the "Load More" button if you have multiple "pages" of donor cards.', 'give'),
                'default' => __('Load More', 'give'),
            ]
        );

        $this->add_control(
            'readmore_text',
            [
                'label' => __('Read More Text', 'give'),
                'type' => \Elementor\Controls_Manager::TEXT,
                'description' => __('The text that appears on the "Read More" link if your donor comments are truncated.', 'give'),
                'default' => __('Read More', 'give'),
            ]
        );

        $this->end_controls_section();
    }

    /**
     * Render the Donor Wall widget output.
     *
     * @since 4.7.0
     */
    protected function render(): void
    {
        $settings = $this->get_settings_for_display();

        // Build shortcode attributes
        $attributes = [];

        if (isset($settings['donors_per_page'])) {
            $attributes[] = sprintf('donors_per_page="%s"', esc_attr($settings['donors_per_page']));
        }

        if ($settings['all_forms'] !== 'yes' && isset($settings['form_id'])) {
            $attributes[] = sprintf('form_id="%s"', esc_attr($settings['form_id']));
        }

        if (isset($settings['orderby'])) {
            $attributes[] = sprintf('orderby="%s"', esc_attr($settings['orderby']));
        }

        if (isset($settings['order'])) {
            $attributes[] = sprintf('order="%s"', esc_attr($settings['order']));
        }

        if (isset($settings['columns'])) {
            $attributes[] = sprintf('columns="%s"', esc_attr($settings['columns']));
        }

        if (isset($settings['show_avatar'])) {
            $attributes[] = sprintf('show_avatar="%s"', esc_attr($settings['show_avatar']));
        }

        if (isset($settings['avatar_size'])) {
            $attributes[] = sprintf('avatar_size="%s"', esc_attr($settings['avatar_size']));
        }

        if (isset($settings['show_name'])) {
            $attributes[] = sprintf('show_name="%s"', esc_attr($settings['show_name']));
        }

        if (isset($settings['show_total'])) {
            $attributes[] = sprintf('show_total="%s"', esc_attr($settings['show_total']));
        }

        if (isset($settings['show_time'])) {
            $attributes[] = sprintf('show_time="%s"', esc_attr($settings['show_time']));
        }

        if (isset($settings['show_comments'])) {
            $attributes[] = sprintf('show_comments="%s"', esc_attr($settings['show_comments']));
        }

        if (isset($settings['comment_length'])) {
            $attributes[] = sprintf('comment_length="%s"', esc_attr($settings['comment_length']));
        }

        if (isset($settings['only_comments'])) {
            $attributes[] = sprintf('only_comments="%s"', esc_attr($settings['only_comments']));
        }

        if (isset($settings['anonymous'])) {
            $attributes[] = sprintf('anonymous="%s"', esc_attr($settings['anonymous']));
        }

        if (isset($settings['loadmore_text'])) {
            $attributes[] = sprintf('loadmore_text="%s"', esc_attr($settings['loadmore_text']));
        }

        if (isset($settings['readmore_text'])) {
            $attributes[] = sprintf('readmore_text="%s"', esc_attr($settings['readmore_text']));
        }

        $shortcode = '[give_donor_wall ' . implode(' ', $attributes) . ']';

        echo do_shortcode($shortcode);
    }
}
