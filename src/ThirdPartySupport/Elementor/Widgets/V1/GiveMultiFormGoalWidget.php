<?php

namespace Give\ThirdPartySupport\Elementor\Widgets\V1;

use Give\MultiFormGoals\MultiFormGoal\Shortcode;
use Elementor\Widget_Base;

/**
 * Elementor Give Multi Form Goal Widget.
 *
 * Elementor widget that inserts the GiveWP [give_multi_form_goal] shortcode to output a Give Multi Form Goal.
 *
 * @since 4.7.0 migrated from givewp-elementor-widgets
 */

class GiveMultiFormGoalWidget extends Widget_Base
{
    /**
     * GiveMultiFormGoalWidget constructor.
     *
     * @param  array  $data
     * @param  null  $args
     */
    /**
     * Get widget name.
     *
     * Retrieve Give Multi Form Goal widget name.
     *
     * @access public
     *
     * @return string Widget name.
     */
    public function get_name()
    {
        return 'Give Multi Form Goal';
    }

    /**
     * Get widget title.
     *
     * Retrieve Give Multi Form Goal widget title.
     *
     * @access public
     *
     * @return string Widget title.
     */
    public function get_title()
    {
        return __('Give Multi Form Goal (Legacy)', 'give');
    }

    /**
     * Get widget icon.
     *
     * Retrieve Give Multi Form Goal widget icon.
     *
     * @access public
     *
     * @return string Widget icon.
     */
    public function get_icon()
    {
        return 'give-icon';
    }

    /**
     * Get widget categories.
     *
     * Retrieve the list of categories the Give Multi Form Goal widget belongs to.
     *
     * @access public
     *
     * @return array Widget categories.
     */
    public function get_categories()
    {
        return ['givewp-category-legacy'];
    }

    /**
     * Widget inner wrapper.
     *
     * Use optimized DOM structure, without the inner wrapper.
     *
     * @since 4.7.0 migrated from givewp-elementor-widgets
     * @access public
     */
    public function has_widget_inner_wrapper(): bool
    {
        return false;
    }

    /**
     * Goal Give Multi Form Goal widget controls.
     *
     * Adds different input fields to allow the user to change and customize the widget settings.
     *
     * @access protected
     */
    protected function register_controls()
    {
        $this->start_controls_section(
            'give_multi_form_goal_settings',
            [
                'label' => esc_html__('Give Multi Form Goal Widget', 'give'),
                'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
            ]
        );

        $this->add_control(
            'ids',
            [
                'label'       => esc_html__('Donation Form IDs', 'give'),
                'type'        => \Elementor\Controls_Manager::TEXT,
                'description' => esc_html__('Choose the IDs of the forms you want to display in the multi-form goal. Display all the forms (default), a single form ID, or a comma-separated list of IDs', 'give'),
                'default'     => '',
            ]
        );

        if (give_get_option('tags') === 'enabled') {
            $this->add_control(
                'tags',
                [
                    'label'       => esc_html__('Tags', 'give'),
                    'type'        => \Elementor\Controls_Manager::TEXT,
                    'description' => esc_html__('If you have tags enabled in GiveWP, you can list the category IDs that you want displayed in this grid. A comma-separated list of form tag IDs will cause the grid to include only forms with those tags.',
                        'give'),
                    'default'     => '',
                ]
            );
        }

        if (give_get_option('categories') === 'enabled') {
            $this->add_control(
                'categories',
                [
                    'label'       => esc_html__('Categories', 'give'),
                    'type'        => \Elementor\Controls_Manager::TEXT,
                    'description' => esc_html__('If you have categories enabled in GiveWP, you can list the category IDs that you want displayed in this grid. A comma-separated list of form category IDs will cause the grid to include only forms from those categories', 'give'),
                    'default'     => '',
                ]
            );
        }

        $this->add_control(
            'goal',
            [
                'label'       => esc_html__('Goal Amount', 'give'),
                'type'        => \Elementor\Controls_Manager::TEXT,
                'description' => esc_html__('Choose the goal amount to be displayed in the multi-form goal card.', 'give'),
                'default'     => '1000',
            ]
        );

        $this->add_control(
            'enddate',
            [
                'label'       => esc_html__('End Date', 'give'),
                'type'        => \Elementor\Controls_Manager::TEXT,
                'description' => esc_html__('Define when the multi-form goal should come to an end.', 'give'),
                'default'     => '',
            ]
        );

        $this->add_control(
            'color',
            [
                'label'       => esc_html__('Color', 'give'),
                'type'        => \Elementor\Controls_Manager::COLOR,
                'description' => esc_html__('Choose the primary color of the multi-form goal card', 'give'),
                'default'     => '#28c77b',
            ]
        );

        $this->add_control(
            'heading',
            [
                'label'       => esc_html__('Heading Title', 'give'),
                'type'        => \Elementor\Controls_Manager::TEXT,
                'description' => esc_html__('Choose the heading to be displayed on the multi-form goal card.', 'give'),
                'default'     => 'Example Heading',
            ]
        );

        $this->add_control(
            'image',
            [
                'label'       => esc_html__('Featured Image of the Card', 'give'),
                'type'        => \Elementor\Controls_Manager::MEDIA,
                'description' => esc_html__('Choose the image URL of the multi-form goal card.', 'give'),
                'default'     => [
                    'url' => GIVE_PLUGIN_URL . 'assets/dist/images/onboarding-preview-form-image.min.jpg',
                ]
            ]
        );

        $this->add_control(
            'summary',
            [
                'label'       => esc_html__('Summary', 'give'),
                'type'        => \Elementor\Controls_Manager::TEXT,
                'description' => esc_html__('Choose the summary text placed below the heading title.', 'give'),
                'default'     => 'This is a summary.',
            ]
        );

        $this->end_controls_section();
    }

    /**
     * Render the [give_multi_form_goal] output on the frontend.
     *
     * Written in PHP and used to generate the final HTML.
     *
     * @access protected
     */
    protected function render()
    {
        $settings = $this->get_settings_for_display();

        $attributes = [
            'ids'        => isset($settings['ids']) ? esc_attr($settings['ids']) : '',
            'tags'       => isset($settings['tags']) ? esc_attr($settings['tags']) : '',
            'categories' => isset($settings['categories']) ? esc_attr($settings['categories']) : '',
            'goal'       => isset($settings['goal']) ? (int) $settings['goal'] : 1000,
            'color'      => isset($settings['color']) ? esc_attr($settings['color']) : '#28c77b',
            'enddate'    => isset($settings['enddate']) ? esc_attr($settings['enddate']) : '',
            'heading'    => isset($settings['heading']) ? esc_html($settings['heading']) : '',
            'image'      => isset($settings['image']['url']) ? esc_url($settings['image']['url']) : GIVE_PLUGIN_URL . 'assets/dist/images/onboarding-preview-form-image.min.jpg',
            'summary'    => isset($settings['summary']) ? esc_html($settings['summary']) : 'This is a summary.',
        ];

        $shortcode = new Shortcode();

        printf('<div class="givewp-elementor-widget give-multi-form-goal-wrap">%s</div>', $shortcode->renderCallback($attributes));
    }
}
