<?php

declare(strict_types=1);

namespace Give\Framework\Permissions\Migrations;

use Give\Framework\Migrations\Contracts\Migration;

/**
 * Fixes give_worker role capabilities to match documentation.
 *
 * The give_worker role should only be able to read donations (view_give_payments),
 * not edit them. This migration removes the incorrectly assigned edit_give_payments
 * capability from the give_worker role. It also adds view_give_forms for consistency.
 *
 * @since 4.14.0
 */
class FixGiveWorkerCapabilities extends Migration
{
    /**
     * @inheritdoc
     */
    public static function id(): string
    {
        return 'fix_give_worker_capabilities';
    }

    /**
     * @inheritdoc
     */
    public static function title(): string
    {
        return 'Fix GiveWP Worker role capabilities';
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

        // Remove edit_give_payments from give_worker - they should only be able to view payments
        $wp_roles->remove_cap('give_worker', 'edit_give_payments');

        // Ensure give_worker has view_give_payments for reading donations
        $wp_roles->add_cap('give_worker', 'view_give_payments');

        // Ensure give_worker has view_give_forms for consistency
        $wp_roles->add_cap('give_worker', 'view_give_forms');
    }

    /**
     * Fix capabilities at the user level for all give_worker users.
     *
     * This handles cases where edit_give_payments was manually granted to individual users.
     *
     * @since 4.14.0
     */
    private function fixUserCapabilities(): void
    {
        $giveWorkers = get_users([
            'role' => 'give_worker',
            'fields' => 'ID',
        ]);

        foreach ($giveWorkers as $userId) {
            $user = get_userdata($userId);

            if (!$user) {
                continue;
            }

            // Remove edit_give_payments if it was individually granted
            if ($user->has_cap('edit_give_payments')) {
                $user->remove_cap('edit_give_payments');
            }

            // Ensure view_give_payments is present
            if (!$user->has_cap('view_give_payments')) {
                $user->add_cap('view_give_payments');
            }

            // Ensure view_give_forms is present
            if (!$user->has_cap('view_give_forms')) {
                $user->add_cap('view_give_forms');
            }
        }
    }
}

