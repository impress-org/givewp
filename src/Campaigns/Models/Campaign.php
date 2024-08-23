<?php

namespace Give\Campaigns\Models;

use Give\Framework\Models\Contracts\ModelCrud;
use Give\Framework\Models\Model;

class Campaign extends Model implements ModelCrud
{
    /**
     * @inheritdoc
     */
    protected $properties = [
        'title' => 'string',
    ];

    public static function find($id)
    {
        // TODO: Implement find() method.
    }

    /**
     * @unreleased
     */
    public static function create(array $attributes): Campaign
    {
        $campaign = new static($attributes);

        // TODO: Implement new campaign model persistence.

        return $campaign;
    }

    public function save()
    {
        // TODO: Implement save() method.
    }

    public function delete()
    {
        // TODO: Implement delete() method.
    }

    public static function query()
    {
        // TODO: Implement query() method.
    }

    public static function fromQueryBuilderObject($object)
    {
        // TODO: Implement fromQueryBuilderObject() method.
    }
}
