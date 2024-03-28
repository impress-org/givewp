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
        $donorModel = Donor::find($donorId);
        $donorModel->phone = $args['give_donor_phone_number--international-format'];
        $donorModel->save();
    }
}
