<?php

namespace Give\DonationForms\Repositories;

use Give\Donations\ValueObjects\DonationMetaKeys;
use Give\Framework\Database\DB;

/**
 * @since 2.19.0
 */
class DonationFormsRepository
{
    /**
     * @param int $formId
     *
     * @return int
     */
    public function getFormDonationsCount(int $formId): int
    {
        return DB::table('posts')
            ->leftJoin('give_donationmeta', 'ID', 'donation_id')
            ->where('meta_key', DonationMetaKeys::FORM_ID)
            ->where('meta_value', $formId)
            ->count();
    }
}
