<?php

namespace Give\ThirdPartySupport\Elementor\Widgets\V2\ElementorDonationFormGridWidget;

use Elementor\Widget_Base;
use Give\DonationForms\AsyncData\Actions\LoadAsyncDataAssets;
use Give\ThirdPartySupport\Elementor\Traits\HasFormOptions;

/**
 * @since 4.7.0
 */
class ElementorDonationFormGridWidget extends Widget_Base
{
    use HasFormOptions;

    /**
     * @inheritDoc
     * @since 4.7.0
     */
    public function get_name(): string
    {
        return 'givewp_donation_form_grid';
    }

    /**
     * @inheritDoc
     * @since 4.7.0
     */
    public function get_title(): string
    {
        return __('GiveWP Donation Form Grid', 'give');
    }

    /**
     * @inheritDoc
     * @since 4.7.0
     */
    public function get_icon(): string
    {
        return 'give-icon';
    }

    /**
     * @inheritDoc
     * @since 4.7.0
     */
    public function get_categories(): array
    {
        return ['givewp-category'];
    }

    /**
     * @inheritDoc
     * @since 4.7.0
     */
    public function get_keywords(): array
    {
        return ['give', 'givewp', 'donation', 'form', 'grid', 'forms'];
    }

    /**
     * @inheritDoc
     * @since 4.7.0
     */
    public function get_custom_help_url(): string
    {
        return 'http://docs.givewp.com/shortcode-form-grid';
    }

    /**
     * @since 4.7.0
     */
    protected function get_upsale_data(): array
    {
        return [];
    }

    /**
     * @inheritDoc
     * @since 4.7.0
     */
    public function get_script_depends(): array
    {
        return [LoadAsyncDataAssets::handleName()];
    }

    /**
     * @inheritDoc
     * @since 4.7.0
     */
    public function get_style_depends(): array
    {
        return [LoadAsyncDataAssets::handleName()];
    }

