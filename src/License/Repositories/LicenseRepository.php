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
     * @unreleased
     */
    public function hasStoredPlatformFeePercentage(): bool
    {
        return !is_null($this->getStoredPlatformFeePercentage());
    }

    /**
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
