<?php

namespace Give\Tracking\Events;

use Give\Tracking\Contracts\TrackEvent;
use Give\Tracking\Enum\EventType;
use Give\Tracking\TrackingData\CampaignsData;
use Give\Tracking\TrackingData\DonationFormsData;
use Give\Tracking\TrackRegisterer;

/**
 * @unreleased
 */
class CampaignsTracking extends TrackEvent
{
    /**
     * @unreleased
     * @var string
     */
    protected $dataClassName = CampaignsData::class;

    /**
     * @unreleased
     */
    public function __construct(TrackRegisterer $track)
    {
        $this->eventType = new EventType(EventType::CAMPAIGN_UPDATED);
        parent::__construct($track);
    }
}
