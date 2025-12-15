<?php

namespace Give\Framework\Permissions;

/**
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
