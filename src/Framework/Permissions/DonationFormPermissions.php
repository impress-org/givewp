<?php

namespace Give\Framework\Permissions;

/**
 * @since 4.14.0
 */
class DonationFormPermissions extends UserPermission
{
    /**
     * @since 4.14.0
     */
    public static function getType(): string
    {
        return 'give_form';
    }

    /**
     * @since 4.14.0
     */
    public function canViewPrivate(): bool
    {
        return $this->canEdit();
    }
}
