<?php

namespace Give\Tracking\Events;

use Give\Tracking\Contracts\TrackEvent;
use Give\Tracking\Enum\EventType;
use Give\Tracking\TrackingData\CampaignsData;
use Give\Tracking\TrackingData\DonationFormsData;
use Give\Tracking\TrackRegisterer;

/**
 * @since 4.3.2
 */
class CampaignsTracking extends TrackEvent
{
    /**
     * @since 4.3.2
     * @var string
     */
    protected $dataClassName = CampaignsData::class;

    /**
     * @since 4.3.2
     */
    public function __construct(TrackRegisterer $track)
    {
        $this->eventType = new EventType(EventType::CAMPAIGN_UPDATED);
        parent::__construct($track);
    }
}
