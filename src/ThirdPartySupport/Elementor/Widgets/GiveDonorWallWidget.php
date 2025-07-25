<?php

namespace Give\ThirdPartySupport\Elementor\Widgets;

use Elementor\Widget_Base;

/**
 * Elementor Give Donor Wall Widget.
 *
 * Elementor widget that inserts the GiveWP [give_donor_wall] shrotcode to output a form total with options.
 *
 * @unreleased migrated from givewp-elementor-widgets
 */

class GiveDonorWallWidget extends Widget_Base
{
    /**
     * Get widget name.
     *
     * Retrieve Give Donor Wall widget name.
     *
     * @unreleased migrated from givewp-elementor-widgets
     * @access public
     *
     * @return string Widget name.
     */
    public function get_name()
    {
        return 'Give_Donor_Wall';
    }

    /**
     * Get widget title.
     *
     * Retrieve Give Donor Wall widget title.
     *
     * @unreleased migrated from givewp-elementor-widgets
     * @access public
     *
     * @return string Widget title.
     */
    public function get_title()
    {
        return __('Give Donor Wall', 'dw4elementor');
    }

    /**
     * Get widget icon.
     *
     * Retrieve Give Donor Wall widget icon.
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
     * Retrieve the list of categories the Give Donor Wall widget belongs to.
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
     * Register Give Donor Wall widget controls.
     *
     * Adds different input fields to allow the user to change and customize the widget settings.
     *
     * @unreleased migrated from givewp-elementor-widgets
     * @access protected
     */
    protected function register_controls()
    {
        $this->start_controls_section(
            'give_donor_wall_settings',
            [
                'label' => __('GiveWP Donor Wall Widget', 'dw4elementor'),
                'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
            ]
        );

        $this->add_control(
            'donors_per_page',
            [
                'label' => __('Donors per Page', 'dw4elementor'),
                'type' => \Elementor\Controls_Manager::NUMBER,
                'description' => __('Set how many donors will show before the "Show More" button appears.', 'dw4elementor'),
                'min'	=> '1',
                'max'	=> '30',
                'default' => '12'
            ]
        );

        $this->add_control(
            'all_forms',
            [
                'label' => __('Show All Donors?', 'dw4elementor'),
                'type' => \Elementor\Controls_Manager::CHOOSE,
                'description' => __('Do you want to show all donors here, or choose one form?', 'dw4elementor'),
                'options' => [
                    'yes' => [
                        'title' => __('Yes', 'dw4elementor'),
                        'icon' => 'fa fa-check',
                    ],
                    'no' => [
                        'title' => __('No', 'dw4elementor'),
                        'icon' => 'fa fa-times-circle',
                    ],
                ],
                'default' => 'yes',
                'toggle' => true
            ]
        );

        $this->add_control(
            'form_id',
            [
                'label' => __('Form ID', 'dw4elementor'),
                'type' => \Elementor\Controls_Manager::TEXT,
                'description' => __('What is the form ID of the form you want to pull the donors from?', 'dw4elementor'),
                'condition' => [
                    'all_forms' => 'no'
                ],
                'input_type' => 'number'
            ]
        );

        $this->add_control(
            'orderby',
            [
                'label' => __('Order By', 'dw4elementor'),
                'type' => \Elementor\Controls_Manager::SELECT,
                'description' => __('The element which the order will be determined by.', 'dw4elementor'),
                'options' => [
                    'post_date' => __('Donation Date', 'dw4elementor'),
                    'donation_amount' => __('Donation Amount', 'dw4elementor')
                ],
                'default' => 'post_date'
            ]
        );

        $this->add_control(
            'order',
            [
                'label' => __('Order By', 'dw4elementor'),
                'type' => \Elementor\Controls_Manager::SELECT,
                'description' => __('The order the donors will be displayed in according to the "Order By" field chosen.', 'dw4elementor'),
                'options' => [
                    'desc' => __('Descending', 'dw4elementor'),
                    'asc' => __('Ascending', 'dw4elementor')
                ],
                'default' => 'desc'
            ]
        );

        $this->add_control(
            'columns',
            [
                'label' => __('Number of Columns', 'dw4elementor'),
                'type' => \Elementor\Controls_Manager::SELECT,
                'description' => __('The number of columns to display. Note that "Best Fit" will always stretch to fill the available width that the donor wall is placed within.', 'dw4elementor'),
                'options' => [
                    'best-fit' => __('Best Fit', 'dw4elementor'),
                    '1' => '1',
                    '2' => '2',
                    '3' => '3',
                    '4' => '4',
                ],
                'default' => 'best-fit'
            ]
        );

        $this->add_control(
            'show_avatar',
            [
                'label' => __('Show Avatar', 'dw4elementor'),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'description' => __('Show/Hide the donor avatar. Will display the donor\'s initials if no avatar is supported for their email address.', 'dw4elementor'),
                'label_on' => __('Show', 'dw4elementor'),
                'label_off' => __('Hide', 'dw4elementor'),
                'return_value' => 'yes',
                'default' => 'yes',
            ]
        );

        $this->add_control(
            'avatar_size',
            [
                'label' => __('Show Avatar', 'dw4elementor'),
                'type' => \Elementor\Controls_Manager::NUMBER,
                'description' => __('Set the size of the Avatar in pixels.', 'dw4elementor'),
                'default' => '60',
                'condition' => [
                    'show_avatar' => 'yes'
                ],
            ]
        );

        $this->add_control(
            'show_name',
            [
                'label' => __('Show Name', 'dw4elementor'),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'description' => __('Show/Hide the donor\'s name.', 'dw4elementor'),
                'label_on' => __('Show', 'dw4elementor'),
                'label_off' => __('Hide', 'dw4elementor'),
                'return_value' => 'yes',
                'default' => 'yes',
            ]
        );

        $this->add_control(
            'show_total',
            [
                'label' => __('Show Total', 'dw4elementor'),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'description' => __('Show/Hide the donation amount.', 'dw4elementor'),
                'label_on' => __('Show', 'dw4elementor'),
                'label_off' => __('Hide', 'dw4elementor'),
                'return_value' => 'yes',
                'default' => 'yes',
            ]
        );

        $this->add_control(
            'show_time',
            [
                'label' => __('Show Date', 'dw4elementor'),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'description' => __('Show/Hide the date the donation was made.', 'dw4elementor'),
                'label_on' => __('Show', 'dw4elementor'),
                'label_off' => __('Hide', 'dw4elementor'),
                'return_value' => 'yes',
                'default' => 'yes',
            ]
        );

        $this->add_control(
            'show_comments',
            [
                'label' => __('Show Comments', 'dw4elementor'),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'description' => __('Show/Hide the donor comment if any was provided by the donor.', 'dw4elementor'),
                'label_on' => __('Show', 'dw4elementor'),
                'label_off' => __('Hide', 'dw4elementor'),
                'return_value' => 'yes',
                'default' => 'yes',
            ]
        );

        $this->add_control(
            'comment_length',
            [
                'label' => __('Comment length in characters', 'dw4elementor'),
                'type' => \Elementor\Controls_Manager::NUMBER,
                'description' => __('Number of characters to allow to be shown in the donor comments. This can help make each donor card a more consistent height for appearance purposes.', 'dw4elementor'),
                'default' => '140',
                'condition' => [
                    'show_comments' => 'yes'
                ]
            ]
        );

        $this->add_control(
            'only_comments',
            [
                'label' => __('Only Show Donations with Comments', 'dw4elementor'),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'description' => __('Only show donations that have comments.', 'dw4elementor'),
                'label_on' => __('Show', 'dw4elementor'),
                'label_off' => __('Hide', 'dw4elementor'),
                'return_value' => 'yes',
                'default' => 'no',
                'condition' => [
                    'show_comments' => 'yes'
                ]
            ]
        );

        $this->add_control(
            'anonymous',
            [
                'label' => __('Show Anonymous Donations', 'dw4elementor'),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'description' => __('Include/Exclude donations made that selected to be "Anonymous". They will be shown without the name or comments.', 'dw4elementor'),
                'label_on' => __('Show', 'dw4elementor'),
                'label_off' => __('Hide', 'dw4elementor'),
                'return_value' => 'yes',
                'default' => 'yes',
            ]
        );

        $this->add_control(
            'loadmore_text',
            [
                'label' => __('Load More Text', 'dw4elementor'),
                'type' => \Elementor\Controls_Manager::TEXT,
                'description' => __('The text that appears on the "Load More" button if you have multiple "pages" of donor cards beyond what the "Donors per Page" setting above is set to.', 'dw4elementor'),
                'label_block' => true,
                'default' => 'Read more',
            ]
        );

        $this->add_control(
            'readmore_text',
            [
                'label' => __('Read More Text', 'dw4elementor'),
                'type' => \Elementor\Controls_Manager::TEXT,
                'description' => __('The text that appears on the "Read More" link if your donor comments are truncated because of the comment length.', 'dw4elementor'),
                'label_block' => true,
                'default' => 'Read more',
            ]
        );

        $this->add_control(
            'give_donor_wall_info',
            [
                'label' => '',
                'type' => \Elementor\Controls_Manager::RAW_HTML,
                'content_classes' => 'dw4e-info',
                'raw' => '
					<div class="dw4e">
						<p class="info-head">
							' . __('GIVEWP DONOR WALL WIDGET', 'dw4elementor') . '</p>
						<p class="info-message">' . __('This is the GiveWP Donor Wall widget. Choose the elements you want to see appear in the donor wall. Note that the live preview only works if you have existing donors to display.', 'dw4elementor') . '</p>
						<p class="dw4e-docs-links">
							<a href="https://givewp.com/documentation/core/shortcodes/give_donor_wall/?utm_source=plugin_settings&utm_medium=referral&utm_campaign=Free_Addons&utm_content=dw4elementor" rel="noopener noreferrer" target="_blank"><i class="fa fa-book" aria-hidden="true"></i>' . __('Visit the GiveWP Docs for more info on the GiveWP Donor Wall.', 'dw4elementor') . '</a>
						</p>
				</div>'
            ]
        );

        $this->end_controls_section();
    }

