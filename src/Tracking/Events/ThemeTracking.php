<?php

namespace Give\Tracking\Events;

use Give\Tracking\Contracts\TrackEvent;
use Give\Tracking\Enum\EventType;
use Give\Tracking\TrackingData\ThemeData;
use Give\Tracking\TrackRegisterer;
use Give\Traits\HasWpTheme;
use WP_Upgrader;

/**
 * Class ThemeTracking
 *
 * This class setup event to send tracked data request when active theme changes.
 *
 * @package Give\Tracking\Admin\Events
 * @since 2.10.0
 */
class ThemeTracking extends TrackEvent
{
    use HasWpTheme;

    /**
     * @var string
     */
    protected $dataClassName = ThemeData::class;

    /**
     * GivePluginSettingsTracking constructor.
     *
     * @param TrackRegisterer $track
     */
    public function __construct(TrackRegisterer $track)
    {
        $this->eventType = new EventType(EventType::THEME_SWITCHED);
        parent::__construct($track);
    }

    /**
     * Theme update tracking handler.
     *
     * @since 2.10.0
     *
     * @param bool|WP_Upgrader $upgrader
     * @param array            $data
     */
    public function themeUpdateTrackingHandler($upgrader = false, $data = [])
    {
        // Return if it's not a WordPress core update.
        if ( ! $upgrader || ! isset($data['type']) || 'theme' !== $data['type']) {
            return;
        }

        foreach ($data['themes'] as $theme) {
            if (get_stylesheet() === $theme || get_template() === $theme || $this->isParentTheme($theme)) {
                $this->trackId = new EventType(EventType::THEME_UPDATED);
                $this->record();
            }
        }
    }
}
