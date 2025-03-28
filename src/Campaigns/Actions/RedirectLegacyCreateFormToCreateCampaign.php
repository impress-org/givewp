<?php

namespace Give\Campaigns\Actions;

use Give\Campaigns\Models\Campaign;

/**
 * @unreleased
 */
class RedirectLegacyCreateFormToCreateCampaign
{
    /**
     * @unreleased
     */
    public function __invoke()
    {
        global $pagenow;

        if (
            $pagenow === 'post-new.php'
            && isset($_GET['post_type']) && $_GET['post_type'] === 'give_forms'
        ) {
            if (
                ! isset($_GET['campaignId'])
                || ! (Campaign::find(absint($_GET['campaignId'])))
            ) {
                wp_safe_redirect(admin_url('edit.php?post_type=give_forms&page=give-campaigns&new=campaign'));
                exit;
            }
        }

        if (
            $pagenow === 'edit.php'
            && isset($_GET['post_type']) && $_GET['post_type'] === 'give_forms'
            && isset($_GET['page']) && $_GET['page'] === 'givewp-form-builder'
        ) {
            if (defined('GIVE_P2P_VERSION') && isset($_GET['p2p'])) {
                if (
                    ! isset($_GET['donationFormID'])
                    || ! give(\GiveP2P\P2P\Repositories\CampaignRepository::class)->getByFormId($_GET['donationFormID'])
                ) {
                    wp_safe_redirect(admin_url('edit.php?post_type=give_forms&page=p2p-add-campaign'));
                    exit;
                }
            } else {
                if (
                    ! isset($_GET['donationFormID'])
                    || ! Campaign::findByFormId(absint($_GET['donationFormID']))
                ) {
                    wp_safe_redirect(admin_url('edit.php?post_type=give_forms&page=give-campaigns&new=campaign'));
                    exit;
                }
            }
        }
    }
}
