<?php

namespace Give\Donations\Repositories;

use Give\Donations\DataTransferObjects\DonationPostData;
use Give\Donations\Models\Donation;

class DonationRepository
{
    /**
     * Get Donation By ID
     *
     * @unreleased
     *
     * @param $donationId
     * @return Donation
     */
    public function getById($donationId)
    {
        $donationPost = get_post($donationId);

        return DonationPostData::fromPost($donationPost)->toDonation();
    }
}
