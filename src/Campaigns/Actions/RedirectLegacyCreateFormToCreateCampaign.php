<?php

namespace Give\Campaigns\Actions;

use Give\Campaigns\Models\Campaign;
use Give\Framework\Database\DB;

/**
 * @since 4.0.0
 */
class RedirectLegacyCreateFormToCreateCampaign
{
    /**
     * @since 4.0.0
     */
    public function __invoke()
    {
        if ($this->isAddingNewForm()) {
            if ($this->isCampaignIdInvalidOrMissing()) {
                $this->redirectToNewCampaignPage();
            }

            return;
        }

        if ($this->isEditingCampaignForm()) {
            if ($this->isP2P()) {
                if ($this->isP2PCampaignFormIdInvalidOrMissing()) {
                    $this->redirectToP2PNewCampaignPage();
                }
            } elseif ($this->isCampaignFormIdInvalidOrMissing()) {
                $this->redirectToNewCampaignPage();
            }
        }
    }

    /**
     * @since 4.0.0
     */
    private function isAddingNewForm(): bool
    {
        return $GLOBALS['pagenow'] === 'post-new.php'
               && isset($_GET['post_type'])
               && $_GET['post_type'] === 'give_forms';
    }

    /**
     * @since 4.0.0
     */
    private function isEditingCampaignForm(): bool
    {
        return $GLOBALS['pagenow'] === 'edit.php'
               && isset($_GET['post_type'])
               && $_GET['post_type'] === 'give_forms'
               && isset($_GET['page']) && $_GET['page'] === 'givewp-form-builder';
    }

    /**
     * @since 4.0.0
     */
    private function isP2P(): bool
    {
        return isset($_GET['p2p']);
    }

    /**
     * @since 4.0.0
     */
    private function redirectToNewCampaignPage(): void
    {
        wp_safe_redirect(admin_url('edit.php?post_type=give_forms&page=give-campaigns&new=campaign'));
        exit;
    }

    /**
     * @since 4.0.0
     */
    private function redirectToP2PNewCampaignPage(): void
    {
        wp_safe_redirect(admin_url('edit.php?post_type=give_forms&page=p2p-add-campaign'));
        exit;
    }

    /**
     * @since 4.0.0
     */
    private function isCampaignIdInvalidOrMissing(): bool
    {
        return ! isset($_GET['campaignId']) || ! (Campaign::find(absint($_GET['campaignId'])));
    }

    /**
     * @since 4.14.2 updated logic to search the DB explicitly for P2P campaigns
     * @since 4.0.0
     */
    private function isP2PCampaignFormIdInvalidOrMissing(): bool
    {
        if (!isset($_GET['donationFormID'])) {
            return true;
        }

        $formId = absint($_GET['donationFormID']);

        // Check give_campaigns.form_id for P2P campaigns
        $campaign = DB::table('give_campaigns', 'c')
            ->select('c.id')
            ->innerJoin('give_p2p_campaigns', 'c.id', 'p2p.campaign_id', 'p2p')
            ->where('c.form_id', $formId)
            ->get();

        if ($campaign) {
            return false;
        }

        // Also check give_campaign_forms junction table for P2P campaigns
        // (migrated v3 forms are stored in the junction table)
        $campaignForm = DB::table('give_campaign_forms')
            ->where('form_id', $formId)
            ->get();

        if ($campaignForm) {
            $p2pCampaign = DB::table('give_p2p_campaigns')
                ->where('campaign_id', $campaignForm->campaign_id)
                ->get();

            return !$p2pCampaign;
        }

        return true;
    }

    /**
     * @since 4.14.2 Also check give_campaign_forms junction table for non-core campaigns (e.g., migrated P2P forms).
     * @since 4.0.0
     */
    private function isCampaignFormIdInvalidOrMissing(): bool
    {
        if (!isset($_GET['donationFormID'])) {
            return true;
        }

        $formId = absint($_GET['donationFormID']);

        // Check core campaigns first
        if (Campaign::findByFormId($formId)) {
            return false;
        }

        // Fallback: check give_campaign_forms junction table for non-core campaigns
        $campaignForm = DB::table('give_campaign_forms')
            ->where('form_id', $formId)
            ->get();

        return !$campaignForm;
    }
}
