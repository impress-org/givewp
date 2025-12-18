<?php

namespace Give\Framework\Permissions;

/**
 * @unreleased
 */
class DonationFormPermissions extends UserPermission
{
    /**
     * @unreleased
     */
    public static function getType(): string
    {
        return 'give_form';
    }

    /**
     * Check if user can view/read (maps to edit capability).
     *
     * @unreleased
     */
    public function canView(): bool
    {
        return $this->canEdit();
    }

    /**
     * @unreleased
     */
    public function canViewPrivate(): bool
    {
        return $this->canEdit();
    }
}
