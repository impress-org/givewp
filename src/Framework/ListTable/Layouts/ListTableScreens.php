<?php

namespace Give\Framework\ListTable\Layouts;

use Give\Framework\Permissions\Facades\UserPermissions;
use Give\Framework\Permissions\UserPermission;

/**
 * The closed list of list table screens that can store a layout, and the permission each one needs.
 *
 * A screen uses the same permission as the admin page that shows it. The two embedded tables use the
 * permission of the details page that hosts them. The list is closed on purpose: there is no filter.
 *
 * @since TBD
 */
class ListTableScreens
{
    /**
     * @since TBD
     *
     * @return string[]
     */
    public function keys(): array
    {
        return array_keys($this->permissions());
    }

    /**
     * @since TBD
     */
    public function has(string $screen): bool
    {
        return array_key_exists($screen, $this->permissions());
    }

    /**
     * The capability a user needs to use the screen, or an empty string for an unknown screen.
     *
     * @since TBD
     */
    public function capability(string $screen): string
    {
        $permission = $this->permission($screen);

        return $permission ? $permission->viewCap() : '';
    }

    /**
     * @since TBD
     */
    public function currentUserCanView(string $screen): bool
    {
        if (!is_user_logged_in()) {
            return false;
        }

        $permission = $this->permission($screen);

        return $permission ? $permission->canView() : false;
    }

    /**
     * @since TBD
     *
     * @return string[]
     */
    public function viewableByCurrentUser(): array
    {
        return array_values(array_filter($this->keys(), [$this, 'currentUserCanView']));
    }

    /**
     * @since TBD
     */
    private function permission(string $screen): ?UserPermission
    {
        return $this->permissions()[$screen] ?? null;
    }

    /**
     * @since TBD
     *
     * @return array<string, UserPermission>
     */
    private function permissions(): array
    {
        return [
            'campaigns' => UserPermissions::campaigns(),
            'donation-forms' => UserPermissions::donationForms(),
            'campaign-forms' => UserPermissions::campaigns(),
            'donations' => UserPermissions::donations(),
            'donors' => UserPermissions::donors(),
            'subscriptions' => UserPermissions::subscriptions(),
            'event-tickets' => UserPermissions::events(),
            'subscription-donations' => UserPermissions::subscriptions(),
        ];
    }
}
