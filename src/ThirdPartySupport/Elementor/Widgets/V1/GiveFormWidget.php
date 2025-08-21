<?php

namespace Give\ThirdPartySupport\Elementor\Widgets\V1;

use Give\DonationForms\Models\DonationForm;
use Give\Framework\Database\DB;
use Give\Helpers\Form\Utils;
use Elementor\Widget_Base;

/**
 * Elementor Give Form Widget.
 *
 * Elementor widget that inserts the GiveWP [give_form] shortcode to output a form total with options.
 *
 * @since 4.7.0 migrated from givewp-elementor-widgets
 */
class GiveFormWidget extends Widget_Base
{
    /**
     * Get widget name.
     *
     * Retrieve Give Form widget name.
     *
     * @since 4.7.0 migrated from givewp-elementor-widgets
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
     * @since 4.7.0 migrated from givewp-elementor-widgets
     * @access public
     *
     * @return string Widget title.
     */
    public function get_title()
    {
        return __('Give Donation Form (Legacy)', 'give');
    }

    /**
     * Get widget icon.
     *
     * Retrieve Give Form widget icon.
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
     * Retrieve the list of categories the Give Form widget belongs to.
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
     * Register Give Form widget controls.
     *
     * Adds different input fields to allow the user to change and customize the widget settings.
     *
     * @since 4.7.0 migrated from givewp-elementor-widgets
     * @access protected
     */
    protected function register_controls()
    {
        $forms        = $this->getDonationFormsOptions();
        $legacyForms  = $this->getLegacyForms($forms);
        $classicForms = $this->getClassicForms($forms);
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
                'label'       => __('Form ID', 'give'),
                'type'        => \Elementor\Controls_Manager::SELECT,
                'description' => __('Choose the GiveWP Form you want to embed.', 'give'),
                'default'     => '',
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
                            'value'    => $classicForms,
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
                            'operator' => 'in',
                            'value'    => $legacyForms,
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

        $this->add_control(
            'v3_notice',
            [
                'label'           => __('Important Note', 'give'),
                'type'            => \Elementor\Controls_Manager::RAW_HTML,
                'raw'             => esc_html__(
                    'Form Display Style changes will not be visible for Donation forms created using the Visual Form Builder. Save the page and view it on the front end.',
                    'give'
                ),
                'content_classes' => 'give-elementor-notice',
                'conditions'      => [
                    'terms' => [
                        [
                            'name'     => 'form_id',
                            'operator' => 'in',
                            'value'    => $v3Forms,
                        ],
                    ],
                ],
            ]
        );

        $this->add_control(
            'give_form_info',
            [
                'label'           => '',
                'type'            => \Elementor\Controls_Manager::RAW_HTML,
                'content_classes' => 'give-info',
                'raw'             => '
					<div class="give">
						<p class="info-head">
							' . __('GIVEWP FORM WIDGET', 'give') . '</p>
						<p class="info-message">' . __(
                        'This is the GiveWP Form widget. Choose which form you want to embed on this page with it\'s form "ID".',
                        'give'
                    ) . '</p>
						<p class="give-docs-links">
							<a href="https://givewp.com/documentation/core/shortcodes/give_form/?utm_source=plugin_settings&utm_medium=referral&utm_campaign=Free_Addons&utm_content=givelementor" rel="noopener noreferrer" target="_blank"><i class="fa fa-book" aria-hidden="true"></i>' . __(
                                         'Visit the GiveWP Docs for more info on the GiveWP Form.',
                                         'give'
                                     ) . '</a>
						</p>
				</div>',
            ]
        );

        $this->end_controls_section();
    }

    /**
     * Render the [give_form] output on the frontend.
     *
     * Written in PHP and used to generate the final HTML.
     *
     * @since 4.7.0 migrated from givewp-elementor-widgets
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

        if (isset($_POST['action']) && $_POST['action'] === 'elementor_ajax') {
            // is this v3 form?
            if (Utils::isV3Form($formId)) {
                if ($donationForm = DonationForm::find($formId)) {
                    $donationForm->settings->showHeading        = boolval($showTitle);
                    $donationForm->settings->enableDonationGoal = boolval($showGoal);
                    $donationForm->save();
                }
            } else {
                // For some strange reason, passing show_goal attr to give_form shortcode doesn't work, so in order for this to work we have to enable/disable goal by updating meta
                give_update_meta($formId, '_give_goal_option', $showGoal ? 'enabled' : 'disabled');
            }
        }

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
     * Get forms using legacy template from list of forms returned by GiveFormWidget::getDonationFormsOptions
     *
     * @since 4.7.0
     *
     * @param array $forms
     *
     * @return array
     */
    private function getLegacyForms($forms)
    {
        $data = [];

        foreach (array_keys($forms) as $formId) {
            if ('legacy' === $this->getFormTemplate($formId)) {
                $data[] = (string)$formId;
            }
        }

        return $data;
    }

    /**
     * Get forms using classic template from list of forms returned by GiveFormWidget::getDonationFormsOptions
     *
     * @since 4.7.0
     *
     * @param array $forms
     *
     * @return array
     */
    private function getClassicForms($forms)
    {
        $data = [];

        foreach (array_keys($forms) as $formId) {
            if ('classic' === $this->getFormTemplate($formId)) {
                $data[] = (string)$formId;
            }
        }

        return $data;
    }

    /**
     * Get v3 forms from list of forms returned by GiveFormWidget::getDonationFormsOptions
     *
     * @since 4.7.0
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
     * Get form template
     *
     * @since 4.7.0 migrated from givewp-elementor-widgets
     *
     * @param $formId
     *
     * @return string
     */
    private function getFormTemplate($formId)
    {
        return Give()->form_meta->get_meta($formId, '_give_form_template', true);
    }
}
