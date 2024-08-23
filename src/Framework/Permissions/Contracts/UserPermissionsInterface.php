<?php

namespace Give\Framework\Permissions\Contracts;

/**
 * @unreleased
 */
interface UserPermissionsInterface
{
     /**
     * @return string
     */
    public static function getType(): string;

    /**
     * @unreleased
     */
    public function can(string $capability): bool;
}
