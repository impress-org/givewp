<?php

namespace Give\Donors\ViewModels;

use Give\API\REST\V3\Routes\Donors\ValueObjects\DonorAnonymousMode;
use Give\Donors\Models\Donor;

/**
 * @unreleased
 */
class DonorViewModel
{
    private Donor $donor;
    private DonorAnonymousMode $anonymousMode;
    private bool $includeSensitiveData = false;

    /**
     * @unreleased
     */
    public function __construct(Donor $donor)
    {
        $this->donor = $donor;
    }

    /**
     * @unreleased
     */
    public function includeSensitiveData(bool $includeSensitiveData = true): DonorViewModel
    {
        $this->includeSensitiveData = $includeSensitiveData;

        return $this;
    }


    /**
     * @unreleased
     */
    public function anonymousMode(DonorAnonymousMode $mode): DonorViewModel
    {
        $this->anonymousMode = $mode;

        return $this;
    }

    /**
     * @unreleased
     */
    public function exports(): array
    {
        $data = $this->donor->toArray();

        if ( ! $this->includeSensitiveData) {
            $sensitiveData = [
                'userId',
                'email',
                'phone',
                'additionalEmails',
            ];

            foreach ($sensitiveData as $propertyName) {
                if (isset($data[$propertyName])) {
                    $data[$propertyName] = __('anonymous', 'give');
                }
            }
        }


        if ($this->anonymousMode->isRedacted() && $this->donor->isAnonymous()) {
            $sensitiveData = [
                'id',
                'name',
                'firstName',
                'lastName',
                'prefix',
            ];

            foreach ($sensitiveData as $propertyName) {
                if (isset($data[$propertyName])) {
                    $data[$propertyName] = __('anonymous', 'give');
                }
            }
        }

        return $data;
    }
}
