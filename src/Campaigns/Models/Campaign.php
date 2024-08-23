<?php

namespace Give\Campaigns\Models;

use Give\Framework\Models\Contracts\ModelCrud;
use Give\Framework\Models\Model;

/**
 * @unreleased
 */
class Campaign extends Model implements ModelCrud
{
    /**
     * @unreleased
     * @inheritdoc
     */
    protected $properties = [
        'title' => 'string',
    ];

    /**
     * @unreleased
     * @phpstan-ignore return.missing
     */
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

    /**
     * @unreleased
     * @phpstan-ignore return.missing
     */
    public function save()
    {
        // TODO: Implement save() method.
    }

    /**
     * @unreleased
     * @phpstan-ignore return.missing
     */
    public function delete()
    {
        // TODO: Implement delete() method.
    }

    /**
     * @unreleased
     * @phpstan-ignore return.missing
     */
    public static function query()
    {
        // TODO: Implement query() method.
    }

    /**
     * @unreleased
     * @phpstan-ignore return.missing
     */
    public static function fromQueryBuilderObject($object)
    {
        // TODO: Implement fromQueryBuilderObject() method.
    }
}
