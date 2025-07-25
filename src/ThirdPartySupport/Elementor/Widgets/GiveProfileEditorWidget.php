<?php

namespace Give\ThirdPartySupport\Elementor\Widgets;

/**
 * Elementor Give Profile Editor Widget.
 *
 * Elementor widget that inserts the GiveWP [give_profile_editor] shrotcode to output a login form.
 *
 * @unreleased migrated from givewp-elementor-widgets
 */

class GiveProfileEditorWidget extends \Elementor\Widget_Base
{
    /**
     * Get widget name.
     *
     * Retrieve Give Profile Editor widget name.
     *
     * @unreleased migrated from givewp-elementor-widgets
     * @access public
     *
     * @return string Widget name.
     */
    public function get_name()
    {
        return 'Give_Profile_Editor';
    }

    /**
     * Get widget title.
     *
     * Retrieve Give Profile Editor widget title.
     *
     * @unreleased migrated from givewp-elementor-widgets
     * @access public
     *
     * @return string Widget title.
     */
    public function get_title()
    {
        return __('Give Profile Editor', 'dw4elementor');
    }

    /**
     * Get widget icon.
     *
     * Retrieve Give Profile Editor widget icon.
     *
     * @unreleased migrated from givewp-elementor-widgets
     * @access public
     *
     * @return string Widget icon.
     */
    public function get_icon()
    {
        return 'dw4elementor-icon';
    }

    /**
     * Get widget categories.
     *
     * Retrieve the list of categories the Give Profile Editor widget belongs to.
     *
     * @unreleased migrated from givewp-elementor-widgets
     * @access public
     *
     * @return array Widget categories.
     */
    public function get_categories()
    {
        return ['givewp-category'];
    }

    /**
     * Widget inner wrapper.
     *
     * Use optimized DOM structure, without the inner wrapper.
     *
     * @unreleased migrated from givewp-elementor-widgets
     * @access public
     */
    public function has_widget_inner_wrapper(): bool
    {
        return false;
    }

    /**
     * Profile Editor Give Profile Editor widget controls.
     *
     * Adds different input fields to allow the user to change and customize the widget settings.
     *
     * @unreleased migrated from givewp-elementor-widgets
     * @access protected
     */
    protected function register_controls()
    {
        $this->start_controls_section(
            'give_profile_editor_settings',
            [
                'label' => __('GiveWP Profile Editor Widget', 'dw4elementor'),
                'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
            ]
        );

        $this->add_control(
            'give_profile_editor_info',
            [
                'label' => '',
                'type' => \Elementor\Controls_Manager::RAW_HTML,
                'content_classes' => 'dw4e-info',
                'raw' => '
					<div class="dw4e">
						<p class="info-head">
							' . __('GIVEWP PROFILE EDITOR WIDGET', 'dw4elementor') . '</p>
						<p class="info-message">' . __('This is the GiveWP Profile Editor widget. The Profile Editor has no settings at all, just drop it on your page and it\'s ready to go.', 'dw4elementor') . '</p>
						<p class="dw4e-docs-links">
							<a href="https://givewp.com/documentation/core/shortcodes/give_profile_editor/?utm_source=plugin_settings&utm_medium=referral&utm_campaign=Free_Addons&utm_content=dw4elementor" rel="noopener noreferrer" target="_blank"><i class="fa fa-book" aria-hidden="true"></i>' . __('Visit the GiveWP Docs for more info on the GiveWP Profile Editor.', 'dw4elementor') . '</a>
						</p>
				</div>'
            ]
        );

        $this->end_controls_section();
    }

    /**
     * Render the [give_profile_editor] output on the frontend.
     *
     * Written in PHP and used to generate the final HTML.
     *
     * @unreleased migrated from givewp-elementor-widgets
     * @access protected
     */
    protected function render()
    {
        $html = do_shortcode(
            '[give_profile_editor]'
        );

        echo '<div class="givewp-elementor-widget give-login-shortcode-wrap">' . $html . '</div>';
    }
}
