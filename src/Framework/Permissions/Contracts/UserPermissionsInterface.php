<?php

namespace Give\Framework\Permissions\Contracts;

/**
 * @unreleased
 */
interface UserPermissionsInterface
{
    /**
     * Get the capability type (e.g., 'give_form', 'give_payment').
     *
     * @unreleased
     */
    public static function getType(): string;

    /**
     * Check if user can create.
     *
     * @unreleased
     */
    public function canCreate(): bool;

    /**
     * Check if user can view/read.
     *
     * @unreleased
     */
    public function canView(): bool;

    /**
     * Check if user can edit.
     *
     * @unreleased
     */
    public function canEdit(): bool;

    /**
     * Check if user can delete.
     *
     * @unreleased
     */
    public function canDelete(): bool;
}
