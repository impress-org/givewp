<?php

namespace Give\DonationForms\Repositories;

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
            ->where('meta_key', '_give_payment_form_id')
            ->where('meta_value', $formId)
            ->count();
    }
}
