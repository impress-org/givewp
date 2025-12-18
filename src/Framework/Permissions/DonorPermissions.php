<?php

namespace Give\Framework\Permissions;

/**
 * Donor permissions extend DonationPermissions but use view_give_reports for viewing.
 *
 * This is because:
 * - give_worker should be able to view donations (view_give_payments)
 * - give_worker should NOT be able to view donors
 * - view_give_reports is assigned to admin, manager, and accountant but NOT worker
 *
 * @unreleased
 */
class DonorPermissions extends DonationPermissions
{
    /**
     * @unreleased
     */
    public function canView(): bool
    {
        if ($this->isAdmin()) {
            return true;
        }

        return current_user_can('view_give_reports');
    }

    /**
     * @unreleased
     */
    public function viewCap(): string
    {
        return 'view_give_reports';
    }
}
