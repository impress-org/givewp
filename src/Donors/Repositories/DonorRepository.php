<?php

namespace Give\Donors\Repositories;

use Exception;
use Give\Donors\DataTransferObjects\DonorQueryData;
use Give\Donors\Models\Donor;
use Give\Framework\Database\DB;
use Give\Framework\Models\Traits\InteractsWithTime;
use Give\Log\Log;

class DonorRepository
{
    use InteractsWithTime;

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
        $donorObject = DB::table('give_donors')
            ->select('*')
            ->attachMeta('give_donormeta',
                'ID',
                'donor_id',
                ['_give_donor_first_name', 'firstName'],
                ['_give_donor_last_name', 'lastName']
            )
            ->where('id', $donorId)
            ->get();

        return DonorQueryData::fromObject($donorObject)->toDonor();
    }

    /**
     * @unreleased
     *
     * @param  Donor  $donor
     *
     * @return Donor
     * @throws Exception
     */
    public function insert(Donor $donor)
    {
        $date = $donor->createdAt ? $this->getFormattedDateTime(
            $donor->createdAt
        ) : $this->getCurrentFormattedDateForDatabase();

        DB::query('START TRANSACTION');

        try {
            DB::table('give_donors')
                ->insert([
                    'date_created' => $date,
                    'user_id' => isset($donor->userId) ? $donor->userId : 0,
                    'email' => $donor->email,
                    'name' => $donor->name
                ]);

            $donorId = DB::last_insert_id();

            foreach ($this->getCoreDonorMeta($donor) as $metaKey => $metaValue) {
                DB::table('give_donormeta')
                    ->insert([
                        'donor_id' => $donorId,
                        'meta_key' => $metaKey,
                        'meta_value' => $metaValue,
                    ]);
            }
        } catch (Exception $exception) {
            DB::query('ROLLBACK');

            Log::error('Failed creating a donor');

            throw new $exception('Failed creating a donor');
        }

        DB::query('COMMIT');

        return $this->getById($donorId);
    }

    private function getCoreDonorMeta($donor)
    {
        return [
            '_give_donor_first_name' => $donor->firstName,
            '_give_donor_last_name' => $donor->lastName,
        ];
    }
}
