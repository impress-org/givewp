<?php

namespace Give\Promotions\InPluginUpsells;

use DateTimeImmutable;
use DateTimeZone;
use Exception;
use Give\Framework\Shims\Shim;
use Give\Helpers\EnqueueScript;

/**
 * @since 2.17.0
 */
class AddonsAdminPage
{
    protected $containerId = 'give-in-plugin-upsells';

    /**
     * Register menu item
     */
    public function register(): void
    {
        add_submenu_page(
            'edit.php?post_type=give_forms',
            esc_html__('GiveWP Add-ons', 'give'),
            esc_html__('Add-ons', 'give'),
            'manage_give_settings',
            'give-add-ons',
            [$this, 'render']
        );
    }

    /**
     * Load scripts
     */
    public function loadScripts(): void
    {
        $promotionalData = $this->isPromotionActive() ? $this->getPromotionalData() : [];

        $data = array_merge(
            (new AddonsRepository())->getAddons(),
            [
                'assetsUrl' => GIVE_PLUGIN_URL . 'assets/dist/',
                'containerId' => $this->containerId,
                'siteUrl' => site_url(),
                'siteName' => get_bloginfo('name'),
            ],
            ['promotionalData' => $promotionalData]
        );


        EnqueueScript::make('give-in-plugin-upsells-addons', 'assets/dist/js/admin-upsell-addons-page.js')
            ->loadInFooter()
            ->registerTranslations()
            ->registerLocalizeData('GiveAddons', $data)
            ->enqueue();

        wp_enqueue_style('givewp-admin-fonts');
    }

    /**
     * Render admin page
     */
    public function render(): void
    {
        echo '<svg style="display: none"><path id="give-in-plugin-upsells-checkmark" d="M5.595 11.373.72 6.498a.75.75 0 0 1 0-1.06l1.06-1.061a.75.75 0 0 1 1.061 0L6.125 7.66 13.159.627a.75.75 0 0 1 1.06 0l1.061 1.06a.75.75 0 0 1 0 1.061l-8.625 8.625a.75.75 0 0 1-1.06 0Z" fill="currentColor"/></svg>';
        echo "<div id=\"{$this->containerId}\"></div>";
    }

    /**
     * Helper function to determine if current page is Give Add-ons admin page
     *
     * @return bool
     */
    public static function isShowing(): bool
    {
        return isset($_GET['page']) && $_GET['page'] === 'give-add-ons';
    }

    /**
     * @unreleased
     */
    public function getPromotionalData(): array
    {
        return [
            'heading' => __('Take 40% off any GiveWP Pricing Plan through Giving Tuesday', 'give'),
            'description' => __('Increase your donations at the lowest price possible.', 'give'),
            'savingsPercentage' => 40,
            'startDate' => '2023-11-20 00:00',
            'endDate' => '2023-11-29 23:59',
        ];
    }

    /**
     * @unreleased
     */
    public function isPromotionActive(): bool
    {
        $promotionData = $this->getPromotionalData();
        // Get the current date and time
        $currentDateTime = current_datetime();

        // Check if the current date is within the promotion date range
        $giveWPWebsiteTimezone = new DateTimeZone('America/Los_Angeles');
        try {
            $isFuture = $currentDateTime < new DateTimeImmutable($promotionData['startDate'], $giveWPWebsiteTimezone);
            $isPast = $currentDateTime > new DateTimeImmutable($promotionData['endDate'], $giveWPWebsiteTimezone);
        } catch (Exception $exception) {
            // Handle any exceptions, if necessary
            return false;
        }

        return !$isFuture && !$isPast;
    }

}
