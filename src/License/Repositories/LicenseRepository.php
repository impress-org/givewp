<?php

namespace Give\License\Repositories;

use Give\License\DataTransferObjects\License;
use Give\License\ValueObjects\LicenseOptionKeys;

/**
 * @since 4.3.0
 */
class LicenseRepository
{
    /**
     * Check if we have stored licenses in the database.
     *
     * @since 4.3.0
     */
    public function hasStoredLicenses(): bool
    {
        return !empty($this->getStoredLicenses());
    }

    /**
     * Gets the raw, stored licenses from the database.
     *
     * @since 4.3.0
     */
    public function getStoredLicenses(): array
    {
        return (array)get_option(LicenseOptionKeys::LICENSES, []);
    }

    /**
     * Gets the stored licenses from the database and converts them to License objects.
     *
     * @since 4.3.0
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
     * @since 4.3.0
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
     * @since 4.3.0
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
     * @since 4.8.0 add grace period
     * @since 4.3.0
     */
    public function getPlatformFeePercentage(): float
    {
        if (!$this->hasActiveLicenses()) {

            if ($this->isLicenseInGracePeriod()) {
                return 0.0;
            }

            return 2.0;
        }

        if (!$this->hasStoredPlatformFeePercentage()) {
            return 0.0;
        }

        return $this->getStoredPlatformFeePercentage();
    }

    /**
     * Check if licenses are in the grace period.
     *
     * @since 4.8.0
     */
    private function isLicenseInGracePeriod(): bool
    {
        $currentTime = current_time('timestamp', true);
        $lastActiveDate = $this->getLastActiveLicenseDate();

        if ($lastActiveDate && $currentTime <= $lastActiveDate + $this->getGracePeriodInSeconds()) {
            return true;
        }

        return false;
    }

    /**
     * Returns the current timestamp if at least one active license is present.
     * This is used to track the moment a license was last confirmed active,
     *
     * @since 4.8.0
     */
    public function getCurrentActiveLicenseDate(): ?int
    {
        if (!$this->hasActiveLicenses()) {
            return null;
        }

        $currentTime = current_time('timestamp', true);

        return $currentTime;
    }

    /**
     * Retrieves the timestamp of when a license was last active.
     *
     * @since 4.8.0
     */
    public function getLastActiveLicenseDate(): ?int
    {
        $value = get_option(LicenseOptionKeys::LAST_ACTIVE_LICENSE_DATE);
        return $value ?: null;
    }

    /**
     * Grace period is 30 days after the last active license date with a 3 day buffer for the refresh system.
     *
     * @since 4.8.0
     */
    public function getGracePeriodInSeconds(): ?int
    {
        return 33 * DAY_IN_SECONDS;
    }

    /**
     * Check if we have an available platform fee percentage set.
     * This can be used to determine if we can charge a platform fee.
     *
     * @since 4.3.0
     */
    public function hasPlatformFeePercentage(): bool
    {
        return $this->getPlatformFeePercentage() > 0;
    }

    /**
     * The stored platform fee percentage comes from License Server API.
     * When licenses are activated and refreshed, this value is stored in the database.
     *
     * @since 4.3.0
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
     * @since 4.3.0
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
     * @since 4.3.0
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
