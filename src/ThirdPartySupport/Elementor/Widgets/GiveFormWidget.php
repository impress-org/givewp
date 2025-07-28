<?php

namespace Give\ThirdPartySupport\Elementor\Widgets;

use Give\Framework\Database\DB;
use Give\Helpers\Form\Utils;
use Elementor\Widget_Base;

/**
 * Elementor Give Form Widget.
 *
 * Elementor widget that inserts the GiveWP [give_form] shrotcode to output a form total with options.
 *
 * @unreleased migrated from givewp-elementor-widgets
 */
class GiveFormWidget extends Widget_Base
{
    /**
     * Get widget name.
     *
     * Retrieve Give Form widget name.
     *
     * @unreleased migrated from givewp-elementor-widgets
     * @access public
     *
     * @return string Widget name.
     */
    public function get_name()
    {
        return 'Give Form';
    }

    /**
     * Get widget title.
     *
     * Retrieve Give Form widget title.
     *
     * @unreleased migrated from givewp-elementor-widgets
     * @access public
     *
     * @return string Widget title.
     */
    public function get_title()
    {
        return __('GiveWP Donation Form', 'give');
    }

    /**
     * Get widget icon.
     *
     * Retrieve Give Form widget icon.
     *
     * @unreleased migrated from givewp-elementor-widgets
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
     * Retrieve the list of categories the Give Form widget belongs to.
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
     * Register Give Form widget controls.
     *
     * Adds different input fields to allow the user to change and customize the widget settings.
     *
     * @unreleased migrated from givewp-elementor-widgets
     * @access protected
     */
    protected function register_controls()
    {
        $forms        = $this->getDonationFormsOptions();
        $v3Forms      = $this->getV3Forms($forms);

        $this->start_controls_section(
            'give_form_settings',
            [
                'label' => __('GiveWP Form Widget', 'give'),
                'tab'   => \Elementor\Controls_Manager::TAB_CONTENT,
            ]
        );

        $this->add_control(
            'form_id',
            [
                'label'       => __('Donation Form', 'give'),
                'type'        => \Elementor\Controls_Manager::SELECT,
                'description' => __('Choose the GiveWP Form you want to embed.', 'give'),
                'default'     => !empty($forms) ? array_keys($forms)[0] : '',
                'options'     => $forms,
            ]
        );

        $this->add_control(
            'show_title',
            [
                'label'       => __('Show Form Title', 'give'),
                'type'        => \Elementor\Controls_Manager::SWITCHER,
                'description' => __('Show/hide the GiveWP form title.', 'give'),
                'label_on'    => __('Show', 'give'),
                'label_off'   => __('Hide', 'give'),
                'default'     => 'yes',
                'conditions'  => [
                    'terms' => [
                        [
                            'name'     => 'form_id',
                            'operator' => '!in',
                            'value'    => $v3Forms,
                        ],
                    ],
                ],
            ]
        );

        $this->add_control(
            'show_goal',
            [
                'label'        => __('Show Goal', 'give'),
                'type'         => \Elementor\Controls_Manager::SWITCHER,
                'description'  => __('Show/hide the progress bar and goal for this form.', 'give'),
                'label_on'     => __('Show', 'give'),
                'label_off'    => __('Hide', 'give'),
                'return_value' => 'yes',
                'default'      => 'yes',
                'conditions'   => [
                    'terms' => [
                        [
                            'name'     => 'form_id',
                            'operator' => '!in',
                            'value'    => $v3Forms,
                        ],
                    ],
                ],
            ]
        );

        $this->add_control(
            'show_content',
            [
                'label'        => __('Show Form Content', 'give'),
                'type'         => \Elementor\Controls_Manager::SWITCHER,
                'description'  => __('Show/hide the content of this form.', 'give'),
                'label_on'     => __('Show', 'give'),
                'label_off'    => __('Hide', 'give'),
                'return_value' => 'yes',
                'default'      => 'no',
                'conditions'   => [
                    'terms' => [
                        [
                            'name'     => 'form_id',
                            'operator' => '!in',
                            'value'    => $v3Forms,
                        ],
                    ],
                ],
            ]
        );

        $this->add_control(
            'display_style',
            [
                'label'       => __('Form Display Style', 'give'),
                'type'        => \Elementor\Controls_Manager::SELECT,
                'description' => __('Choose which display to use for this GiveWP form.', 'give'),
                'options'     => [
                    'onpage' => __('Full Form', 'give'),
                    'button' => __('Button Only', 'give'),
                    'modal'  => __('Modal Reveal', 'give'),
                    'reveal' => __('Reveal', 'give'),
                ],
                'default'     => 'onpage',
            ]
        );

        $this->add_control(
            'continue_button_title',
            [
                'label'       => __('Reveal Button Text', 'give'),
                'type'        => \Elementor\Controls_Manager::TEXT,
                'description' => __('Text on the button that reveals the form.', 'give'),
                'default'     => __('Continue to Donate', 'give'),
                'condition'   => [
                    'display_style!' => 'onpage',
                ],
            ]
        );

        $this->end_controls_section();
    }

    /**
     * Render the [give_form] output on the frontend.
     *
     * Written in PHP and used to generate the final HTML.
     *
     * @unreleased migrated from givewp-elementor-widgets
     * @access protected
     */
    protected function render()
    {
        $settings = $this->get_data('settings');

        $formId               = (int)$this->get_settings('form_id');
        $showTitle            = isset($settings['show_title']) ? $settings['show_title'] : 'true';
        $showGoal             = isset($settings['show_goal']) ? $settings['show_goal'] : 'true';
        $showContent          = isset($settings['show_content']) ? $settings['show_content'] : 'true';
        $displayStyle         = isset($settings['display_style']) ? $settings['display_style'] : 'onpage';
        $continueButtonTitle = isset($settings['continue_button_title']) ? $settings['continue_button_title'] : __('Continue to Donate', 'give');

        $shortcode = sprintf(
            '[give_form id="%s" show_title="%s" show_goal="%s" show_content="%s" display_style="%s" continue_button_title="%s"]',
            $formId,
            $showTitle,
            $showGoal,
            $showContent,
            $displayStyle,
            $continueButtonTitle
        );

        echo '<div class="givewp-elementor-widget give-form-shortcode-wrap">';

        echo do_shortcode($shortcode);

        echo '</div>';
    }

    /**
     * @return array
     */
    private function getDonationFormsOptions()
    {
        $options = [];

        $forms = DB::table('posts')
                   ->select('ID', 'post_title')
                   ->where('post_type', 'give_forms')
                   ->where('post_status', 'publish')
                   ->getAll();

        foreach ($forms as $form) {
            $options[$form->ID] = $form->post_title;
        }

        return $options;
    }

    /**
     * Get v3 forms from list of forms returned by GiveFormWidget::getDonationFormsOptions
     *
     * @unlreased
     *
     * @param array $forms
     *
     * @return array
     */
    private function getV3Forms($forms)
    {
        $data = [];

        foreach (array_keys($forms) as $formId) {
            if (Utils::isV3Form((int)$formId)) {
                $data[] = (string)$formId;
            }
        }

        return $data;
    }

    /**
     * Get custom help URL.
     *
     * @inheritDoc
     *
     * @unreleased
     */
    public function get_custom_help_url(): string
    {
        return 'https://givewp.com/documentation/core/shortcodes/give_form/?utm_source=plugin_settings&utm_medium=referral&utm_campaign=Free_Addons&utm_content=givelementor';
    }
}
