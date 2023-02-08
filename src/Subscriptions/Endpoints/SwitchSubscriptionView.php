<?php

namespace Give\Subscriptions\Endpoints;

use Give\Donations\Endpoints\SwitchDonationView;

class SwitchSubscriptionView extends SwitchDonationView
{
    /**
     * @inheritDoc
     */
    protected $endpoint = 'admin/subscriptions/view';

    /**
     * @inheritDoc
     */
    protected $slug = 'subscriptions';
}
