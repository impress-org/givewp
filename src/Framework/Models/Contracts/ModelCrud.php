<?php

namespace Give\Framework\Models\Contracts;

use Give\Framework\Models\Model;
use Give\Framework\QueryBuilder\QueryBuilder;

/**
 * @unreleased
 */
interface ModelCrud
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
     * @param  array  $attributes
     * @return Model
     */
    public static function create(array $attributes);

    /**
     * @unreleased
     *
     * @return Model
     */
    public function save();

    /**
     * @unreleased
     *
     * @return bool
     */
    public function delete();

    /**
     * @unreleased
     *
     * @return QueryBuilder
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
