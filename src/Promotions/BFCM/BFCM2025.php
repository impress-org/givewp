<?php

namespace Give\Promotions\BFCM;

use Give\Framework\Views\View;
use Give\Vendors\StellarWP\AdminNotices\AdminNotice;
use Give\Vendors\StellarWP\AdminNotices\AdminNotices;
use Give\Vendors\StellarWP\AdminNotices\DataTransferObjects\NoticeElementProperties;

/**
 * @unreleased
 */
class BFCM2025
{
    /**
     * @var string
     */
    public $id = 'givewp-bfcm-2025';

    /**
     * @unreleased
     */
    public function __invoke()
    {
        $this->render();
    }

    /**
     * @unreleased
     */
    public function render()
    {
        AdminNotices::show($this->id, [$this, 'renderCallback'])
            ->custom()
            ->location('inline')
            ->enqueueStylesheet(GIVE_PLUGIN_URL . 'build/bfcm2025.css', [], '1.0.0')
            ->between('2022-11-24 00:00:00', '2025-12-04 23:59:59')
            ->on('plugins.php')
            ->on('give-campaigns')
            ->on('give-donors')
            ->on('give-payment-history')
            ->on('give-reports');
    }

    /**
     * @unreleased
     */
    public function renderCallback(AdminNotice $notice, NoticeElementProperties $elements): string
    {
        $backgroundLarge = GIVE_PLUGIN_URL . 'build/assets/dist/images/admin/promotions/bfcm-banner/2025/bfcm-background-lg.svg';
        $backgroundMedium = GIVE_PLUGIN_URL . 'build/assets/dist/images/admin/promotions/bfcm-banner/2025/bfcm-background-md.svg';
        $backgroundSmall = GIVE_PLUGIN_URL . 'build/assets/dist/images/admin/promotions/bfcm-banner/2025/bfcm-background-sm.svg';
        $cartIcon = GIVE_PLUGIN_URL . 'build/assets/dist/images/admin/promotions/bfcm-banner/2025/bfcm-cart-icon.svg';

        return View::load(
            'Promotions.BFCM2025',
            [
                'elements'         => $elements,
                'backgroundLarge'    => $backgroundLarge,
                'backgroundMedium' => $backgroundMedium,
                'backgroundSmall' => $backgroundSmall,
                'cartIcon'         => $cartIcon,
            ],
            false
        );
    }
}