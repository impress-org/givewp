<?php

namespace Give\Campaigns\Models;

use Give\Framework\Models\Model;
use Give\Framework\Models\ValueObjects\Relationship;

/**
 * @unreleased
 *
 * @property string $title
 */
class Campaign extends Model
{
    /**
     * @inheritdoc
     */
    protected $properties = [
        'title' => 'string',
    ];

    /**
     * @inheritdoc
     */
    protected $relationships = [
        'donationForm' => Relationship::HAS_MANY,
    ];

    /**
     * @unreleased
     */
    public static function create(array $attributes): Campaign
    {
        $campaign = new static($attributes);

        // Persist the campaign to the database.

        return $campaign;
    }
}
