<?php

namespace Give\Tracking\Contracts;

/**
 * Class TrackData
 *
 * This interface represents a Track Data collection
 *
 * @package Give\Tracking
 * @since 2.10.0
 */
interface TrackData
{

    /**
     * Returns the collection data.
     *
     * @since 2.10.0
     * @return array The collection data.
     */
    public function get();
}
