<?php

namespace Give\DonorDashboards;

use Give\DonorDashboards\App as DonorDashboard;

class Block
{

    protected $donorDashboard;

    public function __construct()
    {
        $this->donorDashboard = give(DonorDashboard::class);
    }

    /**
     * Registers Donor Dashboard block
     *
     * @since 2.10.0
     **/
    public function addBlock()
    {
        register_block_type(
            'give/donor-dashboard',
            [
                'render_callback' => [$this, 'renderCallback'],
                'attributes' => [
                    'align' => [
                        'type' => 'string',
                        'default' => 'wide',
                    ],
                    'accent_color' => [
                        'type' => 'string',
                        'default' => '#68bb6c',
                    ],
                ],
            ]
        );
    }

    /**
     * Returns Donor Profile block markup
     *
     * @since 2.10.0
     **/
    public function renderCallback($attributes)
    {
        return $this->donorDashboard->getOutput($attributes);
    }

    /**
     * Load Donor Profile frontend assets
     *
     * @since 2.10.0
     **/
    public function loadFrontendAssets()
    {
        if (has_block('give/donor-dashboard')) {
            return $this->donorDashboard->loadAssets();
        }
    }

    /**
     * Load Donor Profile block editor assets
     *
     * @since 2.10.0
     **/
    public function loadEditorAssets()
    {
        wp_enqueue_script(
            'give-donor-dashboards-block',
            GIVE_PLUGIN_URL . 'assets/dist/js/donor-dashboards-block.js',
            [],
            GIVE_VERSION,
            true
        );
    }
}
