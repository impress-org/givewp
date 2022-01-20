<?php

namespace Give\Donors\Repositories;

use Give\Donors\DataTransferObjects\DonorObjectData;
use Give\Donors\Models\Donor;

class DonorRepository
{
    /**
     * Get Donor By ID
     *
     * @unreleased
     *
     * @param  int  $donorId
     * @return Donor
     */
    public function getById($donorId)
    {
        $donorObject = give()->donors->get($donorId);

        return DonorObjectData::fromObject($donorObject)->toDonor();
    }
}
