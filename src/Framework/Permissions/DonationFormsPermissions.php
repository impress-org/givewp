<?php

namespace Give\Framework\Permissions;

class DonationFormsPermissions
{
    /**
     * @unreleased
     */
    public function can(string $capability): bool
    {
        switch ($capability) {
            case 'delete':
                $capability = 'delete_give_forms';
                break;
            case 'create':
            case 'update':
            case 'read':
            case 'edit':
                $capability = 'edit_give_forms';
                break;
        }

        return current_user_can($capability);
    }
}
