<?php

namespace Give\Promotions\Campaigns;

namespace Give\Promotions\Campaigns;

use Give\Vendors\StellarWP\AdminNotices\AdminNotice;
use Give\Vendors\StellarWP\AdminNotices\AdminNotices;
use Give\Vendors\StellarWP\AdminNotices\DataTransferObjects\NoticeElementProperties;

/**
 * @unreleased
 */
class CampaignWelcomeBanner
{
    /**
     * @var string
     */
    public static $id = 'givewp-campaigns-welcome-banner-2025';

    /**
     * @unreleased
     */
    public static function render()
    {
        AdminNotices::show(self::$id, [self::class, 'renderCallback'])
            ->custom()
            ->enqueueStylesheet(GIVE_PLUGIN_URL . 'build/campaignWelcomeBannerCss.css', [], '1.0.0')
            ->on('plugins.php');
    }

    /**
     * @unreleased
     */
    public static function renderCallback(AdminNotice $notice, NoticeElementProperties $elements): string
    {
        $backgroundUrl = GIVE_PLUGIN_URL . 'assets/src/images/admin/promotions/campaigns/welcome-banner-background.svg';
        $badgeIconUrl = GIVE_PLUGIN_URL . 'assets/src/images/admin/promotions/campaigns/star-badge-icon.svg';
        $heartIconUrl = GIVE_PLUGIN_URL . 'assets/src/images/admin/promotions/campaigns/heart-icon.svg';
        $exitIconUrl = GIVE_PLUGIN_URL . 'assets/src/images/admin/promotions/campaigns/dismiss-icon.svg';

        return "
            <div style='position: relative; background: url(\"$backgroundUrl\") no-repeat right; background-size: cover; height: 354px; padding: 28px 56px; box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05); border-radius: 8px;'>
               <div class='givewp-campaign-welcome-banner'>
                <div class='givewp-campaign-welcome-banner__actions'>
                    <div class='givewp-campaign-welcome-banner__actions__badge'><img src='$badgeIconUrl' alt='badge'/> NEW</div>
                    <button type='button' class='givewp-campaign-welcome-banner__actions__dismiss' {$elements->closeAttributes()}>
                    <img src='$exitIconUrl' alt='heart'>
                    </button>
                </div>
                <h2 class='givewp-campaign-welcome-banner__title'>Introducing Campaigns in Give 4.0 ! 🎉</h2>
                <p class='givewp-campaign-welcome-banner__description'>Say hello to a whole new way to supercharge your fundraising! 🚀</p>
                <div  class='givewp-campaign-welcome-banner__features'>
                    <div class='givewp-campaign-welcome-banner__features__group'>
                         <div class='givewp-campaign-welcome-banner__features__group__item'>
                            <img src='$heartIconUrl' alt='heart'>
                            <span>Easily create, manage, and level up your campaigns.</span>
                         </div>
                         <div class='givewp-campaign-welcome-banner__features__group__item'>
                            <img src='$heartIconUrl' alt='heart'>
                            <span>Enjoy tailored reports—all in one awesome place!</span>
                         </div>
                    </div>

                    <div class='givewp-campaign-welcome-banner__features__group'>
                         <div class='givewp-campaign-welcome-banner__features__group__item'>
                            <img src='$heartIconUrl' alt='heart'>
                            <span>Add as many donation forms as you want.</span>
                         </div>
                        <div class='givewp-campaign-welcome-banner__features__group__item'>
                            <img src='$heartIconUrl' alt='heart'>
                            <span>Update your campaigns on the fly.</span>
                        </div>
                    </div>
                </div>
                <a class='givewp-campaign-welcome-banner__cta-button'>
                    <span class='givewp-campaign-welcome-banner'>Explore campaigns</span>
                </a>
              </div>
            </div>
    ";
    }
}
