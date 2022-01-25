<?php

namespace Give\Donors\Repositories;

use Give\Donors\DataTransferObjects\DonorQueryData;
use Give\Donors\Models\Donor;
use Give\Framework\Database\DB;

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
        $query = DB::table(DB::prefix('give_donors'))
            ->select('*')
            ->where('id', $donorId)
            ->get();

        return DonorQueryData::fromObject($query)->toDonor();
    }
}
