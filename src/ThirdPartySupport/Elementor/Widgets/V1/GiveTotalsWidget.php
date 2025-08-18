<?php

namespace Give\ThirdPartySupport\Elementor\Widgets\V1;

use Give\Framework\Database\DB;
use Elementor\Widget_Base;

/**
 * Elementor Give Totals Widget.
 *
 * Elementor widget that inserts the GiveWP [give_totals] shortcode to output a form total with options.
 *
 * @since 4.7.0 migrated from givewp-elementor-widgets
 */

class GiveTotalsWidget extends Widget_Base
{
    /**
     * Get widget name.
     *
     * Retrieve Give Totals widget name.
     *
     * @since 4.7.0 migrated from givewp-elementor-widgets
     * @access public
     *
     * @return string Widget name.
     */
    public function get_name()
    {
        return 'Give Totals';
    }

    /**
     * Get widget title.
     *
     * Retrieve Give Totals widget title.
     *
     * @since 4.7.0 migrated from givewp-elementor-widgets
     * @access public
     *
     * @return string Widget title.
     */
    public function get_title()
    {
        return __('Give Totals (Legacy)', 'give');
    }

    /**
     * Get widget icon.
     *
     * Retrieve Give Totals widget icon.
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
     * Retrieve the list of categories the Give Totals widget belongs to.
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
     * Register Give Totals widget controls.
     *
     * Adds different input fields to allow the user to change and customize the widget settings.
     *
     * @since 4.7.0 migrated from givewp-elementor-widgets
     * @access protected
     */
    protected function register_controls()
    {
        $this->start_controls_section(
            'give_totals_settings',
            [
                'label' => __('GiveWP Totals Widget', 'give'),
                'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
            ]
        );

        $this->add_control(
            'forms',
            [
                'label' => __('Forms', 'give'),
                'type' => \Elementor\Controls_Manager::SELECT2,
                'description' => __('Choose the forms you want to combine in this total.', 'give'),
                'separator' => 'after',
                'options' => $this->getDonationForms(),
                'multiple' => true,
                'default' => '',
            ]
        );

        $this->add_control(
            'total_goal',
            [
                'label' => __('Goal Amount', 'give'),
                'type' => \Elementor\Controls_Manager::TEXT,
                'description' => __('Designate a goal amount.', 'give'),
                'default' => '10000'
            ]
        );

        $this->add_control(
            'message',
            [
                'label' => __('Message:', 'give'),
                'type' => \Elementor\Controls_Manager::TEXTAREA,
                'description' => __('(Optional) The total goal you want to reflect in your message.', 'give'),
                'default' => __('Hey! We\'ve raised {total} of the {total_goal} we are trying to raise for this campaign!', 'give'),
            ]
        );

        $this->add_control(
            'link',
            [
                'label' => __('Link', 'give'),
                'type' => \Elementor\Controls_Manager::URL,
                'description' => __('The url of where you want to link donors to encourage them to donate toward the goal.', 'give'),
                'show_external' => false,
                'default' => [
                    'url' => 'https://example.org',
                    'is_external' => false,
                    'nofollow' => false,
                ],
            ]
        );

        $this->add_control(
            'link_text',
            [
                'label' => __('Link Text', 'give'),
                'type' => \Elementor\Controls_Manager::TEXTAREA,
                'description' => __('The text you want to hyperlink with your link.', 'give'),
                'default' => __('Donate Now', 'give'),
            ]
        );

        $this->add_control(
            'show_progress',
            [
                'label' => __('Show Progress', 'give'),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'description' => __('Show or hide a the progress bar.', 'give'),
                'label_on' => __('Show', 'give'),
                'label_off' => __('Hide', 'give'),
                'default' => 'yes'
            ]
        );

        $this->add_control(
            'cats',
            [
                'label' => __('Categories', 'give'),
                'type' => \Elementor\Controls_Manager::TEXT,
                'description' => __('Comma separated list of GiveWP category IDs you want to combine into this total.', 'give'),
            ]
        );

        $this->add_control(
            'tags',
            [
                'label' => __('Tags', 'give'),
                'type' => \Elementor\Controls_Manager::TEXT,
                'description' => __('Comma separated list of GiveWP tag IDs you want to combine into this total.', 'give'),
            ]
        );

        $this->add_control(
            'give_totals_info',
            [
                'label' => '',
                'type' => \Elementor\Controls_Manager::RAW_HTML,
                'content_classes' => 'give-info',
                'raw' => '
					<div class="give">
						<p class="info-head">
							' . __('GIVEWP TOTALS WIDGET', 'give') . '</p>
						<p class="info-message">' . __('This is the GiveWP Totals widget. Use this to display a total of one or many forms.', 'give') . '</p>
						<p class="give-docs-links">
							<a href="https://givewp.com/documentation/core/shortcodes/give_totals/?utm_source=plugin_settings&utm_medium=referral&utm_campaign=Free_Addons&utm_content=givelementor" rel="noopener noreferrer" target="_blank"><i class="fa fa-book" aria-hidden="true"></i>' . __('Visit the GiveWP Docs for more info on the GiveWP Totals.', 'give') . '</a>
						</p>
				</div>'
            ]
        );

        $this->end_controls_section();
    }

    /**
     * Render the [give_totals] output on the frontend.
     *
     * Written in PHP and used to generate the final HTML.
     *
     * @since 4.7.0 migrated from givewp-elementor-widgets
     * @access protected
     */
    protected function render()
    {
        $settings = $this->get_settings_for_display();

        $goal = esc_html($settings['total_goal']);
        $message = esc_html($settings['message']);
        $link = esc_url($settings['link']['url']);
        $linkText = esc_html($settings['link_text']);
        $showProgress = ('yes' === $settings['show_progress'] ? 'progress_bar="true"' : 'progress_bar="false"');
        $cats = esc_html($settings['cats']);
        $tags = esc_html($settings['tags']);

        $html = do_shortcode('
			[give_totals
				ids="' . implode(',', $settings['forms']) . '"
				total_goal="' . $goal . '"
				message="' . $message . '"
				link_text="' . $linkText . '"
				link="' . $link . '" '
                . $showProgress . '
				cats="' . $cats . '"
				tags="' . $tags . '"]'
        );

        echo '<div class="givewp-elementor-widget give-totals-shortcode-wrap">';

        echo $html;

        echo '</div>';
    }

    /**
     * Get donation forms
     *
     * @since 4.7.0 migrated from givewp-elementor-widgets
     *
     * @return array
     */
    private function getDonationForms()
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
}
