<?php

namespace Give\Framework\Permissions\Contracts;

/**
 * @since 4.14.0
 */
interface UserPermissionsInterface
{
    /**
     * Get the capability type (e.g., 'give_form', 'give_payment').
     *
     * @since 4.14.0
     */
    public static function getType(): string;

    /**
     * Check if user can create.
     *
     * @since 4.14.0
     */
    public function canCreate(): bool;

    /**
     * Check if user can view/read.
     *
     * @since 4.14.0
     */
    public function canView(): bool;

    /**
     * Check if user can edit.
     *
     * @since 4.14.0
     */
    public function canEdit(): bool;

    /**
     * Check if user can delete.
     *
     * @since 4.14.0
     */
    public function canDelete(): bool;
}
