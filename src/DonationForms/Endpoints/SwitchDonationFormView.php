<?php

namespace Give\DonationForms\Endpoints;

use Give\Donations\Endpoints\SwitchDonationView;

class SwitchDonationFormView extends SwitchDonationView
{
    /**
     * @inheritDoc
     */
    protected $endpoint = 'admin/forms/view';

    /**
     * @inheritDoc
     */
    protected $slug = 'donation_forms';
}
