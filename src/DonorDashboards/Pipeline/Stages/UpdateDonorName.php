<?php

namespace Give\DonorDashboards\Pipeline\Stages;

use Give\Donors\Models\Donor;

/**
 * @unreleased Use Donor model to update data used by webhooks addon to prevent multiple events creation
 *
 * @since      2.10.0
 */
class UpdateDonorName implements Stage
{
    /**
     * @var array
     */
    protected $data;

    /**
     * @var Donor
     */
    protected $donor;

    /**
     * @unreleased
     */
    public function __construct(Donor &$donor)
    {
        $this->donor = &$donor;
    }

    /**
     * @return mixed
     */
    public function __invoke($payload)
    {
        $this->data = $payload['data'];

        if ( ! empty($this->data['firstName']) && ! empty($this->data['lastName'])) {
            $firstName = $this->data['firstName'];
            $lastName = $this->data['lastName'];
            $this->donor->name = "{$firstName} {$lastName}";
            $this->donor->firstName = $firstName;
            $this->donor->lastName = $lastName;
        }

        return $payload;
    }
}
