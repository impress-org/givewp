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
     * @unreleased
     */
    public function getStoredLicenses(): array
    {
        return (array)get_option(LicenseOptionKeys::LICENSES, []);
    }

    /**
     * @unreleased
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

        if (is_null($this->getStoredPlatformFeeAmount())) {
            return 0.0;
        }

        return $this->getStoredPlatformFeeAmount();
    }

    /**
     * @unreleased
     */
    public function getStoredPlatformFeeAmount(): ?float
    {
        if (!get_option(LicenseOptionKeys::PLATFORM_FEE_PERCENTAGE)) {
            return null;
        }

        return (float)get_option(LicenseOptionKeys::PLATFORM_FEE_PERCENTAGE);
    }
}
