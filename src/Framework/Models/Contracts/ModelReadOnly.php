<?php

namespace Give\Framework\Models\Contracts;

use Give\Framework\Models\Model;
use Give\Framework\Models\ModelQueryBuilder;

/**
 * @unreleased
 */
interface ModelReadOnly
{
    /**
     * @unreleased
     *
     * @param  int  $id
     * @return Model
     */
    public static function find($id);

    /**
     * @unreleased
     *
     * @return ModelQueryBuilder
     */
    public static function query();

    /**
     * @unreleased
     *
     * @param $object
     * @return Model
     */
    public static function fromQueryBuilderObject($object);
}
