<?php

namespace Give\Framework\Models\Contracts;

use Give\Framework\Models\Model;
use Give\Framework\Models\ModelQueryBuilder;

/**
 * @since 2.24.0
 */
interface ModelReadOnly
{
    /**
     * @since 2.24.0
     *
     * @param  int  $id
     * @return Model
     */
    public static function find($id);

    /**
     * @since 2.24.0
     *
     * @return ModelQueryBuilder
     */
    public static function query();

    /**
     * @since 2.24.0
     *
     * @param $object
     * @return Model
     */
    public static function fromQueryBuilderObject($object);
}
