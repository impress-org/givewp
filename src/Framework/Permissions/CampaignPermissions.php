<?php

namespace Give\Framework\Permissions;

/**
 * Campaigns do not have custom capabilities, so we use the same as donation forms.
 *
 * @since 4.14.0
 */
class CampaignPermissions extends DonationFormPermissions
{

    /**
     * @since 4.14.0
     */
    public function canViewPrivate(): bool
    {
        return $this->canEdit();
    }
}
