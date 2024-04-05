<?php

namespace Give\Donors\Actions;

use Exception;
use Give\Donors\Models\Donor;

/**
 * This class allows us to use the Donor model to update data from the donor profile admin legacy page without adding new code directly to the legacy codebase.
 *
 * @unreleased
 */
class UpdateAdminDonorDetails
{
    /**
     * @unreleased
     *
     * @throws Exception
     */
    public function __invoke(array $args, int $donorId)
    {
        $phone = $args['give_donor_phone_number'] ?? false;

        if ($phone) {
            $donor = Donor::find($donorId);
            $donor->phone = $phone;
            $donor->save();
        }
    }
}
