<?php

namespace Give\ThirdPartySupport\Elementor\Widgets\V1;

use Elementor\Widget_Base;

/**
 * Elementor Give Form Grid Widget.
 *
 * Elementor widget that inserts the GiveWP [give_form_grid] shortcode to output a form total with options.
 *
 * @since 4.7.0 migrated from givewp-elementor-widgets
 */

class GiveFormGridWidget extends Widget_Base
{
    /**
     * Get widget name.
     *
     * Retrieve Give Form Grid widget name.
     *
     * @since 4.7.0 migrated from givewp-elementor-widgets
     * @access public
     *
     * @return string Widget name.
     */
    public function get_name()
    {
        return 'GiveWP_Form_Grid';
    }

    /**
     * Get widget title.
     *
     * Retrieve Give Form Grid widget title.
     *
     * @since 4.7.0 migrated from givewp-elementor-widgets
     * @access public
     *
     * @return string Widget title.
     */
    public function get_title()
    {
        return __('Give Form Grid (Legacy)', 'give');
    }

    /**
     * Get widget icon.
     *
     * Retrieve Give Form Grid widget icon.
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
     * Retrieve the list of categories the Give Form Grid widget belongs to.
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
     * Register Give Form Grid widget controls.
     *
     * Adds different input fields to allow the user to change and customize the widget settings.
     *
     * @since 4.7.0 migrated from givewp-elementor-widgets
     * @access protected
     */
    protected function register_controls()
    {
        $this->start_controls_section(
            'give_form_grid_settings',
            [
                'label' => __('GiveWP Form Grid Widget', 'give'),
                'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
            ]
        );

        $this->add_control(
            'forms_cats_tags',
            [
                'label' => __('Collection Method', 'plugin-domain'),
                'type' => \Elementor\Controls_Manager::SELECT,
                'description' => __('Would you like to show forms by ID, by their Categories, or by their Tags?', 'give'),
                'options' => [
                    'forms' => __('Forms', 'plugin-domain'),
                    'cats' => __('Categories', 'plugin-domain'),
                    'tags' => __('Tags', 'plugin-domain'),
                ],
                'default' => 'forms',
            ]
        );

        $this->add_control(
            'all_forms',
            [
                'label' => __('Show All Forms?', 'give'),
                'type' => \Elementor\Controls_Manager::CHOOSE,
                'description' => __('Do you want to show all forms in your grid?', 'give'),
                'options' => [
                    'yes' => [
                        'title' => __('Yes', 'give'),
                        'icon' => 'fa fa-check',
                    ],
                    'no' => [
                        'title' => __('No', 'give'),
                        'icon' => 'fa fa-times-circle',
                    ],
                ],
                'default' => 'yes',
                'toggle' => true,
                'condition' => [
                    'forms_cats_tags' => 'forms'
                ]
            ]
        );

        $this->add_control(
            'form_ids',
            [
                'label' => __('Show by Form IDs', 'give'),
                'type' => \Elementor\Controls_Manager::TEXT,
                'description' => __('Place the form IDs you\'d like to show in your grid here, separated by commas.', 'give'),
                'condition' => [
                    'all_forms' => 'no',
                    'forms_cats_tags' => 'forms'
                ]
            ]
        );

        $this->add_control(
            'cats',
            [
                'label' => __('Show by Form Categories', 'give'),
                'type' => \Elementor\Controls_Manager::TEXT,
                'description' => __('Place the form category IDs you\'d like to show in your grid here, separated by commas.', 'give'),
                'condition' => [
                    'forms_cats_tags' => 'cats'
                ]
            ]
        );

        $this->add_control(
            'tags',
            [
                'label' => __('Show by Form Tags', 'give'),
                'type' => \Elementor\Controls_Manager::TEXT,
                'description' => __('Place the form category IDs you\'d like to show in your grid here, separated by commas.', 'give'),
                'condition' => [
                    'forms_cats_tags' => 'tags'
                ]
            ]
        );

        $this->add_control(
            'exclude',
            [
                'label' => __('Exclude Forms', 'give'),
                'type' => \Elementor\Controls_Manager::TEXT,
                'description' => __('List the form IDs that you want excluded from this Form Grid.', 'give'),
            ]
        );

        $this->add_control(
            'forms_per_page',
            [
                'label' => __('Forms Per Page', 'give'),
                'type' => \Elementor\Controls_Manager::SELECT,
                'description' => __('The number of forms to show in the grid before the "Load More" button appears.', 'give'),
                'options' => [
                    '1' => '1',
                    '2' => '2',
                    '3' => '3',
                    '4' => '4',
                    '5' => '5',
                    '6' => '6',
                    '7' => '7',
                    '8' => '8',
                    '9' => '9',
                    '10' => '10',
                    '11' => '11',
                    '12' => '12',
                ],
                'default' => '12'
            ]
        );

        $this->add_control(
            'paged',
            [
                'label' => __('Show Pagination', 'give'),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'label_on' => __('Show', 'give'),
                'label_off' => __('Hide', 'give'),
                'default' => 'yes',
            ]
        );

        $this->add_control(
            'orderby',
            [
                'label' => __('Order By', 'give'),
                'type' => \Elementor\Controls_Manager::SELECT,
                'description' => __('The element which the order will be determined by.', 'give'),
                'options' => [
                    'date' => __('Date Created', 'give'),
                    'title' => __('Form Name', 'give'),
                    'amount_donated' => __('Amount Donated', 'give'),
                    'number_donations' => __('Number of Donations', 'give'),
                    'menu_order' => __('Menu Order', 'give'),
                    'post__in' => __('Form ID', 'give'),
                    'closest_to_goal' => __('Closest to Goal', 'give'),
                ],
                'default' => 'date'
            ]
        );

        $this->add_control(
            'order',
            [
                'label' => __('Order By', 'give'),
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
                'description' => __('The number of columns to display. Note that "Best Fit" will always stretch to fill the available width that the donor wall is placed within.', 'give'),
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

        $this->add_control(
            'show_title',
            [
                'label' => __('Show Form Title', 'give'),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'description' => __('Show/Hide the form title.', 'give'),
                'label_on' => __('Show', 'give'),
                'label_off' => __('Hide', 'give'),
                'default' => 'yes',
            ]
        );

        $this->add_control(
            'show_excerpt',
            [
                'label' => __('Show Form Excerpt', 'give'),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'description' => __('Show/Hide the form excerpt.', 'give'),
                'label_on' => __('Show', 'give'),
                'label_off' => __('Hide', 'give'),
                'default' => 'yes',
            ]
        );

        $this->add_control(
            'excerpt_length',
            [
                'label' => __('Excerpt Length', 'give'),
                'type' => \Elementor\Controls_Manager::TEXT,
                'description' => __('Excerpt Length.', 'give'),
                'condition' => [
                    'show_excerpt' => 'yes'
                ]
            ]
        );

        $this->add_control(
            'show_goal',
            [
                'label' => __('Show Form Goal', 'give'),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'description' => __('Show/Hide the form goal.', 'give'),
                'label_on' => __('Show', 'give'),
                'label_off' => __('Hide', 'give'),
                'default' => 'yes',
            ]
        );

        $this->add_control(
            'show_featured_image',
            [
                'label' => __('Show Form Featured Image', 'give'),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'description' => __('Show/Hide the form featured image.', 'give'),
                'label_on' => __('Show', 'give'),
                'label_off' => __('Hide', 'give'),
                'default' => 'yes',
            ]
        );

        $this->add_control(
            'image_size',
            [
                'label' => __('Image Size', 'give'),
                'type' => \Elementor\Controls_Manager::TEXT,
                'description' => __('Featured image size. Default "medium". Accepts WordPress image sizes.', 'give'),
                'condition' => [
                    'show_featured_image' => 'yes'
                ]
            ]
        );

        $this->add_control(
            'image_height',
            [
                'label' => __('Image Height', 'give'),
                'type' => \Elementor\Controls_Manager::TEXT,
                'description' => __('Featured image height. Default "auto". Accepts valid CSS heights', 'give'),
                'condition' => [
                    'show_featured_image' => 'yes'
                ]
            ]
        );

        $this->add_control(
            'show_donate_button',
            [
                'label' => __('Show Donate Button', 'give'),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'label_on' => __('Show', 'give'),
                'label_off' => __('Hide', 'give'),
                'default' => 'yes',
            ]
        );

        $this->add_control(
            'donate_button_text_color',
            [
                'label' => __('Donate Button Text Color', 'give'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'default' => '#69B86B',
                'condition' => [
                    'show_donate_button' => 'yes'
                ],
            ]
        );

        $this->add_control(
            'display_style',
            [
                'label' => __('Display Type', 'give'),
                'type' => \Elementor\Controls_Manager::SELECT,
                'description' => __('Choose the behavior when a form is clicked on within the grid.', 'give'),
                'options' => [
                    'redirect' => __('Redirect to the single Form Page.', 'give'),
                    'modal_reveal' => __('Open the form in a modal window.', 'give'),
                ],
                'default' => 'redirect'
            ]
        );

        $this->add_control(
            'give_form_grid_info',
            [
                'label' => '',
                'type' => \Elementor\Controls_Manager::RAW_HTML,
                'content_classes' => 'give-info',
                'raw' => '
					<div class="give">
						<p class="info-head">
							' . __('GIVEWP FORM GRID WIDGET', 'give') . '</p>
						<p class="info-message">' . __('This is the GiveWP Form Grid widget. Choose the elements you want to see appear in the form grid.', 'give') . '</p>
						<p class="give-docs-links">
							<a href="https://givewp.com/documentation/core/shortcodes/give_form_grid/?utm_source=plugin_settings&utm_medium=referral&utm_campaign=Free_Addons&utm_content=givelementor" rel="noopener noreferrer" target="_blank"><i class="fa fa-book" aria-hidden="true"></i>' . __('Visit the GiveWP Docs for more info on the GiveWP Form Grid.', 'give') . '</a>
						</p>
				</div>'
            ]
        );

        $this->end_controls_section();
    }

    /**
     * Render the [give_form_grid] output on the frontend.
     *
     * Written in PHP and used to generate the final HTML.
     *
     * @since 4.7.0 migrated from givewp-elementor-widgets
     * @access protected
     */
    protected function render()
    {
        global $give_receipt_args, $donation;

        $settings = $this->get_settings_for_display();

        $forms = ('yes' === $settings['all_forms'] ? '' : $settings['form_ids']);
        $formsPerPage = esc_html($settings['forms_per_page']);
        $paged = esc_html($settings['paged']);
        $columns = esc_html($settings['columns']);
        $orderby = esc_html($settings['orderby']);
        $order = esc_html($settings['order']);
        $exclude = esc_html($settings['exclude']);
        $cats = esc_html($settings['cats']);
        $tags = esc_html($settings['tags']);
        $showTitle = esc_html($settings['show_title']);
        $showGoal = esc_html($settings['show_goal']);
        $showExcerpt = esc_html($settings['show_excerpt']);
        $excerptLength = esc_html($settings['excerpt_length']);
        $showFeaturedImage = esc_html($settings['show_featured_image']);
        $displayStyle = esc_html($settings['display_style']);
        $imageSize = esc_html($settings['image_size']);
        $imageHeight = esc_html($settings['image_height']);
        $showDonateButton = esc_html($settings['show_donate_button']);
        $buttonTextColor = esc_html($settings['donate_button_text_color']);

        $html = do_shortcode('
			[give_form_grid
				forms_per_page="' . $formsPerPage . '"
				paged="' . $paged . '"
				ids="' . $forms . '"
				columns="' . $columns . '"
				order="' . $order . '"
				exclude="' . $exclude . '"
				cats="' . $cats . '"
				tags="' . $tags . '"
				show_title="' . $showTitle . '"
				show_goal="' . $showGoal . '"
				show_excerpt="' . $showExcerpt . '"
				excerpt_length="' . $excerptLength . '"
				show_featured_image="' . $showFeaturedImage . '"
				image_size="' . $imageSize . '"
				image_height="' . $imageHeight . '"
				show_donate_button="' . $showDonateButton . '"
				donate_button_text_color="' . $buttonTextColor . '"
				display_style="' . $displayStyle . '"
				orderby="' . $orderby .
                '"]'
        );

        echo '<div class="givewp-elementor-widget give-form-grid-shortcode-wrap">';

        echo $html;

        echo '</div>';
    }
}
