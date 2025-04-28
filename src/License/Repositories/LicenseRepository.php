<?php

namespace Give\License\Repositories;

use Give\License\DataTransferObjects\License;

class LicenseRepository
{
    /**
     * @unreleased
     */
    public function hasLicense(): bool
    {
        return !empty($this->getStoredLicenses());
    }

    /**
     * @unreleased
     */
    public function getStoredLicenses(): array
    {
        return (array)get_option('give_licenses', []);
    }

    /**
     * @unreleased
     */
    public function getLicense(): ?License
    {
        if (!$this->hasLicense()) {
            return null;
        }

        $data = current($this->getStoredLicenses());

        return License::fromData($data);
    }

    /**
     * @unreleased
     */
    public function isLicenseValid(): bool
    {
        $license = $this->getLicense();

        return $license->isValid ?? false;
    }

    /**
     * @unreleased
     */
    public function getGatewayFeePercentage(): float
    {
        $license = $this->getLicense();

        return $license->gatewayFee ?? 2.0;
    }
}
