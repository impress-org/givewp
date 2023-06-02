<?php

namespace Give\DonorDashboards\Pipeline\Stages;

use Give\Donors\Models\Donor;

/**
 * @unreleased Use Donor model to update data used by webhooks addon to prevent multiple events creation
 * @since      2.10.0
 */
class UpdateDonorEmails implements Stage
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

        $this->donor->email = $this->data['primaryEmail'];
        $this->donor->additionalEmails = $this->data['additionalEmails'] ? $this->data['additionalEmails'] : [];

        return $payload;
    }
}
