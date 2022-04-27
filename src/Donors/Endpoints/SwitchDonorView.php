<?php

namespace Give\Donors\Endpoints;

use Give\Donations\Endpoints\SwitchDonationView;

class SwitchDonorView extends SwitchDonationView
{
    /**
     * @inheritDoc
     */
    protected $endpoint = 'admin/donors/view';

    /**
     * @inheritDoc
     */
    protected $slug = 'donors';
}
