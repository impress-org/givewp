<?php

namespace Give\Campaigns\Actions;

use Give\Campaigns\Models\Campaign;

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
        if ( ! $this->isAddingNewForm()) {
            return;
        }

        if ($this->isEditingCampaignForm()) {
            return;
        }

        if ( ! isset($_GET['campaignId']) || ! (Campaign::find(absint($_GET['campaignId'])))) {
            wp_safe_redirect(admin_url('edit.php?post_type=give_forms&page=give-campaigns&new=campaign'));
            exit;
        }
    }

    /**
     * @since 4.0.0
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
     * @since 4.0.0
     */
    private function isEditingCampaignForm(): bool
    {
        global $pagenow;

        $formId = $pagenow === 'post.php' && isset($_GET['post']) ? absint($_GET['post']) : 0;
        $formId = $pagenow === 'edit.php' && isset($_GET['donationFormID'], $_GET['page']) && 'givewp-form-builder' === $_GET['page'] ? absint($_GET['donationFormID']) : $formId;
        $isGiveFormsCpt = (isset($_GET['post_type']) && $_GET['post_type'] === 'give_forms') || (get_post_type($formId) === 'give_forms');

        if ($formId && $isGiveFormsCpt) {
            return (bool)Campaign::findByFormId($formId);
        }

        return false;
    }
}
