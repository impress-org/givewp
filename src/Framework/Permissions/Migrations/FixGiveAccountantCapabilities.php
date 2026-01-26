<?php

declare(strict_types=1);

namespace Give\Framework\Permissions\Migrations;

use Give\Framework\Migrations\Contracts\Migration;

/**
 * Fixes give_accountant role capabilities.
 *
 * The give_accountant role should only be able to view donation forms,
 * not edit them. This migration removes the incorrectly assigned edit_give_forms
 * capability from the give_accountant role and replaces it with view_give_forms.
 *
 * @since 4.14.0
 */
class FixGiveAccountantCapabilities extends Migration
{
    /**
     * @inheritdoc
     */
    public static function id(): string
    {
        return 'fix_give_accountant_capabilities';
    }

    /**
     * @inheritdoc
     */
    public static function title(): string
    {
        return 'Fix GiveWP Accountant role capabilities';
    }

    /**
     * @inheritdoc
     */
    public static function timestamp(): int
    {
        return strtotime('2026-01-12');
    }

    /**
     * @inheritdoc
     */
    public function run(): void
    {
        $this->fixRoleCapabilities();
        $this->fixUserCapabilities();
    }

    /**
     * Fix capabilities at the role level.
     *
     * @since 4.14.0
     */
    private function fixRoleCapabilities(): void
    {
        global $wp_roles;

        if (!class_exists('WP_Roles')) {
            return;
        }

        if (!isset($wp_roles)) {
            $wp_roles = new \WP_Roles();
        }

        if (!is_object($wp_roles)) {
            return;
        }

        // Remove edit_give_forms from give_accountant - they should only be able to view forms
        $wp_roles->remove_cap('give_accountant', 'edit_give_forms');

        // Ensure give_accountant has view_give_forms for viewing donation forms
        $wp_roles->add_cap('give_accountant', 'view_give_forms');
    }

    /**
     * Fix capabilities at the user level for all give_accountant users.
     *
     * This handles cases where edit_give_forms was manually granted to individual users.
     *
     * @since 4.14.0
     */
    private function fixUserCapabilities(): void
    {
        $giveAccountants = get_users([
            'role' => 'give_accountant',
            'fields' => 'ID',
        ]);

        foreach ($giveAccountants as $userId) {
            $user = get_userdata($userId);

            if (!$user) {
                continue;
            }

            // Remove edit_give_forms if it was individually granted
            if ($user->has_cap('edit_give_forms')) {
                $user->remove_cap('edit_give_forms');
            }

            // Ensure view_give_forms is present
            if (!$user->has_cap('view_give_forms')) {
                $user->add_cap('view_give_forms');
            }
        }
    }
}
