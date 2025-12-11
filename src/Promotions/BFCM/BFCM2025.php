<?php

namespace Give\Promotions\BFCM;

use DateTimeImmutable;
use Give\Framework\Views\View;
use Give\Vendors\StellarWP\AdminNotices\AdminNotice;
use Give\Vendors\StellarWP\AdminNotices\AdminNotices;
use Give\Vendors\StellarWP\AdminNotices\DataTransferObjects\NoticeElementProperties;

/**
 * @since 4.11.0
 */
class BFCM2025
{
    /**
     * @var string
     */
    public $id = 'givewp-bfcm-2025';

    /**
     * @var string
     */
    private const START_DATE = '2025-11-24 00:00:00';

    /**
     * @var string
     */
    private const END_DATE   = '2025-12-02 23:59:59';

    /**
     * @since 4.11.0
     */
    public function __invoke()
    {
        $this->render();
    }

    /**
     * @since 4.11.0
     */
    public function render()
    {
        [$start, $end] = $this->getDateRange();

        AdminNotices::show($this->id, [$this, 'renderCallback'])
            ->custom()
            ->location('inline')
            ->enqueueStylesheet(GIVE_PLUGIN_URL . 'build/bfcm2025.css', [], '1.0.0')
            ->enqueueScript(GIVE_PLUGIN_URL . 'build/bfcm2025.js', [], '1.0.0')
            ->between($start, $end)
            ->on('give-campaigns')
            ->on('give-donors')
            ->on('give-payment-history')
            ->on('give-settings')
            ->on('give-add-ons')
            ->on('give-reports');
    }

    /**
     * @since 4.11.0
     */
    private function getDateRange(): array
    {
        $timezone = wp_timezone();

        return [
            new DateTimeImmutable(self::START_DATE, $timezone),
            new DateTimeImmutable(self::END_DATE, $timezone),
        ];
    }

    /**
     * @since 4.11.0
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