    /**
     * @inheritDoc
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
     * Register Elementor controls for the widget.
     *
     * @return void
     * @since 4.7.0
     */
    protected function register_controls(): void
    {
        $this->start_controls_section(
            'form_grid_settings',
            [
                'label' => __('Form Grid Settings', 'give'),
            ]
        );

        $this->add_control(
            'forms_per_page',
            [
                'label' => __('Forms Per Page', 'give'),
                'type' => \Elementor\Controls_Manager::NUMBER,
                'description' => __('Sets the number of forms to display per page.', 'give'),
                'min' => 1,
                'max' => 50,
                'default' => 12
            ]
        );

        $this->add_control(
            'columns',
            [
                'label' => __('Columns', 'give'),
                'type' => \Elementor\Controls_Manager::SELECT,
                'description' => __('Sets the number of forms per row.', 'give'),
                'options' => [
                    '' => __('Best Fit', 'give'),
                    '1' => '1',
                    '2' => '2',
                    '3' => '3',
                    '4' => '4',
                ],
                'default' => ''
            ]
        );

        $this->add_control(
            'orderby',
            [
                'label' => __('Order By', 'give'),
                'type' => \Elementor\Controls_Manager::SELECT,
                'description' => __('Different parameter to set the order for the forms display in the form grid.', 'give'),
                'options' => [
                    '' => __('Date Created', 'give'),
                    'title' => __('Form Name', 'give'),
                    'amount_donated' => __('Amount Donated', 'give'),
                    'number_donations' => __('Number of Donations', 'give'),
                    'menu_order' => __('Menu Order', 'give'),
                    'post__in' => __('Provided Form IDs', 'give'),
                    'closest_to_goal' => __('Closest To Goal', 'give'),
                    'random' => __('Random', 'give'),
                ],
                'default' => ''
            ]
        );

        $this->add_control(
            'order',
            [
                'label' => __('Order', 'give'),
                'type' => \Elementor\Controls_Manager::SELECT,
                'description' => __('Display forms based on order.', 'give'),
                'options' => [
                    '' => __('Descending', 'give'),
                    'ASC' => __('Ascending', 'give'),
                ],
                'default' => ''
            ]
        );

        $this->add_control(
            'display_style',
            [
                'label' => __('Display Style', 'give'),
                'type' => \Elementor\Controls_Manager::SELECT,
                'description' => __('Show form as modal window or redirect to a new page?', 'give'),
                'options' => [
                    '' => __('Modal', 'give'),
                    'redirect' => __('Redirect', 'give'),
                ],
                'default' => ''
            ]
        );

        $this->end_controls_section();

        $this->start_controls_section(
            'form_selection_settings',
            [
                'label' => __('Form Selection', 'give'),
            ]
        );

        $this->add_control(
            'selection_type',
            [
                'label' => __('Form Selection', 'give'),
                'type' => \Elementor\Controls_Manager::SELECT,
                'description' => __('Choose how to select forms to display.', 'give'),
                'options' => [
                    'all' => __('All Forms', 'give'),
                    'include' => __('Include Specific Forms', 'give'),
                    'exclude' => __('Exclude Specific Forms', 'give'),
                ],
                'default' => 'all'
            ]
        );

        $this->add_control(
            'ids',
            [
                'label' => __('Include Forms', 'give'),
                'type' => \Elementor\Controls_Manager::SELECT2,
                'description' => __('Select specific forms to include in the grid.', 'give'),
                'multiple' => true,
                'options' => $this->getFormOptions(),
                'condition' => [
                    'selection_type' => 'include'
                ],
            ]
        );

        $this->add_control(
            'exclude',
            [
                'label' => __('Exclude Forms', 'give'),
                'type' => \Elementor\Controls_Manager::SELECT2,
                'description' => __('Select specific forms to exclude from the grid.', 'give'),
                'multiple' => true,
                'options' => $this->getFormOptions(),
                'condition' => [
                    'selection_type' => 'exclude'
                ],
            ]
        );

        $this->end_controls_section();

        $this->start_controls_section(
            'display_options',
            [
                'label' => __('Display Options', 'give'),
            ]
        );

        $this->add_control(
            'show_title',
            [
                'label' => __('Show Title', 'give'),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'description' => __('Show/Hide the form title.', 'give'),
                'label_on' => __('Show', 'give'),
                'label_off' => __('Hide', 'give'),
                'return_value' => 'true',
                'default' => 'true',
            ]
        );

        $this->add_control(
            'show_featured_image',
            [
                'label' => __('Show Featured Image', 'give'),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'description' => __('Do you want to display the featured image?', 'give'),
                'label_on' => __('Show', 'give'),
                'label_off' => __('Hide', 'give'),
                'return_value' => 'true',
                'default' => 'true',
            ]
        );

        $this->add_control(
            'image_size',
            [
                'label' => __('Image Size', 'give'),
                'type' => \Elementor\Controls_Manager::SELECT,
                'description' => __('Select the size of the featured image.', 'give'),
                'options' => [
                    'thumbnail' => __('Thumbnail', 'give'),
                    'medium' => __('Medium', 'give'),
                    'large' => __('Large', 'give'),
                    'full' => __('Full Size', 'give'),
                ],
                'default' => 'medium',
                'condition' => [
                    'show_featured_image' => 'true'
                ],
            ]
        );

        $this->add_control(
            'image_height_options',
            [
                'label' => __('Image Height', 'give'),
                'type' => \Elementor\Controls_Manager::SELECT,
                'description' => __('Set the height of the featured image.', 'give'),
                'options' => [
                    'auto' => __('Auto', 'give'),
                    'fixed' => __('Fixed', 'give'),
                ],
                'default' => 'auto',
                'condition' => [
                    'show_featured_image' => 'true'
                ],
            ]
        );

        $this->add_control(
            'image_height',
            [
                'label' => __('Image Height (px)', 'give'),
                'type' => \Elementor\Controls_Manager::NUMBER,
                'description' => __('Set a fixed height for images in pixels.', 'give'),
                'default' => 200,
                'condition' => [
                    'show_featured_image' => 'true',
                    'image_height_options' => 'fixed'
                ],
            ]
        );

        $this->add_control(
            'show_excerpt',
            [
                'label' => __('Show Excerpt', 'give'),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'description' => __('Do you want to display the excerpt?', 'give'),
                'label_on' => __('Show', 'give'),
                'label_off' => __('Hide', 'give'),
                'return_value' => 'true',
                'default' => 'true',
            ]
        );

        $this->add_control(
            'excerpt_length',
            [
                'label' => __('Excerpt Length (words)', 'give'),
                'type' => \Elementor\Controls_Manager::NUMBER,
                'description' => __('Number of words to display in the excerpt.', 'give'),
                'default' => 16,
                'condition' => [
                    'show_excerpt' => 'true'
                ],
            ]
        );

        $this->add_control(
            'show_goal',
            [
                'label' => __('Show Goal', 'give'),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'description' => __('Do you want to display the goal\'s progress bar?', 'give'),
                'label_on' => __('Show', 'give'),
                'label_off' => __('Hide', 'give'),
                'return_value' => 'true',
                'default' => 'true',
            ]
        );

        $this->add_control(
            'show_donate_button',
            [
                'label' => __('Show Donate Button', 'give'),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'description' => __('Show/Hide the donate button on each form.', 'give'),
                'label_on' => __('Show', 'give'),
                'label_off' => __('Hide', 'give'),
                'return_value' => 'true',
                'default' => 'true',
            ]
        );

        $this->add_control(
            'paged',
            [
                'label' => __('Enable Pagination', 'give'),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'description' => __('Enable pagination for the form grid.', 'give'),
                'label_on' => __('Enable', 'give'),
                'label_off' => __('Disable', 'give'),
                'return_value' => 'true',
                'default' => 'true',
            ]
        );

        $this->end_controls_section();

        $this->start_controls_section(
            'style_options',
            [
                'label' => __('Style Options', 'give'),
            ]
        );

        $this->add_control(
            'progress_bar_color',
            [
                'label' => __('Progress Bar Color', 'give'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'description' => __('Choose the color for the goal progress bar.', 'give'),
                'default' => '#69b86b',
                'condition' => [
                    'show_goal' => 'true',
                ],
            ]
        );

        $this->add_control(
            'tag_background_color',
            [
                'label' => __('Tag Background Color', 'give'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'description' => __('Background color for form tags.', 'give'),
                'default' => '#69b86b',
            ]
        );

        $this->add_control(
            'tag_text_color',
            [
                'label' => __('Tag Text Color', 'give'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'description' => __('Text color for form tags.', 'give'),
                'default' => '#ffffff',
            ]
        );

        $this->add_control(
            'donate_button_text_color',
            [
                'label' => __('Donate Button Text Color', 'give'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'description' => __('Text color for the donate button.', 'give'),
                'default' => '#69b86b',
                'condition' => [
                    'show_donate_button' => 'true'
                ],
            ]
        );

        $this->end_controls_section();

        $this->start_controls_section(
            'filter_options',
            [
                'label' => __('Filter Options', 'give'),
            ]
        );

        $this->add_control(
            'categories',
            [
                'label' => __('Categories', 'give'),
                'type' => \Elementor\Controls_Manager::SELECT2,
                'description' => __('Filter forms by specific categories.', 'give'),
                'multiple' => true,
                'options' => $this->getCategoryOptions(),
            ]
        );

        $this->add_control(
            'tags',
            [
                'label' => __('Tags', 'give'),
                'type' => \Elementor\Controls_Manager::SELECT2,
                'description' => __('Filter forms by specific tags.', 'give'),
                'multiple' => true,
                'options' => $this->getTagOptions(),
            ]
        );

        $this->end_controls_section();
    }

    /**
     * Get available GiveWP form categories.
     *
     * @return array
     * @since 4.7.0
     */
    protected function getCategoryOptions(): array
    {
        $categories = get_terms([
            'taxonomy' => 'give_forms_category',
            'hide_empty' => false,
        ]);

        $options = [];
        if (!is_wp_error($categories)) {
            foreach ($categories as $category) {
                $options[$category->term_id] = $category->name;
            }
        }

        return $options;
    }

    /**
     * Get available GiveWP form tags.
     *
     * @since 4.7.0
     */
    protected function getTagOptions(): array
    {
        $tags = get_terms([
            'taxonomy' => 'give_forms_tag',
            'hide_empty' => false,
        ]);

        $options = [];
        if (!is_wp_error($tags)) {
            foreach ($tags as $tag) {
                $options[$tag->term_id] = $tag->name;
            }
        }

        return $options;
    }

    /**
     * Render the widget output on the frontend.
     *
     * @since 4.7.0
     */
    protected function render(): void
    {
        $settings = $this->get_settings_for_display();

        // Build shortcode attributes
        $attributes = [];

        if (isset($settings['forms_per_page'])) {
            $attributes[] = sprintf('forms_per_page="%s"', esc_attr($settings['forms_per_page']));
        }

        if (isset($settings['columns']) && !empty($settings['columns'])) {
            $attributes[] = sprintf('columns="%s"', esc_attr($settings['columns']));
        }

        if (isset($settings['orderby']) && !empty($settings['orderby'])) {
            $attributes[] = sprintf('orderby="%s"', esc_attr($settings['orderby']));
        }

        if (isset($settings['order']) && !empty($settings['order'])) {
            $attributes[] = sprintf('order="%s"', esc_attr($settings['order']));
        }

        if (isset($settings['display_style']) && !empty($settings['display_style'])) {
            $attributes[] = sprintf('display_style="%s"', esc_attr($settings['display_style']));
        }

        // Handle form selection
        if (isset($settings['selection_type'])) {
            if ($settings['selection_type'] === 'include' && isset($settings['ids']) && !empty($settings['ids'])) {
                $ids = is_array($settings['ids']) ? implode(',', $settings['ids']) : $settings['ids'];
                $attributes[] = sprintf('ids="%s"', esc_attr($ids));
            } elseif ($settings['selection_type'] === 'exclude' && isset($settings['exclude']) && !empty($settings['exclude'])) {
                $exclude = is_array($settings['exclude']) ? implode(',', $settings['exclude']) : $settings['exclude'];
                $attributes[] = sprintf('exclude="%s"', esc_attr($exclude));
            }
        }

        // Handle categories and tags
        if (isset($settings['categories']) && !empty($settings['categories'])) {
            $cats = is_array($settings['categories']) ? implode(',', $settings['categories']) : $settings['categories'];
            $attributes[] = sprintf('cats="%s"', esc_attr($cats));
        }

        if (isset($settings['tags']) && !empty($settings['tags'])) {
            $tags = is_array($settings['tags']) ? implode(',', $settings['tags']) : $settings['tags'];
            $attributes[] = sprintf('tags="%s"', esc_attr($tags));
        }

        // Handle display options (only add if false to override defaults)
        if (isset($settings['show_title']) && $settings['show_title'] !== 'true') {
            $attributes[] = 'show_title="false"';
        }

        if (isset($settings['show_goal']) && $settings['show_goal'] !== 'true') {
            $attributes[] = 'show_goal="false"';
        }

        if (isset($settings['show_excerpt']) && $settings['show_excerpt'] !== 'true') {
            $attributes[] = 'show_excerpt="false"';
        }

        if (isset($settings['show_featured_image']) && $settings['show_featured_image'] !== 'true') {
            $attributes[] = 'show_featured_image="false"';
        }

        if (isset($settings['show_donate_button']) && $settings['show_donate_button'] !== 'true') {
            $attributes[] = 'show_donate_button="false"';
        }

        if (isset($settings['paged']) && $settings['paged'] !== 'true') {
            $attributes[] = 'paged="false"';
        }

        // Handle optional settings with values
        if (isset($settings['excerpt_length']) && !empty($settings['excerpt_length'])) {
            $attributes[] = sprintf('excerpt_length="%s"', esc_attr($settings['excerpt_length']));
        }

        if (isset($settings['image_size']) && !empty($settings['image_size'])) {
            $attributes[] = sprintf('image_size="%s"', esc_attr($settings['image_size']));
        }

        if (isset($settings['image_height_options']) && !empty($settings['image_height_options'])) {
            $attributes[] = sprintf('image_height_options="%s"', esc_attr($settings['image_height_options']));
        }

        if (isset($settings['image_height']) && !empty($settings['image_height']) && $settings['image_height_options'] === 'fixed') {
            $attributes[] = sprintf('image_height="%s"', esc_attr($settings['image_height']));
        }

        // Handle color settings
        if (isset($settings['progress_bar_color']) && !empty($settings['progress_bar_color'])) {
            $attributes[] = sprintf('progress_bar_color="%s"', esc_attr($settings['progress_bar_color']));
        }

        if (isset($settings['tag_background_color']) && !empty($settings['tag_background_color'])) {
            $attributes[] = sprintf('tag_background_color="%s"', esc_attr($settings['tag_background_color']));
        }

        if (isset($settings['tag_text_color']) && !empty($settings['tag_text_color'])) {
            $attributes[] = sprintf('tag_text_color="%s"', esc_attr($settings['tag_text_color']));
        }

        if (isset($settings['donate_button_text_color']) && !empty($settings['donate_button_text_color'])) {
            $attributes[] = sprintf('donate_button_text_color="%s"', esc_attr($settings['donate_button_text_color']));
        }

        $shortcode = '[give_form_grid ' . implode(' ', $attributes) . ']';

        echo do_shortcode($shortcode);
    }
}
