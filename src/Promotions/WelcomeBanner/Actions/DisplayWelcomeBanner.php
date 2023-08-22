<?php

namespace Give\Promotions\WelcomeBanner\Actions;

use Give\Framework\Views\View;

class DisplayWelcomeBanner
{
    protected $data = [
        'action' => 'givewp_next_gen_welcome_release_banner_dismiss',
        'nonce' => '',
    ];

    public function __construct()
    {
        $this->data['nonce'] = wp_create_nonce($this->data['action']);
    }

    public function __invoke()
    {
        wp_enqueue_style('givewp-design-system-foundation');
        echo View::load('Promotions/WelcomeBanner.welcome-banner', $this->data);
    }

    /**
     * @unreleased
     */
    public static function isShowing(): bool
    {
        global $pagenow;

        return $pagenow === 'plugins.php';
    }
}
