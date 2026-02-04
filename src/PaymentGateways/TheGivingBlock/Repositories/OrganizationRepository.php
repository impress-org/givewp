<?php

namespace Give\PaymentGateways\TheGivingBlock\Repositories;

/**
 * @unreleased
 */
class OrganizationRepository
{
    /**
     * @unreleased
     */
    public static function isConnected(): bool
    {
        $isConnected = get_option('give_tgb_organization_connected', false);
        return !empty($isConnected);
    }

    /**
     * Whether there is any organization data stored (give_tgb_organization or connected flag).
     * Use this to decide if "Delete All" should be available.
     *
     * @unreleased
     */
    public static function hasStoredData(): bool
    {
        $organization = get_option('give_tgb_organization', []);
        $hasOrganization = is_array($organization) && !empty($organization);
        return self::isConnected() || $hasOrganization;
    }

    /**
     * @unreleased
     */
    public static function save(array $organizationData): void
    {
        $organizationId = $organizationData['id'] ?? null;
        if (!$organizationId) {
            throw new \InvalidArgumentException('Organization data must contain an ID');
        }

        update_option('give_tgb_organization_connected', true);
        update_option('give_tgb_organization', $organizationData);
    }

    /**
     * @unreleased
     */
    public static function disconnect(): void
    {
        delete_option('give_tgb_organization_connected');
    }

    /**
     * @unreleased
     */
    public static function delete(): void
    {
        delete_option('give_tgb_organization_connected');
        delete_option('give_tgb_organization');
    }

    /**
     * @unreleased
     */
    public static function update(array $organizationData): void
    {
        update_option('give_tgb_organization', $organizationData);
    }
}
