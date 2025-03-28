<?php

namespace Give\Campaigns\Actions;

use Give\Campaigns\Models\Campaign;
use Give\Campaigns\ValueObjects\CampaignType;
use Give\Framework\Database\DB;

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
     * @unreleased
     */
    private function isAddingNewForm(): bool
    {
        return $GLOBALS['pagenow'] === 'post-new.php'
               && isset($_GET['post_type'])
               && $_GET['post_type'] === 'give_forms';
    }

    /**
     * @unreleased
     */
    private function isEditingCampaignForm(): bool
    {
        return $GLOBALS['pagenow'] === 'edit.php'
               && isset($_GET['post_type'])
               && $_GET['post_type'] === 'give_forms'
               && isset($_GET['page']) && $_GET['page'] === 'givewp-form-builder';
    }

    /**
     * @unreleased
     */
    private function isP2P(): bool
    {
        return isset($_GET['p2p']);
    }

    /**
     * @unreleased
     */
    private function redirectToNewCampaignPage(): void
    {
        wp_safe_redirect(admin_url('edit.php?post_type=give_forms&page=give-campaigns&new=campaign'));
        exit;
    }

    /**
     * @unreleased
     */
    private function redirectToP2PNewCampaignPage(): void
    {
        wp_safe_redirect(admin_url('edit.php?post_type=give_forms&page=p2p-add-campaign'));
        exit;
    }

    /**
     * @unreleased
     */
    private function isCampaignIdInvalidOrMissing(): bool
    {
        return ! isset($_GET['campaignId']) || ! (Campaign::find(absint($_GET['campaignId'])));
    }

    /**
     * @unreleased
     */
    private function isP2PCampaignFormIdInvalidOrMissing(): bool
    {
        $form = DB::table('give_campaigns')
            ->where('form_id', $_GET['donationFormID'])
            ->where('campaign_type', CampaignType::CORE, '!=')
            ->get();

        return ! isset($_GET['donationFormID']) || ! $form;
    }

    /**
     * @unreleased
     */
    private function isCampaignFormIdInvalidOrMissing(): bool
    {
        return ! isset($_GET['donationFormID']) || ! Campaign::findByFormId(absint($_GET['donationFormID']));
    }
}
