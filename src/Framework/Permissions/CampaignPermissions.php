<?php

namespace Give\Framework\Permissions;

/**
 * Campaigns do not have custom capabilities, so we use the same as donation forms.
 *
 * @unreleased
 */
class CampaignPermissions extends DonationFormPermissions
{

    /**
     * @unreleased
     */
    public function canViewPrivate(): bool
    {
        return $this->canEdit();
    }
}
