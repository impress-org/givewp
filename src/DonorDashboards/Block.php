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
     * @since 2.22.1 Add script for iframe onload event to activate gutenberg edit mode.
     *             Gutenberg block edit mode activates when focus set to block container.
     * @since 2.10.0
     **/
    public function renderCallback($attributes)
    {
        $output =  $this->donorDashboard->getOutput($attributes);

        if( defined( 'REST_REQUEST' ) && REST_REQUEST ) {
            $output = str_replace(
                'onload="',
                sprintf(
                    'onload="%s;',
                    'const iframe = this;this.contentWindow.document.addEventListener(\'click\', function(){iframe.closest(\'[data-block]\').focus({preventScroll: true});})'
                ),
                $output
            );
        }

        return $output;
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
