<?php

namespace Give\Campaigns\Actions;

use Give\Campaigns\Models\Campaign;
use Give\DonationForms\Models\DonationForm;

/**
 * @unreleased
 */
class PreventAddFormWithoutCampaign
{
    /**
     * @unreleased
     */
    public function __invoke()
    {
        if ( ! $this->isAddingNewForm()) {
            return;
        }

        if ($this->isEditingForm()) {
            return;
        }

        if ( ! isset($_GET['campaignId']) || ! (Campaign::find(absint($_GET['campaignId'])))) {
            wp_safe_redirect(admin_url('edit.php?post_type=give_forms&page=give-campaigns&new=campaign'));
            exit;
        }
    }

    /**
     * @unreleased
     */
    private function isAddingNewForm(): bool
    {
        global $pagenow;
        $isOptionBasedFormEditorPage = $pagenow === 'post-new.php';
        $isVisualFormBuilderPage = $pagenow === 'edit.php' && isset($_GET['page']) && 'givewp-form-builder' === $_GET['page'];
        $isGiveFormsCpt = isset($_GET['post_type']) && $_GET['post_type'] === 'give_forms';

        return ($isOptionBasedFormEditorPage || $isVisualFormBuilderPage) && $isGiveFormsCpt;
    }

    /**
     * @unreleased
     */
    private function isEditingForm(): bool
    {
        global $pagenow;

        $formId = $pagenow === 'post.php' && isset($_GET['post']) ? absint($_GET['post']) : 0;
        $formId = $pagenow === 'edit.php' && isset($_GET['donationFormID'], $_GET['page']) && 'givewp-form-builder' === $_GET['page'] ? absint($_GET['donationFormID']) : $formId;
        $isGiveFormsCpt = (isset($_GET['post_type']) && $_GET['post_type'] === 'give_forms') || (get_post_type($formId) === 'give_forms');

        if ($formId && $isGiveFormsCpt) {
            return (bool)DonationForm::find($formId);
        }

        return false;
    }
}
