<?php

namespace Give\Donors\Actions;

use Exception;
use Give\Donors\Models\Donor;

/**
 * This class allows us to use the Donor model to update data from the donor profile admin legacy page without adding new code directly to the legacy codebase.
 *
 * @since 3.7.0
 */
class UpdateAdminDonorDetails
{
    /**
     * @since 3.7.0
     *
     * @throws Exception
     */
    public function __invoke(array $args, int $donorId)
    {
        $donor = Donor::find($donorId);
        $donor->phone = $args['give_donor_phone_number'] ?? '';
        $donor->save();
    }
}
