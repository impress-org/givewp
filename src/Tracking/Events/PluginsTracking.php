<?php

namespace Give\Tracking\Events;

use Give\Tracking\Contracts\TrackEvent;
use Give\Tracking\Enum\EventType;
use Give\Tracking\TrackingData\PluginsData;
use Give\Tracking\TrackRegisterer;

/**
 * Class PluginsTracking
 *
 * This class setup event to send tracked data request when active plugin list update.
 *
 * @package Give\Tracking\Events
 * @since 2.10.0
 */
class PluginsTracking extends TrackEvent
{
    /**
     * @var string
     */
    protected $dataClassName = PluginsData::class;

    /**
     * GivePluginSettingsTracking constructor.
     *
     * @param TrackRegisterer $track
     */
    public function __construct(TrackRegisterer $track)
    {
        $this->eventType = new EventType(EventType::PLUGIN_LIST_UPDATED);
        parent::__construct($track);
    }
}
