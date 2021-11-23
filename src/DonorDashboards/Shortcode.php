<?php

namespace Give\DonorDashboards;

use Give\DonorDashboards\App as DonorDashboard;

class Shortcode
{

    protected $donorDashboard;

    public function __construct()
    {
        $this->donorDashboard = give(DonorDashboard::class);
    }

    /**
     * Registers Donor Profile Shortcode
     *
     * @since 2.10.0
     **/
    public function addShortcode()
    {
        add_shortcode('give_donor_dashboard', [$this, 'renderCallback']);
    }

    /**
     * Load Donor Profile frontend assets
     *
     * @since 2.9.0
     **/
    public function loadFrontendAssets()
    {
        global $post;
        if (is_a($post, 'WP_Post') && has_shortcode($post->post_content, 'give_donor_dashboard')) {
            return $this->donorDashboard->loadAssets();
        }
    }

    /**
     * Returns Shortcode markup
     *
     * @since 2.10.0
     **/
    public function renderCallback($attributes)
    {
        $attributes = shortcode_atts(
            [
                'accent_color' => '#68bb6c',
            ],
            $attributes,
            'give_donor_dashboard'
        );

        return $this->donorDashboard->getOutput($attributes);
    }
}
