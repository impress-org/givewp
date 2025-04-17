<?php

namespace Give\Promotions\Campaigns;

namespace Give\Promotions\Campaigns;

use Give\Framework\Views\View;
use Give\Vendors\StellarWP\AdminNotices\AdminNotice;
use Give\Vendors\StellarWP\AdminNotices\AdminNotices;
use Give\Vendors\StellarWP\AdminNotices\DataTransferObjects\NoticeElementProperties;

/**
 * @since 4.0.0
 */
class CampaignsWelcomeBanner
{
    /**
     * @var string
     */
    public $id = 'givewp-campaigns-welcome-banner-2025';

    /**
     * @since 4.0.0
     */
    public function __invoke()
    {
        $this->render();
    }

    /**
     * @since 4.0.0
     */
    public function render()
    {
        AdminNotices::show($this->id, [$this, 'renderCallback'])
            ->custom()
            ->location('below_header')
            ->dismissible()
            ->enqueueStylesheet(GIVE_PLUGIN_URL . 'build/campaignWelcomeBannerCss.css', [], '1.0.0')
            ->enqueueScript(GIVE_PLUGIN_URL . 'build/campaignWelcomeBannerJs.js', [], '1.0.0')
            ->on('plugins.php');
    }

    /**
     * @since 4.0.0
     */
    public function renderCallback(AdminNotice $notice, NoticeElementProperties $elements): string
    {
        $backgroundUrl = GIVE_PLUGIN_URL . 'build/assets/dist/images/admin/promotions/campaigns/welcome-banner-background.svg';
        $badgeIconUrl = GIVE_PLUGIN_URL . 'build/assets/dist/images/admin/promotions/campaigns/star-badge-icon.svg';
        $heartIconUrl = GIVE_PLUGIN_URL . 'build/assets/dist/images/admin/promotions/campaigns/heart-icon.svg';
        $exitIconUrl = GIVE_PLUGIN_URL . 'build/assets/dist/images/admin/promotions/campaigns/dismiss-icon.svg';
        $campaignsPageUrl = admin_url('admin.php?page=give-campaigns');

        return View::load(
            'Promotions.CampaignWelcomeBanner',
            [
                'elements'         => $elements,
                'backgroundUrl'    => $backgroundUrl,
                'badgeIconUrl'     => $badgeIconUrl,
                'heartIconUrl'     => $heartIconUrl,
                'exitIconUrl'      => $exitIconUrl,
                'campaignsPageUrl' => $campaignsPageUrl,
            ],
            false
        );
    }
}