    /**
     * Render the [give_donor_wall] output on the frontend.
     *
     * Written in PHP and used to generate the final HTML.
     *
     * @unreleased migrated from givewp-elementor-widgets
     * @access protected
     */
    protected function render()
    {
        global $give_receipt_args, $donation;

        $settings = $this->get_settings_for_display();

        $forms = ('yes' === $settings['all_forms'] ? '' : $settings['form_id']);
        $paged = esc_html($settings['donors_per_page']);
        $columns = esc_html($settings['columns']);
        $anonymous = esc_html($settings['anonymous']);
        $showAvatar = esc_html($settings['show_avatar']);
        $showName = esc_html($settings['show_name']);
        $showTotal = esc_html($settings['show_total']);
        $showTime = esc_html($settings['show_time']);
        $showComments = esc_html($settings['show_comments']);
        $commentLength = esc_html($settings['comment_length']);
        $onlyComments = esc_html($settings['only_comments']);
        $readmoreText = esc_html($settings['readmore_text']);
        $loadmoreText = esc_html($settings['loadmore_text']);
        $avatarSize = esc_html($settings['avatar_size']);
        $orderby = esc_html($settings['orderby']);
        $order = esc_html($settings['order']);

        $html = do_shortcode('
			[give_donor_wall
				donors_per_page="' . $paged . '"
				form_id="' . $forms . '"
				columns="' . $columns . '"
				anonymous="' . $anonymous . '"
				show_avatar="' . $showAvatar . '"
				show_name="' . $showName . '"
				show_total="' . $showTotal . '"
				show_time="' . $showTime . '"
				show_comments="' . $showComments . '"
				comment_length="' . $commentLength . '"
				only_comments="' . $onlyComments . '"
				readmore_text="' . $readmoreText . '"
				loadmore_text="' . $loadmoreText . '"
				avatar_size="' . $avatarSize . '"
				order="' . $order . '"
				orderby="' . $orderby .
                '"]'
        );

        echo '<div class="givewp-elementor-widget give-donor-wall-shortcode-wrap">';

        echo $html;

        echo '</div>';
    }
}
