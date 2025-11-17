<?php

namespace Give\ThirdPartySupport\Elementor\Widgets\V1;

use Elementor\Widget_Base;

/**
 * Elementor Give Goal Widget.
 *
 * Elementor widget that inserts the GiveWP [give_goal] shortcode to output a login form.
 *
 * @since 4.7.0 migrated from givewp-elementor-widgets
 */

class GiveGoalWidget extends Widget_Base
{
    /**
     * Get widget name.
     *
     * Retrieve Give Goal widget name.
     *
     * @since 4.7.0 migrated from givewp-elementor-widgets
     * @access public
     *
     * @return string Widget name.
     */
    public function get_name()
    {
        return 'Give Goal';
    }

    /**
     * Get widget title.
     *
     * Retrieve Give Goal widget title.
     *
     * @since 4.7.0 migrated from givewp-elementor-widgets
     * @access public
     *
     * @return string Widget title.
     */
    public function get_title()
    {
        return __('Give Goal (Legacy)', 'give');
    }

    /**
     * Get widget icon.
     *
     * Retrieve Give Goal widget icon.
     *
     * @since 4.7.0 migrated from givewp-elementor-widgets
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
     * Retrieve the list of categories the Give Goal widget belongs to.
     *
     * @since 4.7.0 migrated from givewp-elementor-widgets
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
     * Goal Give Goal widget controls.
     *
     * Adds different input fields to allow the user to change and customize the widget settings.
     *
     * @since 4.7.0 migrated from givewp-elementor-widgets
     * @access protected
     */
    protected function register_controls()
    {
        $this->start_controls_section(
            'give_login_settings',
            [
                'label' => __('GiveWP Goal Widget', 'give'),
                'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
            ]
        );

        $this->add_control(
            'form_id',
            [
                'label' => __('Form ID', 'give'),
                'type' => \Elementor\Controls_Manager::TEXT,
                'input_type' => 'number'
            ]
        );

        $this->add_control(
            'show_text',
            [
                'label' => __('Show Text', 'give'),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'description' => __('Show or hide the goal text.', 'give'),
                'label_on' => __('Show', 'give'),
                'label_off' => __('Hide', 'give'),
                'default' => 'yes'
            ]
        );

        $this->add_control(
            'show_bar',
            [
                'label' => __('Show Progress Bar', 'give'),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'description' => __('Show or hide progress bar.', 'give'),
                'label_on' => __('Show', 'give'),
                'label_off' => __('Hide', 'give'),
                'default' => 'yes'
            ]
        );

        $this->add_control(
            'give_goal_info',
            [
                'label' => '',
                'type' => \Elementor\Controls_Manager::RAW_HTML,
                'content_classes' => 'give-info',
                'raw' => '
					<div class="give">
						<p class="info-head">
							' . __('GIVEWP GOAL WIDGET', 'give') . '</p>
						<p class="info-message">' . __('This is the GiveWP Goal widget. Choose how you want your form goal to be displayed. Note that this widget only supports forms that have a goal enabled. If you want to show progress of any form or multiple forms, use the "GiveWP Totals" widget instead.', 'give') . '</p>
						<p class="give-docs-links">
							<a href="https://givewp.com/documentation/core/shortcodes/give_goal/?utm_source=plugin_settings&utm_medium=referral&utm_campaign=Free_Addons&utm_content=givelementor" rel="noopener noreferrer" target="_blank"><i class="fa fa-book" aria-hidden="true"></i>' . __('Visit the GiveWP Docs for more info on the GiveWP Goal.', 'give') . '</a>
						</p>
				</div>'
            ]
        );

        $this->end_controls_section();
    }

    /**
     * Render the [give_goal] output on the frontend.
     *
     * Written in PHP and used to generate the final HTML.
     *
     * @since 4.7.0 migrated from givewp-elementor-widgets
     * @access protected
     */
    protected function render()
    {
        $settings = $this->get_settings_for_display();

        $id = esc_html($settings['form_id']);
        $showText = esc_html($settings['show_text']);
        $showBar = esc_html($settings['show_bar']);

        $html = do_shortcode(
            '[give_goal
				id="' . $id . '"
				show_text="' . $showText . '"
				show_bar="' . $showBar . '"
				]'
        );

        echo '<div class="givewp-elementor-widget give-login-shortcode-wrap">' . $html . '</div>';
    }
}
