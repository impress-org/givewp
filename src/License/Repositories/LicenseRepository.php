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
    public function hasLicenses(): bool
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
        if (!$this->hasLicenses()) {
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
    public function hasActiveLicense(): bool
    {
        if (!$this->hasLicenses()) {
            return false;
        }

        $licenses = $this->getLicenses();

        foreach ($licenses as $license) {
            if ($license->isValid) {
                return true;
            }
        }

        return false;
    }

    /**
     * @unreleased
     */
    public function getPlatformFeePercentage(): float
    {
        if (!$this->hasActiveLicense()) {
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
}
