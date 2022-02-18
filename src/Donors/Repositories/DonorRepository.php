<?php

namespace Give\Donors\Repositories;

use Exception;
use Give\Donors\DataTransferObjects\DonorQueryData;
use Give\Donors\Models\Donor;
use Give\Framework\Database\DB;
use Give\Framework\Exceptions\Primitives\InvalidArgumentException;
use Give\Framework\Models\Traits\InteractsWithTime;
use Give\Log\Log;

class DonorRepository
{
    use InteractsWithTime;

    /**
     * @var string[]
     */
    private $requiredDonorProperties = [
        // TODO: name should be an accessor
        'name',
        'firstName',
        'lastName',
        'email',
    ];

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
        $this->validateDonor($donor);

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

    /**
     * @unreleased
     *
     * @param  Donor  $donor
     * @return Donor
     * @throws Exception
     */
    public function update(Donor $donor)
    {
        $this->validateDonor($donor);
        
        DB::query('START TRANSACTION');

        try {
            DB::table('give_donors')
                ->where('id', $donor->id)
                ->update([
                    'user_id' => $donor->userId,
                    'email' => $donor->email,
                    'name' => $donor->name
                ]);

            foreach ($this->getCoreDonorMeta($donor) as $metaKey => $metaValue) {
                DB::table('give_donormeta')
                    ->where('donor_id', $donor->id)
                    ->where('meta_key', $metaKey)
                    ->update([
                        'meta_value' => $metaValue,
                    ]);
            }
        } catch (Exception $exception) {
            DB::query('ROLLBACK');

            Log::error('Failed updating a donor');

            throw new $exception('Failed updating a donor');
        }

        DB::query('COMMIT');

        return $donor;
    }

    /**
     * @throws Exception
     */
    public function delete(Donor $donor)
    {
        DB::query('START TRANSACTION');

        try {
            DB::table('give_donors')
                ->where('id', $donor->id)
                ->delete();

            foreach ($this->getCoreDonorMeta($donor) as $metaKey => $metaValue) {
                DB::table('give_donormeta')
                    ->where('donor_id', $donor->id)
                    ->where('meta_key', $metaKey)
                    ->delete();
            }
        } catch (Exception $exception) {
            DB::query('ROLLBACK');

            Log::error('Failed deleting a donor');

            throw new $exception('Failed deleting a donor');
        }

        DB::query('COMMIT');

        return true;
    }

    /**
     * @unreleased
     *
     * @param  Donor  $donor
     * @return array
     */
    private function getCoreDonorMeta(Donor $donor)
    {
        return [
            '_give_donor_first_name' => $donor->firstName,
            '_give_donor_last_name' => $donor->lastName,
        ];
    }

    /**
     * @unreleased
     *
     * @param  Donor  $donor
     * @return void
     */
    private function validateDonor(Donor $donor)
    {
        foreach ($this->requiredDonorProperties as $key) {
            if (!isset($donor->$key)) {
                throw new InvalidArgumentException("'$key' is required.");
            }
        }
    }
}
