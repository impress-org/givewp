<?php

namespace Give\License\Repositories;

use Give\License\DataTransferObjects\License;
use Give\License\ValueObjects\LicenseOptionKeys;

/**
 * @unreleased
 */
class LicenseRepository
{
    /**
     * Check if we have stored licenses in the database.
     *
     * @unreleased
     */
    public function hasStoredLicenses(): bool
    {
        return !empty($this->getStoredLicenses());
    }

    /**
     * Gets the raw, stored licenses from the database.
     *
     * @unreleased
     */
    public function getStoredLicenses(): array
    {
        return (array)get_option(LicenseOptionKeys::LICENSES, []);
    }

    /**
     * Gets the stored licenses from the database and converts them to License objects.
     *
     * @unreleased
     * @return License[]
     */
    public function getLicenses(): array
    {
        if (!$this->hasStoredLicenses()) {
            return [];
        }

        $storedLicenses = $this->getStoredLicenses();
        $licenses = [];

        foreach ($storedLicenses as $license) {
            $licenses[] = License::fromData($license);
        }

        return $licenses;
    }

    /**
     * Returns only the active licenses.
     *
     * @unreleased
     */
    public function getActiveLicenses(): array
    {
        if (!$this->hasStoredLicenses()) {
            return [];
        }

        $licenses = $this->getLicenses();

        return array_filter($licenses, static function(License $license) {
            return $license->isActive;
        });
    }

    /**
     * Checks if we have any active licenses.
     *
     * @unreleased
     */
    public function hasActiveLicenses(): bool
    {
        if (!$this->hasStoredLicenses()) {
            return false;
        }

        $activeLicenses = $this->getActiveLicenses();

        return !empty($activeLicenses);
    }

    /**
     * The platform fee percentage is used by gateways to calculate a platform fee.
     *
     * @unreleased
     */
    public function getPlatformFeePercentage(): float
    {
        if (!$this->hasActiveLicenses()) {
            return 2.0;
        }

        if (!$this->hasStoredPlatformFeePercentage()) {
            return 0.0;
        }

        return $this->getStoredPlatformFeePercentage();
    }

    /**
     * Check if we have an available platform fee percentage set.
     * This can be used to determine if we can charge a platform fee.
     *
     * @unreleased
     */
    public function hasPlatformFeePercentage(): bool
    {
        return $this->getPlatformFeePercentage() > 0;
    }

    /**
     * The stored platform fee percentage comes from License Server API.
     * When licenses are activated and refreshed, this value is stored in the database.
     *
     * @unreleased
     */
    public function getStoredPlatformFeePercentage(): ?float
    {
        if (!get_option(LicenseOptionKeys::PLATFORM_FEE_PERCENTAGE)) {
            return null;
        }

        return (float)get_option(LicenseOptionKeys::PLATFORM_FEE_PERCENTAGE);
    }

    /**
     * Check if we have a stored platform fee percentage.
     *
     * @unreleased
     */
    public function hasStoredPlatformFeePercentage(): bool
    {
        return !is_null($this->getStoredPlatformFeePercentage());
    }

    /**
     * Find the lowest platform fee percentage from active licenses.
     * Since there can be multiple licenses, we need to find the lowest fee from all active licenses.
     * Once we have the lowest fee, we store it in the database for future use.
     *
     * @unreleased
     */
    public function findLowestPlatformFeePercentageFromActiveLicenses(): ?float
    {
        if (!$this->hasActiveLicenses()) {
            return null;
        }

        $fees = array_map(static function(License $license) {
            return $license->gatewayFee;

        }, $this->getActiveLicenses());

        if (empty($fees)) {
            return null;
        }

        return (float)min($fees);
    }
}
