<?php

declare(strict_types=1);

namespace Give\Framework\Permissions\Migrations;

use Give\Framework\Migrations\Contracts\Migration;

/**
 * Adds new view capabilities to give_manager and administrator roles.
 *
 * This migration adds the new view_give_forms and view_give_payments capabilities that were added to get_core_caps() for existing installations.
 *
 * @since 4.14.0
 */
class AddViewCapabilitiesToAdminRoles extends Migration
{
    /**
     * @inheritdoc
     */
    public static function id(): string
    {
        return 'add_view_capabilities_to_admin_roles';
    }

    /**
     * @inheritdoc
     */
    public static function title(): string
    {
        return 'Add view capabilities to GiveWP Manager and Administrator roles';
    }

    /**
     * @inheritdoc
     */
    public static function timestamp(): int
    {
        return strtotime('2026-01-16');
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
     * Add view capabilities at the role level.
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

        // Add new view capabilities to give_manager
        $wp_roles->add_cap('give_manager', 'view_give_forms');
        $wp_roles->add_cap('give_manager', 'view_give_payments');

        // Add new view capabilities to administrator
        $wp_roles->add_cap('administrator', 'view_give_forms');
        $wp_roles->add_cap('administrator', 'view_give_payments');
    }

    /**
     * Add view capabilities at the user level for all give_manager and administrator users.
     *
     * @since 4.14.0
     */
    private function fixUserCapabilities(): void
    {
        $users = get_users([
            'role__in' => ['give_manager', 'administrator'],
            'fields' => 'ID',
        ]);

        foreach ($users as $userId) {
            $user = get_userdata($userId);

            if (!$user) {
                continue;
            }

            if (!$user->has_cap('view_give_forms')) {
                $user->add_cap('view_give_forms');
            }

            if (!$user->has_cap('view_give_payments')) {
                $user->add_cap('view_give_payments');
            }
        }
    }
}
